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
use Invertus\SaferPay\Service\PaymentRestrictionValidation\KlarnaPaymentRestrictionValidation;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;

class KlarnaPaymentRestrictionValidationTest extends UnitTestCase
{
    /**
     * @dataProvider getKlarnaPaymentRestrictionValidationDataProvider
     */
    public function testIsValid($context, $expectedResult)
    {
        $klarnaValidation = new KlarnaPaymentRestrictionValidation($context);
        $this->assertEquals($expectedResult, $klarnaValidation->isValid(SaferPayConfig::PAYMENT_KLARNA));
    }

    /**
     * @dataProvider getKlarnaPaymentRestrictionSupportedDataProvider
     */
    public function testIsSupported($context, $paymentName, $expectedResult)
    {
        $klarnaValidation = new KlarnaPaymentRestrictionValidation($context);
        $this->assertEquals($expectedResult, $klarnaValidation->supports($paymentName));
    }

    public function getKlarnaPaymentRestrictionValidationDataProvider()
    {
        return [
            [
                'context' => $this->mockContext('AT', 'AUD'),
                'expectedResult' => true,
            ],
            [
                'context' => $this->mockContext('DK', 'CAD'),
                'expectedResult' => true,
            ],
            [
                'context' => $this->mockContext('DE', 'EUR'),
                'expectedResult' => true,
            ],
            [
                'context' => $this->mockContext('LT', 'USD'),
                'expectedResult' => false,
            ],
            [
                'context' => $this->mockContext('DE', 'LT'),
                'expectedResult' => false,
            ],
            [
                'context' => $this->mockContext('IT', 'EUR'),
                'expectedResult' => true,
            ],
            [
                'context' => $this->mockContext('FR', 'USD'),
                'expectedResult' => true,
            ],
        ];
    }

    public function getKlarnaPaymentRestrictionSupportedDataProvider()
    {
        return [
            [
                'context' => $this->mockContext('AT', 'AUD'),
                'paymentName' => SaferPayConfig::PAYMENT_KLARNA,
                'expectedResult' => true,
            ],
            [
                'context' => $this->mockContext('AT', 'AUD'),
                'paymentName' => SaferPayConfig::PAYMENT_VISA,
                'expectedResult' => false,
            ],
        ];
    }
}
