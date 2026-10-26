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


use Invertus\SaferPay\Utility\LogDataUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_1(SaferPayOfficial $module)
{
    saferpayofficial_2_1_1_remove_credentials_from_logs();

    return true;
}

/**
 * Earlier versions stored the Basic Authorization header of every logged API call, which decodes
 * to the Saferpay API username and password, so it is removed from the logs already saved.
 */
function saferpayofficial_2_1_1_remove_credentials_from_logs()
{
    $db = Db::getInstance();
    $lastId = 0;
    $columns = ['context', 'request', 'response'];

    do {
        $query = new DbQuery();
        $query->select('`id_saferpay_log`, `context`, `request`, `response`');
        $query->from('saferpay_log');
        $query->where('`id_saferpay_log` > ' . (int) $lastId);
        $query->where("`context` LIKE '%Authorization%' OR `request` LIKE '%Authorization%' OR `response` LIKE '%Authorization%'");
        $query->orderBy('`id_saferpay_log` ASC');
        $query->limit(500);

        $rows = $db->executeS($query);

        if (empty($rows)) {
            return;
        }

        foreach ($rows as $row) {
            $lastId = (int) $row['id_saferpay_log'];
            $update = [];

            foreach ($columns as $column) {
                if ($row[$column] === null) {
                    continue;
                }

                $cleanValue = LogDataUtility::removeCredentialsFromJson($row[$column]);

                if ($cleanValue !== $row[$column]) {
                    $update[$column] = pSQL($cleanValue, true);
                }
            }

            if (empty($update)) {
                continue;
            }

            $db->update('saferpay_log', $update, '`id_saferpay_log` = ' . $lastId);
        }
    } while (count($rows) === 500);
}
