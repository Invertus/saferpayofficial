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

namespace Invertus\SaferPay\Tests\Unit\Config;

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;

class SaferPayConfigTest extends UnitTestCase
{
    /**
     * @dataProvider immediatelyCapturedPaymentMethods
     */
    public function testItRejectsCaptureForImmediatelyCapturedMethods($paymentMethod)
    {
        $this->assertFalse(SaferPayConfig::supportsOrderCapture($paymentMethod));
    }

    /**
     * @dataProvider immediatelyCapturedPaymentMethods
     */
    public function testItRejectsCancelForImmediatelyCapturedMethods($paymentMethod)
    {
        $this->assertFalse(SaferPayConfig::supportsOrderCancel($paymentMethod));
    }

    /**
     * @dataProvider capturablePaymentMethods
     */
    public function testItAllowsCaptureAndCancelForCapturableMethods($paymentMethod)
    {
        $this->assertTrue(SaferPayConfig::supportsOrderCapture($paymentMethod));
        $this->assertTrue(SaferPayConfig::supportsOrderCancel($paymentMethod));
    }

    public function immediatelyCapturedPaymentMethods()
    {
        return [
            [SaferPayConfig::PAYMENT_WECHATPAY],
            [SaferPayConfig::PAYMENT_ACCOUNTTOACCOUNT],
            [SaferPayConfig::PAYMENT_GIFTCARD],
            [SaferPayConfig::PAYMENT_IDEAL],
            [SaferPayConfig::PAYMENT_BANCONTACT],
            [SaferPayConfig::PAYMENT_BLIK],
            [SaferPayConfig::PAYMENT_EPS],
            [SaferPayConfig::PAYMENT_EPRZELEWY],
            [SaferPayConfig::PAYMENT_ALIPAY],
        ];
    }

    public function capturablePaymentMethods()
    {
        return [
            [SaferPayConfig::PAYMENT_VISA],
            [SaferPayConfig::PAYMENT_MASTERCARD],
            [SaferPayConfig::PAYMENT_PAYPAL],
            [SaferPayConfig::PAYMENT_TWINT],
            [SaferPayConfig::PAYMENT_KLARNA],
            [SaferPayConfig::PAYMENT_POSTFINANCE_PAY],
            [SaferPayConfig::PAYMENT_REKA],
        ];
    }
}
