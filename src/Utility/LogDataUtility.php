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


namespace Invertus\SaferPay\Utility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LogDataUtility
{
    const CREDENTIAL_KEYS = ['authorization'];

    /**
     * @param array $data
     *
     * @return array
     */
    public static function removeCredentials(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), self::CREDENTIAL_KEYS, true)) {
                unset($data[$key]);

                continue;
            }

            if (is_array($value)) {
                $data[$key] = self::removeCredentials($value);
            }
        }

        return $data;
    }

    /**
     * @param string $json
     *
     * @return string
     */
    public static function removeCredentialsFromJson($json)
    {
        $data = json_decode($json, true);

        if (!is_array($data)) {
            return preg_replace('/("authorization"\s*:\s*)"[^"]*"?/i', '$1"[removed]"', $json);
        }

        $cleanData = self::removeCredentials($data);

        if ($cleanData === $data) {
            return $json;
        }

        return json_encode($cleanData);
    }
}
