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

use Invertus\SaferPay\Adapter\Configuration;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Service\CardAliasRegistrationGuard;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;

class CardAliasRegistrationGuardTest extends UnitTestCase
{
    public function testItRegistersWhenTheShopperOptedIn()
    {
        $guard = new CardAliasRegistrationGuard($this->mockConfiguration(true, true));

        $this->assertTrue($guard->shouldRegister(SaferPayConfig::CREDIT_CARD_OPTION_SAVE));
    }

    public function testItDoesNotRegisterWhenTheShopperPaysWithANewCardOnce()
    {
        $guard = new CardAliasRegistrationGuard($this->mockConfiguration(true, true));

        $this->assertFalse($guard->shouldRegister(SaferPayConfig::CREDIT_CARD_DONT_OPTION_SAVE));
    }

    public function testItDoesNotRegisterWhenTheShopperPaysWithASavedCard()
    {
        $guard = new CardAliasRegistrationGuard($this->mockConfiguration(true, true));

        $this->assertFalse($guard->shouldRegister(7));
    }

    public function testItDoesNotRegisterWhenCardSavingIsDisabled()
    {
        $guard = new CardAliasRegistrationGuard($this->mockConfiguration(false, true));

        $this->assertFalse($guard->shouldRegister(SaferPayConfig::CREDIT_CARD_OPTION_SAVE));
    }

    public function testItDoesNotRegisterWithoutABusinessLicence()
    {
        $guard = new CardAliasRegistrationGuard($this->mockConfiguration(true, false));

        $this->assertFalse($guard->shouldRegister(SaferPayConfig::CREDIT_CARD_OPTION_SAVE));
    }

    private function mockConfiguration($cardSavingEnabled, $hasBusinessLicence)
    {
        $configurationMock = $this
            ->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurationMock
            ->method('getAsBoolean')
            ->willReturnMap([
                [SaferPayConfig::CREDIT_CARD_SAVE, null, $cardSavingEnabled],
                [
                    SaferPayConfig::BUSINESS_LICENSE . SaferPayConfig::getConfigSuffix(),
                    null,
                    $hasBusinessLicence,
                ],
            ])
        ;

        return $configurationMock;
    }
}
