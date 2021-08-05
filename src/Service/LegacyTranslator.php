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
 * @author INVERTUS UAB www.invertus.eu  <support@invertus.eu>
 * @copyright SIX Payment Services
 * @license   SIX Payment Services
 */

namespace Invertus\SaferPay\Service;

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Factory\ModuleFactory;

class LegacyTranslator implements TranslatorInterface
{
    private const FILE_NAME = 'LegacyTranslator';

    private $module;

    public function __construct(ModuleFactory $moduleFactory)
    {
        $this->module = $moduleFactory->getModule();
    }

    public function translate(string $key): string
    {
        return $this->getTranslations()[$key] ?? $key;
    }

    private function getTranslations(): array
    {
        return [
            SaferPayConfig::PAYMENT_ALIPAY => $this->module->l('Pay by Ali',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_AMEX => $this->module->l('Pay by Amex',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_BANCONTACT => $this->module->l('Pay by Bancontact',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_DINERS => $this->module->l('Pay by Diners',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_DIRECTDEBIT => $this->module->l('Pay by Directdebit',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_EPRZELEWY => $this->module->l('Pay by Eprzelewy',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_EPS => $this->module->l('Pay by Eps',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_GIROPAY => $this->module->l('Pay by Giropay',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_IDEAL => $this->module->l('Pay by Ideal',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_INVOICE => $this->module->l('Pay by Invoice',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_JCB => $this->module->l('Pay by Jcb',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_MAESTRO => $this->module->l('Pay by Maestro',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_MASTERCARD => $this->module->l('Pay by Mastercard',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_MYONE => $this->module->l('Pay by Myone',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_PAYDIREKT => $this->module->l('Pay by Paydirect',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_POSTCARD => $this->module->l('Pay by Postcard',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_POSTFINANCE => $this->module->l('Pay by Postfinance',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_SOFORT => $this->module->l('Pay by Sofort',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_TWINT => $this->module->l('Pay by Twint',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_UNIONPAY => $this->module->l('Pay by Unionpay',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_VISA => $this->module->l('Pay by Visa',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_VPAY => $this->module->l('Pay by Vpay',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_APPLEPAY => $this->module->l('Pay by Applepay',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_KLARNA => $this->module->l('Pay by Klarna',  self::FILE_NAME),
            SaferPayConfig::PAYMENT_WLCRYPTOPAYMENTS => $this->module->l('Pay by Wlcryptopayments',  self::FILE_NAME),
        ];
    }
}