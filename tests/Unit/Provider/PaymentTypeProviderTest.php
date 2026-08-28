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

namespace Invertus\SaferPay\Tests\Unit\Provider;

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Enum\PaymentType;
use Invertus\SaferPay\Provider\PaymentTypeProvider;
use Invertus\SaferPay\Repository\SaferPayFieldRepository;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;

class PaymentTypeProviderTest extends UnitTestCase
{
    public function testItResolvesFieldsFromTheFieldTokenWhateverBrandCameBack()
    {
        $provider = $this->mockProviderExpectingNoBrandLookup();

        $this->assertEquals(
            PaymentType::HOSTED_IFRAME,
            $provider->getForReturn(SaferPayConfig::PAYMENT_MASTERCARD, 'field-token', false)
        );
    }

    public function testItResolvesFieldsForASavedCard()
    {
        $provider = $this->mockProviderExpectingNoBrandLookup();

        $this->assertEquals(
            PaymentType::HOSTED_IFRAME,
            $provider->getForReturn(SaferPayConfig::PAYMENT_CARDS, null, true)
        );
    }

    public function testItFallsBackToTheBrandLookupWhenNeitherIsPresent()
    {
        $provider = $this->mockProvider();

        $provider
            ->expects($this->once())
            ->method('get')
            ->with(SaferPayConfig::PAYMENT_VISA)
            ->willReturn(PaymentType::BASIC)
        ;

        $this->assertEquals(
            PaymentType::BASIC,
            $provider->getForReturn(SaferPayConfig::PAYMENT_VISA, null, false)
        );
    }

    private function mockProviderExpectingNoBrandLookup()
    {
        $provider = $this->mockProvider();

        $provider
            ->expects($this->never())
            ->method('get')
        ;

        return $provider;
    }

    /**
     * get() reads the module configuration directly, so it is stubbed out to keep getForReturn
     * testable without a configured shop.
     */
    private function mockProvider()
    {
        $fieldRepositoryMock = $this
            ->getMockBuilder(SaferPayFieldRepository::class)
            ->getMock();

        return $this
            ->getMockBuilder(PaymentTypeProvider::class)
            ->setConstructorArgs([$fieldRepositoryMock])
            ->setMethods(['get'])
            ->getMock();
    }
}
