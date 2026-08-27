<?php
/**
 *NOTICE OF LICENSE
 *
 *This source file is subject to the Open Software License (OSL 3.0)
 *that is bundled with this package in the file LICENSE.txt.
 *It is also available through the world-wide-web at this URL:
 *http://opensource.org/licenses/osl-3.0.php
 *If you did not receive a copy of the license and are unable to
 *obtain it through the world-wide-web, please send an email
 *to license@prestashop.com so we can send you a copy immediately.
 *
 *DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *versions in the future. If you wish to customize PrestaShop for your
 *needs please refer to http://www.prestashop.com for more information.
 *
 *@author INVERTUS UAB www.invertus.eu  <support@invertus.eu>
 *@copyright SIX Payment Services
 *@license   SIX Payment Services
 */

use Invertus\SaferPay\Api\Enum\TransactionStatus;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Controller\AbstractSaferPayController;
use Invertus\SaferPay\Core\Payment\DTO\CheckoutData;
use Invertus\SaferPay\DTO\Response\Assert\AssertBody;
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Enum\PaymentType;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\Processor\CheckoutProcessor;
use Invertus\SaferPay\Provider\PaymentTypeProvider;
use Invertus\SaferPay\Repository\SaferPayFieldRepository;
use Invertus\SaferPay\Response\Response;
use Invertus\SaferPay\Service\CardAliasRegistrationGuard;
use Invertus\SaferPay\Service\SaferPayOrderStatusService;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAssertion;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAuthorization;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionProcessedGuard;
use Invertus\SaferPay\Utility\ExceptionUtility;
use Invertus\SaferPay\Adapter\Cart as CartAdapter;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialReturnModuleFrontController extends AbstractSaferPayController
{
    const FILE_NAME = 'return';

    public function postProcess()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        $cartId = (int) Tools::getValue('cartId');
        $order = new Order(Order::getIdByCartId($cartId));
        $secureKey = Tools::getValue('secureKey');
        $selectedCard = Tools::getValue('selectedCard');
        $paymentMethod = $order->id ? $order->payment : Tools::getValue('paymentMethod');
        $cart = new Cart($cartId);
        $failController = $this->getFailController($paymentMethod);

        if (!Validate::isLoadedObject($cart)) {
            $this->warning[] = $this->module->l('An unknown error error occurred. Please contact support', self::FILE_NAME);
            $this->redirectWithNotifications($this->getRedirectionToControllerUrl($failController));
        }

        if ($cart->secure_key !== $secureKey) {
            $this->warning[] = $this->module->l('Error. Insecure cart', self::FILE_NAME);
            $this->redirectWithNotifications($this->getRedirectionToControllerUrl($failController));
        }

        // Saferpay sends the redirect and the notification in parallel, and with a business licence
        // the assert below authorizes the transaction, which may only ever happen once. The same lock
        // key is used by the notify controller, so whichever arrives second waits out the first.
        $lockResult = $this->applyLock(sprintf('%s-%s', $cartId, $secureKey));

        // Only a conflict means the notification holds the lock. Any other failure is the locking
        // itself being unavailable, and the processed check below still guards the repeated assert.
        if ($lockResult->getStatusCode() === Response::HTTP_CONFLICT) {
            $logger->debug(sprintf('%s - Notification is already being processed, skipping assert', self::FILE_NAME));

            return;
        }

        /** @var SaferPayTransactionProcessedGuard $processedGuard */
        $processedGuard = $this->module->getService(SaferPayTransactionProcessedGuard::class);

        if ($processedGuard->isProcessed($cartId)) {
            $logger->debug(sprintf('%s - Payment already processed, skipping assert', self::FILE_NAME));

            return;
        }

        /** @var SaferPayTransactionAssertion $transactionAssert */
        $transactionAssert = $this->module->getService(SaferPayTransactionAssertion::class);

        /** @var CardAliasRegistrationGuard $aliasRegistrationGuard */
        $aliasRegistrationGuard = $this->module->getService(CardAliasRegistrationGuard::class);

        $assertResponseBody = null;
        $transactionStatus = null;

        try {
            $assertResponseBody = $transactionAssert->assert(
                $cartId,
                $aliasRegistrationGuard->shouldRegister($selectedCard),
                $selectedCard,
                (int) Tools::getValue(SaferPayConfig::IS_BUSINESS_LICENCE)
            );

            $transactionStatus = $assertResponseBody->getTransaction()->getStatus();
        } catch (Exception $e) {
            $logger->error($e->getMessage(), [
                'context' => [],
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            Tools::redirect($this->getRedirectionToControllerUrl($failController));
        }

        $orderPayment = $assertResponseBody->getPaymentMeans()->getBrand()->getPaymentMethod();

        if (!empty($assertResponseBody->getPaymentMeans()->getWallet())) {
            $orderPayment = $assertResponseBody->getPaymentMeans()->getWallet();
        }

        /** @var SaferPayFieldRepository $saferPayFieldRepository */
        $saferPayFieldRepository = $this->module->getService(SaferPayFieldRepository::class);

        /**
         * NOTE: This flow is for hosted & iframe payment method
         */

        /** @var PaymentTypeProvider $paymentTypeProvider */
        $paymentTypeProvider = $this->module->getService(PaymentTypeProvider::class);

        $paymentType = $paymentTypeProvider->getForReturn(
            $orderPayment,
            Tools::getValue('fieldToken'),
            (int) $selectedCard > 0
        );

        if ($paymentType === PaymentType::HOSTED_IFRAME) {
            $order = new Order(Order::getIdByCartId($cartId));

            try {
                if (!Tools::getValue('isWebhook')) {
                    $this->createAndValidateOrder($assertResponseBody, $transactionStatus, $cartId, $orderPayment);
                }
            } catch (Exception $e) {
                $logger->debug($e->getMessage(), [
                    'context' => [],
                    'exceptions' => ExceptionUtility::getExceptions($e),
                ]);

                $this->warning[] = $this->module->l('An error occurred. Please contact support', self::FILE_NAME);
                $this->redirectWithNotifications($this->getRedirectionToControllerUrl($failController));
            }
        }

        try {
            /** @var SaferPayOrderStatusService $orderStatusService */
            $orderStatusService = $this->module->getService(SaferPayOrderStatusService::class);
            if ($assertResponseBody->getTransaction()->getStatus() === TransactionStatus::PENDING) {
                $orderStatusService->setPending($order);
            }
        } catch (SaferPayApiException $e) {
            $logger->debug($e->getMessage(), [
                'context' => [],
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);
            // we only care if we have a response with pending status, else we skip further actions
        }

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));
    }

    /**
     * @throws PrestaShopException
     */
    public function initContent()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $cartId = Tools::getValue('cartId');
        $secureKey = Tools::getValue('secureKey');
        $isBusinessLicence = (int) Tools::getValue(SaferPayConfig::IS_BUSINESS_LICENCE);
        $fieldToken = Tools::getValue('fieldToken');
        $moduleId = $this->module->id;
        $selectedCard = Tools::getValue('selectedCard');
        $cart = new Cart($cartId);

        if (!Validate::isLoadedObject($cart)) {
            $logger->error(sprintf('%s - Cart not found', self::FILE_NAME), [
                'context' => [],
                'exceptions' => [],
            ]);

            $this->ajaxRender(json_encode([
                'error_type' => 'unknown_error',
                'error_text' => $this->module->l('An unknown error error occurred. Please contact support', self::FILE_NAME),
            ]));
        }

        if ($cart->secure_key !== $secureKey) {
            $logger->error(sprintf('%s - Secure key does not match', self::FILE_NAME), [
                'context' => [
                    'cartId' => $cartId,
                ],
            ]);

            $this->ajaxRender(json_encode([
                'error_type' => 'unknown_error',
                'error_text' => $this->module->l('An unknown error error occurred. Please contact support', self::FILE_NAME),
            ]));
        }

        /** @var CartAdapter $cartAdapter */
        $cartAdapter = $this->module->getService(Cart::class);

        if ($cartAdapter->orderExists($cartId)) {
            $order = new Order(Order::getIdByCartId($cartId));

            $saferPayAuthorizedStatus = (int) Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_AUTHORIZED);
            $saferPayCapturedStatus = (int) Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_COMPLETED);

            $usingSavedCard = $selectedCard > 0;

            if ((int) $order->current_state === $saferPayAuthorizedStatus || (int) $order->current_state === $saferPayCapturedStatus) {
                Tools::redirect($this->context->link->getModuleLink(
                    $this->module->name,
                    $this->getSuccessControllerName($isBusinessLicence, $fieldToken, $usingSavedCard),
                    [
                        'cartId' => $cartId,
                        'orderId' => $order->id,
                        'moduleId' => $moduleId,
                        'secureKey' => $secureKey,
                        'selectedCard' => $selectedCard,
                    ]
                ));
            }
        }

        $this->context->smarty->assign(
            'checkStatusEndpoint',
            $this->context->link->getModuleLink(
                $this->module->name,
                'ajax',
                [
                    'ajax' => 1,
                    'action' => 'getStatus',
                    'secureKey' => $secureKey,
                    'cartId' => $cartId,
                ],
                true
            )
        );

        $this->setTemplate(SaferPayConfig::SAFERPAY_TEMPLATE_LOCATION . '/front/saferpay_wait.tpl');
    }

    private function getSuccessControllerName($isBusinessLicence, $fieldToken, $usingSavedCard)
    {
        if ($fieldToken || $usingSavedCard) {
            return ControllerName::SUCCESS_HOSTED;
        }

        return ControllerName::SUCCESS;
    }

    /**
     * @param int $orderId
     * @param int $selectedCard
     *
     * @return AssertBody
     * @throws Exception
     */
    private function executeTransaction($orderId, $selectedCard)
    {
        /** @var SaferPayTransactionAuthorization $saferPayTransactionAuthorization */
        $saferPayTransactionAuthorization = $this->module->getService(SaferPayTransactionAuthorization::class);

        return $saferPayTransactionAuthorization->authorize(
            $orderId,
            $selectedCard === SaferPayConfig::CREDIT_CARD_OPTION_SAVE,
            $selectedCard
        );
    }

    /**
     * @param string $controllerName
     *
     * @return string
     */
    private function getRedirectionToControllerUrl($controllerName)
    {
        $cartId = (int) Tools::getValue('cartId') ?: (int) $this->context->cart->id;
        $cart = new Cart($cartId);
        $secureKey = Validate::isLoadedObject($cart) ? $cart->secure_key : $this->context->cart->secure_key;

        return $this->context->link->getModuleLink(
            $this->module->name,
            $controllerName,
            [
                'cartId' => $cartId,
                'orderId' => Order::getIdByCartId($cartId),
                'secureKey' => $secureKey,
                'moduleId' => $this->module->id,
            ]
        );
    }

    private function createAndValidateOrder($assertResponseBody, $transactionStatus, $cartId, $orderPayment)
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        if (SaferPayConfig::isRedirectPayment($orderPayment)) {
            $logger->debug('Redirect payment selected, skipping order creation', [
                'context' => [],
                'controller' => self::FILE_NAME,
                'order_payment' => $orderPayment,
            ]);
            return;
        }

        $logger->debug('Not redirect payment selected, creating order', [
            'context' => [],
            'controller' => self::FILE_NAME,
            'order_payment' => $orderPayment,
        ]);

        /** @var CheckoutProcessor $checkoutProcessor * */
        $checkoutProcessor = $this->module->getService(CheckoutProcessor::class);

        $checkoutData = CheckoutData::create(
            (int) $cartId,
            $assertResponseBody->getPaymentMeans()->getBrand()->getPaymentMethod(),
            (int) Configuration::get(SaferPayConfig::IS_BUSINESS_LICENCE)
        );
        $checkoutData->setOrderStatus($transactionStatus);

        /**
         * NOTE: This check is needed because ACCOUNTTOACCOUNT payment method
         * is always being created before initialize API request
         */
        if ($orderPayment !== SaferPayConfig::PAYMENT_ACCOUNTTOACCOUNT) {
            $checkoutProcessor->run($checkoutData);
        }

        $orderId = Order::getIdByCartId($cartId);

        $order = new Order($orderId);
        $paymentBehaviorWithout3D = (int) Configuration::get(SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D);

        if (!$assertResponseBody->getLiability()->getLiabilityShift() &&
            in_array($order->payment, SaferPayConfig::SUPPORTED_3DS_PAYMENT_METHODS)
        ) {
            /** @var SaferPayOrderStatusService $orderStatusService */
            $orderStatusService = $this->module->getService(SaferPayOrderStatusService::class);

            if ($paymentBehaviorWithout3D === SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D_CANCEL) {
                $orderStatusService->cancel($order);

                return;
            }

            if ($paymentBehaviorWithout3D === SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D_AUTHORIZE) {
                return;
            }

            if ($paymentBehaviorWithout3D === SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D_CAPTURE
                && SaferPayConfig::supportsOrderCapture($order->payment)
                && $transactionStatus !== TransactionStatus::CAPTURED
            ) {
                $orderStatusService->capture($order);

                return;
            }
        }

        //NOTE to get latest information possible and not override new information.

        $paymentMethod = $assertResponseBody->getPaymentMeans()->getBrand()->getPaymentMethod();

        // if payment does not support order capture, it means it always auto-captures it (at least with accountToAccount payment),
        // so in this case if status comes back "captured" we just update the order state accordingly
        if (!SaferPayConfig::supportsOrderCapture($paymentMethod) &&
            $transactionStatus === TransactionStatus::CAPTURED
        ) {
            /** @var SaferPayOrderStatusService $orderStatusService */
            $orderStatusService = $this->module->getService(SaferPayOrderStatusService::class);
            $orderStatusService->setComplete($order);

            return;
        }

        if (SaferPayConfig::supportsOrderCapture($paymentMethod) &&
            (int) Configuration::get(SaferPayConfig::PAYMENT_BEHAVIOR) === SaferPayConfig::DEFAULT_PAYMENT_BEHAVIOR_CAPTURE &&
            $transactionStatus !== TransactionStatus::CAPTURED
        ) {
            /** @var SaferPayOrderStatusService $orderStatusService */
            $orderStatusService = $this->module->getService(SaferPayOrderStatusService::class);
            $orderStatusService->capture($order);

            return;
        }
    }

    private function getFailController($orderPayment)
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug('Fail controller is FAIL', [
            'context' => [],
            'controller' => self::FILE_NAME,
            'order_payment' => $orderPayment,
        ]);

        return ControllerName::FAIL;
    }
}
