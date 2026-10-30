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

namespace Invertus\SaferPay\Tests\Unit\Service\TransactionFlow;

use Invertus\SaferPay\DTO\Response\Amount;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayAuthorizedAmountGuard;
use PHPUnit\Framework\TestCase;

class SaferPayAuthorizedAmountGuardTest extends TestCase
{
    public function testItAcceptsAnAuthorizationForTheWholeTotal()
    {
        $guard = new SaferPayAuthorizedAmountGuard();

        $this->assertTrue($guard->matchesTotal(new Amount('18660', 'EUR'), 186.6, 'EUR'));
    }

    public function testItRejectsAnAuthorizationBelowTheTotal()
    {
        $guard = new SaferPayAuthorizedAmountGuard();

        $this->assertFalse($guard->matchesTotal(new Amount('1428', 'EUR'), 186.6, 'EUR'));
    }

    public function testItRejectsAnAuthorizationAboveTheTotal()
    {
        $guard = new SaferPayAuthorizedAmountGuard();

        $this->assertFalse($guard->matchesTotal(new Amount('18660', 'EUR'), 14.28, 'EUR'));
    }

    public function testItRejectsAnAuthorizationInAnotherCurrency()
    {
        $guard = new SaferPayAuthorizedAmountGuard();

        $this->assertFalse($guard->matchesTotal(new Amount('1428', 'EUR'), 14.28, 'GBP'));
    }

    public function testItRoundsTheTotalTheSameWayThePaymentRequestDoes()
    {
        $guard = new SaferPayAuthorizedAmountGuard();

        $this->assertTrue($guard->matchesTotal(new Amount('1429', 'EUR'), 14.285, 'EUR'));
        $this->assertTrue($guard->matchesTotal(new Amount('3590', 'EUR'), 35.9, 'EUR'));
    }

    public function testItConvertsTheAuthorizedAmountToMajorUnits()
    {
        $guard = new SaferPayAuthorizedAmountGuard();

        $this->assertSame(14.28, $guard->toMajorUnits(new Amount('1428', 'EUR')));
    }
}
