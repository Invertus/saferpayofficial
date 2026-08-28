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

namespace Invertus\SaferPay\Tests\Unit\Service\PaymentRestrictionValidation;

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Repository\SaferPayPaymentRepository;
use Invertus\SaferPay\Repository\SaferPayRestrictionRepository;
use Invertus\SaferPay\Service\PaymentRestrictionValidation\BasePaymentRestrictionValidation;
use Invertus\SaferPay\Service\SaferPayObtainPaymentMethods;
use Invertus\SaferPay\Service\SaferPayRestrictionCreator;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;

class BasePaymentRestrictionValidationTest extends UnitTestCase
{
    /**
     * @dataProvider getBasePaymentRestrictionValidationDataProvider
     */
    public function testIsValid(
        $paymentName,
        $paymentResults,
        $restrictionResults,
        $expectedResult
    ) {
        $basePaymentRestrictionValidation = new BasePaymentRestrictionValidation(
            $this->mockContext('AT', 'AUD'),
            $this->getPaymentRepositoryMock($paymentName, $paymentResults),
            $this->getRestrictionRepositoryMock($paymentName, $restrictionResults),
            $this->getObtainPaymentMethodsMock()
        );
        $this->assertEquals($expectedResult, $basePaymentRestrictionValidation->isValid($paymentName));
    }

    public function getBasePaymentRestrictionValidationDataProvider()
    {
        return [
            [
                'paymentName' => SaferPayConfig::PAYMENT_PAYPAL,
                'paymentResults' => true,
                'restrictionResults' => [
                    [0], //ALL COUNTRIES
                    [0], //ALL CURRENCIES
                ],
                'expectedResult' => true,
            ],
            [
                'paymentName' => SaferPayConfig::PAYMENT_PAYPAL,
                'paymentResults' => true,
                'restrictionResults' => [
                    [1,2,3], //ENGLISH, LITHUANIAN, GERMAN
                    [0], //ALL CURRENCIES
                ],
                'expectedResult' => true,
            ],
            [
                'paymentName' => SaferPayConfig::PAYMENT_PAYPAL,
                'paymentResults' => true,
                'restrictionResults' => [
                    [0], //ALL COUNTRIES
                    [1], //EUR
                ],
                'expectedResult' => true,
            ],
            [
                'paymentName' => SaferPayConfig::PAYMENT_PAYPAL,
                'paymentResults' => false, //FALSE
                'restrictionResults' => [
                    [0], //ALL COUNTRIES
                    [0], //ALL CURRENCIES
                ],
                'expectedResult' => false,
            ],
            [
                'paymentName' => SaferPayConfig::PAYMENT_PAYPAL,
                'paymentResults' => true,
                'restrictionResults' => [
                    [], //NO COUNTRIES
                    [0], //ALL CURRENCIES
                ],
                'expectedResult' => false,
            ],
            [
                'paymentName' => SaferPayConfig::PAYMENT_PAYPAL,
                'paymentResults' => true,
                'restrictionResults' => [
                    [0], //ALL COUNTRIES
                    [], //NO CURRENCIES
                ],
                'expectedResult' => false,
            ],
            [
                'paymentName' => SaferPayConfig::PAYMENT_PAYPAL,
                'paymentResults' => true,
                'restrictionResults' => [
                    [], //NO COUNTRIES
                    [], //NO CURRENCIES
                ],
                'expectedResult' => false,
            ],
        ];
    }

    /**
     * @dataProvider getGroupedCardsDataProvider
     */
    public function testItValidatesGroupedCardsThroughTheBrandsBehindThem(
        $enabledBrands,
        $enabledCountries,
        $expectedResult
    ) {
        $basePaymentRestrictionValidation = new BasePaymentRestrictionValidation(
            $this->mockContext('AT', 'AUD'),
            $this->getBrandPaymentRepositoryMock($enabledBrands),
            $this->getBrandRestrictionRepositoryMock($enabledCountries),
            $this->getObtainPaymentMethodsMock()
        );

        $this->assertEquals(
            $expectedResult,
            $basePaymentRestrictionValidation->isValid(SaferPayConfig::PAYMENT_CARDS)
        );
    }

    public function getGroupedCardsDataProvider()
    {
        return [
            [
                'enabledBrands' => [SaferPayConfig::PAYMENT_VISA],
                'enabledCountries' => [0], //ALL COUNTRIES
                'expectedResult' => true,
            ],
            [
                'enabledBrands' => SaferPayConfig::CARD_BRANDS,
                'enabledCountries' => [0], //ALL COUNTRIES
                'expectedResult' => true,
            ],
            [
                'enabledBrands' => [], //EVERY BRAND DISABLED
                'enabledCountries' => [0], //ALL COUNTRIES
                'expectedResult' => false,
            ],
            [
                'enabledBrands' => SaferPayConfig::CARD_BRANDS,
                'enabledCountries' => [], //NO COUNTRIES
                'expectedResult' => false,
            ],
        ];
    }

    private function getBrandPaymentRepositoryMock(array $enabledBrands)
    {
        $paymentRepositoryMock = $this
            ->getMockBuilder(SaferPayPaymentRepository::class)
            ->getMock();

        $paymentRepositoryMock
            ->method('isActiveByName')
            ->willReturnCallback(function ($paymentName) use ($enabledBrands) {
                return in_array($paymentName, $enabledBrands, true);
            })
        ;

        return $paymentRepositoryMock;
    }

    private function getBrandRestrictionRepositoryMock(array $enabledCountries)
    {
        $restrictionMock = $this
            ->getMockBuilder(SaferPayRestrictionRepository::class)
            ->getMock();

        $restrictionMock
            ->method('getSelectedIdsByName')
            ->willReturnCallback(function ($paymentName, $restrictionType) use ($enabledCountries) {
                if ($restrictionType === SaferPayRestrictionCreator::RESTRICTION_COUNTRY) {
                    return $enabledCountries;
                }

                return [0]; //ALL CURRENCIES
            })
        ;

        return $restrictionMock;
    }

    private function getObtainPaymentMethodsMock()
    {
        return $this
            ->getMockBuilder(SaferPayObtainPaymentMethods::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
