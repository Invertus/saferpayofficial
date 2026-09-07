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

namespace Invertus\SaferPay\Tests\Unit\DTO\Request;

use Invertus\SaferPay\DTO\Request\GetPaymentPageConfigurations\GetPaymentPageConfigurationsRequest;
use PHPUnit\Framework\TestCase;

class GetPaymentPageConfigurationsRequestTest extends TestCase
{
    public function testGenerateRequestUrlUsesPaymentPageConfigurationsPath()
    {
        $request = new GetPaymentPageConfigurationsRequest('278741');

        // The Management API path separates payment-page and configurations with a
        // slash. The all-hyphen spelling answers 404, and the docs navigation slug
        // (payment-page_configurations) is not the request path either.
        $this->assertSame(
            'rest/customers/278741/payment-page/configurations',
            $request->generateRequestUrl()
        );
    }

    public function testGetCustomerIdReturnsGivenCustomerId()
    {
        $request = new GetPaymentPageConfigurationsRequest('278741');

        $this->assertSame('278741', $request->getCustomerId());
    }

    /**
     * @dataProvider validCustomerIdProvider
     */
    public function testAcceptsAlphanumericDashAndUnderscoreCustomerIds($customerId)
    {
        $request = new GetPaymentPageConfigurationsRequest($customerId);

        $this->assertSame($customerId, $request->getCustomerId());
    }

    /**
     * @dataProvider invalidCustomerIdProvider
     */
    public function testRejectsCustomerIdOutsideTheAllowedCharacterSet($customerId)
    {
        $this->expectException(\InvalidArgumentException::class);

        new GetPaymentPageConfigurationsRequest($customerId);
    }

    public function validCustomerIdProvider()
    {
        return [
            'numeric' => ['278741'],
            'alphanumeric' => ['cust278741'],
            'dashed' => ['cust-278741'],
            'underscored' => ['cust_278741'],
        ];
    }

    public function invalidCustomerIdProvider()
    {
        return [
            'empty' => [''],
            'path traversal' => ['../278741'],
            'slash' => ['278741/configurations'],
            'whitespace' => ['278 741'],
            'semicolon' => ['278741;'],
        ];
    }
}
