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

use Invertus\SaferPay\Factory\HttpClientFactory;
use SaferPayLog;

class ApiRequest
{
    /**
     * @var HttpClientFactory
     */
    private $clientFactory;

    /**
     * ApiRequest constructor.
     * @param HttpClientFactory $clientFactory
     */
    public function __construct(HttpClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    /**
     * API Request Post Method.
     *
     * @param string $url
     * @param array $params
     * @return array |null
     * @throws \Exception
     */
    public function post($url, $params = [])
    {
        $response = null;

        try {
            $response = $this->clientFactory->getClient()->post($url, $params);

            return $response ?: [];
        } catch (\Exception $exception) {
            $logs = new SaferPayLog();
            $logs->message = $exception->getResponse()->getBody()->getContents();
            $logs->payload = $params['body'];
            $logs->add();
            throw $exception;
        }
    }

    /**
     * API Request Post Method.
     *
     * @param string $url
     * @param array $params
     * @return array |null
     * @throws \Exception
     */
    public function get($url, $params = [])
    {
        $response = null;

        try {
            $response = $this->clientFactory->getClient()->get($url, $params);

            return $response ?: [];
        } catch (\Exception $exception) {
            $logs = new SaferPayLog();
            $logs->message = $exception->getResponse() ? $exception->getResponse()->getBody()->getContents() : 'missing response';
            $logs->payload = json_encode($params);
            $logs->add();
            throw $exception;
        }
    }
}
