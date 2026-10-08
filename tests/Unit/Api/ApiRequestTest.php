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

use Invertus\SaferPay\Api\ApiRequest;
use Invertus\SaferPay\Api\Http\CurlHttpClient;
use Invertus\SaferPay\Api\Http\HttpResponse;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Logger\LoggerInterface;
use PHPUnit\Framework\TestCase;

class ApiRequestTest extends TestCase
{
    public function testGetWithCredentialsSendsBasicAuthAndReturnsDecodedBody()
    {
        $client = $this->createMock(CurlHttpClient::class);
        $client->expects($this->once())
            ->method('get')
            ->with(
                'https://test.saferpay.com/api/rest/customers/1/terminals',
                $this->callback(function (array $headers) {
                    return $headers['Authorization'] === 'Basic ' . base64_encode('user:pass')
                        && $headers['Content-Type'] === 'application/json';
                })
            )
            ->willReturn(new HttpResponse(200, '{"Terminals":[{"TerminalId":"17771564"}]}'));

        $apiRequest = new ApiRequest($this->createMock(LoggerInterface::class), $client);

        $result = $apiRequest->getWithCredentials('rest/customers/1/terminals', 'user', 'pass', 'https://test.saferpay.com/api/');

        $this->assertSame('17771564', $result->Terminals[0]->TerminalId);
    }

    public function testPostWithCredentialsSendsJsonBody()
    {
        $client = $this->createMock(CurlHttpClient::class);
        $client->expects($this->once())
            ->method('post')
            ->with('https://test.saferpay.com/api/Payment/v1/Token', $this->isType('array'), '{"a":1}')
            ->willReturn(new HttpResponse(200, '{"Token":"abc"}'));

        $apiRequest = new ApiRequest($this->createMock(LoggerInterface::class), $client);

        $result = $apiRequest->postWithCredentials('Payment/v1/Token', 'user', 'pass', 'https://test.saferpay.com/api/', ['a' => 1]);

        $this->assertSame('abc', $result->Token);
    }

    public function testErrorResponseThrowsApiException()
    {
        $client = $this->createMock(CurlHttpClient::class);
        $client->method('get')->willReturn(new HttpResponse(401, '{"ErrorName":"AUTHENTICATION_FAILED"}'));

        $apiRequest = new ApiRequest($this->createMock(LoggerInterface::class), $client);

        $this->expectException(SaferPayApiException::class);

        $apiRequest->getWithCredentials('rest/customers/1/terminals', 'user', 'wrong', 'https://test.saferpay.com/api/');
    }

    public function testAlreadyCapturedResponseIsReturnedInsteadOfThrowing()
    {
        $client = $this->createMock(CurlHttpClient::class);
        $client->method('post')->willReturn(new HttpResponse(402, '{"ErrorName":"TRANSACTION_ALREADY_CAPTURED"}'));

        $apiRequest = new ApiRequest($this->createMock(LoggerInterface::class), $client);

        $result = $apiRequest->post('Payment/v1/Transaction/Capture', ['a' => 1]);

        $this->assertSame('TRANSACTION_ALREADY_CAPTURED', $result->ErrorName);
    }

    /**
     * Another module (e.g. clicktopay) can register its own copy of Unirest\Request
     * first. Saferpay calls must not go through that class at all.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRequestsDoNotUseAnotherModulesUnirestRequestClass()
    {
        eval('namespace Unirest; class Request { public function get() { throw new \LogicException("shadowed Unirest used"); } public function post() { throw new \LogicException("shadowed Unirest used"); } }');

        $client = $this->createMock(CurlHttpClient::class);
        $client->method('get')->willReturn(new HttpResponse(200, '{"Terminals":[]}'));

        $apiRequest = new ApiRequest($this->createMock(LoggerInterface::class), $client);

        $result = $apiRequest->getWithCredentials('rest/customers/1/terminals', 'user', 'pass', 'https://test.saferpay.com/api/');

        $this->assertSame([], $result->Terminals);
    }
}
