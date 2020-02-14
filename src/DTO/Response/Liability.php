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

class Liability
{

    /**
     * @var string
     */
    private $liabilityShift;
    /**
     * @var string
     */
    private $liableEntity;
    /**
     * @var ThreeDs
     */
    private $threeDs;
    /**
     * @var FraudFree
     */
    private $fraudFree;

    /**
     * Liability constructor.
     * @param string $liabilityShift
     * @param string $liableEntity
     * @param ThreeDs|null $threeDs
     * @param FraudFree|null $fraudFree
     */
    public function __construct(
        $liabilityShift = null,
        $liableEntity = null,
        ThreeDs $threeDs = null,
        FraudFree $fraudFree = null
    ) {
        $this->liabilityShift = $liabilityShift;
        $this->liableEntity = $liableEntity;
        $this->threeDs = $threeDs;
        $this->fraudFree = $fraudFree;
    }

    /**
     * @return string
     */
    public function getLiabilityShift()
    {
        return $this->liabilityShift;
    }

    /**
     * @param null $liabilityShift
     */
    public function setLiabilityShift($liabilityShift)
    {
        $this->liabilityShift = $liabilityShift;
    }

    /**
     * @return string
     */
    public function getLiableEntity()
    {
        return $this->liableEntity;
    }

    /**
     * @param null $liableEntity
     */
    public function setLiableEntity($liableEntity)
    {
        $this->liableEntity = $liableEntity;
    }

    /**
     * @return ThreeDs
     */
    public function getThreeDs()
    {
        return $this->threeDs;
    }

    /**
     * @param ThreeDs $threeDs
     */
    public function setThreeDs($threeDs)
    {
        $this->threeDs = $threeDs;
    }

    /**
     * @return FraudFree
     */
    public function getFraudFree()
    {
        return $this->fraudFree;
    }

    /**
     * @param FraudFree $fraudFree
     */
    public function setFraudFree($fraudFree)
    {
        $this->fraudFree = $fraudFree;
    }
}
