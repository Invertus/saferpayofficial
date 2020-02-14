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

use Cart;
use Configuration;
use Country;
use Customer;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Request\Address;
use Invertus\SaferPay\DTO\Request\DeliveryAddressForm;
use Invertus\SaferPay\DTO\Request\Payment;
use Invertus\SaferPay\DTO\Request\RequestHeader;
use Invertus\SaferPay\DTO\Request\ReturnUrls;
use Invertus\SaferPay\DTO\Request\SaferPayNotification;
use Invertus\SaferPay\DTO\Response\Amount;
use SaferPayOfficial;

class RequestObjectCreator
{
    /**
     * @var SaferPayOfficial
     */
    protected $module;

    public function __construct(SaferPayOfficial $module)
    {
        $this->module = $module;
    }

    protected function createRequestHeader()
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

    protected function createPayment(Cart $cart, $totalPrice)
    {
        $currency = \Currency::getCurrency($cart->id_currency);
        return new Payment($totalPrice, $currency['iso_code'], $cart->id);
    }

    protected function createReturnUrls($successUrl, $failUrl)
    {
        return new ReturnUrls($successUrl, $failUrl);
    }

    protected function createNotification($customerEmail, $notifyUrl)
    {
        $payerEmail = $customerEmail;
        $merchantEmail = Configuration::get(SaferPayConfig::MERCHANT_EMAILS . SaferPayConfig::getConfigSuffix());
        return new SaferPayNotification($payerEmail, $merchantEmail, $notifyUrl);
    }

    protected function createDeliveryAddressForm()
    {
        return new DeliveryAddressForm(DeliveryAddressForm::MANDATORY_FIELDS);
    }

    protected function createAmount($value, $currencyCode)
    {
        return new Amount($value, $currencyCode);
    }

    protected function createAddressObject(\Address $address, Customer $customer)
    {
        $saferpayAddress = new Address();
        $saferpayAddress->setFirstName($address->firstname);
        $saferpayAddress->setLastName($address->lastname);
        $saferpayAddress->setCompany($address->company);
//        $saferpayAddress->setGender($address->get);
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
}
