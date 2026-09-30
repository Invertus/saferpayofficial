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

use Invertus\SaferPay\Adapter\Configuration;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Enum\PaymentType;
use Invertus\SaferPay\Provider\PaymentTypeProvider;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;

class PaymentTypeProviderTest extends UnitTestCase
{
    public function testItResolvesFieldsFromTheFieldTokenWhateverBrandCameBack()
    {
        $provider = $this->makeProvider($this->fieldsDisabledConfiguration());

        $this->assertEquals(
            PaymentType::HOSTED_IFRAME,
            $provider->getForReturn(SaferPayConfig::PAYMENT_MASTERCARD, 'field-token', false)
        );
    }

    public function testItResolvesFieldsForASavedCard()
    {
        $provider = $this->makeProvider($this->fieldsDisabledConfiguration());

        $this->assertEquals(
            PaymentType::HOSTED_IFRAME,
            $provider->getForReturn(SaferPayConfig::PAYMENT_CARDS, null, true)
        );
    }

    public function testItFallsBackToTheConfiguredModeWhenNeitherIsPresent()
    {
        $provider = $this->makeProvider($this->fieldsDisabledConfiguration());

        $this->assertEquals(
            PaymentType::BASIC,
            $provider->getForReturn(SaferPayConfig::PAYMENT_VISA, null, false)
        );
    }

    public function testItResolvesFieldsForTheGroupedCardsOption()
    {
        $provider = $this->makeProvider($this->fieldsEnabledConfiguration());

        $this->assertEquals(
            PaymentType::HOSTED_IFRAME,
            $provider->get(SaferPayConfig::PAYMENT_CARDS)
        );
    }

    public function testItResolvesFieldsForAnIndividualCardBrand()
    {
        $provider = $this->makeProvider($this->fieldsEnabledConfiguration());

        $this->assertEquals(
            PaymentType::HOSTED_IFRAME,
            $provider->get(SaferPayConfig::PAYMENT_VISA)
        );
    }

    public function testANonCardMethodNeverUsesFields()
    {
        $provider = $this->makeProvider($this->fieldsEnabledConfiguration());

        $this->assertEquals(
            PaymentType::BASIC,
            $provider->get(SaferPayConfig::PAYMENT_TWINT)
        );
    }

    public function testItFallsBackToThePaymentPageWithoutABusinessLicense()
    {
        $provider = $this->makeProvider($this->fieldsEnabledConfiguration([
            SaferPayConfig::BUSINESS_LICENSE => null,
        ]));

        $this->assertEquals(
            PaymentType::BASIC,
            $provider->get(SaferPayConfig::PAYMENT_CARDS)
        );
    }

    public function testItFallsBackToThePaymentPageWhenFieldsAreTurnedOff()
    {
        $provider = $this->makeProvider($this->fieldsEnabledConfiguration([
            SaferPayConfig::SAFERPAY_USE_FIELDS => null,
        ]));

        $this->assertEquals(
            PaymentType::BASIC,
            $provider->get(SaferPayConfig::PAYMENT_CARDS)
        );
    }

    public function testItFallsBackToThePaymentPageWhenTheAccessTokenIsMissing()
    {
        $provider = $this->makeProvider($this->fieldsEnabledConfiguration([
            SaferPayConfig::FIELDS_ACCESS_TOKEN => null,
        ]));

        $this->assertEquals(
            PaymentType::BASIC,
            $provider->get(SaferPayConfig::PAYMENT_CARDS)
        );
    }

    public function testTestModeReadsTheTestSuffixedConfiguration()
    {
        $provider = $this->makeProvider($this->makeConfiguration([
            SaferPayConfig::TEST_MODE => '1',
            SaferPayConfig::SAFERPAY_USE_FIELDS => '1',
            SaferPayConfig::BUSINESS_LICENSE . SaferPayConfig::TEST_SUFFIX => '1',
            SaferPayConfig::FIELDS_ACCESS_TOKEN . SaferPayConfig::TEST_SUFFIX => 'test-token',
        ]));

        $this->assertEquals(
            PaymentType::HOSTED_IFRAME,
            $provider->get(SaferPayConfig::PAYMENT_CARDS)
        );
    }

    private function makeProvider(Configuration $configuration)
    {
        return new PaymentTypeProvider($configuration);
    }

    private function fieldsEnabledConfiguration(array $overrides = [])
    {
        return $this->makeConfiguration(array_merge([
            SaferPayConfig::TEST_MODE => null,
            SaferPayConfig::BUSINESS_LICENSE => '1',
            SaferPayConfig::SAFERPAY_USE_FIELDS => '1',
            SaferPayConfig::FIELDS_ACCESS_TOKEN => 'access-token',
        ], $overrides));
    }

    private function fieldsDisabledConfiguration()
    {
        return $this->fieldsEnabledConfiguration([
            SaferPayConfig::SAFERPAY_USE_FIELDS => null,
        ]);
    }

    private function makeConfiguration(array $values)
    {
        $configuration = $this
            ->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getAsBoolean'])
            ->getMock();

        $configuration
            ->method('get')
            ->willReturnCallback(function ($id) use ($values) {
                return isset($values[$id]) ? $values[$id] : null;
            });

        $configuration
            ->method('getAsBoolean')
            ->willReturnCallback(function ($id) use ($values) {
                return !empty($values[$id]);
            });

        return $configuration;
    }
}
