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
use Exception;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\Utility\ExceptionUtility;
use Unirest\Request;
use Unirest\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApiRequest
{
    const FILE_NAME = 'ApiRequest';

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * API Request Post Method.
     *
     * @param string $url
     * @param array $params
     * @return \stdClass|null
     * @throws Exception
     */
    public function post(string $url, array $params = []): ?\stdClass
    {
        try {
            $response = Request::post(
                $this->getBaseUrl() . $url,
                $this->getHeaders(),
                json_encode($params)
            );

            $this->logger->debug(sprintf('%s - POST response: %d', self::FILE_NAME, $response->code), [
                'context' => [
                    'uri' => $this->getBaseUrl() . $url,
                    'headers' => $this->getHeaders(),
                ],
                'request' => $params,
                'response' => $response->body,
            ]);

            $this->isValidResponse($response);

            return json_decode($response->raw_body);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * API Request Get Method.
     *
     * @param string $url
     * @param array $params
     * @return \stdClass|null
     * @throws Exception
     */
    public function get(string $url, array $params = []): ?\stdClass
    {
        $response = null;

        try {
            $response = Request::get(
                $this->getBaseUrl() . $url,
                $this->getHeaders(),
                $params
            );

            $this->logger->debug(sprintf('%s - GET response: %d', self::FILE_NAME, $response->code), [
                'context' => [
                    'uri' => $this->getBaseUrl() . $url,
                    'headers' => $this->getHeaders(),
                ],
                'request' => $params,
                'response' => $response->body,
            ]);

            $this->isValidResponse($response);

            return json_decode($response->raw_body);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), [
                'context' => [
                    'headers' => $this->getHeaders(),
                ],
                'request' => $params,
                'response' => json_decode($response->raw_body),
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            throw $exception;
        }
    }

    /**
     * @return array
     */
    private function getHeaders(): array
    {
        $username = Configuration::get(SaferPayConfig::USERNAME . SaferPayConfig::getConfigSuffix());
        $password = Configuration::get(SaferPayConfig::PASSWORD . SaferPayConfig::getConfigSuffix());

        $credentials = base64_encode("$username:$password");

        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Saferpay-ApiVersion' => SaferPayConfig::API_VERSION,
            'Saferpay-RequestId' => 'false',
            'Authorization' => "Basic $credentials",
        ];
    }

    /**
     * @return string
     */
    private function getBaseUrl(): string
    {
        return SaferPayConfig::getBaseApiUrl();
    }

    /**
     * @param Response $response
     * @return void
     * @throws SaferPayApiException
     */
    private function isValidResponse(Response $response): void
    {
        if (isset($response->body->ErrorName) && $response->body->ErrorName === SaferPayConfig::TRANSACTION_ALREADY_CAPTURED) {
            $this->logger->debug('Tried to apply state CAPTURED to already captured order', [
                'context' => [],
            ]);

            return;
        }

        if ($response->code >= 300) {
            $this->logger->error(sprintf('%s - API thrown code: %d', self::FILE_NAME, $response->code), [
                'context' => [],
                'response' => $response->body,
            ]);

            throw new SaferPayApiException(sprintf('Initialize API failed: %s', $response->raw_body), SaferPayApiException::INITIALIZE);
        }
    }
}
