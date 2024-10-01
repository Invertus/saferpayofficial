<?php

namespace Invertus\Saferpay\Context;

use Invertus\SaferPay\Adapter\LegacyContext as Context;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class GlobalShopContext implements GlobalShopContextInterface
{
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function getShopId()
    {
        return $this->context->getShopId();
    }

    public function getLanguageId()
    {
        return $this->context->getLanguageId();
    }

    public function getLanguageIso()
    {
        return $this->context->getLanguageIso();
    }

    public function getCurrencyIso()
    {
        return $this->context->getCurrencyIso();
    }

    public function getCurrency()
    {
        return $this->context->getCurrency();
    }

    public function getShopDomain()
    {
        return $this->context->getShopDomain();
    }

    public function getShopName()
    {
        return $this->context->getShopName();
    }

    public function isShopSingleShopContext()
    {
        return \Shop::getContext() === \Shop::CONTEXT_SHOP;
    }
}