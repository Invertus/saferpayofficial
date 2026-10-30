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

namespace Invertus\SaferPay\Processor;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Cart;
use Invertus\SaferPay\Api\Enum\TransactionStatus;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Core\Payment\DTO\CheckoutData;
use Invertus\SaferPay\EntityBuilder\SaferPayOrderBuilder;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Exception\AuthorizedAmountMismatchException;
use Invertus\SaferPay\Exception\CouldNotProcessCheckout;
use Invertus\SaferPay\Factory\ModuleFactory;
use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\SaferPayInitialize;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayAuthorizedAmountGuard;
use Invertus\SaferPay\Utility\ExceptionUtility;
use Order;
use PrestaShopException;
use SaferPayOrder;

class CheckoutProcessor
{
    const FILE_NAME = 'CheckoutProcessor';

    /** @var \SaferPayOfficial */
    private $module;

    /** @var SaferPayOrderBuilder */
    private $saferPayOrderBuilder;

    /** @var SaferPayInitialize */
    private $saferPayInitialize;

    /** @var SaferPayOrderRepository */
    private $saferPayOrderRepository;

    /** @var SaferPayAuthorizedAmountGuard */
    private $authorizedAmountGuard;

    public function __construct(
        ModuleFactory $module,
        SaferPayOrderBuilder $saferPayOrderBuilder,
        SaferPayInitialize $saferPayInitialize,
        SaferPayOrderRepository $saferPayOrderRepository,
        SaferPayAuthorizedAmountGuard $authorizedAmountGuard
    ) {
        $this->module = $module->getModule();
        $this->saferPayOrderBuilder = $saferPayOrderBuilder;
        $this->saferPayInitialize = $saferPayInitialize;
        $this->saferPayOrderRepository = $saferPayOrderRepository;
        $this->authorizedAmountGuard = $authorizedAmountGuard;
    }

    public function run(CheckoutData $data)
    {
        $cart = new Cart($data->getCartId());

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        if (!$cart) {
            $logger->debug(sprintf('%s - Cart not found', self::FILE_NAME), [
                'context' => [
                    'cartId' => $data->getCartId(),
                ],
            ]);

            throw CouldNotProcessCheckout::failedToFindCart($data->getCartId());
        }

        if (!$data->getCreateAfterAuthorization()) {
            $this->processCreateOrder($cart, $data->getPaymentMethod());
        }

        $authorizedStates = [
            TransactionStatus::AUTHORIZED,
            TransactionStatus::CAPTURED,
        ];

        if (in_array($data->getOrderStatus(), $authorizedStates)) {
            $this->processAuthorizedOrder($data, $cart);
            return '';
        }

        try {
            $response = $this->processInitializePayment(
                $data->getPaymentMethod(),
                $data->getIsBusinessLicense(),
                $data->getSelectedCard(),
                $data->getFieldToken(),
                $data->getSuccessController(),
                $data->getIsWebhook()
            );
        } catch (\Exception $exception) {
            throw new SaferPayApiException('Failed to initialize payment API', SaferPayApiException::INITIALIZE);
        }

        try {
            $this->processCreateSaferPayOrder(
                $response,
                $cart->id,
                $cart->id_customer,
                $data->getIsTransaction()
            );
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage(), [
                'context' => [
                    'cartId' => $data->getCartId(),
                ],
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            throw CouldNotProcessCheckout::failedToCreateSaferPayOrder($data->getCartId());
        }

        return $response;
    }

    /**
     * @param Cart $cart
     * @param $paymentMethod
     * @param int|null $orderStateId defaults to the awaiting payment state
     * @param float|null $amountPaid defaults to the cart total
     * @return void
     * @throws PrestaShopException
     */
    private function processCreateOrder(Cart $cart, $paymentMethod, $orderStateId = null, $amountPaid = null)
    {
        /** @var \Invertus\SaferPay\Adapter\Cart $cartAdapter */
        $cartAdapter = $this->module->getService(\Invertus\SaferPay\Adapter\Cart::class);

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        // Notify and return webhooks triggers together leading into order created previously
        if ($cartAdapter->orderExists($cart->id)) {
            $logger->debug(sprintf('%s - Order already exists, returning', self::FILE_NAME), [
                'context' => [
                    'cartId' => $cart->id,
                ],
            ]);

            return;
        }

        $customer = new \Customer($cart->id_customer);

        $logger->debug(sprintf('%s - Creating order', self::FILE_NAME), [
            'context' => [
                'cartId' => $cart->id,
            ],
        ]);

        $this->module->validateOrder(
            $cart->id,
            $orderStateId ?: \Configuration::get(SaferPayConfig::SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT),
            $amountPaid !== null ? $amountPaid : (float) $cart->getOrderTotal(),
            $paymentMethod,
            null,
            [],
            null,
            false,
            $customer->secure_key
        );
    }

    /**
     * @param $paymentMethod
     * @param $isBusinessLicense
     * @param $selectedCard
     * @param $fieldToken
     * @param $successController
     * @return ?object
     */
    private function processInitializePayment(
        $paymentMethod,
        $isBusinessLicense,
        $selectedCard,
        $fieldToken,
        $successController,
        $isWebhook
    ) {
        $request = $this->saferPayInitialize->buildRequest(
            $paymentMethod,
            $isBusinessLicense,
            $selectedCard,
            $fieldToken,
            $successController,
            $isWebhook
        );

        return $this->saferPayInitialize->initialize($request, $isBusinessLicense);
    }

    /**
     * @param $initializeBody
     * @param $cartId
     * @param $customerId
     * @param $isTransaction
     * @return void
     */
    private function processCreateSaferPayOrder($initializeBody, $cartId, $customerId, $isTransaction)
    {
        $this->saferPayOrderBuilder->create(
            $initializeBody,
            $cartId,
            $customerId,
            $isTransaction
        );
    }

    private function processAuthorizedOrder(CheckoutData $data, Cart $cart)
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Processing authorized order', self::FILE_NAME), [
            'context' => [
                'id_cart' => $cart->id,
            ],
        ]);

        $guardAmount = $data->getCreateAfterAuthorization() && $data->getAuthorizedAmount() !== null;

        try {
            $this->createAuthorizedOrder($data, $cart, $guardAmount);

            $order = new Order(Order::getIdByCartId($cart->id));
            $saferPayOrder = new SaferPayOrder($this->saferPayOrderRepository->getIdByCartId($cart->id));

            if ($guardAmount && !$this->ordersMatchAuthorizedAmount($data, $order)) {
                $this->leaveOrdersForReview($data, $order, $saferPayOrder);

                throw AuthorizedAmountMismatchException::forCart($cart->id);
            }

            if (
                $order->getCurrentState() == _SAFERPAY_PAYMENT_AUTHORIZED_
                || $order->getCurrentState() == _SAFERPAY_PAYMENT_COMPLETED_
            ) {
                return;
            }

            if ($data->getOrderStatus() === TransactionStatus::AUTHORIZED) {
                if ($order->getCurrentState() == (int) _SAFERPAY_PAYMENT_AUTHORIZED_) {
                    return;
                }

                $saferPayOrder->authorized = true;
                $data->setIsAuthorizedOrder(true);
                $order->setCurrentState(_SAFERPAY_PAYMENT_AUTHORIZED_);
            } else {
                if ($order->getCurrentState() == _SAFERPAY_PAYMENT_COMPLETED_) {
                    return;
                }

                $saferPayOrder->captured = true;
                $logger->debug('Order set completed CheckoutProcessor.php');
                $order->setCurrentState(_SAFERPAY_PAYMENT_COMPLETED_);
            }

            $saferPayOrder->id_order = $order->id;
            $saferPayOrder->update();
        } catch (AuthorizedAmountMismatchException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->error($exception->getMessage(), [
                'context' => [],
                'cartId' => $data->getCartId(),
            ]);

            throw CouldNotProcessCheckout::failedToCreateOrder($data->getCartId());
        }
    }

    /**
     * With the "After authorization" rule the payment page was opened for the cart total at that time,
     * so a cart changed meanwhile creates the order straight in Payment error, without an order confirmation.
     *
     * @throws PrestaShopException
     */
    private function createAuthorizedOrder(CheckoutData $data, Cart $cart, $guardAmount)
    {
        if (!$guardAmount) {
            $this->processCreateOrder($cart, $data->getPaymentMethod());

            return;
        }

        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $currency = new \Currency($cart->id_currency);

        if ($this->authorizedAmountGuard->matchesTotal($data->getAuthorizedAmount(), $total, $currency->iso_code)) {
            $this->processCreateOrder($cart, $data->getPaymentMethod(), null, $total);

            return;
        }

        $this->processCreateOrder(
            $cart,
            $data->getPaymentMethod(),
            (int) \Configuration::get('PS_OS_ERROR'),
            $this->authorizedAmountGuard->toMajorUnits($data->getAuthorizedAmount())
        );
    }

    /**
     * Checked again on the created orders, a cart change can land between the check above and the order.
     */
    private function ordersMatchAuthorizedAmount(CheckoutData $data, Order $order)
    {
        $currency = new \Currency($order->id_currency);

        // Sums every order a cart split by carrier produced, they share one authorization.
        return $this->authorizedAmountGuard->matchesTotal(
            $data->getAuthorizedAmount(),
            (float) $order->getOrdersTotalPaid(),
            $currency->iso_code
        );
    }

    /**
     * The Saferpay flags follow what Saferpay really holds, so the order page keeps the matching
     * cancel, capture or refund actions for the merchant.
     *
     * @throws PrestaShopException
     */
    private function leaveOrdersForReview(CheckoutData $data, Order $order, SaferPayOrder $saferPayOrder)
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->error(sprintf('%s - Authorized amount does not match the order total', self::FILE_NAME), [
            'context' => [
                'id_cart' => $order->id_cart,
                'authorized_amount' => $data->getAuthorizedAmount()->getValue(),
                'authorized_currency' => $data->getAuthorizedAmount()->getCurrencyCode(),
                'total' => $order->getOrdersTotalPaid(),
            ],
        ]);

        $paymentErrorState = (int) \Configuration::get('PS_OS_ERROR');
        $orders = Order::getByReference($order->reference)->where('id_cart', '=', (int) $order->id_cart);

        /** @var Order $splitOrder */
        foreach ($orders as $splitOrder) {
            if ((int) $splitOrder->getCurrentState() !== $paymentErrorState) {
                $splitOrder->setCurrentState($paymentErrorState);
            }
        }

        $saferPayOrder->id_order = $order->id;
        $saferPayOrder->authorized = $data->getOrderStatus() === TransactionStatus::AUTHORIZED;
        $saferPayOrder->captured = $data->getOrderStatus() === TransactionStatus::CAPTURED;
        $saferPayOrder->update();
    }
}
