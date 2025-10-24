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
use Invertus\SaferPay\Exception\CouldNotAccessDatabase;
use SaferPayOrder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOrderRepository
{

    /**
     * Get SaferPay order by PrestaShop order ID
     *
     * @param int $orderId - PrestaShop order ID
     *
     * @return SaferPayOrder
     *
     * @throws CouldNotAccessDatabase - If database query fails or order not found
     */
    public function getByOrderId($orderId)
    {
        try {
            $saferPayOrderId = $this->getIdByOrderId($orderId);

            if (!$saferPayOrderId) {
                throw CouldNotAccessDatabase::entityNotFound(
                    'SaferPayOrder',
                    ['id_order' => $orderId]
                );
            }

            return new SaferPayOrder($saferPayOrderId);
        } catch (CouldNotAccessDatabase $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw CouldNotAccessDatabase::failedToQuery(
                'SaferPayOrder',
                ['id_order' => $orderId],
                $exception
            );
        }
    }

    /**
     * Get SaferPay order ID by PrestaShop order ID
     *
     * @param int $orderId - PrestaShop order ID
     *
     * @return int|false - SaferPay order ID or false if not found
     *
     * @throws CouldNotAccessDatabase - If database query fails
     */
    public function getIdByOrderId($orderId)
    {
        try {
            $query = new DbQuery();
            $query->select('`id_saferpay_order`');
            $query->from('saferpay_order');
            $query->where('id_order = ' . (int) $orderId);
            $query->orderBy('`id_saferpay_order` DESC');

            return Db::getInstance()->getValue($query);
        } catch (\Exception $exception) {
            throw CouldNotAccessDatabase::failedToQuery(
                'SaferPayOrder',
                ['id_order' => $orderId],
                $exception
            );
        }
    }

    /**
     * Get SaferPay order ID by cart ID
     *
     * @param int $cartId - PrestaShop cart ID
     *
     * @return int|false - SaferPay order ID or false if not found
     *
     * @throws CouldNotAccessDatabase - If database query fails
     */
    public function getIdByCartId($cartId)
    {
        try {
            $query = new DbQuery();
            $query->select('`id_saferpay_order`');
            $query->from('saferpay_order');
            $query->where('id_cart = ' . (int) $cartId);
            $query->orderBy('`id_saferpay_order` DESC');

            return Db::getInstance()->getValue($query);
        } catch (\Exception $exception) {
            throw CouldNotAccessDatabase::failedToQuery(
                'SaferPayOrder',
                ['id_cart' => $cartId],
                $exception
            );
        }
    }

    /**
     * Get SaferPay assert ID by SaferPay order ID
     *
     * @param int $saferPayOrderId - SaferPay order ID
     *
     * @return int|false - Assert ID or false if not found
     *
     * @throws CouldNotAccessDatabase - If database query fails
     */
    public function getAssertIdBySaferPayOrderId($saferPayOrderId)
    {
        try {
            $query = new DbQuery();
            $query->select('`id_saferpay_assert`');
            $query->from('saferpay_assert');
            $query->where('id_saferPay_order = ' . (int) $saferPayOrderId);
            $query->orderBy('id_saferpay_assert DESC');

            return Db::getInstance()->getValue($query);
        } catch (\Exception $exception) {
            throw CouldNotAccessDatabase::failedToQuery(
                'SaferPayAssert',
                ['id_saferPay_order' => $saferPayOrderId],
                $exception
            );
        }
    }

    /**
     * Get all refunds for a SaferPay order
     *
     * @param int $saferPayOrderId - SaferPay order ID
     *
     * @return array - Array of refund records
     *
     * @throws CouldNotAccessDatabase - If database query fails
     * @throws \PrestaShopDatabaseException
     */
    public function getOrderRefunds($saferPayOrderId)
    {
        try {
            $query = new DbQuery();
            $query->select('*');
            $query->from('saferpay_order_refund');
            $query->where('id_saferPay_order = ' . (int) $saferPayOrderId);
            $query->orderBy('id_saferpay_order_refund DESC');

            $result = Db::getInstance()->executeS($query);

            // Return empty array if no results instead of false
            return is_array($result) ? $result : [];
        } catch (\Exception $exception) {
            throw CouldNotAccessDatabase::failedToQuery(
                'SaferPayOrderRefund',
                ['id_saferPay_order' => $saferPayOrderId],
                $exception
            );
        }
    }

    /**
     * Get payment brand by SaferPay order ID
     *
     * @param int $saferpayOrderId - SaferPay order ID
     *
     * @return string|false - Payment brand name or false if not found
     *
     * @throws CouldNotAccessDatabase - If database query fails
     */
    public function getPaymentBrandBySaferpayOrderId($saferpayOrderId)
    {
        try {
            $query = new DbQuery();
            $query->select('`brand`');
            $query->from('saferpay_assert');
            $query->where('id_saferpay_order = ' . (int) $saferpayOrderId);

            return Db::getInstance()->getValue($query);
        } catch (\Exception $exception) {
            throw CouldNotAccessDatabase::failedToQuery(
                'SaferPayAssert',
                ['id_saferpay_order' => $saferpayOrderId],
                $exception
            );
        }
    }
}
