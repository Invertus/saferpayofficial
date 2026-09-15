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

class FraudFree
{

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $liabilityShift;

    /**
     * @var string|null
     */
    private $score;

    /**
     * @var array
     */
    private $investigationPoints;

    /**
     * FraudFree constructor.
     * @param string $id
     * @param string $liabilityShift
     * @param string $score
     * @param array $investigationPoints
     */
    public function __construct($id = null, $liabilityShift = null, $score = null, array $investigationPoints = [])
    {
        $this->id = $id;
        $this->liabilityShift = $liabilityShift;
        $this->score = $score;
        $this->investigationPoints = $investigationPoints;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getLiabilityShift()
    {
        return $this->liabilityShift;
    }

    /**
     * @param $liabilityShift
     */
    public function setLiabilityShift($liabilityShift)
    {
        $this->liabilityShift = $liabilityShift;
    }

    /**
     * @return string $score
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return array
     */
    public function getInvestigationPoints()
    {
        return $this->investigationPoints;
    }

    /**
     * @param array $investigationPoints
     */
    public function setInvestigationPoints($investigationPoints)
    {
        $this->investigationPoints = $investigationPoints;
    }
}
