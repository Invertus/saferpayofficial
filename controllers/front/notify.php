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
use Invertus\SaferPay\Core\Order\Action\UpdateOrderStatusAction;
use Invertus\SaferPay\Core\Order\Action\UpdateSaferPayOrderAction;
use Invertus\SaferPay\Core\Payment\DTO\CheckoutData;
use Invertus\SaferPay\Processor\CheckoutProcessor;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\SaferPayOrderStatusService;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAssertion;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialNotifyModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'notify';
    const SAFERPAY_ORDER_AUTHORIZE_ACTION = 'AUTHORIZE';

    /**
     * This code is being called by SaferPay by using NotifyUrl in InitializeRequest.
     * # WILL NOT work for local development, to AUTHORIZE payment this must be called manually. #
     * Example manual request: https://saferpay.demo.com/en/module/saferpayofficial/notify?success=1&cartId=12&orderId=12&secureKey=9366c61b59e918b2cd96ed0567c82e90
     */
    public function postProcess()
    {
        $cartId = Tools::getValue('cartId');
        $secureKey = Tools::getValue('secureKey');

        $cart = new Cart($cartId);

        if (!Validate::isLoadedObject($cart)) {
            $this->ajaxDie(json_encode([
                'error_type' => 'unknown_error',
                'error_text' => $this->module->l('An unknown error error occurred. Please contact support', self::FILENAME),
            ]));
        }

        if ($cart->secure_key !== $secureKey) {
            die($this->module->l('Error. Insecure cart', self::FILENAME));
        }

        $lockResult = $this->applyLock(sprintf(
            '%s-%s',
            $cartId,
            $secureKey
        ));

        if (!$lockResult->isSuccessful()) {
            die($this->module->l('Lock already exist', self::FILENAME));
        }

        if ($cart->orderExists()) {
            if (method_exists('Order', 'getIdByCartId')) {
                $orderId = Order::getIdByCartId($cartId);
            } else {
                // For PrestaShop 1.6 use the alternative method
                $orderId = Order::getOrderByCartId($cartId);
            }

            $order = new Order($orderId);

            $saferPayAuthorizedStatus = (int) Configuration::get(\Invertus\SaferPay\Config\SaferPayConfig::SAFERPAY_PAYMENT_AUTHORIZED);
            $saferPayCapturedStatus = (int) Configuration::get(\Invertus\SaferPay\Config\SaferPayConfig::SAFERPAY_PAYMENT_COMPLETED);

            if ((int) $order->current_state === $saferPayAuthorizedStatus || (int) $order->current_state === $saferPayCapturedStatus) {
                die($this->module->l('Order already created', self::FILENAME));
            }
        }

        try {
            $assertResponseBody = $this->assertTransaction($cartId);

            /** @var SaferPayOrderRepository $saferPayOrderRepository */
            $saferPayOrderRepository = $this->module->getService(SaferPayOrderRepository::class);
            $saferPayOrderId = $saferPayOrderRepository->getIdByCartId($cartId);

            /** @var UpdateSaferPayOrderAction $updateSaferPayOrderAction */
            $updateSaferPayOrderAction = $this->module->getService(UpdateSaferPayOrderAction::class);
            $updateSaferPayOrderAction->run(new SaferPayOrder($saferPayOrderId), self::SAFERPAY_ORDER_AUTHORIZE_ACTION);

            // If order does not exist but assertion is valid that means order authorized or captured.
            if (method_exists('Order', 'getIdByCartId')) {
                $orderId = Order::getIdByCartId($cartId);
            } else {
                // For PrestaShop 1.6 use the alternative method
                $orderId = Order::getOrderByCartId($cartId);
            }
            if (!$orderId) {
                /** @var CheckoutProcessor $checkoutProcessor **/
                $checkoutProcessor = $this->module->getService(CheckoutProcessor::class);
                $checkoutData = CheckoutData::create(
                    (int) $cart->id,
                    $assertResponseBody->getPaymentMeans()->getBrand()->getPaymentMethod(),
                    (int) Configuration::get(SaferPayConfig::IS_BUSINESS_LICENCE)
                );

                $checkoutData->setIsAuthorizedOrder(true);
                $checkoutData->setOrderStatus($assertResponseBody->getTransaction()->getStatus());

                $checkoutProcessor->run($checkoutData);

                if (method_exists('Order', 'getIdByCartId')) {
                    $orderId = Order::getIdByCartId($cartId);
                } else {
                    // For PrestaShop 1.6 or lower, use the alternative method
                    $orderId = Order::getOrderByCartId($cartId);
                }
            }

            $order = new Order($orderId);

            /** @var UpdateOrderStatusAction $updateOrderStatusAction **/
            $updateOrderStatusAction = $this->module->getService(UpdateOrderStatusAction::class);
            $updateOrderStatusAction->run((int) $orderId, (int) Configuration::get('SAFERPAY_PAYMENT_AUTHORIZED'));

            if (!$assertResponseBody->getLiability()->getLiabilityShift() &&
                in_array($order->payment, SaferPayConfig::SUPPORTED_3DS_PAYMENT_METHODS) &&
                (int) Configuration::get(SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D) === SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D_CANCEL
            ) {
                /** @var SaferPayOrderStatusService $orderStatusService */
                $orderStatusService = $this->module->getService(SaferPayOrderStatusService::class);
                $orderStatusService->cancel($order);

                die($this->module->l('Liability shift is false', self::FILENAME));
            }

            //NOTE to get latest information possible and not override new information.
            $order = new Order($orderId);

            if ((int) Configuration::get(SaferPayConfig::PAYMENT_BEHAVIOR) === SaferPayConfig::DEFAULT_PAYMENT_BEHAVIOR_CAPTURE &&
                $assertResponseBody->getTransaction()->getStatus() !== TransactionStatus::CAPTURED
            ) {
                /** @var SaferPayOrderStatusService $orderStatusService */
                $orderStatusService = $this->module->getService(SaferPayOrderStatusService::class);

                $orderStatusService->capture($order);
            }
        } catch (Exception $e) {
            $this->releaseLock();
            PrestaShopLogger::addLog(
                sprintf(
                    '%s has caught an error: %s',
                    __CLASS__,
                    $e->getMessage()
                ),
                1,
                null,
                null,
                null,
                true
            );
            die($this->module->l($e->getMessage(), self::FILENAME));
        }

        $this->releaseLock();
        die($this->module->l('Success', self::FILENAME));
    }

    private function assertTransaction($cartId) {
        /** @var SaferPayTransactionAssertion $transactionAssert */
        $transactionAssert = $this->module->getService(SaferPayTransactionAssertion::class);

        return $transactionAssert->assert($cartId);
    }

    protected function displayMaintenancePage()
    {
        return true;
    }
}
