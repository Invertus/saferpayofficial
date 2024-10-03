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

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Request\RequestHeader;

if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_1_1_8(SaferPayOfficial $module)
{
    Configuration::updateValue(SaferPayConfig::SAFERPAY_ALLOW_SAFERPAY_SEND_CUSTOMER_MAIL, 1);
    Configuration::updateValue(
        SaferPayConfig::SAFERPAY_PAYMENT_DESCRIPTION,
        SaferPayConfig::SAFERPAY_PAYMENT_DESCRIPTION_DEFAULT_VALUE
    );

    Configuration::updateValue(RequestHeader::SPEC_VERSION, SaferPayConfig::API_VERSION);
    Configuration::updateValue(RequestHeader::SPEC_REFUND_VERSION, SaferPayConfig::API_VERSION);

    $module->registerHook('actionObjectOrderPaymentAddAfter');

    return true;
}
