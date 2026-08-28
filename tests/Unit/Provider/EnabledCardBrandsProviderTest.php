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
use Invertus\SaferPay\Provider\EnabledCardBrandsProvider;
use Invertus\SaferPay\Service\PaymentRestrictionValidation;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;

class EnabledCardBrandsProviderTest extends UnitTestCase
{
    public function testItReturnsEveryBrandWhenNoneIsRestricted()
    {
        $provider = new EnabledCardBrandsProvider($this->mockValidation(SaferPayConfig::CARD_BRANDS));

        $this->assertEquals(SaferPayConfig::CARD_BRANDS, $provider->get());
    }

    public function testItSkipsRestrictedBrandsAndKeepsTheConfiguredOrder()
    {
        $validBrands = [SaferPayConfig::PAYMENT_MASTERCARD, SaferPayConfig::PAYMENT_VISA];

        $provider = new EnabledCardBrandsProvider($this->mockValidation($validBrands));

        $this->assertEquals($validBrands, $provider->get());
    }

    public function testItReturnsNothingWhenEveryBrandIsRestricted()
    {
        $provider = new EnabledCardBrandsProvider($this->mockValidation([]));

        $this->assertEquals([], $provider->get());
    }

    private function mockValidation(array $validBrands)
    {
        $validationMock = $this
            ->getMockBuilder(PaymentRestrictionValidation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validationMock
            ->method('isPaymentMethodValid')
            ->willReturnCallback(function ($paymentMethod) use ($validBrands) {
                return in_array($paymentMethod, $validBrands, true);
            })
        ;

        return $validationMock;
    }
}
