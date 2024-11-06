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

namespace Invertus\SaferPay\DTO\Request\Initialize;

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Request\Address;
use Invertus\SaferPay\DTO\Request\DeliveryAddressForm;
use Invertus\SaferPay\DTO\Request\Order;
use Invertus\SaferPay\DTO\Request\Payer;
use Invertus\SaferPay\DTO\Request\PayerProfile;
use Invertus\SaferPay\DTO\Request\Payment;
use Invertus\SaferPay\DTO\Request\RequestHeader;
use Invertus\SaferPay\DTO\Request\ReturnUrl;
use Invertus\SaferPay\DTO\Request\SaferPayNotification;

if (!defined('_PS_VERSION_')) {
    exit;
}

class InitializeRequest
{

    /**
     * @var RequestHeader
     */
    private $requestHeader;

    /**
     * @var string
     */
    private $terminalId;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var Payer
     */
    private $payer;

    /**
     * @var ReturnUrl
     */
    private $returnUrl;

    /**
     * @var SaferPayNotification|null
     */
    private $notification;

    /**
     * @var DeliveryAddressForm
     */
    private $deliveryAddressForm;

    /**
     * @var string
     */
    private $configSet;

    /**
     * @var Address
     */
    private $deliveryAddress;

    /**
     * @var Address
     */
    private $billingAddress;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var PayerProfile
     */
    private $payerProfile;

    /**
     * @var string|null
     */
    private $fieldToken;

    public function __construct(
        RequestHeader        $requestHeader,
        $terminalId,
        $paymentMethod,
        Payment              $payment,
        Payer                $payer,
        ReturnUrl            $returnUrl,
        $notification,
        DeliveryAddressForm  $deliveryAddressForm,
        $configSet,
        Address              $deliveryAddress,
        Address              $billingAddress,
        $alias,
        Order                $order,
        PayerProfile         $payerProfile,
        $fieldToken
    ) {
        $this->requestHeader = $requestHeader;
        $this->terminalId = $terminalId;
        $this->paymentMethod = $paymentMethod;
        $this->payment = $payment;
        $this->payer = $payer;
        $this->returnUrl = $returnUrl;
        $this->notification = $notification;
        $this->deliveryAddressForm = $deliveryAddressForm;
        $this->configSet = $configSet;
        $this->deliveryAddress = $deliveryAddress;
        $this->billingAddress = $billingAddress;
        $this->alias = $alias;
        $this->order = $order;
        $this->payerProfile = $payerProfile;
        $this->fieldToken = $fieldToken;
    }

    public function getAsArray()
    {
        if (key_exists($this->paymentMethod, SaferPayConfig::PAYMENT_METHODS_KEYS)) {
            $this->paymentMethod = SaferPayConfig::PAYMENT_METHODS_KEYS[$this->paymentMethod];
        }

        $return = [
            'RequestHeader' => [
                'SpecVersion' => $this->requestHeader->getSpecVersions(),
                'CustomerId' => $this->requestHeader->getCustomerId(),
                'RequestId' => $this->requestHeader->getRequestId(),
                'RetryIndicator' => $this->requestHeader->getRetryIndicator(),
                'ClientInfo' => $this->requestHeader->getClientInfo(),
            ],
            'TerminalId' => $this->terminalId,
            'PaymentMethods' => [
                $this->paymentMethod,
            ],
            'Payment' => [
                'Amount' => [
                    'Value' => $this->payment->getValue(),
                    'CurrencyCode' => $this->payment->getCurrencyCode(),
                ],
                'OrderId' => $this->payment->getOrderReference(),
                'PayerNote' => $this->payment->getPayerNote(),
                'Description' => $this->payment->getDescription(),
            ],
            'PaymentMeans' => $this->getPaymentMeansField() ?: null,
            'Payer' => [
                'IpAddress' => $this->payer->getIpAddress(),
                'LanguageCode' => $this->payer->getLanguageCode(),
                'DeliveryAddress' => [
                    'FirstName' => $this->deliveryAddress->getFirstName() ?: null,
                    'LastName' => $this->deliveryAddress->getLastName() ?: null,
                    'Company' => $this->deliveryAddress->getCompany() ?: null,
                    'Gender' => $this->deliveryAddress->getGender() ?: null,
                    'Street' => $this->deliveryAddress->getStreet() ?: null,
                    'Street2' => $this->deliveryAddress->getStreet2() ?: null,
                    'Zip' => $this->deliveryAddress->getZip() ?: null,
                    'City' => $this->deliveryAddress->getCity() ?: null,
                    'CountryCode' => $this->deliveryAddress->getCountryCode() ?: null,
                    'Email' => $this->deliveryAddress->getEmail() ?: null,
                    'Phone' => $this->deliveryAddress->getPhone() ?: null,
                ],
                'BillingAddress' => [
                    'FirstName' => $this->billingAddress->getFirstName() ?: null,
                    'LastName' => $this->billingAddress->getLastName() ?: null,
                    'Company' => $this->billingAddress->getCompany() ?: null,
                    'Gender' => $this->billingAddress->getGender() ?: null,
                    'Street' => $this->billingAddress->getStreet() ?: null,
                    'Street2' => $this->billingAddress->getStreet2() ?: null,
                    'Zip' => $this->billingAddress->getZip() ?: null,
                    'City' => $this->billingAddress->getCity() ?: null,
                    'CountryCode' => $this->billingAddress->getCountryCode() ?: null,
                    'Email' => $this->billingAddress->getEmail() ?: null,
                    'Phone' => $this->billingAddress->getPhone() ?: null,
                ],
            ],
            'ReturnUrl' => [
                'Url' => $this->returnUrl->getReturnUrl(),
            ],
            'DeliveryAddressForm' => [
                'AddressSource' => $this->deliveryAddressForm->getAddressSource(),
                'MandatoryFields' => $this->deliveryAddressForm->getMandatoryFields(),
            ],
        ];

        if ($this->getPaymentMeansField() === []) {
            $return['CardForm'] = [
                'HolderName' => SaferPayConfig::SAFERPAY_CARDFORM_HOLDERNAME_REQUIRENCE,
            ];
        }

        if ($this->notification !== null) {
            $return['Notification'] = [
                'MerchantEmails' => [$this->notification->getMerchantEmail()],
                'SuccessNotifyUrl' => $this->notification->getNotifyUrl(),
                'FailNotifyUrl' => $this->notification->getNotifyUrl(),
            ];

            if (\Configuration::get(SaferPayConfig::SAFERPAY_ALLOW_SAFERPAY_SEND_CUSTOMER_MAIL)) {
                $return['Notification']['PayerEmail'] = $this->notification->getPayerEmail();
            }
        }

        if ($this->configSet) {
            $return['ConfigSet'] = $this->configSet;
        }

        if ($this->alias || $this->fieldToken) {
            unset($return['PaymentMethods']);
        }

        //Wallet related, payment method must be empty, instead used "Wallets" argument in request.
        if (in_array(\Tools::strtoupper($this->paymentMethod), SaferPayConfig::WALLET_PAYMENT_METHODS)) {
            unset($return['PaymentMethods']);
            $return['Wallets'] = [
                $this->paymentMethod,
            ];
        }

        if (\Tools::strtoupper($this->paymentMethod) == SaferPayConfig::PAYMENT_KLARNA) {
            $return['Order'] = [
                'Items' => $this->order->getItems(),
            ];

            $return['RiskFactors'] = [
                'PayerProfile' => [
                    'CreationDate' => $this->payerProfile->getCreationDate() ?: null,
                    'PasswordLastChangeDate' => $this->payerProfile->getPasswordLastChangeDate() ?: null,
                ],
            ];
        }

        return $return;
    }

    /**
     * @return array
     */
    private function getPaymentMeansField()
    {
        if ($this->alias) {
            return [
                'Alias' => [
                    'Id' => $this->alias,
                ],
            ];
        }

        if ($this->fieldToken) {
            return [
                'SaferpayFields' => [
                    'Token' => $this->fieldToken ?: null,
                ],
            ];
        }

        return [];
    }
}
