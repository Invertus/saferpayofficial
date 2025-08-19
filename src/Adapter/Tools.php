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

use Context as PrestashopContext;
use Invertus\SaferPay\Utility\VersionUtility;
use Tools as PrestashopTools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Tools
{
    public function linkRewrite($str)
    {
        return PrestashopTools::str2url($str);
    }

    public function redirectAdmin($controller)
    {
        PrestashopTools::redirectAdmin($controller);
    }

    public function redirect($url)
    {
        PrestashopTools::redirect($url);
    }

    public function isSubmit($form)
    {
        return PrestashopTools::isSubmit($form);
    }

    public function strtoupper($string)
    {
        return PrestashopTools::strtoupper($string);
    }

    public function strtolower($string)
    {
        return PrestashopTools::strtolower($string);
    }

    public function encrypt($string)
    {
        return PrestashopTools::encrypt($string);
    }

    public function passwdGen($length = 8, $flag = 'ALPHANUMERIC')
    {
        return PrestashopTools::passwdGen($length, $flag);
    }

    public function fileGetContents(
        $url,
        $useIncludePath = false,
        $steamContext = null,
        $curlTimeout = 5,
        $fallback = false
    ) {
        return PrestashopTools::file_get_contents($url, $useIncludePath, $steamContext, $curlTimeout, $fallback);
    }

    public static function replaceAccentedChars($string)
    {
        return PrestashopTools::replaceAccentedChars($string);
    }

    /**
     * @param string $value
     * @param string|false $defaultValue
     *
     * @return mixed Value
     */
    public function getValue($value, $defaultValue = false)
    {
        $toolsValue = PrestashopTools::getValue($value, $defaultValue);

        return is_null($toolsValue) || $toolsValue === '' || $toolsValue === 'null' ? null : $toolsValue;
    }

    /**
     * @param string $value
     * @param string|false $defaultValue
     *
     * @return bool
     */
    public function getValueAsBoolean($value, $defaultValue = false)
    {
        $result = $this->getValue($value, $defaultValue);

        if (in_array($result, ['false', '0', null, false, 0], true)) {
            return false;
        }

        return (bool) $result;
    }

    /**
     * @param string $value
     * @param string|false $defaultValue
     *
     * @return bool
     */
    public function getValueAsInteger($value, $defaultValue = false)
    {
        $result = $this->getValue($value, $defaultValue);

        if (in_array($result, ['false', '0', null, false, 0], true)) {
            return 0;
        }

        return (int) $result;
    }

    public function getAllValues()
    {
        return PrestashopTools::getAllValues();
    }

    public function getValueAsInt($value, $defaultValue = 0)
    {
        return (int) PrestashopTools::getValue($value, $defaultValue);
    }

    public function getShopDomain()
    {
        return PrestashopTools::getShopDomain();
    }

    public function displayPrice($price, $currency = null, $no_utf8 = false, PrestashopContext $context = null)
    {
        if (VersionUtility::isPsVersionGreaterOrEqualTo('9.0.0')) {
            $context = $context ?: PrestashopContext::getContext();
            $currency = $currency ?: $context->currency;

            $contextLocale = PrestashopTools::getContextLocale($currency);

            $currencyCode = is_array($currency) ? $currency['iso_code'] : $currency->iso_code;

            return $contextLocale->formatPrice($price, $currencyCode);
        }

        return PrestashopTools::displayPrice($price, $currency, $no_utf8, $context);
    }

    public function ps_round($value, $precision = 0, $round_mode = null)
    {
        return PrestashopTools::ps_round($value, $precision, $round_mode);
    }

    public function getToken($page = true, PrestashopContext $context = null)
    {
        return PrestashopTools::getToken($page, $context);
    }

    public function convertPriceFull($amount, \Currency $currency_from = null, \Currency $currency_to = null)
    {
        return PrestashopTools::convertPriceFull($amount, $currency_from, $currency_to);
    }
}
