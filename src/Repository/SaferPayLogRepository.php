<?php

use Invertus\Knapsack\Collection;
use Invertus\SaferPay\Repository\CollectionRepository;
use Invertus\SaferPay\Repository\SaferPayLogRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class KlarnaPaymentLogRepository extends CollectionRepository implements SaferPayLogRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\SaferPayLog::class);
    }

    public function prune(int $daysToKeep)
    {
        Collection::from(
            $this->findAllInCollection()
                ->sqlWhere('DATEDIFF(NOW(),date_add) >= ' . $daysToKeep)
        )
            ->each(function (\KlarnaPaymentLog $log) {
                $log->delete();
            })
            ->realize();
    }
}