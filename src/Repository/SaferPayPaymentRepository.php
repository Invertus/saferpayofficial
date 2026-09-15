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

class SaferPayPaymentRepository
{
    public function isActiveByName($paymentName)
    {
        $query = new DbQuery();
        $query->select('`active`');
        $query->from('saferpay_payment');
        $query->where('name = "' . pSQL($paymentName) . '"');

        return Db::getInstance()->getValue($query);
    }

    public function getIdByName($paymentName)
    {
        $query = new DbQuery();
        $query->select('`id_saferpay_payment`');
        $query->from('saferpay_payment');
        $query->where('name = "' . pSQL($paymentName) . '"');

        return Db::getInstance()->getValue($query);
    }

    public function isLogoEnabledByName($paymentName)
    {
        $query = new DbQuery();
        $query->select('`active`');
        $query->from('saferpay_logo');
        $query->where('name = "' . pSQL($paymentName) . '"');

        return Db::getInstance()->getValue($query);
    }

    public function getAllActiveLogosNames()
    {
        $query = new DbQuery();
        $query->select('name');
        $query->from('saferpay_logo');
        $query->where('active = "1"');

        $result = Db::getInstance()->executeS($query);

        if (!$result) {
            return [];
        }

        return $result;
    }

    public function getActivePaymentMethods()
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('saferpay_payment');
        $query->where('active = "1"');

        $result = Db::getInstance()->executeS($query);

        if (!$result) {
            return [];
        }

        return $result;
    }

    public function getActivePaymentMethodsNames()
    {
        $query = new DbQuery();
        $query->select('name');
        $query->from('saferpay_payment');
        $query->where('active = "1"');

        $result = Db::getInstance()->executeS($query);

        if (!$result) {
            return [];
        }

        return $result;
    }

    public function truncateTable()
    {
        $query = 'TRUNCATE TABLE ' . _DB_PREFIX_ . 'saferpay_payment;';

        return Db::getInstance()->execute($query);
    }

    public function insertPayment($data)
    {
        Db::getInstance()->insert('saferpay_payment', $data);
    }
}
