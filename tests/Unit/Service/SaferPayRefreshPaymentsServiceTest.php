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

use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\Repository\SaferPayFieldRepository;
use Invertus\SaferPay\Repository\SaferPayPaymentRepository;
use Invertus\SaferPay\Repository\SaferPayRestrictionRepository;
use Invertus\SaferPay\Service\SaferPayObtainPaymentMethods;
use Invertus\SaferPay\Service\SaferPayRefreshPaymentsService;
use PHPUnit\Framework\TestCase;

class SaferPayRefreshPaymentsServiceTest extends TestCase
{
    public function testReconcilePreservesEnabledAddsNewDisabledDropsRemoved()
    {
        // Stored: VISA (enabled), AMEX (enabled). Account: VISA (kept), TWINT (added), AMEX removed.
        $paymentRepository = $this->mockPaymentRepository([
            ['name' => 'VISA', 'active' => '1'],
            ['name' => 'AMEX', 'active' => '1'],
        ]);
        $fieldRepository = $this->mockFieldRepository(['VISA' => true, 'AMEX' => false]);
        $obtainPaymentMethods = $this->mockObtainPaymentMethods(['VISA', 'TWINT']);

        // Both tables are rebuilt.
        $paymentRepository->expects($this->once())->method('truncateTable');
        $fieldRepository->expects($this->once())->method('truncateTable');

        // VISA keeps active=1; TWINT added as active=0; AMEX (removed) is never re-inserted.
        $paymentRepository->expects($this->exactly(2))
            ->method('insertPayment')
            ->withConsecutive(
                [['name' => 'VISA', 'active' => 1]],
                [['name' => 'TWINT', 'active' => 0]]
            );
        // Custom-form flag preserved for VISA (true -> 1), default 0 for the new TWINT.
        $fieldRepository->expects($this->exactly(2))
            ->method('insertField')
            ->withConsecutive(
                [['name' => 'VISA', 'active' => 1]],
                [['name' => 'TWINT', 'active' => 0]]
            );

        $this->makeService($paymentRepository, $obtainPaymentMethods, $fieldRepository)->refreshPayments();
    }

    public function testDoesNothingWhenNoActivePaymentMethodsStored()
    {
        $paymentRepository = $this->mockPaymentRepository([]);
        $fieldRepository = $this->mockFieldRepository([]);
        $obtainPaymentMethods = $this->mockObtainPaymentMethods(['VISA']);

        // Early return: no API reconciliation and no destructive rebuild.
        $obtainPaymentMethods->expects($this->never())->method('obtainPaymentMethodsNamesAsArray');
        $paymentRepository->expects($this->never())->method('truncateTable');
        $paymentRepository->expects($this->never())->method('insertPayment');

        $this->makeService($paymentRepository, $obtainPaymentMethods, $fieldRepository)->refreshPayments();
    }

    private function makeService($paymentRepository, $obtainPaymentMethods, $fieldRepository)
    {
        return new SaferPayRefreshPaymentsService(
            $paymentRepository,
            $obtainPaymentMethods,
            $this->createMockWithMethods(SaferPayRestrictionRepository::class, []),
            $fieldRepository,
            $this->createMockWithMethods(LoggerInterface::class, [])
        );
    }

    private function mockPaymentRepository(array $activePayments)
    {
        $mock = $this->createMockWithMethods(
            SaferPayPaymentRepository::class,
            ['getActivePaymentMethods', 'truncateTable', 'insertPayment']
        );
        $mock->method('getActivePaymentMethods')->willReturn($activePayments);

        return $mock;
    }

    private function mockFieldRepository(array $activeByName)
    {
        $mock = $this->createMockWithMethods(
            SaferPayFieldRepository::class,
            ['isActiveByName', 'truncateTable', 'insertField']
        );
        $mock->method('isActiveByName')->willReturnCallback(function ($name) use ($activeByName) {
            return isset($activeByName[$name]) ? $activeByName[$name] : false;
        });

        return $mock;
    }

    private function mockObtainPaymentMethods(array $names)
    {
        $mock = $this->createMockWithMethods(
            SaferPayObtainPaymentMethods::class,
            ['obtainPaymentMethodsNamesAsArray']
        );
        $mock->method('obtainPaymentMethodsNamesAsArray')->willReturn($names);

        return $mock;
    }

    private function createMockWithMethods($class, array $methods)
    {
        $builder = $this->getMockBuilder($class)->disableOriginalConstructor();
        if (!empty($methods)) {
            $builder->setMethods($methods);
        }

        return $builder->getMock();
    }
}
