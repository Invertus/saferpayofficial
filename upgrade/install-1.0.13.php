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

use Invertus\SaferPay\Install\Installer;

if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_1_0_13(SaferPayOfficial $module)
{
    $installer = new Installer($module);
    $installer->createAllOrderStatus();

    $sql = [];

    $sql[] = '
        ALTER TABLE ' . _DB_PREFIX_ . 'saferpay_assert
        ADD `pending_refund_amount` INT(64) DEFAULT 0;
    ';

    $sql[] = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'saferpay_order_refund' . '(
            `id_saferpay_order_refund` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `id_saferpay_order` INTEGER(10) DEFAULT 0,
            `id_order` INTEGER(10) DEFAULT 0,
            `transaction_id` VARCHAR(64) NOT NULL,
            `amount` INTEGER(20) NOT NULL,
            `currency` VARCHAR(64) NOT NULL,
            `status` VARCHAR(64) NOT NULL
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';

    foreach ($sql as $query) {
        if (false == Db::getInstance()->execute($query)) {
            return false;
        }
    }


    return true;
}
