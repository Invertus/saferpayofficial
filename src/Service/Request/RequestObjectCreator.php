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
use Invertus\SaferPay\DTO\Request\ReturnUrls;
use Invertus\SaferPay\DTO\Request\SaferPayNotification;
use Invertus\SaferPay\DTO\Response\Amount;
use Invertus\SaferPay\Enum\GenderEnum;
use Invertus\SaferPay\Repository\OrderRepositoryInterface;
use Invertus\SaferPay\Utility\PriceUtility;
use SaferPayOfficial;
use Tax;

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

    //TODO extract logic to appropriate services.
    public function __construct(
        SaferPayOfficial $module,
        PriceUtility $priceUtility,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->module = $module;
        $this->priceUtility = $priceUtility;
        $this->orderRepository = $orderRepository;
    }

    public function createRequestHeader()
    {
        $specVersion = Configuration::get(RequestHeader::SPEC_VERSION);
        $customerId = Configuration::get(RequestHeader::CUSTOMER_ID . SaferPayConfig::getConfigSuffix());
        $requestId = Configuration::get(RequestHeader::REQUEST_ID);
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
    public function createPayment(Cart $cart, $totalPrice)
    {
        $currency = \Currency::getCurrency($cart->id_currency);
        /** @var \Order|null $order */
        $order = $this->orderRepository->findOneByCartId($cart->id);

        if (empty($order)) {
            return null;
        }
        $payment = new Payment();
        $payment->setValue($totalPrice);
        $payment->setCurrencyCode($currency['iso_code']);
        $payment->setOrderReference($order->reference);

        return $payment;
    }

    public function createReturnUrls($successUrl, $failUrl)
    {
        return new ReturnUrls($successUrl, $failUrl);
    }

    public function createNotification($customerEmail, $notifyUrl)
    {
        $payerEmail = $customerEmail;
        $merchantEmail = Configuration::get(SaferPayConfig::MERCHANT_EMAILS . SaferPayConfig::getConfigSuffix());
        return new SaferPayNotification($payerEmail, $merchantEmail, $notifyUrl);
    }

    public function createDeliveryAddressForm()
    {
        return new DeliveryAddressForm(DeliveryAddressForm::MANDATORY_FIELDS);
    }

    public function createAmount($value, $currencyCode)
    {
        return new Amount($value, $currencyCode);
    }

    public function createAddressObject(\Address $address, Customer $customer)
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
    public function buildOrderItem(array $product)
    {
        $orderItem = new OrderItem();
        $orderItem->setVariantId($product['id_product_attribute']);
        $orderItem->setName($product['name']);
        $orderItem->setQuantity($product['quantity']);
        $orderItem->setType($product['is_virtual'] ? OrderItem::ITEM_DIGITAL : OrderItem::ITEM_PHYSICAL);
        $orderItem->setUnitPrice($this->priceUtility->convertToCents($product['price_wt']));
        $orderItem->setTaxRate($this->priceUtility->convertToCents($product['rate']));
        $orderItem->setTaxAmount($this->priceUtility->convertToCents($product['price_wt'] - $product['price']));

        return $orderItem;
    }

    public function buildOrderItemShippingFee(Cart $cart)
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
    public function buildOrder(Cart $cart)
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
    public function createPayerProfile(Customer $customer)
    {
        $payerProfile = new PayerProfile();
        $payerProfile->setCreationDate((new \DateTime($customer->date_add))->format(\DateTime::ISO8601));
        $payerProfile->setPasswordLastChangeDate((new \DateTime($customer->date_upd))->format(\DateTime::ISO8601));

        return $payerProfile;
    }
}
