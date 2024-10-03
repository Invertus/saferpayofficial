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
        return (int) $this->getContext()->shop->id;
    }

    public function getLanguageId()
    {
        return (int) $this->getContext()->language->id;
    }

    public function getLanguageIso()
    {
        return (string) $this->getContext()->language->iso_code ?: 'en';
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

    public function getAdminLink($controllerName, $params = [])
    {
        /* @noinspection PhpMethodParametersCountMismatchInspection - its valid for PS1.7 */
        return (string) Context::getContext()->link->getAdminLink($controllerName, true, [], $params);
    }

    public function getLanguageCode()
    {
        return (string) $this->getContext()->language->language_code ?: 'en-us';
    }

    public function getCurrencyIso()
    {
        if (!$this->getContext()->currency) {
            return '';
        }

        return (string) $this->getContext()->currency->iso_code;
    }

    public function getCountryIso()
    {
        if (!$this->getContext()->country) {
            return '';
        }

        return (string) $this->getContext()->country->iso_code;
    }

    public function getCurrency()
    {
        return $this->getContext()->currency;
    }

    public function getCustomerId()
    {
        if (!$this->getContext()->customer) {
            return 0;
        }

        return (int) $this->getContext()->customer->id;
    }

    public function isCustomerLoggedIn()
    {
        if (!$this->getContext()->customer) {
            return false;
        }

        return (bool) $this->getContext()->customer->isLogged();
    }

    public function getCustomerEmail()
    {
        if (!$this->getContext()->customer) {
            return '';
        }

        return $this->getContext()->customer->email;
    }

    public function getShopDomain()
    {
        return (string) $this->getContext()->shop->domain;
    }

    public function getShopName()
    {
        return (string) $this->getContext()->shop->name;
    }

    public function getController()
    {
        return $this->getContext()->controller;
    }

    /**
     * @throws \Throwable
     */
    public function setCurrentCart(\Cart $cart)
    {
        $this->getContext()->cart = $cart;
        $this->getContext()->cart->update();

        $this->getContext()->cookie->__set('id_cart', (int) $cart->id);
        $this->getContext()->cookie->write();
    }

    public function setCountry(\Country $country)
    {
        $this->getContext()->country = $country;
    }

    public function setCurrency(\Currency $currency)
    {
        $this->getContext()->currency = $currency;
    }

    public function getBaseLink($shopId = null, $ssl = null)
    {
        return (string) $this->getContext()->link->getBaseLink($shopId, $ssl);
    }

    public function getCartProducts()
    {
        $cart = $this->getContext()->cart;

        if (!$cart) {
            return [];
        }

        return $cart->getProducts();
    }

    public function getCart()
    {
        return isset($this->getContext()->cart) ? $this->getContext()->cart : null;
    }

    public function getShopThemeName()
    {
        return $this->getContext()->shop->theme_name;
    }

    public function updateCustomer(\Customer $customer)
    {
        $this->getContext()->updateCustomer($customer);
    }
}
