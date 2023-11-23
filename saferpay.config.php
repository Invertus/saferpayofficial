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

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('_SAFERPAY_PAYMENT_COMPLETED_')) {
    /** @var URL to module IMG files directory */
    define('_SAFERPAY_PAYMENT_COMPLETED_', Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_COMPLETED));
}
if (!defined('_SAFERPAY_PAYMENT_AUTHORIZED_')) {
    /** @var URL to module IMG files directory */
    define('_SAFERPAY_PAYMENT_AUTHORIZED_', Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_AUTHORIZED));
}
if (!defined('_SAFERPAY_PAYMENT_REJECTED_')) {
    /** @var URL to module IMG files directory */
    define('_SAFERPAY_PAYMENT_REJECTED_', Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_REJECTED));
}
if (!defined('_SAFERPAY_PAYMENT_REFUND_')) {
    /** @var URL to module IMG files directory */
    define('_SAFERPAY_PAYMENT_REFUND_', Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_REFUNDED));
}
if (!defined('_SAFERPAY_PAYMENT_PARTLY_REFUND_')) {
    /** @var URL to module IMG files directory */
    define('_SAFERPAY_PAYMENT_PARTLY_REFUND_', Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_PARTLY_REFUNDED));
}
if (!defined('_SAFERPAY_PAYMENT_PENDING_REFUND_')) {
    /** @var URL to module IMG files directory */
    define('_SAFERPAY_PAYMENT_PENDING_REFUND_', Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_PENDING_REFUND));
}
if (!defined('_SAFERPAY_PAYMENT_CANCELED_')) {
    /** @var URL to module IMG files directory */
    define('_SAFERPAY_PAYMENT_CANCELED_', Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_CANCELED));
}
if (!defined('_SAFERPAY_PAYMENT_AUTHORIZATION_FAILED_')) {
    /** @var URL to module IMG files directory */
    define(
        '_SAFERPAY_PAYMENT_AUTHORIZATION_FAILED_',
        Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_AUTHORIZATION_FAILED)
    );
}
