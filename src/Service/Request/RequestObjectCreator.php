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

namespace Invertus\SaferPay\Service\Request;

use Carrier;
use Cart;
use CartRule;
use Configuration;
use Country;
use Customer;
use Gender;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Request\Address;
use Invertus\SaferPay\DTO\Request\DeliveryAddressForm;
use Invertus\SaferPay\DTO\Request\Order;
use Invertus\SaferPay\DTO\Request\OrderItem;
use Invertus\SaferPay\DTO\Request\PayerProfile;
use Invertus\SaferPay\DTO\Request\Payment;
use Invertus\SaferPay\DTO\Request\RequestHeader;
use Invertus\SaferPay\DTO\Request\ReturnUrl;
use Invertus\SaferPay\DTO\Request\SaferPayNotification;
use Invertus\SaferPay\DTO\Response\Amount;
use Invertus\SaferPay\Enum\GenderEnum;
use Invertus\SaferPay\Factory\ModuleFactory;
use Invertus\SaferPay\Provider\IdempotencyProviderInterface;
use Invertus\SaferPay\Repository\OrderRepositoryInterface;
use Invertus\SaferPay\Utility\PriceUtility;
use SaferPayOfficial;
use Tax;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RequestObjectCreator
{
    /**
     * @var SaferPayOfficial
     */
    protected $module;

    /**
     * @var PriceUtility
     */
    private $priceUtility;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @NOTE: Consider splitting this class into focused services (AddressBuilder, PaymentBuilder, etc.)
     * @var IdempotencyProviderInterface
     */
    private $idempotencyProvider;

    public function __construct(
        ModuleFactory $module,
        PriceUtility $priceUtility,
        OrderRepositoryInterface $orderRepository,
        IdempotencyProviderInterface $idempotencyProvider
    ) {
        $this->module = $module->getModule();
        $this->priceUtility = $priceUtility;
        $this->orderRepository = $orderRepository;
        $this->idempotencyProvider = $idempotencyProvider;
    }

    public function createRequestHeader(): RequestHeader
    {
        $specVersion = SaferPayConfig::API_VERSION;
        $customerId = Configuration::get(RequestHeader::CUSTOMER_ID . SaferPayConfig::getConfigSuffix());
        $requestId = $this->idempotencyProvider->getIdempotencyKey();
        $retryIndicator = Configuration::get(RequestHeader::RETRY_INDICATOR);
        $clientInfo = [
            'ShopInfo' => 'PrestaShop_' . _PS_VERSION_ . ':Invertus_' . $this->module->version,
        ];
        return new RequestHeader($specVersion, $customerId, $requestId, $retryIndicator, $clientInfo);
    }

    /**
     * @param Cart $cart
     * @param string $totalPrice
     *
     * @return Payment|null
     * @throws \PrestaShopException
     */
    public function createPayment(Cart $cart, string $totalPrice): ?Payment
    {
        $currency = \Currency::getCurrency($cart->id_currency);
        /** @var \Order|null $order */
        $order = $this->orderRepository->findOneByCartId($cart->id);

        if (!(int) \Configuration::get(SaferPayConfig::SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION) && empty($order)) {
            return null;
        }

        $payment = new Payment();
        $payment->setValue($totalPrice);
        $payment->setCurrencyCode($currency['iso_code']);

        $description = (string) Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_DESCRIPTION);
        $orderIdOption = (int) Configuration::get(SaferPayConfig::SAFERPAY_ORDER_ID_OPTION);

        if ($orderIdOption === 0 && !empty($order)) {
            $payment->setDescription($order->reference);
        } else {
            $payment->setDescription($description);
        }

        if (!empty($order)) {
            $payment->setOrderReference($order->reference);
        }

        return $payment;
    }

    public function createReturnUrl(string $returnUrl): ReturnUrl
    {
        return new ReturnUrl($returnUrl);
    }

    public function createNotification(string $customerEmail, string $notifyUrl): SaferPayNotification
    {
        $payerEmail = $customerEmail;
        $merchantEmail = Configuration::get(SaferPayConfig::MERCHANT_EMAILS . SaferPayConfig::getConfigSuffix());
        return new SaferPayNotification($payerEmail, $merchantEmail, $notifyUrl);
    }

    public function createDeliveryAddressForm(): DeliveryAddressForm
    {
        return new DeliveryAddressForm(DeliveryAddressForm::MANDATORY_FIELDS, DeliveryAddressForm::ADDRESS_SOURCE);
    }

    public function createAmount(string $value, string $currencyCode): Amount
    {
        return new Amount($value, $currencyCode);
    }

    public function createAddressObject(\Address $address, Customer $customer): Address
    {
        $saferpayAddress = new Address();
        $saferpayAddress->setFirstName($address->firstname);
        $saferpayAddress->setLastName($address->lastname);
        $saferpayAddress->setCompany($address->company);

        $gender = new Gender($customer->id_gender);

        $genderArray = GenderEnum::SAFERPAY_GENDERS;
        $saferpayAddress->setGender(
            isset($genderArray[$gender->type]) ? $genderArray[$gender->type]: $genderArray[GenderEnum::GENDER_NEUTRAL]
        );
        $saferpayAddress->setStreet($address->address1);
        $saferpayAddress->setStreet2($address->address2);
        $saferpayAddress->setZip($address->postcode);
        $saferpayAddress->setCity($address->city);
        $saferpayAddress->setCountryCode(Country::getIsoById($address->id_country));
        $saferpayAddress->setEmail($customer->email);
        $saferpayAddress->setDateOfBirth($customer->birthday);
        $saferpayAddress->setPhone($address->phone);

        return $saferpayAddress;
    }

    /**
     * UnitPrice is sent as total product price. (That includes discount reduction)
     * TaxRate is calculated with Price Utility because business case needs to make it to integer (Ex. 21% = 2100)
     *
     * @param array $product
     *
     * @return OrderItem
     */
    public function buildOrderItem(array $product): OrderItem
    {
        $orderItem = new OrderItem();
        $orderItem->setVariantId($product['id_product_attribute']);
        $orderItem->setName($product['name']);
        $orderItem->setQuantity($product['quantity']);
        $orderItem->setType($product['is_virtual'] ? OrderItem::ITEM_DIGITAL : OrderItem::ITEM_PHYSICAL);
        $orderItem->setUnitPrice($this->priceUtility->convertToCents($product['price_wt']));
        $orderItem->setTaxRate($this->priceUtility->convertToCents($product['rate']));
        $orderItem->setTaxAmount($this->priceUtility->convertToCents(($product['price_wt'] - $product['price']) * $product['cart_quantity']));

        return $orderItem;
    }

    public function buildOrderItemShippingFee(Cart $cart): OrderItem
    {
        $carrier = new Carrier($cart->id_carrier);
        $cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_SHIPPING, false);
        $isFreeShipping = false;

        if (!empty($cartRules)) {
            foreach ($cartRules as $cartRule) {
                $isFreeShipping = (bool) $cartRule['free_shipping'];
            }
        }

        $orderItem = new OrderItem();
        $orderItem->setQuantity(1);
        $orderItem->setName($carrier->name);
        $orderItem->setType(OrderItem::ITEM_SHIPPING_FEE);

        if ($isFreeShipping) {
            $orderItem->setUnitPrice(0);
            $orderItem->setTaxAmount(0);
        } else {
            $orderItem->setUnitPrice($this->priceUtility->convertToCents($cart->getTotalShippingCost()));
            $orderItem->setTaxAmount($this->priceUtility->convertToCents(
                $cart->getTotalShippingCost(null, true) -
                $cart->getTotalShippingCost(null, false)
            ));
        }

        $orderItem->setTaxRate($this->priceUtility->convertToCents(
            Tax::getCarrierTaxRate($cart->id_carrier, $cart->id_address_delivery)
        ));

        return $orderItem;
    }

    /**
     * @param Cart $cart
     *
     * @return Order
     */
    public function buildOrder(Cart $cart): Order
    {
        $order = new Order();
        $products = $cart->getProducts();

        foreach ($products as $product) {
            $order->addItem($this->buildOrderItem($product));
        }
        $order->addItem($this->buildOrderItemShippingFee($cart));

        return $order;
    }

    /**
     * PasswordLastChangedDate is taken from customer updated field
     * (It updates if personal information or email was updated aswell)
     *
     * @param Customer $customer
     *
     * @return PayerProfile
     */
    public function createPayerProfile(Customer $customer): PayerProfile
    {
        $payerProfile = new PayerProfile();
        $payerProfile->setCreationDate((new \DateTime($customer->date_add))->format(\DateTime::ISO8601));
        $payerProfile->setPasswordLastChangeDate((new \DateTime($customer->date_upd))->format(\DateTime::ISO8601));

        return $payerProfile;
    }
}
