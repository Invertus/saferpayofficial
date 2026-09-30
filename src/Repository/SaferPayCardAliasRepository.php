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

namespace Invertus\SaferPay\Repository;

use Db;
use DbQuery;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayCardAliasRepository
{
    /**
     * The grouped "Cards" option covers several brands at once, and an alias is always stored
     * under the brand Saferpay reported, never under "CARD". The brand comes back with the row so
     * the checkout can tell two saved cards of different brands apart.
     *
     * @param int $userId
     * @param array $paymentMethods
     * @param string $currentDate
     *
     * @return array
     */
    public function getSavedValidCardsByUserIdAndPaymentMethods($userId, array $paymentMethods, $currentDate)
    {
        if (empty($paymentMethods)) {
            return [];
        }

        $escapedMethods = array_map(function ($paymentMethod) {
            return '"' . pSQL($paymentMethod) . '"';
        }, $paymentMethods);

        $query = new DbQuery();
        $query->select('`id_saferpay_card_alias`, `card_number`, `payment_method`');
        $query->from('saferpay_card_alias');
        $query->where('id_customer = ' . (int) $userId);
        $query->where('payment_method IN (' . implode(', ', $escapedMethods) . ')');
        $query->where('valid_till > "' . pSQL($currentDate) . '"');

        return Db::getInstance()->executeS($query);
    }

    public function getSavedCardAliasFromId($id)
    {
        $query = new DbQuery();
        $query->select('`alias_id`');
        $query->from('saferpay_card_alias');
        $query->where('id_saferpay_card_alias = ' . (int) $id);

        return Db::getInstance()->getValue($query);
    }

    public function getSavedCardIdByCustomerIdAndAliasId($customerId, $aliasId)
    {
        $query = new DbQuery();
        $query->select('`id_saferpay_card_alias`');
        $query->from('saferpay_card_alias');
        $query->where('id_customer = ' . (int) $customerId);
        $query->where('alias_id = "' . pSQL($aliasId) . '"');

        return Db::getInstance()->getValue($query);
    }

    public function getSavedCardsByCustomerId($customerId)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('saferpay_card_alias');
        $query->where('id_customer = ' . (int) $customerId);

        return Db::getInstance()->executeS($query);
    }

    public function getCustomerIdByReferenceId($cardAliasId, $idCustomer)
    {
        $query = new DbQuery();
        $query->select('`id_customer`');
        $query->from('saferpay_card_alias');
        $query->where('id_saferpay_card_alias = "' . pSQL($cardAliasId) . '"');
        $query->where('id_customer = "' . (int) $idCustomer . '"');

        return Db::getInstance()->getValue($query);
    }
}
