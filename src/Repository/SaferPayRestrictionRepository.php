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
use Invertus\SaferPay\Service\SaferPayRestrictionCreator;

class SaferPayRestrictionRepository
{
    public function getRestrictionIdsByName($paymentName, $restrictionType)
    {
        switch ($restrictionType) {
            case SaferPayRestrictionCreator::RESTRICTION_COUNTRY:
                $query = new DbQuery();
                $query->select('`id_saferpay_country` as id');
                $query->from('saferpay_country');
                $query->where('payment_name = "' . pSQL($paymentName) . '"');
                break;
            case SaferPayRestrictionCreator::RESTRICTION_CURRENCY:
                $query = new DbQuery();
                $query->select('`id_saferpay_currency` as id');
                $query->from('saferpay_currency');
                $query->where('payment_name = "' . pSQL($paymentName) . '"');
                break;
            default:
                return false;
        }

        return Db::getInstance()->executeS($query);
    }

    public function getSelectedIdsByName($paymentName, $restrictionType)
    {
        switch ($restrictionType) {
            case SaferPayRestrictionCreator::RESTRICTION_COUNTRY:
                $query = new DbQuery();
                $query->select('`id_country`');
                $query->from('saferpay_country');
                $query->where('payment_name = "' . pSQL($paymentName) . '"');
                $db = Db::getInstance();
                $resource = $db->query($query);
                $result = [];
                while ($row = $db->nextRow($resource)) {
                    $result[] = $row['id_country'];
                }
                break;
            case SaferPayRestrictionCreator::RESTRICTION_CURRENCY:
                $query = new DbQuery();
                $query->select('`id_currency`');
                $query->from('saferpay_currency');
                $query->where('payment_name = "' . pSQL($paymentName) . '"');
                $db = Db::getInstance();
                $resource = $db->query($query);
                $result = [];
                while ($row = $db->nextRow($resource)) {
                    $result[] = $row['id_currency'];
                }
                break;
            default:
                return false;
        }

        return $result;
    }
}
