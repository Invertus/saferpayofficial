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

namespace Invertus\SaferPay\Api;

use Configuration;
use Invertus\SaferPay\Config\SaferPayConfig;
use SaferPayLog;
use Unirest\Request;
use Unirest\Response;

class ApiRequest
{
    /**
     * API Request Post Method.
     *
     * @param string $url
     * @param array $params
     * @return array |null
     * @throws \Exception
     */
    public function post($url, $params)
    {
        try {
            $response = Request::post(
                $this->getBaseUrl() . $url,
                $this->getHeaders(),
                $params
            );

            return json_decode($response->raw_body);
        } catch (\Exception $exception) {
            $logs = new SaferPayLog();
            $logs->message = $exception->getResponse() ? $exception->getResponse()->getBody()->getContents() : 'missing response';
            $logs->payload = $params['body'];
            $logs->add();
            throw $exception;
        }
    }

    /**
     * API Request Get Method.
     *
     * @param string $url
     * @param array $params
     * @return array |null
     * @throws \Exception
     */
    public function get($url, $params = [])
    {

        try {
            $response = Request::get(
                $this->getBaseUrl() . $url,
                $this->getHeaders(),
                $params
            );

            #TODO: Add validation http codes and handlers
            //$this->isValidResponse($response);

            return json_decode($response->raw_body);
        } catch (\Exception $exception) {
            $logs = new SaferPayLog();
            $logs->message = $exception->getResponse() ? $exception->getResponse()->getBody()->getContents() : 'missing response';
            $logs->payload = json_encode($params);
            $logs->add();
            throw $exception;
        }
    }

    private function getHeaders()
    {
        $username = Configuration::get(SaferPayConfig::USERNAME . SaferPayConfig::getConfigSuffix());
        $password = Configuration::get(SaferPayConfig::PASSWORD . SaferPayConfig::getConfigSuffix());
        $credentials = base64_encode("$username:$password");

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Saferpay-ApiVersion' => SaferPayConfig::API_VERSION,
            'Saferpay-RequestId' => 'false',
            'Authorization' => "Basic $credentials"
        ];

        return $headers;
    }

    private function getBaseUrl()
    {
        return SaferPayConfig::getBaseApiUrl();
    }

    private function isValidResponse(Response $response)
    {
        #TODO: add new exception throw
        return 1;
    }

}

