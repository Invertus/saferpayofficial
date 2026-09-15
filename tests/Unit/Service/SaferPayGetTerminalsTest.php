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

use Invertus\SaferPay\Api\Request\GetTerminalsService;
use Invertus\SaferPay\Service\SaferPayGetTerminals;
use PHPUnit\Framework\TestCase;

class SaferPayGetTerminalsTest extends TestCase
{
    /**
     * @dataProvider terminalTypePropertyProvider
     */
    public function testExcludesMpoAndSpgKeepsOthersAndUntyped($typeProperty)
    {
        $service = new SaferPayGetTerminals($this->mockGetTerminalsService([
            $this->terminal('MPO_TERMINAL_ID', $typeProperty, 'MPO'),
            $this->terminal('SPG_TERMINAL_ID', $typeProperty, 'SPG'),
            $this->terminal('OTHER_TERMINAL_ID', $typeProperty, 'EMONEY'),
            $this->terminal('NO_TYPE_TERMINAL_ID', $typeProperty, null),
        ]));

        $ids = array_column(
            $service->fetchTerminalsWithCredentials('u', 'p', 'cust', true),
            'id'
        );

        $this->assertNotContains('MPO_TERMINAL_ID', $ids);
        $this->assertNotContains('SPG_TERMINAL_ID', $ids);
        $this->assertContains('OTHER_TERMINAL_ID', $ids);
        $this->assertContains('NO_TYPE_TERMINAL_ID', $ids);
    }

    public function testExclusionIsCaseInsensitive()
    {
        $service = new SaferPayGetTerminals($this->mockGetTerminalsService([
            $this->terminal('LOWER_MPO', 'Type', 'mpo'),
            $this->terminal('MIXED_SPG', 'Type', 'Spg'),
            $this->terminal('KEEP', 'Type', 'card'),
        ]));

        $ids = array_column(
            $service->fetchTerminalsWithCredentials('u', 'p', 'cust', false),
            'id'
        );

        $this->assertSame(['KEEP'], $ids);
    }

    public function testPreservesIdAndNameShape()
    {
        $service = new SaferPayGetTerminals($this->mockGetTerminalsService([
            $this->terminal('T1', 'Type', 'card', 'Main terminal'),
        ]));

        $result = $service->fetchTerminalsWithCredentials('u', 'p', 'cust', true);

        $this->assertSame([['id' => 'T1', 'name' => 'Main terminal (T1)']], $result);
    }

    public function terminalTypePropertyProvider()
    {
        // The Management API terminal-type property name is confirmed defensively:
        // both "Type" and "TerminalType" are honoured.
        return [
            'Type property' => ['Type'],
            'TerminalType property' => ['TerminalType'],
        ];
    }

    private function terminal($id, $typeProperty, $typeValue, $description = null)
    {
        $terminal = new \stdClass();
        $terminal->TerminalId = $id;
        if ($description !== null) {
            $terminal->Description = $description;
        }
        if ($typeValue !== null) {
            $terminal->{$typeProperty} = $typeValue;
        }

        return $terminal;
    }

    private function mockGetTerminalsService(array $terminals)
    {
        $response = new \stdClass();
        $response->Terminals = $terminals;

        $mock = $this->getMockBuilder(GetTerminalsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTerminals'])
            ->getMock();
        $mock->method('getTerminals')->willReturn($response);

        return $mock;
    }
}
