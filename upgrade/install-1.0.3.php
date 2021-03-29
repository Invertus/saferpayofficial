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

if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_1_0_3($module)
{
    $result = true;

    Configuration::updateValue(
        \Invertus\SaferPay\DTO\Request\RequestHeader::SPEC_VERSION,
        '1.20'
    );
    Configuration::updateValue(
        \Invertus\SaferPay\Config\SaferPayConfig::FIELDS_LIBRARY,
        \Invertus\SaferPay\Config\SaferPayConfig::FIELDS_LIBRARY_DEFAULT_VALUE
    );
    Configuration::updateValue(
        \Invertus\SaferPay\Config\SaferPayConfig::FIELDS_LIBRARY .
        \Invertus\SaferPay\Config\SaferPayConfig::TEST_SUFFIX,
        \Invertus\SaferPay\Config\SaferPayConfig::FIELDS_LIBRARY_TEST_DEFAULT_VALUE
    );
    Configuration::updateValue(
        \Invertus\SaferPay\Config\SaferPayConfig::HOSTED_FIELDS_TEMPLATE,
        \Invertus\SaferPay\Config\SaferPayConfig::HOSTED_FIELDS_TEMPLATE_DEFAULT
    );

    $result &= Db::getInstance()->execute(
        'ALTER TABLE ' . _DB_PREFIX_ . 'saferpay_log 
            MODIFY COLUMN message TEXT NOT NULL,
            MODIFY COLUMN payload TEXT NOT NULL;'
    );

    $result &= Db::getInstance()->execute(
        'ALTER TABLE ' . _DB_PREFIX_ . 'saferpay_assert
        ADD COLUMN `authorized` TINYINT(1) DEFAULT 0'
    );

    $installer = new \Invertus\SaferPay\Install\Installer($module);
    $installer->installTab(
        SaferPayOfficial::ADMIN_FIELDS_CONTROLLER,
        SaferPayOfficial::ADMIN_SAFERPAY_MODULE_CONTROLLER,
        $module->l('Fields')
    );

    return $result;
}
