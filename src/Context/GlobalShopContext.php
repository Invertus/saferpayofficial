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

namespace Invertus\SaferPay\Context;

use Invertus\SaferPay\Adapter\LegacyContext;

if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * Gets shop context data
 * NOTE: Done without interface because throwing error in the module
 */
class GlobalShopContext implements GlobalShopContextInterface
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
