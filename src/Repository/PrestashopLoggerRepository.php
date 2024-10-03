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

use Invertus\Knapsack\Collection;
use Invertus\SaferPay\Logger\Logger;
use Invertus\SaferPay\Utility\VersionUtility;
use Invertus\SaferPay\Repository\CollectionRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestashopLoggerRepository extends CollectionRepository implements PrestashopLoggerRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\PrestaShopLogger::class);
    }

    /** {@inheritDoc} */
    public function getLogIdByObjectId($objectId, $shopId)
    {
        $query = new \DbQuery();

        $query
            ->select('l.id_log')
            ->from('log', 'l')
            ->where('l.object_id = "' . pSQL($objectId) . '"')
            ->orderBy('l.id_log DESC');

        if (VersionUtility::isPsVersionGreaterOrEqualTo('1.7.8.0')) {
            $query->where('l.id_shop = ' . (int) $shopId);
        }

        $logId = \Db::getInstance()->getValue($query);

        return (int) $logId ?: null;
    }

    public function prune($daysToKeep)
    {
        Collection::from(
            $this->findAllInCollection()
                ->sqlWhere('DATEDIFF(NOW(),date_add) >= ' . $daysToKeep)
                ->where('object_type', '=', Logger::LOG_OBJECT_TYPE)
        )
            ->each(function (\PrestaShopLogger $log) {
                $log->delete();
            })
            ->realize();
    }
}