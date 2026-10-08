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

use Invertus\SaferPay\Validation\CardAliasOwnershipValidation;
use PHPUnit\Framework\TestCase;
use SaferPayCardAlias;

class CardAliasOwnershipValidationTest extends TestCase
{
    public function testItAcceptsTheCardOfTheCustomer()
    {
        $validation = new CardAliasOwnershipValidation();

        $this->assertTrue($validation->validate($this->cardAlias(2, 3), 3));
    }

    public function testItRejectsTheCardOfAnotherCustomer()
    {
        $validation = new CardAliasOwnershipValidation();

        $this->assertFalse($validation->validate($this->cardAlias(1, 2), 3));
    }

    public function testItRejectsAGuestWithoutCustomerId()
    {
        $validation = new CardAliasOwnershipValidation();

        $this->assertFalse($validation->validate($this->cardAlias(1, 0), 0));
    }

    public function testItRejectsACardThatDoesNotExist()
    {
        $validation = new CardAliasOwnershipValidation();

        $this->assertFalse($validation->validate($this->cardAlias(0, 3), 3));
    }

    private function cardAlias($cardAliasId, $customerId)
    {
        $cardAlias = new SaferPayCardAlias();
        $cardAlias->id = $cardAliasId;
        $cardAlias->id_customer = $customerId;

        return $cardAlias;
    }
}
