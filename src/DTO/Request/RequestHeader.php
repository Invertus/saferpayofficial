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

namespace Invertus\SaferPay\DTO\Request;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RequestHeader
{
    const SPEC_VERSION = 'SAFERPAY_SPEC_VERSION';

    const SPEC_REFUND_VERSION = 'SAFERPAY_SPEC_REFUND_VERSION';

    const CUSTOMER_ID = 'SAFERPAY_CUSTOMER_ID';

    const REQUEST_ID = 'SAFERPAY_REQUEST_ID';

    const RETRY_INDICATOR = 'SAFERPAY_RETRY_INDICATOR';

    private $specVersions;
    private $CustomerId;
    private $requestId;
    private $retryIndicator;
    private $clientInfo;

    public function __construct($specVersions, $CustomerId, $requestIdId, $retryIndicator, $clientInfo)
    {
        $this->specVersions = $specVersions;
        $this->CustomerId = $CustomerId;
        $this->requestId = $requestIdId;
        $this->retryIndicator = $retryIndicator;
        $this->clientInfo = $clientInfo;
    }

    /**
     * @return mixed
     */
    public function getSpecVersions()
    {
        return $this->specVersions;
    }

    /**
     * @param mixed $specVersions
     */
    public function setSpecVersions($specVersions)
    {
        $this->specVersions = $specVersions;
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->CustomerId;
    }

    /**
     * @param mixed $CustomerId
     */
    public function setCustomerId($CustomerId)
    {
        $this->CustomerId = $CustomerId;
    }

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param $requestId
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * @return mixed
     */
    public function getRetryIndicator()
    {
        return $this->retryIndicator;
    }

    /**
     * @param mixed $retryIndicator
     */
    public function setRetryIndicator($retryIndicator)
    {
        $this->retryIndicator = $retryIndicator;
    }

    /**
     * @return mixed
     */
    public function getClientInfo()
    {
        return $this->clientInfo;
    }

    /**
     * @param mixed $clientInfo
     */
    public function setClientInfo($clientInfo)
    {
        $this->clientInfo = $clientInfo;
    }
}
