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

namespace Invertus\SaferPay\Tests\Unit\Tools;

use Cart;
use Invertus\SaferPay\Adapter\LegacyContext;
use Invertus\SaferPay\Api\SaferPayApiClient;
use Invertus\SaferPay\Repository\SaferPayPaymentRepository;
use Invertus\SaferPay\Repository\SaferPayRestrictionRepository;
use Invertus\SaferPay\Service\SaferPayRestrictionCreator;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use SaferPayOfficial;

class UnitTestCase extends TestCase
{
    public function createSaferPayApiClient()
    {
        $guzzleClient = new Client([
            'base_url' => SaferPayApiClient::TEST_API,
        ]);

        return new SaferPayApiClient(
            $guzzleClient,
            getenv('SAFERPAY_API_TEST_USERNAME'),
            getenv('SAFERPAY_API_TEST_PASSWORD')
        );
    }

    public function mockSaferPayModule()
    {
        $moduleMock = $this->getMockBuilder(SaferPayOfficial::class)
            ->disableOriginalConstructor()
            ->setMethods(['l'])
            ->getMock()
        ;

        $moduleMock
            ->method('l')
            ->willReturn('TEST_STRING')
        ;

        return $moduleMock;
    }

    public function mockCart()
    {
        $cartMock = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $cartMock
            ->method('getSummaryDetails')
            ->willReturn(['total_price' => 50])
        ;

        $cartMock->id = 0;
        $cartMock->id_currency = 1;

        return $cartMock;
    }

    public function mockContext($countryCode, $currencyCode)
    {
        $contextMock = $this->getMockBuilder(LegacyContext::class)
            ->getMock();

        $contextMock
            ->method('getCountryIsoCode')
            ->willReturn($countryCode)
        ;

        $contextMock
            ->method('getCurrencyIsoCode')
            ->willReturn($currencyCode)
        ;

        $contextMock
            ->method('getCurrencyId')
            ->willReturn(1)
        ;

        $contextMock
            ->method('getCountryId')
            ->willReturn(1)
        ;

        return $contextMock;
    }

    public function getRestrictionRepositoryMock($paymentName, $result)
    {
        $restrictionMock = $this
            ->getMockBuilder(SaferPayRestrictionRepository::class)
            ->getMock();

        $restrictionMock
            ->method('getSelectedIdsByName')
            ->withConsecutive(
                [$paymentName, SaferPayRestrictionCreator::RESTRICTION_COUNTRY],
                [$paymentName, SaferPayRestrictionCreator::RESTRICTION_CURRENCY]
            )
            ->willReturnOnConsecutiveCalls(...$result);

        return $restrictionMock;
    }

    public function getPaymentRepositoryMock($paymentName, $result)
    {
        $paymentRepositoryMock = $this
            ->getMockBuilder(SaferPayPaymentRepository::class)
            ->getMock();

        $paymentRepositoryMock
            ->method('isActiveByName')
            ->with($paymentName)
            ->willReturn($result)
        ;

        return $paymentRepositoryMock;
    }
}
