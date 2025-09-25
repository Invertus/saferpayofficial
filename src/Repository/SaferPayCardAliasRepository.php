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
    public function getSavedValidCardsByUserIdAndPaymentMethod($userId, $paymentMethod, $currentDate)
    {
        $query = new DbQuery();
        $query->select('`id_saferpay_card_alias`, `card_number`, `alias_id`, `valid_till`');
        $query->from('saferpay_card_alias');
        $query->where('id_customer = ' . (int) $userId);
        $query->where('payment_method = "' . pSQL($paymentMethod) . '"');
        $query->where('valid_till > "' . pSQL($currentDate) . '"');
        $query->where('success = 1');
        $query->orderBy('date_add DESC');
        $query->limit(10);

        return Db::getInstance()->executeS($query);
    }

    public function getSavedCardAliasFromId($id)
    {
        $query = new DbQuery();
        $query->select('`alias_id`');
        $query->from('saferpay_card_alias');
        $query->where('id_saferpay_card_alias = ' . (int) $id);
        $query->where('success = 1');
        $query->limit(1);

        return Db::getInstance()->getValue($query);
    }

    public function getSavedCardIdByCustomerIdAndAliasId($customerId, $aliasId)
    {
        $query = new DbQuery();
        $query->select('`id_saferpay_card_alias`');
        $query->from('saferpay_card_alias');
        $query->where('id_customer = ' . (int) $customerId);
        $query->where('alias_id = "' . pSQL($aliasId) . '"');
        $query->where('success = 1');
        $query->limit(1);

        return Db::getInstance()->getValue($query);
    }

    public function getSavedCardsByCustomerId($customerId)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('saferpay_card_alias');
        $query->where('id_customer = ' . (int) $customerId);
        $query->where('success = 1');
        $query->orderBy('date_add DESC');

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
