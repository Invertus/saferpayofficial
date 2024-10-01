<?php

namespace Invertus\SaferPay\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface SaferPayLogRepositoryInterface extends ReadOnlyCollectionRepositoryInterface
{
    public function prune(int $daysToKeep);
}