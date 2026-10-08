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

namespace Invertus\SaferPay\Api\Http;

use Invertus\SaferPay\Exception\Api\SaferPayApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Plain cURL transport kept in the module namespace, so another module that ships
 * its own copy of a shared HTTP library cannot replace it.
 */
class CurlHttpClient
{
    const USER_AGENT = 'saferpayofficial-prestashop';

    const MAX_REDIRECTS = 10;

    /**
     * @param string $url
     * @param array $headers
     * @param array $query
     *
     * @return HttpResponse
     *
     * @throws SaferPayApiException
     */
    public function get(string $url, array $headers = [], array $query = []): HttpResponse
    {
        return $this->send($this->buildUrl($url, $query), $headers, null);
    }

    /**
     * @param string $url
     * @param array $headers
     * @param string $body
     *
     * @return HttpResponse
     *
     * @throws SaferPayApiException
     */
    public function post(string $url, array $headers = [], string $body = ''): HttpResponse
    {
        return $this->send($url, $headers, $body);
    }

    public function buildUrl(string $url, array $query): string
    {
        if (empty($query)) {
            return $url;
        }

        $separator = strpos($url, '?') === false ? '?' : '&';

        return $url . $separator . http_build_query($query, '', '&');
    }

    public function formatHeaders(array $headers): array
    {
        $formatted = [];

        foreach ($headers as $name => $value) {
            $formatted[] = $name . ': ' . $value;
        }

        $formatted[] = 'Expect:';

        return $formatted;
    }

    /**
     * @param string $url
     * @param array $headers
     * @param string|null $body null sends a GET request
     *
     * @return HttpResponse
     *
     * @throws SaferPayApiException
     */
    private function send(string $url, array $headers, $body): HttpResponse
    {
        $handle = curl_init();

        if ($handle === false) {
            throw new SaferPayApiException('Saferpay API request failed: cURL could not be initialised', SaferPayApiException::INITIALIZE);
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => self::MAX_REDIRECTS,
            CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
            CURLOPT_USERAGENT => self::USER_AGENT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_ENCODING => '',
        ];

        if ($body !== null) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($handle, $options);

        $rawBody = curl_exec($handle);
        $error = curl_error($handle);
        $errorNumber = curl_errno($handle);
        $code = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if ($rawBody === false || $errorNumber !== 0) {
            throw new SaferPayApiException(sprintf('Saferpay API request failed: cURL error %d %s', $errorNumber, $error), SaferPayApiException::INITIALIZE);
        }

        return new HttpResponse($code, (string) $rawBody);
    }
}
