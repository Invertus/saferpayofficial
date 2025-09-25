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

function upgrade_module_2_0_2(SaferPayOfficial $module)
{
    $db = Db::getInstance();
    $success = true;

    // Add indexes for saferpay_order table
    $orderIndexes = [
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_order` ADD INDEX `idx_id_order` (`id_order`)",
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_order` ADD INDEX `idx_id_cart` (`id_cart`)",
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_order` ADD INDEX `idx_id_customer` (`id_customer`)",
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_order` ADD INDEX `idx_transaction_id` (`transaction_id`)",
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_order` ADD INDEX `idx_status_flags` (`authorized`, `captured`, `pending`)",
    ];

    foreach ($orderIndexes as $indexSql) {
        try {
            $result = $db->execute($indexSql);
            if (!$result) {
                $error = $db->getMsgError();
                if (strpos($error, 'Duplicate key name') === false) {
                    $success = false;
                    PrestaShopLogger::addLog('SaferPay: Failed to add order index - ' . $error, 3, null, 'SaferPayOrder');
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('SaferPay: Order index creation skipped - ' . $e->getMessage(), 1, null, 'SaferPayOrder');
        }
    }

    // Add indexes for saferpay_card_alias table
    $cardAliasIndexes = [
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_card_alias` ADD INDEX `idx_id_customer` (`id_customer`)",
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_card_alias` ADD INDEX `idx_payment_method` (`payment_method`)",
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_card_alias` ADD INDEX `idx_customer_payment` (`id_customer`, `payment_method`)",
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_card_alias` ADD INDEX `idx_valid_till` (`valid_till`)",
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_card_alias` ADD INDEX `idx_alias_id` (`alias_id`)",
    ];

    foreach ($cardAliasIndexes as $indexSql) {
        try {
            $result = $db->execute($indexSql);
            if (!$result) {
                $error = $db->getMsgError();
                if (strpos($error, 'Duplicate key name') === false) {
                    $success = false;
                    PrestaShopLogger::addLog('SaferPay: Failed to add card alias index - ' . $error, 3, null, 'SaferPayCardAlias');
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('SaferPay: Card alias index creation skipped - ' . $e->getMessage(), 1, null, 'SaferPayCardAlias');
        }
    }

    // Add indexes for saferpay_assert table
    $assertIndexes = [
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_assert` ADD INDEX `idx_id_saferpay_order` (`id_saferpay_order`)",
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_assert` ADD INDEX `idx_payment_method` (`payment_method`)",
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_assert` ADD INDEX `idx_brand` (`brand`)",
    ];

    foreach ($assertIndexes as $indexSql) {
        try {
            $result = $db->execute($indexSql);
            if (!$result) {
                $error = $db->getMsgError();
                if (strpos($error, 'Duplicate key name') === false) {
                    $success = false;
                    PrestaShopLogger::addLog('SaferPay: Failed to add assert index - ' . $error, 3, null, 'SaferPayAssert');
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('SaferPay: Assert index creation skipped - ' . $e->getMessage(), 1, null, 'SaferPayAssert');
        }
    }

    // Add indexes for saferpay_order_refund table
    $refundIndexes = [
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_order_refund` ADD INDEX `idx_id_saferpay_order` (`id_saferpay_order`)",
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_order_refund` ADD INDEX `idx_id_order` (`id_order`)",
        "ALTER TABLE `" . _DB_PREFIX_ . "saferpay_order_refund` ADD INDEX `idx_transaction_id` (`transaction_id`)",
    ];

    foreach ($refundIndexes as $indexSql) {
        try {
            $result = $db->execute($indexSql);
            if (!$result) {
                $error = $db->getMsgError();
                if (strpos($error, 'Duplicate key name') === false) {
                    $success = false;
                    PrestaShopLogger::addLog('SaferPay: Failed to add refund index - ' . $error, 3, null, 'SaferPayOrderRefund');
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('SaferPay: Refund index creation skipped - ' . $e->getMessage(), 1, null, 'SaferPayOrderRefund');
        }
    }

    if ($success) {
        PrestaShopLogger::addLog('SaferPay: Database indexes added successfully', 1, null, 'SaferPayOptimization');
    }

    return $success;
}