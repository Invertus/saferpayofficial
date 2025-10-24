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
use Invertus\SaferPay\Exception\CouldNotProcessCheckout;
use Invertus\SaferPay\Factory\ModuleFactory;
use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\SaferPayInitialize;
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

    public function __construct(
        ModuleFactory $module,
        SaferPayOrderBuilder $saferPayOrderBuilder,
        SaferPayInitialize $saferPayInitialize,
        SaferPayOrderRepository $saferPayOrderRepository
    ) {
        $this->module = $module->getModule();
        $this->saferPayOrderBuilder = $saferPayOrderBuilder;
        $this->saferPayInitialize = $saferPayInitialize;
        $this->saferPayOrderRepository = $saferPayOrderRepository;
    }

    /**
     * Process checkout flow for SaferPay payment
     *
     * This method orchestrates the entire checkout process including:
     * - Cart validation
     * - Order creation (if needed)
     * - Payment initialization
     * - SaferPay order entity creation
     *
     * @param CheckoutData $data - Checkout data containing cart, payment method, and configuration
     *
     * @return object|string - Payment initialization response or empty string for authorized orders
     *
     * @throws CouldNotProcessCheckout - If cart not found or SaferPay order creation fails
     * @throws SaferPayApiException - If payment initialization API call fails
     * @throws PrestaShopException - If PrestaShop order validation fails
     */
    public function run(CheckoutData $data)
    {
        $cart = new Cart($data->getCartId());

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        // Validate cart exists
        if (!$cart || empty($cart->id)) {
            $logger->error(sprintf('%s - Cart not found or invalid', self::FILE_NAME), [
                'context' => [
                    'cart_id' => $data->getCartId(),
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
        } catch (SaferPayApiException $exception) {
            // Log API exception with full context
            $logger->error(sprintf('%s - Payment initialization API call failed', self::FILE_NAME), [
                'context' => [
                    'cart_id' => $data->getCartId(),
                    'payment_method' => $data->getPaymentMethod(),
                    'is_business_license' => $data->getIsBusinessLicense(),
                ],
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);
            // Re-throw the original API exception with context preserved
            throw $exception;
        } catch (\Exception $exception) {
            // Log unexpected exception
            $logger->error(sprintf('%s - Unexpected error during payment initialization', self::FILE_NAME), [
                'context' => [
                    'cart_id' => $data->getCartId(),
                    'payment_method' => $data->getPaymentMethod(),
                ],
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);
            // Wrap in SaferPayApiException
            throw new SaferPayApiException(
                sprintf('Failed to initialize payment API: %s', $exception->getMessage()),
                SaferPayApiException::INITIALIZE
            );
        }

        try {
            $this->processCreateSaferPayOrder(
                $response,
                $cart->id,
                $cart->id_customer,
                $data->getIsTransaction()
            );
        } catch (CouldNotProcessCheckout $exception) {
            // Log checkout exception with full context
            $logger->error(sprintf('%s - %s', self::FILE_NAME, $exception->getMessage()), [
                'context' => array_merge(
                    [
                        'cart_id' => $data->getCartId(),
                        'customer_id' => $cart->id_customer,
                        'is_transaction' => $data->getIsTransaction(),
                    ],
                    $exception->getContext()
                ),
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);
            // Re-throw the domain exception
            throw $exception;
        } catch (\Exception $exception) {
            // Log unexpected exception with full details
            $logger->error(sprintf('%s - Unexpected error creating SaferPay order', self::FILE_NAME), [
                'context' => [
                    'cart_id' => $data->getCartId(),
                    'customer_id' => $cart->id_customer ?? null,
                    'is_transaction' => $data->getIsTransaction(),
                ],
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            throw CouldNotProcessCheckout::failedToCreateSaferPayOrder($data->getCartId());
        }

        return $response;
    }

    /**
     * Create PrestaShop order from cart if it doesn't exist
     *
     * This method handles the order creation process, including:
     * - Checking if order already exists (idempotency)
     * - Validating cart and customer
     * - Creating order with awaiting payment status
     *
     * @param Cart $cart - PrestaShop cart object
     * @param string $paymentMethod - Payment method name
     *
     * @return void
     *
     * @throws PrestaShopException - If order validation fails
     */
    private function processCreateOrder(Cart $cart, $paymentMethod)
    {
        /** @var \Invertus\SaferPay\Adapter\Cart $cartAdapter */
        $cartAdapter = $this->module->getService(\Invertus\SaferPay\Adapter\Cart::class);

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        // Idempotency check: prevent duplicate order creation from webhooks/retries
        if ($cartAdapter->orderExists($cart->id)) {
            $logger->debug(sprintf('%s - Order already exists for cart, skipping creation', self::FILE_NAME), [
                'context' => [
                    'cart_id' => $cart->id,
                ],
            ]);

            return;
        }

        $customer = new \Customer($cart->id_customer);

        $logger->debug(sprintf('%s - Creating PrestaShop order', self::FILE_NAME), [
            'context' => [
                'cart_id' => $cart->id,
                'customer_id' => $customer->id,
                'payment_method' => $paymentMethod,
            ],
        ]);

        // Create order with "awaiting payment" status
        $this->module->validateOrder(
            $cart->id,
            \Configuration::get(SaferPayConfig::SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT),
            (float) $cart->getOrderTotal(),
            $paymentMethod,
            null,
            [],
            null,
            false,
            $customer->secure_key
        );

        $logger->info(sprintf('%s - PrestaShop order created successfully', self::FILE_NAME), [
            'context' => [
                'cart_id' => $cart->id,
                'order_id' => Order::getIdByCartId($cart->id),
            ],
        ]);
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

        try {
            $this->processCreateOrder($cart, $data->getPaymentMethod());

            $order = new Order(Order::getIdByCartId($cart->id));
            $saferPayOrder = new SaferPayOrder($this->saferPayOrderRepository->getIdByCartId($cart->id));

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
}
