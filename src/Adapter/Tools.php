<?php

namespace Invertus\SaferPay\Adapter;

use Context as PrestashopContext;
use Tools as PrestashopTools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Tools
{
    public function linkRewrite(string $str): string
    {
        return PrestashopTools::str2url($str);
    }

    public function redirectAdmin(string $controller): void
    {
        PrestashopTools::redirectAdmin($controller);
    }

    public function redirect(string $url): void
    {
        PrestashopTools::redirect($url);
    }

    public function isSubmit(string $form): bool
    {
        return PrestashopTools::isSubmit($form);
    }

    public function strtoupper(string $string): string
    {
        return PrestashopTools::strtoupper($string);
    }

    public function strtolower(string $string): string
    {
        return PrestashopTools::strtolower($string);
    }

    public function encrypt(string $string): string
    {
        return PrestashopTools::encrypt($string);
    }

    public function passwdGen(int $length = 8, string $flag = 'ALPHANUMERIC'): string
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