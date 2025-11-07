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

declare(strict_types=1);

namespace Invertus\SaferPay\Tests\Unit\ServiceProvider;

use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\ServiceProvider\LeagueServiceContainerProvider;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;

class LeagueServiceContainerProviderTest extends UnitTestCase
{
    /**
     * @var LeagueServiceContainerProvider
     */
    private $provider;

    protected function setUp()
    {
        parent::setUp();
        $this->provider = new LeagueServiceContainerProvider();
    }

    /**
     * Test that container is built only once (singleton pattern)
     */
    public function testItReusesContainerInstance()
    {
        // Get service twice
        $service1 = $this->provider->getService(LoggerInterface::class);
        $service2 = $this->provider->getService(LoggerInterface::class);

        // Both should return the same instance from the same container
        self::assertSame($service1, $service2, 'Container should return same service instance');
    }

    /**
     * Test that extending services resets the container
     */
    public function testItResetsContainerWhenExtending()
    {
        // Get initial service
        $service1 = $this->provider->getService(LoggerInterface::class);

        // Extend the service with a mock
        $mockLogger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->provider->extend(LoggerInterface::class, $mockLogger);

        // Get service again - should be the extended mock
        $service2 = $this->provider->getService(LoggerInterface::class);

        self::assertNotSame($service1, $service2, 'Container should rebuild after extension');
        self::assertSame($mockLogger, $service2, 'Should return extended service');
    }

    /**
     * Test that services can be retrieved successfully
     */
    public function testItRetrievesServices()
    {
        $logger = $this->provider->getService(LoggerInterface::class);

        self::assertInstanceOf(LoggerInterface::class, $logger, 'Should return logger instance');
    }

    /**
     * Test extend method returns provider for chaining
     */
    public function testItReturnsProviderForChaining()
    {
        $mockLogger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $result = $this->provider->extend(LoggerInterface::class, $mockLogger);

        self::assertSame($this->provider, $result, 'Should return provider for method chaining');
    }
}
