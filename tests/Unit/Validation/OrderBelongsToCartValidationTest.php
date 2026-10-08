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

namespace Invertus\SaferPay\Tests\Unit\Validation;

use Cart;
use Invertus\SaferPay\Validation\OrderBelongsToCartValidation;
use Order;
use PHPUnit\Framework\TestCase;

class OrderBelongsToCartValidationTest extends TestCase
{
    public function testItAcceptsTheModuleOrderOfTheValidatedCart()
    {
        $validation = new OrderBelongsToCartValidation();

        $this->assertTrue($validation->validate($this->order(5, 9, 'saferpayofficial'), $this->cart(9), 'saferpayofficial'));
    }

    public function testItRejectsAnOrderOfAnotherCart()
    {
        $validation = new OrderBelongsToCartValidation();

        $this->assertFalse($validation->validate($this->order(4, 4, 'saferpayofficial'), $this->cart(9), 'saferpayofficial'));
    }

    public function testItRejectsAnOrderPaidWithAnotherModule()
    {
        $validation = new OrderBelongsToCartValidation();

        $this->assertFalse($validation->validate($this->order(5, 9, 'ps_checkpayment'), $this->cart(9), 'saferpayofficial'));
    }

    public function testItRejectsAnOrderThatDoesNotExist()
    {
        $validation = new OrderBelongsToCartValidation();

        $this->assertFalse($validation->validate($this->order(0, 0, ''), $this->cart(0), 'saferpayofficial'));
    }

    public function testItRejectsAnOrderWhenTheCartDoesNotExist()
    {
        $validation = new OrderBelongsToCartValidation();

        $this->assertFalse($validation->validate($this->order(5, 0, 'saferpayofficial'), $this->cart(0), 'saferpayofficial'));
    }

    public function testItRejectsAnOrderThatDoesNotExistForAnExistingCart()
    {
        $validation = new OrderBelongsToCartValidation();

        $this->assertFalse($validation->validate($this->order(0, 9, 'saferpayofficial'), $this->cart(9), 'saferpayofficial'));
    }

    private function order($orderId, $cartId, $module)
    {
        $order = new Order();
        $order->id = $orderId;
        $order->id_cart = $cartId;
        $order->module = $module;

        return $order;
    }

    private function cart($cartId)
    {
        $cart = new Cart();
        $cart->id = $cartId;

        return $cart;
    }
}
