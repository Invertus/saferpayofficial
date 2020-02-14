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

class Card
{
    /**
     * @var string|null
     */
    private $maskedNumber;

    /**
     * @var int|null
     */
    private $expYear;

    /**
     * @var int|null
     */
    private $expMonth;

    /**
     * @var string|null
     */
    private $holderName;

    /**
     * @var string|null
     */
    private $countryCode;

    /**
     * Card constructor.
     * @param string $maskedNumber
     * @param int $expYear
     * @param int $expMonth
     * @param string $holderName
     * @param string $countryCode
     */
    public function __construct(
        $maskedNumber = null,
        $expYear = null,
        $expMonth = null,
        $holderName = null,
        $countryCode = null
    ) {
        $this->maskedNumber = $maskedNumber;
        $this->expYear = $expYear;
        $this->expMonth = $expMonth;
        $this->holderName = $holderName;
        $this->countryCode = $countryCode;
    }

    /**
     * @return mixed
     */
    public function getMaskedNumber()
    {
        return $this->maskedNumber;
    }

    /**
     * @param mixed $maskedNumber
     */
    public function setMaskedNumber($maskedNumber)
    {
        $this->maskedNumber = $maskedNumber;
    }

    /**
     * @return mixed
     */
    public function getExpYear()
    {
        return $this->expYear;
    }

    /**
     * @param mixed $expYear
     */
    public function setExpYear($expYear)
    {
        $this->expYear = $expYear;
    }

    /**
     * @return mixed
     */
    public function getExpMonth()
    {
        return $this->expMonth;
    }

    /**
     * @param mixed $expMonth
     */
    public function setExpMonth($expMonth)
    {
        $this->expMonth = $expMonth;
    }

    /**
     * @return mixed
     */
    public function getHolderName()
    {
        return $this->holderName;
    }

    /**
     * @param mixed $holderName
     */
    public function setHolderName($holderName)
    {
        $this->holderName = $holderName;
    }

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param mixed $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }
}
