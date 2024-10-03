<?php

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