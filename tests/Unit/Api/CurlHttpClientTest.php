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

namespace Invertus\SaferPay\Tests\Unit\Api;

use Invertus\SaferPay\Api\Http\CurlHttpClient;
use Invertus\SaferPay\Api\Http\HttpResponse;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use PHPUnit\Framework\TestCase;

class CurlHttpClientTest extends TestCase
{
    public function testItAppendsQueryParametersToUrl()
    {
        $client = new CurlHttpClient();

        $this->assertSame('https://x.test/a', $client->buildUrl('https://x.test/a', []));
        $this->assertSame('https://x.test/a?b=1&c=d+e', $client->buildUrl('https://x.test/a', ['b' => 1, 'c' => 'd e']));
        $this->assertSame('https://x.test/a?z=0&b=1', $client->buildUrl('https://x.test/a?z=0', ['b' => 1]));
    }

    public function testItFormatsHeadersAndDisablesExpectContinue()
    {
        $client = new CurlHttpClient();

        $headers = $client->formatHeaders(['Accept' => 'application/json', 'Authorization' => 'Basic abc']);

        $this->assertContains('Accept: application/json', $headers);
        $this->assertContains('Authorization: Basic abc', $headers);
        $this->assertContains('Expect:', $headers);
    }

    public function testResponseDecodesJsonBody()
    {
        $response = new HttpResponse(402, '{"ErrorName":"TRANSACTION_ALREADY_CAPTURED"}');

        $this->assertSame(402, $response->getCode());
        $this->assertSame('TRANSACTION_ALREADY_CAPTURED', $response->getBody()->ErrorName);
        $this->assertSame('{"ErrorName":"TRANSACTION_ALREADY_CAPTURED"}', $response->getRawBody());
    }

    public function testResponseKeepsRawBodyWhenItIsNotJson()
    {
        $response = new HttpResponse(502, 'Bad Gateway');

        $this->assertSame('Bad Gateway', $response->getBody());
    }

    public function testTransportFailureThrowsApiException()
    {
        $client = new CurlHttpClient();

        $this->expectException(SaferPayApiException::class);

        $client->get('http://127.0.0.1:1/unreachable');
    }
}
