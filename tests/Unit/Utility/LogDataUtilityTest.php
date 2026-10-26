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


namespace Invertus\SaferPay\Tests\Unit\Utility;

use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;
use Invertus\SaferPay\Utility\LogDataUtility;

class LogDataUtilityTest extends UnitTestCase
{
    public function testRemovesAuthorizationHeaderFromNestedContext()
    {
        $context = [
            'context' => [
                'uri' => 'https://test.saferpay.com/api/Payment/v1/PaymentPage/Initialize',
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode('api_user:api_password'),
                ],
            ],
            'id_shop' => 1,
        ];

        $expected = [
            'context' => [
                'uri' => 'https://test.saferpay.com/api/Payment/v1/PaymentPage/Initialize',
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ],
            'id_shop' => 1,
        ];

        $this->assertSame($expected, LogDataUtility::removeCredentials($context));
    }

    public function testRemovesAuthorizationHeaderRegardlessOfCase()
    {
        $headers = [
            'authorization' => 'Basic abc',
            'AUTHORIZATION' => 'Basic def',
            'Accept' => 'application/json',
        ];

        $this->assertSame(['Accept' => 'application/json'], LogDataUtility::removeCredentials($headers));
    }

    public function testKeepsDataWithoutCredentialsUnchanged()
    {
        $data = [
            'TerminalId' => '17771564',
            'Payment' => ['Amount' => ['Value' => '1000', 'CurrencyCode' => 'EUR']],
            0 => 'list item',
        ];

        $this->assertSame($data, LogDataUtility::removeCredentials($data));
    }

    public function testRemovesAuthorizationInsideListsAndWithArrayValue()
    {
        $data = [
            'calls' => [
                ['headers' => ['Authorization' => 'Basic abc', 'Accept' => 'application/json']],
                ['headers' => ['Authorization' => ['Basic def']]],
            ],
        ];

        $expected = [
            'calls' => [
                ['headers' => ['Accept' => 'application/json']],
                ['headers' => []],
            ],
        ];

        $this->assertSame($expected, LogDataUtility::removeCredentials($data));
    }
}
