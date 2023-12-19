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

namespace Invertus\SaferPay\Core\Payment\DTO;

use Configuration;
use Invertus\SaferPay\Config\SaferPayConfig;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CheckoutData
{
    private $cartId;
    private $paymentMethod;
    private $isBusinessLicense;
    private $selectedCard;
    private $fieldToken;
    private $successController;
    private $isTransaction;
    private $createAfterAuthorization;
    private $isAuthorizedOrder;

    public function __construct(
          $cartId,
          $paymentMethod,
          $isBusinessLicense,
          $selectedCard = -1,
          $fieldToken = null,
          $successController = null,
          $isTransaction = false
    )
    {
        $this->cartId = $cartId;
        $this->paymentMethod = $paymentMethod;
        $this->isBusinessLicense = $isBusinessLicense;
        $this->selectedCard = $selectedCard;
        $this->fieldToken = $fieldToken;
        $this->successController = $successController;
        $this->isTransaction = $isTransaction;
        $this->createAfterAuthorization = Configuration::get(SaferPayConfig::SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION);
        $this->isAuthorizedOrder = false;
    }

    public static function createFromRequest(
        $cartId,
          $paymentMethod,
          $isBusinessLicense,
          $selectedCard = -1,
          $fieldToken = null,
          $successController = null,
          $isTransaction = false
    )
    {
        return new self(
            $cartId,
            $paymentMethod,
            $isBusinessLicense,
            $selectedCard,
            $fieldToken,
            $successController,
            $isTransaction
        );
    }

    /**
     * @return int $cartId
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return string
     */
    public function getIsBusinessLicense()
    {
        return $this->isBusinessLicense;
    }

    /**
     * @return int|mixed
     */
    public function getSelectedCard()
    {
        return $this->selectedCard;
    }

    /**
     * @return string|null
     */
    public function getFieldToken()
    {
        return $this->fieldToken;
    }

    /**
     * @return string|null
     */
    public function getSuccessController()
    {
        return $this->successController;
    }

    /**
     * @return bool
     */
    public function getIsTransaction()
    {
        return $this->isTransaction;
    }

    /**
     * @return bool
     */
    public function getCreateAfterAuthorization()
    {
        return (bool) $this->createAfterAuthorization;
    }

    /**
     * @return bool
     */
    public function getIsAuthorizedOrder()
    {
        return $this->isAuthorizedOrder;
    }

    /**
     * @param bool $isAuthorized
     *
     * @return void
     */
    public function setIsAuthorizedOrder($isAuthorized)
    {
        $this->isAuthorizedOrder = $isAuthorized;
    }
}