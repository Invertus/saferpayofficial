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

class OrderItem implements \JsonSerializable
{
    const ITEM_PHYSICAL = 'PHYSICAL';
    const ITEM_DIGITAL = 'DIGITAL';
    const ITEM_SHIPPING_FEE = 'SHIPPINGFEE';

    /**
     * @var string [DIGITAL|PHYSICAL|SHIPPINGFEE]
     */
    private $type;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $unitPrice;

    /**
     * @var int
     */
    private $taxRate = 0;

    /**
     * @var int
     */
    private $taxAmount = 0;

    /**
     * @var string
     */
    private $variantId;

    /**
     * @return int
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * @param int $taxAmount
     */
    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param int $unitPrice
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * @return int
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @param int $taxRate
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getVariantId()
    {
        return $this->variantId;
    }

    /**
     * @param string $variantId
     */
    public function setVariantId($variantId)
    {
        $this->variantId = $variantId;
    }

    public function jsonSerialize()
    {
        return [
            'Type' => $this->getType(),
            'VariantId' => $this->getVariantId() ?: null,
            'Quantity' => $this->getQuantity(),
            'Name' => $this->getName(),
            'UnitPrice' => $this->getUnitPrice(),
            'TaxAmount' => $this->getTaxAmount(),
            'TaxRate' => $this->getTaxRate(),
        ];
    }
}
