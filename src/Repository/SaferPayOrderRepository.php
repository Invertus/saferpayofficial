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
use SaferPayOrder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOrderRepository
{

    /**
     * @param int $orderId
     * @return SaferPayOrder
     */
    public function getByOrderId(int $orderId): SaferPayOrder
    {
        return new SaferPayOrder($this->getIdByOrderId($orderId));
    }

    /**
     * @param int $cartId
     * @return SaferPayOrder
     */
    public function getByCartId(int $cartId): SaferPayOrder
    {
        return new SaferPayOrder((int) $this->getIdByCartId($cartId));
    }

    /**
     * @param int $customerId
     * @param int $shopId
     * @param string $placedAfter date in Y-m-d H:i:s
     * @return SaferPayOrder
     */
    public function getLatestByCustomerIdPlacedAfter(int $customerId, int $shopId, string $placedAfter): SaferPayOrder
    {
        $query = new DbQuery();
        $query->select('so.`id_saferpay_order`');
        $query->from('saferpay_order', 'so');
        $query->innerJoin('orders', 'o', 'o.`id_order` = so.`id_order`');
        $query->where('so.id_customer = ' . (int) $customerId);
        $query->where('o.id_shop = ' . (int) $shopId);
        $query->where('o.date_add >= \'' . pSQL($placedAfter) . '\'');
        $query->orderBy('so.`id_saferpay_order` DESC');

        return new SaferPayOrder((int) Db::getInstance()->getValue($query, false));
    }

    /**
     * @param int $orderId
     * @return false|string|null
     */
    public function getIdByOrderId(int $orderId)
    {
        $query = new DbQuery();
        $query->select('`id_saferpay_order`');
        $query->from('saferpay_order');
        $query->where('id_order = ' . (int) $orderId);
        $query->orderBy('`id_saferpay_order` DESC');

        return Db::getInstance()->getValue($query);
    }

    /**
     * @param int $cartId
     * @return false|string|null
     */
    public function getIdByCartId(int $cartId)
    {
        $query = new DbQuery();
        $query->select('`id_saferpay_order`');
        $query->from('saferpay_order');
        $query->where('id_cart = ' . (int) $cartId);
        $query->orderBy('`id_saferpay_order` DESC');

        return Db::getInstance()->getValue($query);
    }

    /**
     * @param int $saferPayOrderId
     * @return false|string|null
     */
    public function getAssertIdBySaferPayOrderId(int $saferPayOrderId)
    {
        $query = new DbQuery();
        $query->select('`id_saferpay_assert`');
        $query->from('saferpay_assert');
        $query->where('id_saferPay_order = ' . (int) $saferPayOrderId);
        $query->orderBy('id_saferpay_assert DESC');

        return Db::getInstance()->getValue($query);
    }

    /**
     * @param int $saferPayOrderId
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    public function getOrderRefunds(int $saferPayOrderId): array
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('saferpay_order_refund');
        $query->where('id_saferPay_order = ' . (int) $saferPayOrderId);
        $query->orderBy('id_saferpay_order_refund DESC');

        return Db::getInstance()->executeS($query);
    }

    /**
     * @param int $saferpayOrderId
     * @return false|string|null
     */
    public function getPaymentBrandBySaferpayOrderId(int $saferpayOrderId)
    {
        $query = new DbQuery();
        $query->select('`brand`');
        $query->from('saferpay_assert');
        $query->where('id_saferpay_order = ' . (int) $saferpayOrderId);

        return Db::getInstance()->getValue($query);
    }
}
