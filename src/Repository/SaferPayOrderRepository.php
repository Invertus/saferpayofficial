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
     *
     * @return SaferPayOrder
     */
    public function getByOrderId($orderId)
    {
        return new SaferPayOrder($this->getIdByOrderId($orderId));
    }

    public function getIdByOrderId($orderId)
    {
        $pOrderId = (int) pSQL($orderId);

        $query = new DbQuery();
        $query->select('`id_saferpay_order`');
        $query->from('saferpay_order');
        $query->where('id_order = "' . $pOrderId . '"');
        $query->orderBy('`id_saferpay_order` DESC');

        return Db::getInstance()->getValue($query);
    }

    public function getIdByCartId($cartId)
    {
        $pCartId = (int) pSQL($cartId);

        $query = new DbQuery();
        $query->select('`id_saferpay_order`');
        $query->from('saferpay_order');
        $query->where('id_cart = "' . $pCartId . '"');
        $query->orderBy('`id_saferpay_order` DESC');

        return Db::getInstance()->getValue($query);
    }
    public function getAssertIdBySaferPayOrderId($saferPayOrderId)
    {
        $pSaferPayOrderId = (int) pSQL($saferPayOrderId);

        $query = new DbQuery();
        $query->select('`id_saferpay_assert`');
        $query->from('saferpay_assert');
        $query->where('id_saferPay_order = "' . $pSaferPayOrderId . '"');
        $query->orderBy('id_saferpay_assert DESC');

        return Db::getInstance()->getValue($query);
    }

    /***
     * @param $saferPayOrderId
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    public function getOrderRefunds($saferPayOrderId)
    {
        $pSaferPayOrderId = (int) pSQL($saferPayOrderId);

        $query = new DbQuery();
        $query->select('*');
        $query->from('saferpay_order_refund');
        $query->where('id_saferPay_order = "' . $pSaferPayOrderId . '"');

        return Db::getInstance()->executeS($query);
    }

    public function getPaymentBrandBySaferpayOrderId($saferPayOrderId)
    {
        $pSaferPayOrderId = (int) pSQL($saferPayOrderId);

        $query = new DbQuery();
        $query->select('`brand`');
        $query->from('saferpay_assert');
        $query->where('id_saferpay_order = "' . $pSaferPayOrderId . '"');

        return Db::getInstance()->getValue($query);
    }
}
