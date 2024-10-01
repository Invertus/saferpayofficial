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

namespace Invertus\SaferPay\Adapter;

use Context;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LegacyContext
{
    public function getContext()
    {
        return Context::getContext();
    }

    public function getShopId()
    {
        return $this->getContext()->shop->id;
    }

    public function getCurrencyIsoCode()
    {
        return $this->getContext()->currency->iso_code;
    }

    public function getCountryIsoCode()
    {
        return $this->getContext()->country->iso_code;
    }

    public function getCountryId()
    {
        return $this->getContext()->country->id;
    }

    public function getCurrencyId()
    {
        return $this->getContext()->currency->id;
    }

    public function getMobileDetect()
    {
        return $this->getContext()->getMobileDetect();
    }

    public function getLink()
    {
        return $this->getContext()->link;
    }

    /**
     * @return int
     */
    public function getDeviceDetect()
    {
        return (int) $this->getContext()->getDevice();
    }

    public function getAdminLink($controllerName, array $params = []): string
    {
        /* @noinspection PhpMethodParametersCountMismatchInspection - its valid for PS1.7 */
        return (string) Context::getContext()->link->getAdminLink($controllerName, true, [], $params);
    }
}
