<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Klarna Bank AB www.klarna.com
 * @copyright Copyright (c) permanent, Klarna Bank AB
 * @license   ISC
 *
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Klarna Bank AB
 */

namespace Invertus\Saferpay\Context;

use Invertus\SaferPay\Adapter\LegacyContext;

if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * Gets shop context data
 * NOTE: Done without interface because throwing error in the module
 */
class GlobalShopContext
{
    private $context;

    public function __construct(LegacyContext $context)
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