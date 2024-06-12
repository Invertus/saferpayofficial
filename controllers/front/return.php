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
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Service\SaferPayOrderStatusService;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAssertion;
use Invertus\SaferPay\Processor\CheckoutProcessor;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAuthorization;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialReturnModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'return';

    /**
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $cartId = Tools::getValue('cartId');
        $secureKey = Tools::getValue('secureKey');
        $isBusinessLicence = (int) Tools::getValue(SaferPayConfig::IS_BUSINESS_LICENCE);
        $fieldToken = Tools::getValue('fieldToken');
        $moduleId = $this->module->id;
        $selectedCard = Tools::getValue('selectedCard');

        $cart = new Cart($cartId);

        if (!Validate::isLoadedObject($cart)) {
            $this->ajaxDie(json_encode([
                'error_type' => 'unknown_error',
                'error_text' => $this->module->l('An unknown error error occurred. Please contact support', self::FILENAME),
            ]));
        }

        if ($cart->secure_key !== $secureKey) {
            $this->ajaxDie(json_encode([
                'error_type' => 'unknown_error',
                'error_text' => $this->module->l('An unknown error error occurred. Please contact support', self::FILENAME),
            ]));
        }

        $lockResult = $this->applyLock(
            sprintf(
                '%s-%s',
                $cartId,
                $secureKey
            )
        );

        if (!$lockResult->isSuccessful()) {
            $lockExist = true;
            $timeStarted = time();

            while ($lockExist) {
                $currentTime = time();
                if ($timeStarted + 30 < $currentTime) {
                    break; // Exit the loop after 30 seconds
                }

                if ($this->lock->acquire()) {
                    $lockExist = false;
                }

                sleep(1); // Add a small delay to prevent tight loop
            }
        }

        $orderId = Order::getIdByCartId($cartId);

        if ($orderId) {
            $order = new Order($orderId);

            $saferPayAuthorizedStatus = (int) Configuration::get(\Invertus\SaferPay\Config\SaferPayConfig::SAFERPAY_PAYMENT_AUTHORIZED);
            $saferPayCapturedStatus = (int) Configuration::get(\Invertus\SaferPay\Config\SaferPayConfig::SAFERPAY_PAYMENT_COMPLETED);

            if ((int) $order->current_state === $saferPayAuthorizedStatus || (int) $order->current_state === $saferPayCapturedStatus) {
                Tools::redirect($this->context->link->getModuleLink(
                    $this->module->name,
                    $this->getSuccessControllerName($isBusinessLicence, $fieldToken),
                    [
                        'cartId' => $cartId,
                        'orderId' => $orderId,
                        'moduleId' => $moduleId,
                        'secureKey' => $secureKey,
                        'selectedCard' => $selectedCard,
                    ],
                    true
                ));
                exit; // Ensure the script stops after redirect
            }
        }

        if ($cart->orderExists()) {
            if (method_exists('Order', 'getIdByCartId')) {
                $orderId = Order::getIdByCartId($cartId);
            } else {
                // For PrestaShop 1.6 use the alternative method
                $orderId = Order::getOrderByCartId($cartId);
            }
        }

        try {
            if ($isBusinessLicence) {
                $response = $this->executeTransaction((int) $cartId, (int) $selectedCard);
            } else {
                $response = $this->executePaymentPageAssertion((int) $cartId, (int) $isBusinessLicence);
            }

            $checkoutData = CheckoutData::create(
                (int) $cartId,
                $response->getPaymentMeans()->getBrand()->getPaymentMethod(),
                (int) $isBusinessLicence
            );

            $checkoutData->setIsAuthorizedOrder(true);
            $checkoutData->setOrderStatus($response->getTransaction()->getStatus());

            /** @var CheckoutProcessor $checkoutProcessor **/
            $checkoutProcessor = $this->module->getService(CheckoutProcessor::class);
            $checkoutProcessor->run($checkoutData);

            if (method_exists('Order', 'getIdByCartId')) {
                $orderId = Order::getIdByCartId($cartId);
            } else {
                // For PrestaShop 1.6 use the alternative method
                $orderId = Order::getOrderByCartId($cartId);
            }

            $paymentBehaviourWithout3DS = (int) Configuration::get(SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D);

            /** @var SaferPayOrderStatusService $orderStatusService */
            $orderStatusService = $this->module->getService(SaferPayOrderStatusService::class);

            $order = new Order($orderId);

            if (
                (!$response->getLiability()->getLiabilityShift() &&
                    in_array($order->payment, SaferPayConfig::SUPPORTED_3DS_PAYMENT_METHODS) &&
                    $paymentBehaviourWithout3DS === SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D_CANCEL) ||
                $response->getTransaction()->getStatus() === SaferPayConfig::TRANSACTION_STATUS_CANCELED
            ) {
                $orderStatusService->cancel($order);

                $this->warning[] = $this->module->l('We couldn\'t authorize your payment. Please try again.', self::FILENAME);

                $this->redirectWithNotifications($this->context->link->getModuleLink(
                    $this->module->name,
                    ControllerName::FAIL,
                    [
                        'cartId' => $cartId,
                        'secureKey' => $secureKey,
                        'orderId' => $orderId,
                        'moduleId' => $moduleId,
                    ],
                    true
                ));
            }

            if ((int) Configuration::get(SaferPayConfig::PAYMENT_BEHAVIOR) === SaferPayConfig::DEFAULT_PAYMENT_BEHAVIOR_CAPTURE &&
                $response->getTransaction()->getStatus() !== TransactionStatus::CAPTURED
            ) {
                $orderStatusService->capture(new Order($orderId));
            }

            $this->releaseLock();

            Tools::redirect($this->context->link->getModuleLink(
                $this->module->name,
                $this->getSuccessControllerName($isBusinessLicence, $fieldToken),
                [
                    'cartId' => $cartId,
                    'orderId' => $orderId,
                    'moduleId' => $moduleId,
                    'secureKey' => $secureKey,
                    'selectedCard' => $selectedCard,
                ],
                true
            ));
        } catch (Exception $e) {
            $this->releaseLock();

            PrestaShopLogger::addLog(
                sprintf(
                    'Failed to assert transaction. Message: %s. File name: %s',
                    $e->getMessage(),
                    self::FILENAME
                )
            );

            Tools::redirect($this->context->link->getModuleLink(
                $this->module->name,
                'failValidation',
                [
                    'cartId' => $cartId,
                    'orderId' => $orderId,
                    'secureKey' => $secureKey,
                ],
                true
            ));
        }
    }

    private function getSuccessControllerName($isBusinessLicence, $fieldToken)
    {
        $successController = ControllerName::SUCCESS;

        if ($isBusinessLicence) {
            $successController = ControllerName::SUCCESS_IFRAME;
        }

        if ($fieldToken) {
            $successController = ControllerName::SUCCESS_HOSTED;
        }

        return $successController;
    }

    /**
     * @param int $orderId
     * @param int $selectedCard
     *
     * @return \Invertus\SaferPay\DTO\Response\Assert\AssertBody
     * @throws Exception
     */
    private function executeTransaction($orderId, $selectedCard)
    {
        /** @var SaferPayTransactionAuthorization $saferPayTransactionAuthorization */
        $saferPayTransactionAuthorization = $this->module->getService(SaferPayTransactionAuthorization::class);

        $response = $saferPayTransactionAuthorization->authorize(
            $orderId,
            $selectedCard === SaferPayConfig::CREDIT_CARD_OPTION_SAVE,
            $selectedCard
        );

        return $response;
    }

    /**
     * @param int $cartId
     * @param int $isBusinessLicence
     *
     * @return \Invertus\SaferPay\DTO\Response\Assert\AssertBody|null
     * @throws Exception
     */
    private function executePaymentPageAssertion($cartId, $isBusinessLicence)
    {

        /** @var SaferPayTransactionAssertion $transactionAssert */
        $transactionAssert = $this->module->getService(SaferPayTransactionAssertion::class);
        $assertionResponse = $transactionAssert->assert($cartId);

        return $assertionResponse;
    }
}
