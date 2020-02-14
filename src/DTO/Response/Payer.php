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

class Payer
{

    /**
     * @var string|null
     */
    private $ipAddress;

    /**
     * @var string|null
     */
    private $ipLocation;

    /**
     * @var DeliveryAddress|null
     */
    private $deliveryAddress;

    /**
     * Payer constructor.
     * @param string $ipAddress
     * @param string $ipLocation
     * @param DeliveryAddress|null $deliveryAddress
     */
    public function __construct($ipAddress = null, $ipLocation = null, DeliveryAddress $deliveryAddress = null)
    {
        $this->ipAddress = $ipAddress;
        $this->ipLocation = $ipLocation;
        $this->deliveryAddress = $deliveryAddress;
    }

    /**
     * @return mixed
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @param mixed $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * @return mixed
     */
    public function getIpLocation()
    {
        return $this->ipLocation;
    }

    /**
     * @param mixed $ipLocation
     */
    public function setIpLocation($ipLocation)
    {
        $this->ipLocation = $ipLocation;
    }

    /**
     * @return DeliveryAddress
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * @param DeliveryAddress $deliveryAddress
     */
    public function setDeliveryAddress($deliveryAddress)
    {
        $this->deliveryAddress = $deliveryAddress;
    }
}
