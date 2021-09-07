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

namespace Invertus\SaferPay\DTO\Request\AssertRefund;

use Invertus\SaferPay\DTO\Request\RequestHeader;

class AssertRefundRequest
{

    /**
     * @var RequestHeader
     */
    private $requestHeader;

    /**
     * @var string
     */
    private $token;

    public function __construct(
        RequestHeader $requestHeader,
        $token
    ) {
        $this->requestHeader = $requestHeader;
        $this->token = $token;
    }

    public function getAsArray()
    {
        $return = [
            'RequestHeader' => [
                'SpecVersion' => $this->requestHeader->getSpecVersions(),
                'CustomerId' => $this->requestHeader->getCustomerId(),
                'RequestId' => $this->requestHeader->getRequestId() ?: time(),
                'RetryIndicator' => $this->requestHeader->getRetryIndicator(),
                'ClientInfo' => $this->requestHeader->getClientInfo(),
            ],
            'TransactionReference' => [
                'TransactionId' => $this->token
            ]
        ];

        return $return;
    }
}
