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

namespace Invertus\SaferPay\Factory;

use Configuration;
use GuzzleHttp\Client;
use Invertus\SaferPay\Config\SaferPayConfig;

/**
 * Class HttpClientFactory
 *
 * @package Invertus\ViaBill\Factory
 */
class HttpClientFactory
{
    /**
     * Gets Guzzle HTTP Client.
     *
     * @return Client
     */
    public function getClient()
    {
        $username = Configuration::get(SaferPayConfig::USERNAME . SaferPayConfig::getConfigSuffix());
        $password = Configuration::get(SaferPayConfig::PASSWORD . SaferPayConfig::getConfigSuffix());
        $config = [
            'base_url' => SaferPayConfig::getBaseApiUrl(),
            'defaults' => [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'auth' => [
                    $username,
                    $password,
                ],
            ],
        ];

        return new Client($config);
    }
}
