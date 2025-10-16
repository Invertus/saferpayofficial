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
    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return Context::getContext();
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return (int) $this->getContext()->shop->id;
    }

    /**
     * @return int
     */
    public function getLanguageId(): int
    {
        return (int) $this->getContext()->language->id;
    }

    /**
     * @return string
     */
    public function getLanguageIso(): string
    {
        return (string) $this->getContext()->language->iso_code ?: 'en';
    }

    /**
     * @return string
     */
    public function getCurrencyIsoCode(): string
    {
        return $this->getContext()->currency->iso_code;
    }

    /**
     * @return string
     */
    public function getCountryIsoCode(): string
    {
        return $this->getContext()->country->iso_code;
    }

    /**
     * @return int
     */
    public function getCountryId(): int
    {
        return $this->getContext()->country->id;
    }

    /**
     * @return int
     */
    public function getCurrencyId(): int
    {
        return $this->getContext()->currency->id;
    }

    /**
     * @return \Mobile_Detect
     */
    public function getMobileDetect()
    {
        return $this->getContext()->getMobileDetect();
    }

    /**
     * @return \Link
     */
    public function getLink()
    {
        return $this->getContext()->link;
    }

    /**
     * @return int
     */
    public function getDeviceDetect(): int
    {
        return (int) $this->getContext()->getDevice();
    }

    /**
     * @param string $controllerName
     * @param array $params
     * @return string
     */
    public function getAdminLink(string $controllerName, array $params = []): string
    {
        /* @noinspection PhpMethodParametersCountMismatchInspection - its valid for PS1.7 */
        return (string) Context::getContext()->link->getAdminLink($controllerName, true, [], $params);
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return (string) $this->getContext()->language->language_code ?: 'en-us';
    }

    /**
     * @return string
     */
    public function getCurrencyIso(): string
    {
        if (!$this->getContext()->currency) {
            return '';
        }

        return (string) $this->getContext()->currency->iso_code;
    }

    /**
     * @return string
     */
    public function getCountryIso(): string
    {
        if (!$this->getContext()->country) {
            return '';
        }

        return (string) $this->getContext()->country->iso_code;
    }

    /**
     * @return \Currency
     */
    public function getCurrency()
    {
        return $this->getContext()->currency;
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        if (!$this->getContext()->customer) {
            return 0;
        }

        return (int) $this->getContext()->customer->id;
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn(): bool
    {
        if (!$this->getContext()->customer) {
            return false;
        }

        return (bool) $this->getContext()->customer->isLogged();
    }

    /**
     * @return string
     */
    public function getCustomerEmail(): string
    {
        if (!$this->getContext()->customer) {
            return '';
        }

        return $this->getContext()->customer->email;
    }

    /**
     * @return string
     */
    public function getShopDomain(): string
    {
        return (string) $this->getContext()->shop->domain;
    }

    /**
     * @return string
     */
    public function getShopName(): string
    {
        return (string) $this->getContext()->shop->name;
    }

    /**
     * @return \Controller|\AdminController|\FrontController|null
     */
    public function getController()
    {
        return $this->getContext()->controller;
    }

    /**
     * @param \Cart $cart
     * @return void
     * @throws \Throwable
     */
    public function setCurrentCart(\Cart $cart): void
    {
        $this->getContext()->cart = $cart;
        $this->getContext()->cart->update();

        $this->getContext()->cookie->__set('id_cart', (int) $cart->id);
        $this->getContext()->cookie->write();
    }

    /**
     * @param \Country $country
     * @return void
     */
    public function setCountry(\Country $country): void
    {
        $this->getContext()->country = $country;
    }

    /**
     * @param \Currency $currency
     * @return void
     */
    public function setCurrency(\Currency $currency): void
    {
        $this->getContext()->currency = $currency;
    }

    /**
     * @param int|null $shopId
     * @param bool|null $ssl
     * @return string
     */
    public function getBaseLink($shopId = null, $ssl = null): string
    {
        return (string) $this->getContext()->link->getBaseLink($shopId, $ssl);
    }

    /**
     * @return array
     */
    public function getCartProducts(): array
    {
        $cart = $this->getContext()->cart;

        if (!$cart) {
            return [];
        }

        return $cart->getProducts();
    }

    /**
     * @return \Cart|null
     */
    public function getCart()
    {
        return isset($this->getContext()->cart) ? $this->getContext()->cart : null;
    }

    /**
     * @return string
     */
    public function getShopThemeName(): string
    {
        return $this->getContext()->shop->theme_name;
    }

    /**
     * @param \Customer $customer
     * @return void
     */
    public function updateCustomer(\Customer $customer): void
    {
        $this->getContext()->updateCustomer($customer);
    }
}
