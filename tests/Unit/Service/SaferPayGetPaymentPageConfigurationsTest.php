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

namespace Invertus\SaferPay\Tests\Unit\Service;

use Invertus\SaferPay\Api\Request\GetPaymentPageConfigurationsService;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Request\GetPaymentPageConfigurations\GetPaymentPageConfigurationsRequest;
use Invertus\SaferPay\Service\SaferPayGetPaymentPageConfigurations;
use PHPUnit\Framework\TestCase;

class SaferPayGetPaymentPageConfigurationsTest extends TestCase
{
    public function testReturnsConfigurationNamesInResponseOrder()
    {
        $service = new SaferPayGetPaymentPageConfigurations(
            $this->mockApiService([
                $this->configuration('Test'),
                $this->configuration('Test_1'),
            ])
        );

        $this->assertSame(
            ['Test', 'Test_1'],
            $service->fetchConfigurationsWithCredentials('u', 'p', '278741', true)
        );
    }

    public function testSkipsConfigurationsWithoutAName()
    {
        $service = new SaferPayGetPaymentPageConfigurations(
            $this->mockApiService([
                $this->configuration('Test'),
                new \stdClass(),
                $this->configuration('Test_1'),
            ])
        );

        $this->assertSame(
            ['Test', 'Test_1'],
            $service->fetchConfigurationsWithCredentials('u', 'p', '278741', true)
        );
    }

    public function testIgnoresIsDefaultFlag()
    {
        $configuration = $this->configuration('Test');
        $configuration->IsDefault = true;

        $service = new SaferPayGetPaymentPageConfigurations(
            $this->mockApiService([$configuration])
        );

        $this->assertSame(
            ['Test'],
            $service->fetchConfigurationsWithCredentials('u', 'p', '278741', true)
        );
    }

    public function testReturnsEmptyArrayWhenAccountHasNoConfigurations()
    {
        $service = new SaferPayGetPaymentPageConfigurations($this->mockApiService([]));

        $this->assertSame(
            [],
            $service->fetchConfigurationsWithCredentials('u', 'p', '278741', true)
        );
    }

    public function testReturnsEmptyArrayWhenResponseHasNoConfigurationsProperty()
    {
        $service = new SaferPayGetPaymentPageConfigurations($this->mockApiService(null));

        $this->assertSame(
            [],
            $service->fetchConfigurationsWithCredentials('u', 'p', '278741', true)
        );
    }

    /**
     * @dataProvider baseUrlProvider
     */
    public function testSelectsBaseUrlFromTestMode($isTestMode, $expectedBaseUrl)
    {
        $response = new \stdClass();
        $response->Configurations = [$this->configuration('Test')];

        $apiService = $this->getMockBuilder(GetPaymentPageConfigurationsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPaymentPageConfigurations'])
            ->getMock();

        $apiService
            ->expects($this->once())
            ->method('getPaymentPageConfigurations')
            ->with(
                $this->isInstanceOf(GetPaymentPageConfigurationsRequest::class),
                'u',
                'p',
                $expectedBaseUrl
            )
            ->willReturn($response);

        $service = new SaferPayGetPaymentPageConfigurations($apiService);

        $service->fetchConfigurationsWithCredentials('u', 'p', '278741', $isTestMode);
    }

    public function baseUrlProvider()
    {
        return [
            'test mode' => [true, SaferPayConfig::TEST_API],
            'live mode' => [false, SaferPayConfig::API],
        ];
    }

    private function configuration($name)
    {
        $configuration = new \stdClass();
        $configuration->Name = $name;

        return $configuration;
    }

    private function mockApiService($configurations)
    {
        $response = new \stdClass();
        if ($configurations !== null) {
            $response->Configurations = $configurations;
        }

        $mock = $this->getMockBuilder(GetPaymentPageConfigurationsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPaymentPageConfigurations'])
            ->getMock();
        $mock->method('getPaymentPageConfigurations')->willReturn($response);

        return $mock;
    }
}
