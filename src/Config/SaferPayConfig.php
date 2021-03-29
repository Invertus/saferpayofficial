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

use Configuration;
use Invertus\SaferPay\DTO\Request\RequestHeader;

class SaferPayConfig
{
    const TEST_API = 'https://test.saferpay.com/api/';
    const API = 'https://www.saferpay.com/api/';
    const TEST_URL = 'https://test.saferpay.com';
    const URL = 'https://www.saferpay.com';
    const TEST_MODE = 'SAFERPAY_TEST_MODE';
    const USERNAME = 'SAFERPAY_USERNAME';
    const PASSWORD = 'SAFERPAY_PASSWORD';
    const CUSTOMER_ID = 'SAFERPAY_CUSTOMER_ID';
    const TERMINAL_ID = 'SAFERPAY_TERMINAL_ID';
    const MERCHANT_EMAILS = 'SAFERPAY_MERCHANT_EMAILS';
    const BUSINESS_LICENSE = 'SAFERPAY_BUSINESS_LICENSE';
    const PAYMENT_BEHAVIOR = 'SAFERPAY_PAYMENT_BEHAVIOR';
    const PAYMENT_BEHAVIOR_WITHOUT_3D = 'SAFERPAY_PAYMENT_BEHAVIOR_WITHOUT_3D';
    const CREDIT_CARD_SAVE = 'SAFERPAY_CREDIT_CARD_SAVE';
    const CONFIGURATION_NAME = 'SAFERPAY_CONFIGURATION_NAME';
    const CSS_FILE = 'SAFERPAY_CSS_FILE';
    const TEST_SUFFIX = '_TEST';
    const PAYMENT_METHODS = [
        self::PAYMENT_ALIPAY,
        self::PAYMENT_AMEX,
        self::PAYMENT_BANCONTACT,
        self::PAYMENT_BONUS,
        self::PAYMENT_DINERS,
        self::PAYMENT_DIRECTDEBIT,
        self::PAYMENT_EPRZELEWY,
        self::PAYMENT_EPS,
        self::PAYMENT_GIROPAY,
        self::PAYMENT_IDEAL,
        self::PAYMENT_INVOICE,
        self::PAYMENT_JCB,
        self::PAYMENT_MAESTRO,
        self::PAYMENT_MASTERCARD,
        self::PAYMENT_MYONE,
        self::PAYMENT_PAYPAL,
        self::PAYMENT_PAYDIREKT,
        self::PAYMENT_POSTCARD,
        self::PAYMENT_POSTFINANCE,
        self::PAYMENT_SOFORT,
        self::PAYMENT_TWINT,
        self::PAYMENT_UNIONPAY,
        self::PAYMENT_VISA,
        self::PAYMENT_VPAY,
        self::PAYMENT_APPLEPAY,
        self::PAYMENT_KLARNA,
    ];

    const PAYMENT_ALIPAY = 'ALIPAY';
    const PAYMENT_AMEX = 'AMEX';
    const PAYMENT_BANCONTACT = 'BANCONTACT';
    const PAYMENT_BONUS = 'BONUS';
    const PAYMENT_DINERS = 'DINERS';
    const PAYMENT_DIRECTDEBIT = 'DIRECTDEBIT';
    const PAYMENT_EPRZELEWY = 'EPRZELEWY';
    const PAYMENT_EPS = 'EPS';
    const PAYMENT_GIROPAY = 'GIROPAY';
    const PAYMENT_IDEAL = 'IDEAL';
    const PAYMENT_INVOICE = 'INVOICE';
    const PAYMENT_JCB = 'JCB';
    const PAYMENT_MAESTRO = 'MAESTRO';
    const PAYMENT_MASTERCARD = 'MASTERCARD';
    const PAYMENT_MYONE = 'MYONE';
    const PAYMENT_PAYPAL = 'PAYPAL';
    const PAYMENT_PAYDIREKT = 'PAYDIREKT';
    const PAYMENT_POSTCARD = 'POSTCARD';
    const PAYMENT_POSTFINANCE = 'POSTFINANCE';
    const PAYMENT_SOFORT = 'SOFORT';
    const PAYMENT_TWINT = 'TWINT';
    const PAYMENT_UNIONPAY = 'UNIONPAY';
    const PAYMENT_VISA = 'VISA';
    const PAYMENT_VPAY = 'VPAY';
    const PAYMENT_KLARNA = 'KLARNA';
    const PAYMENT_APPLEPAY = 'APPLEPAY';

    const WALLET_PAYMENT_METHODS = [
        self::PAYMENT_APPLEPAY,
    ];

    const FIELD_SUPPORTED_PAYMENT_METHODS = [
        self::PAYMENT_VISA,
        self::PAYMENT_VPAY,
        self::PAYMENT_MASTERCARD,
        self::PAYMENT_MAESTRO,
        self::PAYMENT_BANCONTACT,
        self::PAYMENT_DINERS,
        self::PAYMENT_JCB,
        self::PAYMENT_BONUS,
        self::PAYMENT_MYONE,
    ];

    const KLARNA_SUPPORTED_CURRENCIES = [
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

    const KLARNA_SUPPORTED_COUNTRY_CODES = [
        'AT', //Austria
        'DK', //Denmark
        'FI', //Finland
        'DE', //Germany
        'NL', //Netherlands
        'NO', //Norway
        'SE', //Sweden
    ];

    const TRANSACTION_METHODS = [
        self::PAYMENT_AMEX,
        self::PAYMENT_BANCONTACT,
        self::PAYMENT_BONUS,
        self::PAYMENT_DINERS,
        self::PAYMENT_INVOICE,
        self::PAYMENT_JCB,
        self::PAYMENT_MAESTRO,
        self::PAYMENT_MASTERCARD,
        self::PAYMENT_MYONE,
        self::PAYMENT_VISA,
        self::PAYMENT_VPAY,
    ];

    const SUPPORTED_3DS_PAYMENT_METHODS = [
        self::PAYMENT_BANCONTACT,
        self::PAYMENT_DINERS,
        self::PAYMENT_MAESTRO,
        self::PAYMENT_MASTERCARD,
        self::PAYMENT_VISA,
        self::PAYMENT_VPAY,
        self::PAYMENT_UNIONPAY,
        self::PAYMENT_APPLEPAY,
        self::PAYMENT_AMEX,
    ];

    const WEB_SERVICE_PASSWORD_PLACEHOLDER = '&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;';
    const SAFERPAY_PAYMENT_COMPLETED = 'SAFERPAY_PAYMENT_COMPLETED';
    const SAFERPAY_PAYMENT_AUTHORIZED = 'SAFERPAY_PAYMENT_AUTHORIZED';
    const SAFERPAY_PAYMENT_REJECTED = 'SAFERPAY_PAYMENT_REJECTED';
    const SAFERPAY_PAYMENT_AWAITING = 'SAFERPAY_PAYMENT_AWAITING';
    const SAFERPAY_PAYMENT_REFUNDED = 'SAFERPAY_PAYMENT_REFUNDED';
    const SAFERPAY_PAYMENT_CANCELED = 'SAFERPAY_PAYMENT_CANCELED';
    const SAFERPAY_PAYMENT_AUTHORIZATION_FAILED = 'SAFERPAY_PAYMENT_AUTHORIZATION_FAILED';
    const SAFERPAY_SEND_ORDER_CONFIRMATION = 'SAFERPAY_SEND_ORDER_CONFIRMATION';

    const SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT = 'SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT';

    const SAFERPAY_TEMPLATE_LOCATION = 'module:saferpayofficial/views/templates/';
    const SAFERPAY_HOSTED_TEMPLATE_LOCATION = 'module:saferpayofficial/views/templates/front/hosted-templates/';

    const AMOUNT_MULTIPLIER_FOR_API = 100;
    const DEFAULT_PAYMENT_BEHAVIOR_CAPTURE = 0;

    const CREDIT_CARD_OPTION_SAVE = 0;
    const CREDIT_CARD_DONT_OPTION_SAVE = -1;

    const TRANSACTION_STATUS_AUTHORIZED = 'AUTHORIZED';
    const TRANSACTION_STATUS_CAPTURED = 'CAPTURED';

    const LOG_TYPE_SUCCESS = 'SUCCESS';
    const LOG_TYPE_ERROR = 'ERROR';
    const LOG_TYPE_CRITICAL_ERROR = 'CRITICAL ERROR';

    const FIELDS_ACCESS_TOKEN = 'SAFERPAY_FIELDS_ACCESS_TOKEN';
    const FIELDS_LIBRARY = 'SAFERPAY_FIELDS_JAVASCRIPT_LIBRARY';
    const FIELDS_LIBRARY_DEFAULT_VALUE = 'https://www.saferpay.com/Fields/lib/1/saferpay-fields.js';
    const FIELDS_LIBRARY_TEST_DEFAULT_VALUE = 'https://www.saferpay.com/Fields/lib/1/saferpay-fields.js';

    const HOSTED_FIELDS_TEMPLATE_DEFAULT = 1;
    const HOSTED_FIELDS_TEMPLATE = 'SAFERPAY_HOSTED_FIELDS_TEMPLATE';

    const IS_BUSINESS_LICENCE = 'isBusinessLicence';

    public static function getConfigSuffix()
    {
        if (Configuration::get(self::TEST_MODE)) {
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
        return Configuration::get(
            \Invertus\SaferPay\Config\SaferPayConfig::FIELDS_ACCESS_TOKEN .
            \Invertus\SaferPay\Config\SaferPayConfig::getConfigSuffix()
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
            \Invertus\SaferPay\Config\SaferPayConfig::getBaseUrl(),
            Configuration::get(
                \Invertus\SaferPay\Config\SaferPayConfig::CUSTOMER_ID .
                \Invertus\SaferPay\Config\SaferPayConfig::getConfigSuffix()
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
        if (Configuration::get(self::TEST_MODE)) {
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
        if (Configuration::get(self::TEST_MODE)) {
            return self::TEST_URL;
        }

        return self::URL;
    }

    public static function getDefaultConfiguration()
    {
        return [
            RequestHeader::SPEC_VERSION => '1.20',
            RequestHeader::RETRY_INDICATOR => 0,
            SaferPayConfig::PAYMENT_BEHAVIOR => 1,
            SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D => 1,
            SaferPayConfig::SAFERPAY_SEND_ORDER_CONFIRMATION => 1,
            SaferPayConfig::FIELDS_LIBRARY => self::FIELDS_LIBRARY_DEFAULT_VALUE,
            SaferPayConfig::FIELDS_LIBRARY . SaferPayConfig::TEST_SUFFIX => self::FIELDS_LIBRARY_TEST_DEFAULT_VALUE,
            self::TEST_MODE => 1,
            self::HOSTED_FIELDS_TEMPLATE => self::HOSTED_FIELDS_TEMPLATE_DEFAULT,
            self::SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT => Configuration::get(
                self::SAFERPAY_PAYMENT_AWAITING
            ),
        ];
    }

    public static function getUninstallConfiguration()
    {
        return [
            RequestHeader::SPEC_VERSION,
            RequestHeader::RETRY_INDICATOR,
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
            self::CSS_FILE,
            self::PAYMENT_BEHAVIOR_WITHOUT_3D,
            self::CREDIT_CARD_SAVE,
            self::FIELDS_ACCESS_TOKEN,
            self::FIELDS_ACCESS_TOKEN . self::TEST_SUFFIX,
            self::FIELDS_LIBRARY,
            self::FIELDS_LIBRARY . self::TEST_SUFFIX,
        ];
    }

    public static function isTestMode()
    {
        return (bool) Configuration::get(self::TEST_MODE);
    }

    public static function isVersion17()
    {
        return (bool) version_compare(_PS_VERSION_, '1.7', '>=');
    }

    public static function isVersionAbove177()
    {
        return (bool) version_compare(_PS_VERSION_, '1.7.7', '>=');
    }
}
