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
        'ALIPAY',
        'AMEX',
        'BANCONTACT',
        'BONUS',
        'DINERS',
        'DIRECTDEBIT',
        'EPRZELEWY',
        'EPS',
        'GIROPAY',
        'IDEAL',
        'INVOICE',
        'JCB',
        'MAESTRO',
        'MASTERCARD',
        'MYONE',
        'PAYPAL',
        'PAYDIREKT',
        'POSTCARD',
        'POSTFINANCE',
        'SOFORT',
        'TWINT',
        'UNIONPAY',
        'VISA',
        'VPAY',
    ];

    const TRANSACTION_METHODS = [
        'AMEX',
        'BANCONTACT',
        'BONUS',
        'DINERS',
        'INVOICE',
        'JCB',
        'MAESTRO',
        'MASTERCARD',
        'MYONE',
        'POSTCARD',
        'POSTFINANCE',
        'VISA',
        'VPAY',
    ];

    const WEB_SERVICE_PASSWORD_PLACEHOLDER = '&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;';
    const SAFERPAY_PAYMENT_COMPLETED = 'SAFERPAY_PAYMENT_COMPLETED';
    const SAFERPAY_PAYMENT_AUTHORIZED = 'SAFERPAY_PAYMENT_AUTHORIZED';
    const SAFERPAY_PAYMENT_REJECTED = 'SAFERPAY_PAYMENT_REJECTED';
    const SAFERPAY_PAYMENT_AWAITING = 'SAFERPAY_PAYMENT_AWAITING';
    const SAFERPAY_PAYMENT_REFUNDED = 'SAFERPAY_PAYMENT_REFUNDED';
    const SAFERPAY_PAYMENT_CANCELED = 'SAFERPAY_PAYMENT_CANCELED';
    const SAFERPAY_PAYMENT_AUTHORIZATION_FAILED = 'SAFERPAY_PAYMENT_AUTHORIZATION_FAILED';

    const SAFERPAY_TEMPLATE_LOCATION = 'module:saferpayofficial/views/templates/';

    const AMOUNT_MULTIPLIER_FOR_API = 100;
    const DEFAULT_PAYMENT_BEHAVIOR_CAPTURE = 0;

    const CREDIT_CARD_OPTION_SAVE = 0;
    const CREDIT_CARD_DONT_OPTION_SAVE = -1;

    const TRANSACTION_STATUS_AUTHORIZED = 'AUTHORIZED';
    const TRANSACTION_STATUS_CAPTURED = 'CAPTURED';

    const LOG_TYPE_SUCCESS = 'SUCCESS';
    const LOG_TYPE_ERROR = 'ERROR';
    const LOG_TYPE_CRITICAL_ERROR = 'CRITICAL ERROR';

    public static function getConfigSuffix()
    {
        if (Configuration::get(self::TEST_MODE)) {
            return self::TEST_SUFFIX;
        }

        return '';
    }

    /**
     * Gets Base URL For Testing Or Live Environments.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if (Configuration::get(self::TEST_MODE)) {
            return self::TEST_API;
        }

        return self::API;
    }

    public static function getDefaultConfiguration()
    {
        return [
            RequestHeader::SPEC_VERSION => '1.14',
            RequestHeader::RETRY_INDICATOR => 0,
            SaferPayConfig::PAYMENT_BEHAVIOR => 1,
            SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D => 1,

            self::TEST_MODE => 1,
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
        ];
    }

    public static function isVersion17()
    {
        return (bool) version_compare(_PS_VERSION_, '1.7', '>=');
    }
}
