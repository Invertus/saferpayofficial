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

namespace Invertus\SaferPay\Service;

use Configuration;
use Exception;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Logger\LoggerInterface;
use Unirest\Request;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayTerminalService
{
    const FILE_NAME = 'SaferPayTerminalService';

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Fetch available terminals from SaferPay REST API
     *
     * @param string|null $customerId Optional customer ID, if not provided uses config
     * @return array Array of terminals with TerminalId and Description
     */
    public function getAvailableTerminals($customerId = null)
    {
        try {
            $customerId = $customerId ?: Configuration::get(
                SaferPayConfig::CUSTOMER_ID . SaferPayConfig::getConfigSuffix()
            );

            if (empty($customerId)) {
                $this->logger->debug(sprintf('%s - Customer ID not configured', self::FILE_NAME));
                return [];
            }

            $url = $this->getBaseRestUrl() . '/api/rest/customers/' . $customerId . '/terminals';
            $headers = $this->getHeaders();

            $this->logger->debug(sprintf('%s - Fetching terminals from: %s', self::FILE_NAME, $url));

            $response = Request::get($url, $headers);

            $this->logger->debug(sprintf('%s - Terminal API response: %d', self::FILE_NAME, $response->code), [
                'context' => [
                    'uri' => $url,
                ],
                'response' => $response->body,
            ]);

            if ($response->code >= 300) {
                $this->logger->error(sprintf('%s - Failed to fetch terminals: %d', self::FILE_NAME, $response->code), [
                    'context' => [],
                    'response' => $response->body,
                ]);
                return [];
            }

            return $this->parseTerminalsResponse($response->body);
        } catch (Exception $exception) {
            $this->logger->error(sprintf('%s - Exception: %s', self::FILE_NAME, $exception->getMessage()), [
                'context' => [],
                'exception' => $exception,
            ]);
            return [];
        }
    }

    /**
     * Validate if a terminal ID exists in available terminals
     *
     * @param string $terminalId
     * @return bool
     */
    public function isValidTerminal($terminalId)
    {
        if (empty($terminalId)) {
            return false;
        }

        $terminals = $this->getAvailableTerminals();

        foreach ($terminals as $terminal) {
            if ($terminal['TerminalId'] === $terminalId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse terminals response from API
     *
     * @param mixed $responseBody
     * @return array
     */
    private function parseTerminalsResponse($responseBody)
    {
        $terminals = [];

        if (empty($responseBody)) {
            return $terminals;
        }

        // Convert stdClass to array if necessary
        if (is_object($responseBody)) {
            $responseBody = json_decode(json_encode($responseBody), true);
        }

        // Response has a 'Terminals' property containing the array
        $terminalsList = $responseBody['Terminals'] ?? $responseBody;

        if (is_array($terminalsList)) {
            foreach ($terminalsList as $terminal) {
                // Handle both array and object formats
                $terminalId = is_array($terminal) ? ($terminal['TerminalId'] ?? null) : ($terminal->TerminalId ?? null);
                $description = is_array($terminal) ? ($terminal['Description'] ?? null) : ($terminal->Description ?? null);

                if ($terminalId) {
                    $terminals[] = [
                        'TerminalId' => $terminalId,
                        'Description' => $description ?: $terminalId,
                    ];
                }
            }
        }

        $this->logger->debug(sprintf('%s - Parsed %d terminals', self::FILE_NAME, count($terminals)));

        return $terminals;
    }

    /**
     * Get REST API base URL
     *
     * @return string
     */
    private function getBaseRestUrl()
    {
        return SaferPayConfig::getBaseUrl();
    }

    /**
     * Get headers for REST API request
     *
     * @return array
     */
    private function getHeaders()
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
}
