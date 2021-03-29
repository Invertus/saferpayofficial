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
 * @author INVERTUS UAB www.invertus.eu  <support@invertus.eu>
 * @copyright SIX Payment Services
 * @license   SIX Payment Services
 */

namespace Invertus\SaferPay\Tests\Unit\Utility;

use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;
use Invertus\SaferPay\Utility\PriceUtility;

class PriceUtilityTest extends UnitTestCase
{
    /**
     * @dataProvider getConvertToCentsDataProvider
     */
    public function testConvertToCents($price, $expectedPrice)
    {
        $numberUtility = new PriceUtility();
        $this->assertEquals($expectedPrice, $numberUtility->convertToCents($price));
    }

    public function testConvertToCentsWithBadArgument()
    {
        $numberUtility = new PriceUtility();
        $this->expectException(\InvalidArgumentException::class);
        $numberUtility->convertToCents('test');
    }

    public function getConvertToCentsDataProvider()
    {
        return [
            [
                'price' => 100.00,
                'expectedPrice' => 10000,
            ],
            [
                'price' => 100.0,
                'expectedPrice' => 10000,
            ],
            [
                'price' => 100,
                'expectedPrice' => 10000,
            ],
            [
                'price' => 100.46789,
                'expectedPrice' => 10046,
            ],
        ];
    }
}
