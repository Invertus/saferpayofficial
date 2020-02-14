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

namespace Invertus\SaferPay\Service;

use Cart;
use Currency;
use Exception;
use Invertus\SaferPay\Api\Request\CancelService;
use Invertus\SaferPay\Api\Request\CaptureService;
use Invertus\SaferPay\Api\Request\RefundService;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\Request\CancelRequestObjectCreator;
use Invertus\SaferPay\Service\Request\CaptureRequestObjectCreator;
use Invertus\SaferPay\Service\Request\RefundRequestObjectCreator;
use Order;
use SaferPayAssert;
use SaferPayOrder;

class SaferPayOrderStatusService
{
    /**
     * @var CaptureService
     */
    private $captureService;
    /**
     * @var CaptureRequestObjectCreator
     */
    private $captureRequestObjectCreator;
    /**
     * @var SaferPayOrderRepository
     */
    private $orderRepository;
    /**
     * @var CancelService
     */
    private $cancelService;
    /**
     * @var CancelRequestObjectCreator
     */
    private $cancelRequestObjectCreator;
    /**
     * @var RefundService
     */
    private $refundService;
    /**
     * @var RefundRequestObjectCreator
     */
    private $requestObjectCreator;

    public function __construct(
        CaptureService $captureService,
        CaptureRequestObjectCreator $captureRequestObjectCreator,
        SaferPayOrderRepository $orderRepository,
        CancelService $cancelService,
        CancelRequestObjectCreator $cancelRequestObjectCreator,
        RefundService $refundService,
        RefundRequestObjectCreator $requestObjectCreator
    ) {
        $this->captureService = $captureService;
        $this->captureRequestObjectCreator = $captureRequestObjectCreator;
        $this->orderRepository = $orderRepository;
        $this->cancelService = $cancelService;
        $this->cancelRequestObjectCreator = $cancelRequestObjectCreator;
        $this->refundService = $refundService;
        $this->requestObjectCreator = $requestObjectCreator;
    }

    public function capture(Order $order, $refundedAmount = 0, $isRefund = false)
    {
        $saferPayOrderId = $this->orderRepository->getIdByOrderId($order->id);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);
        $cart = new Cart($order->id_cart);
        $transactionId = $saferPayOrder->transaction_id;
        $cartDetails = $cart->getSummaryDetails();
        $totalPrice = (int) ($cartDetails['total_price'] * SaferPayConfig::AMOUNT_MULTIPLIER_FOR_API);
        if ($isRefund) {
            $transactionId = $saferPayOrder->refund_id;
            $totalPrice = $refundedAmount;
        }
        $captureRequest = $this->captureRequestObjectCreator->create($cart, $transactionId, $totalPrice);
        try {
            $captureResponse = $this->captureService->capture($captureRequest);
        } catch (Exception $e) {
            throw new SaferPayApiException('Capture API failed', SaferPayApiException::CAPTURE);
        }
        $assertId = $this->orderRepository->getAssertIdBySaferPayOrderId($saferPayOrder->id);
        $saferPayAssert = new SaferPayAssert($assertId);
        if ($isRefund) {
            $saferPayAssert->refunded_amount += $refundedAmount;
            $saferPayAssert->update();
            if ((int) $saferPayAssert->refunded_amount === (int) $saferPayAssert->amount) {
                $saferPayOrder->refunded = 1;
                $saferPayOrder->update();
                $order->setCurrentState(_SAFERPAY_PAYMENT_REFUND_);
                $order->update();
            }

            return;
        }
        $order->setCurrentState(_SAFERPAY_PAYMENT_COMPLETED_);
        $order->update();
        $saferPayOrder->captured = 1;
        $saferPayOrder->update();
        $saferPayAssert->status = $captureResponse->Status;
        $saferPayAssert->update();
    }

    public function cancel(Order $order)
    {
        $saferPayOrderId = $this->orderRepository->getIdByOrderId($order->id);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);
        $cancelRequest = $this->cancelRequestObjectCreator->create($saferPayOrder->transaction_id);
        try {
            $this->cancelService->cancel($cancelRequest);
        } catch (Exception $e) {
            throw new SaferPayApiException('Cancel API failed', SaferPayApiException::CANCEL);
        }
        $order->setCurrentState(_SAFERPAY_PAYMENT_CANCELED_);
        $order->update();
        $saferPayOrder->canceled = 1;
        $saferPayOrder->update();
    }

    public function refund(Order $order, $refundedAmount)
    {
        $saferPayOrderId = $this->orderRepository->getIdByOrderId($order->id);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);

        $assertId = $this->orderRepository->getAssertIdBySaferPayOrderId($saferPayOrder->id);
        $saferPayAssert = new SaferPayAssert($assertId);

        $refundAmount = (int) ($refundedAmount * SaferPayConfig::AMOUNT_MULTIPLIER_FOR_API);

        $isRefundValid = ($saferPayAssert->amount >= $saferPayAssert->refunded_amount + $refundAmount);
        if (!$isRefundValid) {
            throw new SaferPayApiException(SaferPayApiException::REFUND);
        }

        $currency = new Currency($order->id_currency);
        $refundRequest = $this->requestObjectCreator->create(
            $saferPayOrder->transaction_id,
            $refundAmount,
            $currency->iso_code
        );

        try {
            $refundResponse = $this->refundService->refund($refundRequest);
        } catch (Exception $e) {
            throw new SaferPayApiException('Refund API failed', SaferPayApiException::REFUND);
        }
        $saferPayOrder->refund_id = $refundResponse->Transaction->Id;
        $saferPayOrder->update();

        if ($refundResponse->Transaction->Status === SaferPayConfig::TRANSACTION_STATUS_AUTHORIZED) {
            $this->capture($order, $refundAmount, true);
        }

        if ($refundResponse->Transaction->Status === SaferPayConfig::TRANSACTION_STATUS_CAPTURED) {
            $saferPayAssert->refunded_amount += $refundAmount;
            $saferPayAssert->update();
            if ((int) $saferPayAssert->refunded_amount === (int) $saferPayAssert->amount) {
                $saferPayOrder->refunded = 1;
                $saferPayOrder->update();
                $order->setCurrentState(_SAFERPAY_PAYMENT_REFUND_);
                $order->update();
            }
        }
    }
}
