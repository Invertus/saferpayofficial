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
use Invertus\SaferPay\Service\PaymentRestrictionValidation\WlcryptopaymentsPaymentRestrictionValidation;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;

class WlcryptopaymentsPaymentRestrictionValidationTest extends UnitTestCase
{
    /**
     * @dataProvider getWlcryptopaymentsPaymentRestrictionValidationDataProvider
     */
    public function testIsValid($context, $expectedResult)
    {
        $wlcryptopaymentsValidation = new WlcryptopaymentsPaymentRestrictionValidation($context);
        $this->assertEquals($expectedResult, $wlcryptopaymentsValidation->isValid(SaferPayConfig::PAYMENT_WLCRYPTOPAYMENTS));
    }

    /**
     * @dataProvider getWlcryptopaymentsPaymentRestrictionSupportedDataProvider
     */
    public function testIsSupported($context, $paymentName, $expectedResult)
    {
        $wlcryptopaymentsValidation = new WlcryptopaymentsPaymentRestrictionValidation($context);
        $this->assertEquals($expectedResult, $wlcryptopaymentsValidation->supports($paymentName));
    }

    public function getWlcryptopaymentsPaymentRestrictionValidationDataProvider()
    {
        return [
            [
                'context' => $this->mockContext('FR', 'AUD'),
                'expectedResult' => false,
            ],
            [
                'context' => $this->mockContext('IT', 'CAD'),
                'expectedResult' => false,
            ],
            [
                'context' => $this->mockContext('CH', 'CHF'),
                'expectedResult' => true,
            ],
            [
                'context' => $this->mockContext('FI', 'CHF'),
                'expectedResult' => true,
            ],
            [
                'context' => $this->mockContext('FR', 'CHF'),
                'expectedResult' => true,
            ],
        ];
    }

    public function getWlcryptopaymentsPaymentRestrictionSupportedDataProvider()
    {
        return [
            [
                'context' => $this->mockContext('AT', 'CHF'),
                'paymentName' => SaferPayConfig::PAYMENT_WLCRYPTOPAYMENTS,
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
