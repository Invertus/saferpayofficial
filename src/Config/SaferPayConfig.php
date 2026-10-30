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

namespace Invertus\SaferPay\Config;

use Invertus\SaferPay\DTO\Request\RequestHeader;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayConfig
{
    public const TEST_API = 'https://test.saferpay.com/api/';
    public const API = 'https://www.saferpay.com/api/';
    public const TEST_URL = 'https://test.saferpay.com';
    public const URL = 'https://www.saferpay.com';
    public const TEST_MODE = 'SAFERPAY_TEST_MODE';
    public const USERNAME = 'SAFERPAY_USERNAME';
    public const PASSWORD = 'SAFERPAY_PASSWORD';
    public const CUSTOMER_ID = 'SAFERPAY_CUSTOMER_ID';
    public const TERMINAL_ID = 'SAFERPAY_TERMINAL_ID';
    public const MERCHANT_EMAILS = 'SAFERPAY_MERCHANT_EMAILS';
    public const BUSINESS_LICENSE = 'SAFERPAY_BUSINESS_LICENSE';
    public const PAYMENT_BEHAVIOR = 'SAFERPAY_PAYMENT_BEHAVIOR';
    public const PAYMENT_BEHAVIOR_WITHOUT_3D = 'SAFERPAY_PAYMENT_BEHAVIOR_WITHOUT_3D';
    public const CREDIT_CARD_SAVE = 'SAFERPAY_CREDIT_CARD_SAVE';
    public const RESTRICT_REFUND_AMOUNT_TO_CAPTURED_AMOUNT = 'SAFERPAY_RESTRICT_REFUND_AMOUNT_TO_CAPTURED_AMOUNT';
    public const CONFIGURATION_NAME = 'SAFERPAY_CONFIGURATION_NAME';
    public const TEST_SUFFIX = '_TEST';
    public const API_VERSION = '1.53';

    public const HOOKS = [
        'paymentOptions',
        'displayAdminOrder',
        'actionFrontControllerSetMedia',
        'displayCustomerAccount',
        'actionEmailSendBefore',
        'displayAdminOrderTabContent',
        'actionAdminControllerSetMedia',
        'actionObjectOrderPaymentAddAfter',
        'displayOrderConfirmation',
    ];

    public const PAYMENT_METHODS = [
        self::PAYMENT_ALIPAY,
        self::PAYMENT_AMEX,
        self::PAYMENT_BANCONTACT,
        self::PAYMENT_DINERS,
        self::PAYMENT_DIRECTDEBIT,
        self::PAYMENT_EPRZELEWY,
        self::PAYMENT_EPS,
        self::PAYMENT_IDEAL,
        self::PAYMENT_INVOICE,
        self::PAYMENT_JCB,
        self::PAYMENT_MAESTRO,
        self::PAYMENT_MASTERCARD,
        self::PAYMENT_MYONE,
        self::PAYMENT_PAYPAL,
        self::PAYMENT_POSTCARD,
        self::PAYMENT_POSTFINANCE,
        self::PAYMENT_TWINT,
        self::PAYMENT_VISA,
        self::PAYMENT_VPAY,
        self::PAYMENT_APPLEPAY,
        self::PAYMENT_KLARNA,
        self::PAYMENT_WECHATPAY,
        self::PAYMENT_ACCOUNTTOACCOUNT,
        self::PAYMENT_CLICKTOPAY,
        self::PAYMENT_REKA,
        self::PAYMENT_CARDS,
        self::PAYMENT_WERO,
        self::PAYMENT_GIFTCARD,
    ];

    public const PAYMENT_ALIPAY = 'ALIPAY';
    public const PAYMENT_AMEX = 'AMEX';
    public const PAYMENT_BANCONTACT = 'BANCONTACT';
    public const PAYMENT_DINERS = 'DINERS';
    public const PAYMENT_DIRECTDEBIT = 'DIRECTDEBIT';
    public const PAYMENT_EPRZELEWY = 'EPRZELEWY';
    public const PAYMENT_EPS = 'EPS';
    public const PAYMENT_GIROPAY = 'GIROPAY';
    public const PAYMENT_IDEAL = 'IDEAL';
    public const PAYMENT_INVOICE = 'INVOICE';
    public const PAYMENT_JCB = 'JCB';
    public const PAYMENT_MAESTRO = 'MAESTRO';
    public const PAYMENT_MASTERCARD = 'MASTERCARD';
    public const PAYMENT_MYONE = 'MYONE';
    public const PAYMENT_PAYPAL = 'PAYPAL';
    public const PAYMENT_PAYDIREKT = 'PAYDIREKT';
    public const PAYMENT_POSTCARD = 'POSTCARD';
    public const PAYMENT_POSTFINANCE = 'POSTFINANCE';
    public const PAYMENT_SOFORT = 'SOFORT';
    public const PAYMENT_TWINT = 'TWINT';
    public const PAYMENT_VISA = 'VISA';
    public const PAYMENT_VPAY = 'VPAY';
    public const PAYMENT_KLARNA = 'KLARNA';
    public const PAYMENT_APPLEPAY = 'APPLEPAY';
    public const PAYMENT_GOOGLEPAY = 'GOOGLEPAY';
    public const PAYMENT_BONUS = 'BONUS';
    public const PAYMENT_LASTSCHRIFT = 'DIRECTDEBIT';
    public const PAYMENT_ACCOUNTTOACCOUNT = 'ACCOUNTTOACCOUNT';
    public const PAYMENT_PAYCONIQ = 'PAYCONIQ';
    public const PAYMENT_CARDS = 'CARD';
    public const PAYMENT_POSTFINANCE_PAY = 'POSTFINANCEPAY';
    public const PAYMENT_WECHATPAY = 'WECHATPAY';
    public const PAYMENT_CLICKTOPAY = 'CLICKTOPAY';
    public const PAYMENT_BLIK = 'BLIK';
    public const PAYMENT_REKA = 'REKA';
    public const PAYMENT_WERO = 'WERO';
    public const PAYMENT_GIFTCARD = 'GIFTCARD';

    public const WALLET_PAYMENT_METHODS = [
        self::PAYMENT_APPLEPAY,
        self::PAYMENT_GOOGLEPAY,
        self::PAYMENT_CLICKTOPAY,
    ];

    public const PAYMENT_METHODS_KEYS = [
        'AmericanExpress' => self::PAYMENT_AMEX,
        'PostEFinance' => self::PAYMENT_POSTFINANCE,
        'DinersClub' => self::PAYMENT_DINERS,
        'Alipay' => self::PAYMENT_ALIPAY,
        'Applepay' => self::PAYMENT_APPLEPAY,
        'Bancontact' => self::PAYMENT_BANCONTACT,
        'KlarnaPayments' => self::PAYMENT_KLARNA,
        'MaestroInternational' => self::PAYMENT_MAESTRO,
        'Mastercard' => self::PAYMENT_MASTERCARD,
        'myOne' => self::PAYMENT_MYONE,
        'PayPal' => self::PAYMENT_PAYPAL,
        'Twint' => self::PAYMENT_TWINT,
        'Visa' => self::PAYMENT_VISA,
        'Postcard' => self::PAYMENT_POSTCARD,
        'BonusCard' => self::PAYMENT_BONUS,
        'Lastschrift' => self::PAYMENT_LASTSCHRIFT,
        'AccountToAccount' => self::PAYMENT_ACCOUNTTOACCOUNT,
        'Payconiq' => self::PAYMENT_PAYCONIQ,
        'Cards' => self::PAYMENT_CARDS,
        'PostFinancePay' => self::PAYMENT_POSTFINANCE_PAY,
        'WeChatPay' => self::PAYMENT_WECHATPAY,
        'Blik' => self::PAYMENT_BLIK,
        'Reka' => self::PAYMENT_REKA,
        'Wero' => self::PAYMENT_WERO,
        'Giftcard' => self::PAYMENT_GIFTCARD,
    ];

    public const FIELD_SUPPORTED_PAYMENT_METHODS = [
        self::PAYMENT_AMEX,
        self::PAYMENT_VISA,
        self::PAYMENT_VPAY,
        self::PAYMENT_MASTERCARD,
        self::PAYMENT_MAESTRO,
        self::PAYMENT_BANCONTACT,
        self::PAYMENT_DINERS,
        self::PAYMENT_JCB,
        self::PAYMENT_MYONE,
    ];

    public const KLARNA_SUPPORTED_CURRENCIES = [
        'AUD',
        'CAD',
        'CHF',
        'DKK',
        'EUR',
        'GBP',
        'NOK',
        'SEK',
        'USD',
    ];

    public const KLARNA_SUPPORTED_COUNTRY_CODES = [
        'AT', // Austria
        'BE', // Belgium
        'DK', // Denmark
        'FI', // Finland
        'FR', // France
        'DE', // Germany
        'IT', // Italy
        'NL', // Netherlands
        'NO', // Norway
        'ES', // Spain
        'SE', // Sweden
        'CH', // Switzerland
        'GB', // United Kingdom
    ];

    public const TRANSACTION_METHODS = [
        self::PAYMENT_AMEX,
        self::PAYMENT_BANCONTACT,
        self::PAYMENT_DINERS,
        self::PAYMENT_INVOICE,
        self::PAYMENT_JCB,
        self::PAYMENT_MAESTRO,
        self::PAYMENT_MASTERCARD,
        self::PAYMENT_MYONE,
        self::PAYMENT_VISA,
        self::PAYMENT_VPAY,
    ];

    public const SUPPORTED_3DS_PAYMENT_METHODS = [
        self::PAYMENT_BANCONTACT,
        self::PAYMENT_DINERS,
        self::PAYMENT_MAESTRO,
        self::PAYMENT_MASTERCARD,
        self::PAYMENT_VISA,
        self::PAYMENT_VPAY,
        self::PAYMENT_APPLEPAY,
        self::PAYMENT_AMEX,
    ];

    public const WEB_SERVICE_PASSWORD_PLACEHOLDER = '&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;';

    public const SAFERPAY_PAYMENT_COMPLETED = 'SAFERPAY_PAYMENT_COMPLETED';
    public const SAFERPAY_PAYMENT_AUTHORIZED = 'SAFERPAY_PAYMENT_AUTHORIZED';
    public const SAFERPAY_PAYMENT_PENDING = 'SAFERPAY_PAYMENT_PENDING';
    public const SAFERPAY_PAYMENT_REJECTED = 'SAFERPAY_PAYMENT_REJECTED';
    public const SAFERPAY_PAYMENT_AWAITING = 'SAFERPAY_PAYMENT_AWAITING';
    public const SAFERPAY_PAYMENT_REFUNDED = 'SAFERPAY_PAYMENT_REFUNDED';
    public const SAFERPAY_PAYMENT_PARTLY_REFUNDED = 'SAFERPAY_PAYMENT_PARTLY_REFUNDED';
    public const SAFERPAY_PAYMENT_PENDING_REFUND = 'SAFERPAY_PAYMENT_PENDING_REFUND';
    public const SAFERPAY_PAYMENT_CANCELED = 'SAFERPAY_PAYMENT_CANCELED';
    public const SAFERPAY_PAYMENT_AUTHORIZATION_FAILED = 'SAFERPAY_PAYMENT_AUTHORIZATION_FAILED';

    public const SAFERPAY_SEND_NEW_ORDER_MAIL = 'SAFERPAY_SEND_NEW_ORDER_MAIL';
    public const SAFERPAY_SEND_ORDER_CONF_MAIL = 'SAFERPAY_SEND_ORDER_CONF_MAIL';
    public const SAFERPAY_ALLOW_SAFERPAY_SEND_CUSTOMER_MAIL = 'SAFERPAY_ALLOW_SAFERPAY_SEND_CUSTOMER_MAIL';
    public const SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION = 'SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION';

    public const STATUS_PS_OS_OUTOFSTOCK_PAID = 'PS_OS_OUTOFSTOCK_PAID';

    public const SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT = 'SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT';

    public const SAFERPAY_PAYMENT_DESCRIPTION = 'SAFERPAY_PAYMENT_DESCRIPTION';
    public const SAFERPAY_PAYMENT_DESCRIPTION_DEFAULT_VALUE = 'Prestashop Payment';

    public const SAFERPAY_TEMPLATE_LOCATION = 'module:saferpayofficial/views/templates/';

    public const AMOUNT_MULTIPLIER_FOR_API = 100;
    public const DEFAULT_PAYMENT_BEHAVIOR_CAPTURE = 0;

    public const CREDIT_CARD_OPTION_SAVE = 0;
    public const CREDIT_CARD_DONT_OPTION_SAVE = -1;

    public const FIELDS_ACCESS_TOKEN = 'SAFERPAY_FIELDS_ACCESS_TOKEN';
    public const FIELDS_LIBRARY = 'SAFERPAY_FIELDS_JAVASCRIPT_LIBRARY';
    public const FIELDS_LIBRARY_DEFAULT_VALUE = 'https://www.saferpay.com/Fields/lib/1/saferpay-fields.js';

    public const IS_BUSINESS_LICENCE = 'isBusinessLicence';

    public const EMAIL_ALERTS_MODULE_NAME = 'ps_emailalerts';

    public const PAYMENT_BEHAVIOR_WITHOUT_3D_CANCEL = 0;
    public const PAYMENT_BEHAVIOR_WITHOUT_3D_AUTHORIZE = 1;
    public const PAYMENT_BEHAVIOR_WITHOUT_3D_CAPTURE = 2;

    public const SAFERPAY_ORDER_ID_OPTION = 'SAFERPAY_ORDER_ID_OPTION';

    public const SAFERPAY_CARDFORM_HOLDERNAME_REQUIRENCE = 'MANDATORY';
    public const SAFERPAY_DEBUG_MODE = 'SAFERPAY_DEBUG_MODE';

    public const LOG_SEVERITY_LEVEL_INFORMATIVE = 1;

    public const LOG_SEVERITY_LEVEL_WARNING = 2;

    public const LOG_SEVERITY_LEVEL_ERROR = 3;

    public const LOG_SEVERITY_LEVEL_MAJOR = 4;
    public const TRANSACTION_ALREADY_CAPTURED = 'TRANSACTION_ALREADY_CAPTURED';

    public const ONE_PAGE_CHECKOUT_MODULE = 'onepagecheckoutps';
    public const THE_CHECKOUT_MODULE = 'thecheckout';
    public const SUPER_CHECKOUT_MODULE = 'supercheckout';
    public const OPC_MODULE_LIST = [
        self::ONE_PAGE_CHECKOUT_MODULE,
        self::THE_CHECKOUT_MODULE,
        self::SUPER_CHECKOUT_MODULE,
    ];

    public const SAFERPAY_GROUP_CARDS = 'SAFERPAY_GROUP_CARDS';
    public const SAFERPAY_GROUP_CARDS_LOGO = 'SAFERPAY_GROUP_CARDS_LOGO';
    public const SAFERPAY_USE_FIELDS = 'SAFERPAY_USE_FIELDS';
    /**
     * Card brands that can be grouped under 'Cards' method
     */
    public const CARD_BRANDS = [
        self::PAYMENT_AMEX,
        self::PAYMENT_MAESTRO,
        self::PAYMENT_MASTERCARD,
        self::PAYMENT_VISA,
        self::PAYMENT_VPAY,
        self::PAYMENT_JCB,
        self::PAYMENT_DINERS,
        self::PAYMENT_MYONE,
        self::PAYMENT_BANCONTACT,
    ];

    /**
     * Brand names the Saferpay Fields SDK accepts in its paymentMethods option, which are not the
     * module's own constants. VPAY and MYONE have no SDK equivalent, so an option covering either
     * cannot be restricted in the browser at all.
     */
    public const FIELDS_SDK_BRANDS = [
        self::PAYMENT_AMEX => 'amex',
        self::PAYMENT_BANCONTACT => 'bancontact',
        self::PAYMENT_DINERS => 'diners',
        self::PAYMENT_JCB => 'jcb',
        self::PAYMENT_MAESTRO => 'maestro',
        self::PAYMENT_MASTERCARD => 'mastercard',
        self::PAYMENT_VISA => 'visa',
    ];

    public static function supportsOrderCapture($paymentMethod)
    {
        // payments that DOES NOT SUPPORT capture
        $unsupportedCapturePayments = [
            self::PAYMENT_WECHATPAY,
            self::PAYMENT_ACCOUNTTOACCOUNT,
            self::PAYMENT_GIFTCARD,
        ];

        return !in_array($paymentMethod, $unsupportedCapturePayments);
    }

    public static function supportsOrderCancel($paymentMethod)
    {
        // payments that DOES NOT SUPPORT order cancel
        $unsupportedCancelPayments = [
            self::PAYMENT_WECHATPAY,
            self::PAYMENT_ACCOUNTTOACCOUNT,
            self::PAYMENT_GIFTCARD,
        ];

        return !in_array($paymentMethod, $unsupportedCancelPayments);
    }

    public static function isRedirectPayment($paymentMethod)
    {
        $paymentsAlwaysRedirect = [
            self::PAYMENT_WECHATPAY,
            self::PAYMENT_ACCOUNTTOACCOUNT,
            self::PAYMENT_APPLEPAY,
            self::PAYMENT_GOOGLEPAY,
            self::PAYMENT_TWINT,
            self::PAYMENT_POSTFINANCE_PAY,
            self::PAYMENT_DIRECTDEBIT,
            self::PAYMENT_PAYPAL,
            self::PAYMENT_CLICKTOPAY,
            self::PAYMENT_BLIK,
            self::PAYMENT_REKA,
            self::PAYMENT_WERO,
            self::PAYMENT_GIFTCARD,
        ];

        return in_array($paymentMethod, $paymentsAlwaysRedirect);
    }

    public static function getConfigSuffix()
    {
        if (\Configuration::get(self::TEST_MODE)) {
            return self::TEST_SUFFIX;
        }

        return '';
    }

    /**
     * Gets Field Access Token For Testing Or Live Environments.
     *
     * @return string
     */
    public static function getFieldAccessToken()
    {
        return \Configuration::get(
            SaferPayConfig::FIELDS_ACCESS_TOKEN .
                SaferPayConfig::getConfigSuffix()
        );
    }

    /**
     * Gets Field url For Testing Or Live Environments.
     *
     * @return string
     */
    public static function getFieldUrl()
    {
        return sprintf(
            '%s/Fields/%s',
            SaferPayConfig::getBaseUrl(),
            \Configuration::get(
                SaferPayConfig::CUSTOMER_ID .
                    SaferPayConfig::getConfigSuffix()
            )
        );
    }

    /**
     * Gets Base API URL For Testing Or Live Environments.
     *
     * @return string
     */
    public static function getBaseApiUrl()
    {
        if (\Configuration::get(self::TEST_MODE)) {
            return self::TEST_API;
        }

        return self::API;
    }

    /**
     * Gets Base URL For Testing Or Live Environments.
     *
     * @return string
     */
    public static function getBaseUrl()
    {
        if (\Configuration::get(self::TEST_MODE)) {
            return self::TEST_URL;
        }

        return self::URL;
    }

    public static function getDefaultConfiguration()
    {
        return [
            RequestHeader::RETRY_INDICATOR => 0,
            SaferPayConfig::PAYMENT_BEHAVIOR => 1,
            SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D => 0,
            SaferPayConfig::SAFERPAY_ALLOW_SAFERPAY_SEND_CUSTOMER_MAIL => 1,
            SaferPayConfig::SAFERPAY_PAYMENT_DESCRIPTION => self::SAFERPAY_PAYMENT_DESCRIPTION_DEFAULT_VALUE,
            self::SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION => 0,
            self::SAFERPAY_ORDER_ID_OPTION => 0,
            self::TEST_MODE => 1,
            self::SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT => (int) \Configuration::get(
                self::SAFERPAY_PAYMENT_AWAITING
            ),
            self::SAFERPAY_SEND_ORDER_CONF_MAIL => 0,
            self::SAFERPAY_GROUP_CARDS => 1,
            self::SAFERPAY_USE_FIELDS => 1,
        ];
    }

    public static function getUninstallConfiguration()
    {
        return [
            RequestHeader::SPEC_VERSION,
            RequestHeader::RETRY_INDICATOR,
            RequestHeader::SPEC_REFUND_VERSION,
            self::SAFERPAY_ALLOW_SAFERPAY_SEND_CUSTOMER_MAIL,
            self::SAFERPAY_PAYMENT_DESCRIPTION,
            self::TEST_MODE,
            self::USERNAME,
            self::PASSWORD,
            self::CUSTOMER_ID,
            self::TERMINAL_ID,
            self::MERCHANT_EMAILS,
            self::BUSINESS_LICENSE,
            self::USERNAME . self::TEST_SUFFIX,
            self::PASSWORD . self::TEST_SUFFIX,
            self::CUSTOMER_ID . self::TEST_SUFFIX,
            self::TERMINAL_ID . self::TEST_SUFFIX,
            self::MERCHANT_EMAILS . self::TEST_SUFFIX,
            self::BUSINESS_LICENSE . self::TEST_SUFFIX,
            self::PAYMENT_BEHAVIOR,
            self::CONFIGURATION_NAME,
            self::PAYMENT_BEHAVIOR_WITHOUT_3D,
            self::CREDIT_CARD_SAVE,
            self::FIELDS_ACCESS_TOKEN,
            self::FIELDS_ACCESS_TOKEN . self::TEST_SUFFIX,
            self::SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION,
            self::SAFERPAY_ORDER_ID_OPTION,
            self::SAFERPAY_SEND_ORDER_CONF_MAIL,
            self::SAFERPAY_GROUP_CARDS,
            self::SAFERPAY_USE_FIELDS,
        ];
    }

    public static function isTestMode()
    {
        return (bool) \Configuration::get(self::TEST_MODE);
    }

    public static function isDebugMode()
    {
        return (bool) \Configuration::get(self::SAFERPAY_DEBUG_MODE);
    }

    public static function isVersionAbove177()
    {
        return (bool) version_compare(_PS_VERSION_, '1.7.7', '>=');
    }
}
