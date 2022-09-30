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

use Invertus\SaferPay\Service\SaferPayPaymentNotation;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;

class SaferPayPaymentNotationTest extends UnitTestCase
{
    /** @dataProvider getPaymentMethodForDisplayDataProvider */
    public function testGetForDisplay(string $paymentMethod, string $expectedResult)
    {
        $result = (new \Invertus\SaferPay\Service\SaferPayPaymentNotation)->getForDisplay($paymentMethod);
        $this->assertEquals($expectedResult, $result);
    }

    /** @dataProvider getPaymentMethodDataProvider */
    public function testGetShortName(string $paymentMethod, string $expectedResult)
    {
        $result = (new \Invertus\SaferPay\Service\SaferPayPaymentNotation)->getShortName($paymentMethod);
        $this->assertEquals($expectedResult, $result);
    }

    public function getPaymentMethodDataProvider()
    {
        return [
            ['paymentMethod' => 'TWINT', 'expected' => 'TWINT'],
            ['paymentMethod' => 'VISA', 'expected' => 'VISA'],
            ['paymentMethod' => 'MasterCard', 'expected' => 'MASTERCARD'],
            ['paymentMethod' => 'Diners Club', 'expected' => 'DINERS'],
            ['paymentMethod' => 'American Express', 'expected' => 'AMEX'],
        ];
    }

    public function getPaymentMethodForDisplayDataProvider()
    {
        return [
            ['paymentMethod' => 'TWINT', 'expected' => 'Twint'],
            ['paymentMethod' => 'VISA', 'expected' => 'Visa'],
            ['paymentMethod' => 'MASTERCARD', 'expected' => 'Mastercard'],
            ['paymentMethod' => 'DINERS', 'expected' => 'DinersClub'],
            ['paymentMethod' => 'AMEX', 'expected' => 'AmericanExpress'],
        ];
    }

}
