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

namespace Invertus\SaferPay\Presentation\ViewModel;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * ViewModel for payment options display
 * Prepares data for payment method rendering on checkout page
 */
class PaymentOptionViewModel
{
    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var string
     */
    private $paymentMethodName;

    /**
     * @var string
     */
    private $logoUrl;

    /**
     * @var string
     */
    private $actionUrl;

    /**
     * @var array
     */
    private $savedCards;

    /**
     * @var int
     */
    private $selectedCardId;

    /**
     * @var bool
     */
    private $isCreditCardSavingEnabled;

    /**
     * @param string $paymentMethod
     * @param string $paymentMethodName
     * @param string $logoUrl
     * @param string $actionUrl
     * @param array $savedCards
     * @param int $selectedCardId
     * @param bool $isCreditCardSavingEnabled
     */
    public function __construct(
        $paymentMethod,
        $paymentMethodName,
        $logoUrl,
        $actionUrl,
        array $savedCards = [],
        $selectedCardId = 0,
        $isCreditCardSavingEnabled = false
    ) {
        $this->paymentMethod = $paymentMethod;
        $this->paymentMethodName = $paymentMethodName;
        $this->logoUrl = $logoUrl;
        $this->actionUrl = $actionUrl;
        $this->savedCards = $savedCards;
        $this->selectedCardId = $selectedCardId;
        $this->isCreditCardSavingEnabled = $isCreditCardSavingEnabled;
    }

    /**
     * Get payment method identifier
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Get translated payment method name
     *
     * @return string
     */
    public function getPaymentMethodName()
    {
        return $this->paymentMethodName;
    }

    /**
     * Get logo URL
     *
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    /**
     * Get action URL for payment
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->actionUrl;
    }

    /**
     * Get saved cards for this payment method
     *
     * @return array
     */
    public function getSavedCards()
    {
        return $this->savedCards;
    }

    /**
     * Get selected card ID
     *
     * @return int
     */
    public function getSelectedCardId()
    {
        return $this->selectedCardId;
    }

    /**
     * Check if credit card saving is enabled
     *
     * @return bool
     */
    public function isCreditCardSavingEnabled()
    {
        return $this->isCreditCardSavingEnabled;
    }

    /**
     * Check if there are saved cards
     *
     * @return bool
     */
    public function hasSavedCards()
    {
        return !empty($this->savedCards);
    }

    /**
     * Convert to array for template assignment
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'paymentMethod' => $this->paymentMethod,
            'paymentMethodName' => $this->paymentMethodName,
            'logoUrl' => $this->logoUrl,
            'actionUrl' => $this->actionUrl,
            'savedCards' => $this->savedCards,
            'selectedCardId' => $this->selectedCardId,
            'isCreditCardSavingEnabled' => $this->isCreditCardSavingEnabled,
            'hasSavedCards' => $this->hasSavedCards(),
        ];
    }
}
