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
use Invertus\SaferPay\Service\PaymentRestrictionValidation\ApplePayPaymentRestrictionValidation;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;

class ApplePayPaymentRestrictionValidationTest extends UnitTestCase
{
    /**
     * @dataProvider getApplePayPaymentRestrictionSupportedDataProvider
     */
    public function testIsSupported($paymentName, $expectedResult)
    {
        $applePayValidation = new ApplePayPaymentRestrictionValidation($this->mockContext('AT', 'AUD'));
        $this->assertEquals($expectedResult, $applePayValidation->supports($paymentName));
    }

    public function getApplePayPaymentRestrictionSupportedDataProvider()
    {
        return [
            [
                'paymentName' => SaferPayConfig::PAYMENT_APPLEPAY,
                'expectedResult' => true,
            ],
            [
                'paymentName' => SaferPayConfig::PAYMENT_VISA,
                'expectedResult' => false,
            ],
        ];
    }
}
