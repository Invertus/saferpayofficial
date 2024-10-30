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

function upgrade_module_1_2_4(SaferPayOfficial $module)
{
    return Db::getInstance()->execute(
        'ALTER TABLE ' . _DB_PREFIX_ . pSQL(SaferPayLog::$definition['table']) . ' 
        ADD COLUMN `id_log` INT(10) DEFAULT 0,
        ADD COLUMN `id_shop` INT(10) DEFAULT ' . (int) Configuration::get('PS_SHOP_DEFAULT') . ',
        CHANGE `payload` `request` TEXT,
        ADD COLUMN `response` MEDIUMTEXT DEFAULT NULL,
        ADD COLUMN `context` MEDIUMTEXT DEFAULT NULL,
        DROP COLUMN `message`,
        DROP PRIMARY KEY,
        ADD PRIMARY KEY (`id_saferpay_log`, `id_log`, `id_shop`),
        ADD INDEX (`id_log`);'
    );
}
