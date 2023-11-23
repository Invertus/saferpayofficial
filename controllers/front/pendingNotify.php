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

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Controller\AbstractSaferPayController;
use Invertus\SaferPay\DTO\Response\AssertRefund\AssertRefundBody;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionRefundAssertion;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialPendingNotifyModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'pendingNotify';

    /**
     * This code is being called by SaferPay by using NotifyUrl in InitializeRequest.
     * # WILL NOT work for local development, to AUTHORIZE payment this must be called manually. #
     * Example manual request: https://saferpay.demo.com/en/module/saferpayofficial/notify?success=1&cartId=12&orderId=12&secureKey=9366c61b59e918b2cd96ed0567c82e90
     */
    public function postProcess()
    {
        $cartId = Tools::getValue('cartId');
        $orderId = Tools::getValue('orderId');
        $secureKey = Tools::getValue('secureKey');

        $cart = new Cart($cartId);
        if ($cart->secure_key !== $secureKey) {
            die($this->module->l('Error. Insecure cart', self::FILENAME));
        }
        /** @var SaferPayOrderRepository $saferPayOrderRepository */
        $saferPayOrderRepository = $this->module->getService(SaferPayOrderRepository::class);
        $saferPayOrderId = $saferPayOrderRepository->getIdByOrderId($orderId);

        $orderRefunds = $saferPayOrderRepository->getOrderRefunds($saferPayOrderId);
        foreach ($orderRefunds as $orderRefund) {
            if ($orderRefund['status'] === SaferPayConfig::TRANSACTION_STATUS_CAPTURED) {
                continue;
            }
            $assertRefundResponse = $this->assertRefundTransaction($orderRefund['transaction_id']);
            if ($assertRefundResponse->getStatus() === SaferPayConfig::TRANSACTION_STATUS_CAPTURED) {
                $this->handleCapturedRefund($orderRefund['id_saferpay_order_refund']);
            }
        }

        die($this->module->l('Success', self::FILENAME));
    }

    /**
     * @param string $transactionId
     *
     * @return AssertRefundBody
     * @throws Exception
     */
    private function assertRefundTransaction($transactionId)
    {
        /** @var SaferPayTransactionRefundAssertion $transactionAssertRefund */
        $transactionAssertRefund = $this->module->getService(SaferPayTransactionRefundAssertion::class);

        return $transactionAssertRefund->assertRefund($transactionId);
    }

    private function handleCapturedRefund($orderRefundId)
    {
        /** @var SaferPayOrderRepository $saferPayOrderRepository */
        $saferPayOrderRepository = $this->module->getService(SaferPayOrderRepository::class);

        $orderRefund = new SaferPayOrderRefund($orderRefundId);
        $orderRefund->status = SaferPayConfig::TRANSACTION_STATUS_CAPTURED;
        $orderRefund->update();

        $orderAssertId = $saferPayOrderRepository->getAssertIdBySaferPayOrderId($orderRefund->id_saferpay_order);
        $orderAssert = new SaferPayAssert($orderAssertId);
        $orderAssert->refunded_amount += $orderRefund->amount;
        $orderAssert->pending_refund_amount -= $orderRefund->amount;
        $orderAssert->update();

        $order = new Order($orderRefund->id_order);

        if ((int)$orderAssert->refunded_amount === (int)$orderAssert->amount) {
            $saferPayOrder = new SaferPayOrder($orderRefund->id_saferpay_order);
            $saferPayOrder->refunded = 1;
            $saferPayOrder->save();

            $order->setCurrentState(_SAFERPAY_PAYMENT_REFUND_);
            $order->update();
        } elseif ($order->getCurrentState() !== _SAFERPAY_PAYMENT_PARTLY_REFUND_) {
            $saferPayOrder = new SaferPayOrder($orderRefund->id_saferpay_order);
            $saferPayOrder->save();

            $order = new Order($orderRefund->id_order);
            $order->setCurrentState(_SAFERPAY_PAYMENT_PARTLY_REFUND_);
            $order->update();
        }
    }

    protected function displayMaintenancePage()
    {
        return true;
    }
}
