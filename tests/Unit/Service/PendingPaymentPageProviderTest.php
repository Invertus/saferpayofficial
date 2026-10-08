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

use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\PendingPaymentPageProvider;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;
use SaferPayOrder;

class PendingPaymentPageProviderTest extends UnitTestCase
{
    const PAYMENT_PAGE = 'https://test.saferpay.com/vt2/api/PaymentPage/1/2/token';

    public function testItReturnsThePaymentPageOfTheCartWhenTheCartIsKnown()
    {
        $repository = $this->mockRepository();
        $repository->expects($this->once())->method('getByCartId')->with(58)->willReturn($this->saferPayOrder());
        $repository->expects($this->never())->method('getLatestByCustomerIdPlacedAfter');

        $provider = new PendingPaymentPageProvider($repository);

        $this->assertSame(self::PAYMENT_PAGE, $provider->getUrl(58, 37, 1));
    }

    public function testItLooksUpTheCustomersRecentOrderWhenTheCartWasAlreadyConverted()
    {
        $repository = $this->mockRepository();
        $repository->expects($this->never())->method('getByCartId');
        $repository
            ->expects($this->once())
            ->method('getLatestByCustomerIdPlacedAfter')
            ->with(37, 1, $this->callback(function ($placedAfter) {
                $cutoff = strtotime($placedAfter);

                return $cutoff <= time() - PendingPaymentPageProvider::REUSE_WINDOW_SECONDS + 5
                    && $cutoff >= time() - PendingPaymentPageProvider::REUSE_WINDOW_SECONDS - 5;
            }))
            ->willReturn($this->saferPayOrder());

        $provider = new PendingPaymentPageProvider($repository);

        $this->assertSame(self::PAYMENT_PAGE, $provider->getUrl(0, 37, 1));
    }

    public function testItReturnsNothingForAVisitorWhoIsNotLoggedIn()
    {
        $repository = $this->mockRepository();
        $repository->expects($this->never())->method('getLatestByCustomerIdPlacedAfter');

        $provider = new PendingPaymentPageProvider($repository);

        $this->assertSame('', $provider->getUrl(0, 0, 1));
    }

    public function testItReturnsNothingWhenNoPaymentWasStarted()
    {
        $repository = $this->mockRepository();
        $repository->method('getByCartId')->willReturn(new SaferPayOrder());
        $repository->expects($this->never())->method('getLatestByCustomerIdPlacedAfter');

        $provider = new PendingPaymentPageProvider($repository);

        $this->assertSame('', $provider->getUrl(58, 37, 1));
    }

    /**
     * @dataProvider finishedPaymentProvider
     */
    public function testItNeverReusesAPaymentThatAlreadyMovedOn($property)
    {
        $saferPayOrder = $this->saferPayOrder();
        $saferPayOrder->{$property} = 1;

        $repository = $this->mockRepository();
        $repository->method('getByCartId')->willReturn($saferPayOrder);

        $provider = new PendingPaymentPageProvider($repository);

        $this->assertSame('', $provider->getUrl(58, 37, 1));
    }

    public function finishedPaymentProvider()
    {
        return [
            'authorized' => ['authorized'],
            'captured' => ['captured'],
            'canceled' => ['canceled'],
            'refunded' => ['refunded'],
            'pending' => ['pending'],
        ];
    }

    private function saferPayOrder()
    {
        $saferPayOrder = new SaferPayOrder();
        $saferPayOrder->id = 3;
        $saferPayOrder->redirect_url = self::PAYMENT_PAGE;

        return $saferPayOrder;
    }

    private function mockRepository()
    {
        return $this
            ->getMockBuilder(SaferPayOrderRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
