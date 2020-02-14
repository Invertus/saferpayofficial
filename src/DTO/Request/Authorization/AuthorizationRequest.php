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

namespace Invertus\SaferPay\DTO\Request\Authorization;

use Invertus\SaferPay\DTO\Request\RequestHeader;

class AuthorizationRequest
{

    /**
     * @var RequestHeader
     */
    private $requestHeader;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $moduleVersion;

    /**
     * @var bool
     */
    private $saveCard;

    /**
     * AuthorizationRequest constructor.
     * @param RequestHeader $requestHeader
     * @param $token
     * @param $moduleVersion
     */
    public function __construct(
        RequestHeader $requestHeader,
        $token,
        $moduleVersion,
        $saveCard
    ) {
        $this->requestHeader = $requestHeader;
        $this->token = $token;
        $this->moduleVersion = $moduleVersion;
        $this->saveCard = $saveCard;
    }

    public function getAsArray()
    {
        $return = [
            'RequestHeader' => [
                'SpecVersion' => $this->requestHeader->getSpecVersions(),
                'CustomerId' => $this->requestHeader->getCustomerId(),
                'RequestId' => $this->requestHeader->getRequestId(),
                'RetryIndicator' => $this->requestHeader->getRetryIndicator(),
                'ClientInfo' => $this->requestHeader->getClientInfo(),
            ],
            'Token' => $this->token,
        ];

        if ($this->saveCard) {
            $return['RegisterAlias'] = [ 'IdGenerator' => 'RANDOM_UNIQUE'];
        }

        return $return;
    }
}
