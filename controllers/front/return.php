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
use Invertus\SaferPay\DTO\Response\Assert\AssertBody;
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\Service\SaferPayOrderStatusService;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAssertion;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAuthorization;
use Invertus\SaferPay\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialReturnModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'return';

    public function postProcess()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        $cartId = (int) Tools::getValue('cartId');
        $order = new Order($this->getOrderId($cartId));

        if (!$order->id) {
            return;
        }

        try {
            /** @var SaferPayTransactionAssertion $transactionAssert */
            $transactionAssert = $this->module->getService(SaferPayTransactionAssertion::class);
            $transactionResponse = $transactionAssert->assert($cartId, false);

            /** @var SaferPayOrderStatusService $orderStatusService */
            $orderStatusService = $this->module->getService(SaferPayOrderStatusService::class);
            if ($transactionResponse->getTransaction()->getStatus() === TransactionStatus::PENDING) {
                $orderStatusService->setPending($order);
            }
        } catch (SaferPayApiException $e) {
            $logger->debug(sprintf('%s - safe exception', self::FILE_NAME), [
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

            $this->ajaxDie(json_encode([
                'error_type' => 'unknown_error',
                'error_text' => $this->module->l('An unknown error error occurred. Please contact support', self::FILENAME),
            ]));
        }

        if ($cart->secure_key !== $secureKey) {
            $logger->error(sprintf('%s - Secure key does not match', self::FILE_NAME), [
                'context' => [
                    'cartId' => $cartId,
                ],
            ]);

            $this->ajaxDie(json_encode([
                'error_type' => 'unknown_error',
                'error_text' => $this->module->l('An unknown error error occurred. Please contact support', self::FILENAME),
            ]));
        }

        if ($cart->orderExists()) {
            if (method_exists('Order', 'getIdByCartId')) {
                $orderId = Order::getIdByCartId($cartId);
            } else {
                // For PrestaShop 1.6 use the alternative method
                $orderId = Order::getOrderByCartId($cartId);
            }

            $order = new Order($orderId);

            $saferPayAuthorizedStatus = (int) Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_AUTHORIZED);
            $saferPayCapturedStatus = (int) Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_COMPLETED);

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

        if (SaferPayConfig::isVersion17()) {
            $this->setTemplate(SaferPayConfig::SAFERPAY_TEMPLATE_LOCATION . '/front/saferpay_wait.tpl');
            return;
        }

        $this->setTemplate('saferpay_wait_16.tpl');
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
     * @param int $cartId
     *
     * @return bool|int
     */
    private function getOrderId($cartId)
    {
        if (method_exists('Order', 'getIdByCartId')) {
            return Order::getIdByCartId($cartId);
        }
        // For PrestaShop 1.6 use the alternative method
        return Order::getOrderByCartId($cartId);
    }
}
