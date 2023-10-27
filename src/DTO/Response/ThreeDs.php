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

namespace Invertus\SaferPay\DTO\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ThreeDs
{
    /**
     * @var string|null
     */
    private $authenticated;

    /**
     * @var string|null
     */
    private $liabilityShift;

    /**
     * @var string|null
     */
    private $xid;

    /**
     * @var string|null
     */
    private $verificationValue;

    /**
     * ThreeDs constructor.
     * @param string $authenticated
     * @param string $liabilityShift
     * @param string $xid
     * @param string $verificationValue
     */
    public function __construct($authenticated = null, $liabilityShift = null, $xid = null, $verificationValue = null)
    {
        $this->authenticated = $authenticated;
        $this->liabilityShift = $liabilityShift;
        $this->xid = $xid;
        $this->verificationValue = $verificationValue;
    }

    /**
     * @return mixed
     */
    public function getAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * @param mixed $authenticated
     */
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = $authenticated;
    }

    /**
     * @return mixed
     */
    public function getLiabilityShift()
    {
        return $this->liabilityShift;
    }

    /**
     * @param mixed $liabilityShift
     */
    public function setLiabilityShift($liabilityShift)
    {
        $this->liabilityShift = $liabilityShift;
    }

    /**
     * @return mixed
     */
    public function getXid()
    {
        return $this->xid;
    }

    /**
     * @param mixed $xid
     */
    public function setXid($xid)
    {
        $this->xid = $xid;
    }

    /**
     * @return mixed
     */
    public function getVerificationValue()
    {
        return $this->verificationValue;
    }

    /**
     * @param mixed $verificationValue
     */
    public function setVerificationValue($verificationValue)
    {
        $this->verificationValue = $verificationValue;
    }
}
