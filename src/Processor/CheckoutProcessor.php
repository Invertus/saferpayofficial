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

use Cart;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Core\Payment\DTO\CheckoutData;
use Invertus\SaferPay\EntityBuilder\SaferPayOrderBuilder;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Exception\CouldNotProcessCheckout;
use Invertus\SaferPay\Factory\ModuleFactory;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\SaferPayInitialize;
use Order;
use PrestaShopException;
use SaferPayOrder;

class CheckoutProcessor
{
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

    public function run(CheckoutData $data) {

        $cart = new Cart($data->getCartId());

        if (!$cart) {
            throw CouldNotProcessCheckout::failedToFindCart($data->getCartId());
        }

        if ($data->getIsAuthorizedOrder()) {
            try {
                $saferPayOrder = new SaferPayOrder($this->saferPayOrderRepository->getIdByCartId($cart->id));

                if (!$saferPayOrder->id_order) {
                    $this->processCreateOrder($cart, $data->getPaymentMethod());
                    $saferPayOrder->authorized = 1;
                }

                $order = new Order(Order::getIdByCartId($cart->id));
                $saferPayOrder->id_order = $order->id;

                if ($status === 'AUTHORIZED') {
                    $order->setCurrentState(_SAFERPAY_PAYMENT_AUTHORIZED_);
                } elseif ($status === 'CAPTURED') {
                    $order->setCurrentState(_SAFERPAY_PAYMENT_COMPLETED_);
                }

                $saferPayOrder->update();
                $order->update();

                return '';
            } catch (\Exception $exception) {
                throw CouldNotProcessCheckout::failedToCreateOrder($data->getCartId());
            }
        }

        try {
            if (!$data->getCreateAfterAuthorization()) {
                $this->processCreateOrder($cart, $data->getPaymentMethod());
            }
        } catch (\Exception $exception) {
            throw CouldNotProcessCheckout::failedToCreateOrder($data->getCartId());
        }

        try {
            $response = $this->processInitializePayment(
                $data->getPaymentMethod(),
                $data->getIsBusinessLicense(),
                $data->getSelectedCard(),
                $data->getFieldToken(),
                $data->getSuccessController()
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
            throw CouldNotProcessCheckout::failedToCreateSaferPayOrder($data->getCartId());
        }

        return $response;
    }

    /**
     * @param Cart $cart
     * @param $paymentMethod
     * @return void
     * @throws PrestaShopException
     */
    private function processCreateOrder(Cart $cart, $paymentMethod)
    {
        $customer = new \Customer($cart->id_customer);

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
    }

    /**
     * @param $paymentMethod
     * @param $isBusinessLicense
     * @param $selectedCard
     * @param $fieldToken
     * @param $successController
     * @return array|null
     */
    private function processInitializePayment(
        $paymentMethod,
        $isBusinessLicense,
        $selectedCard,
        $fieldToken,
        $successController
    ) {
        $request = $this->saferPayInitialize->buildRequest(
            $paymentMethod,
            $isBusinessLicense,
            $selectedCard,
            $fieldToken,
            $successController
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
}
