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

        if (!SaferPayConfig::isVersion17()) {
            if ($lockResult > 200) {
                die($this->module->l('Lock already exists', self::FILENAME));
            }
        } else {
            if (!$lockResult->isSuccessful()) {
                die($this->module->l('Lock already exists', self::FILENAME));
            }
        }

        if ($cart->orderExists()) {
            $order = new Order($this->getOrderId($cartId));
            $completed = (int) Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_COMPLETED);

            if ((int) $order->current_state === $completed) {
                die($this->module->l('Order already complete', self::FILENAME));
            }
        }

        /** @var SaferPayOrderRepository $saferPayOrderRepository */
        $saferPayOrderRepository = $this->module->getService(SaferPayOrderRepository::class);

        try {
            $assertResponseBody = $this->assertTransaction($cartId);
            $transactionStatus = $assertResponseBody->getTransaction()->getStatus();

            /** @var CheckoutProcessor $checkoutProcessor **/
            $checkoutProcessor = $this->module->getService(CheckoutProcessor::class);
            $checkoutData = CheckoutData::create(
                (int) $cart->id,
                $assertResponseBody->getPaymentMeans()->getBrand()->getPaymentMethod(),
                (int) Configuration::get(SaferPayConfig::IS_BUSINESS_LICENCE)
            );

            $checkoutData->setOrderStatus($transactionStatus);

            $orderId = $this->getOrderId($cartId);

            //TODO look into pipeline design pattern to use when object is modified in multiple places to avoid this issue.
            //NOTE must be left below assert action to get newest information.
            $order = new Order($orderId);

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
            }
        } catch (Exception $e) {
            // this might be executed after pending transaction is declined (e.g. with accountToAccount payment)
            if (!isset($order)) {
                $order = new Order($this->getOrderId($cartId));
            }

            $orderId = (int) $order->id;

            if ($orderId) {
                // assuming order transaction was declined
                $order->setCurrentState(_SAFERPAY_PAYMENT_AUTHORIZATION_FAILED_);
            }

            // using cartId, because ps order might not be assigned yet
            $saferPayOrder = new SaferPayOrder($saferPayOrderRepository->getIdByCartId($cartId));

            if ($saferPayOrder->id) {
                $saferPayOrder->authorized = false;
                $saferPayOrder->pending = false;
                $saferPayOrder->canceled = true;

                if ($orderId) {
                    // assign ps order to saferpay order id in case it was not assigned previously
                    $saferPayOrder->id_order = $orderId;
                }

                $saferPayOrder->update();
                $this->releaseLock();
                die('canceled');
            }

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
            $this->releaseLock();

            die($this->module->l($e->getMessage(), self::FILENAME));
        }

        die($this->module->l('Success', self::FILENAME));
    }

    private function assertTransaction($cartId) {
        /** @var SaferPayTransactionAssertion $transactionAssert */
        $transactionAssert = $this->module->getService(SaferPayTransactionAssertion::class);

        return $transactionAssert->assert($cartId);
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
        } else {
            // For PrestaShop 1.6 use the alternative method
            return Order::getOrderByCartId($cartId);
        }
    }

    protected function displayMaintenancePage()
    {
        return true;
    }
}
