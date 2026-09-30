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

namespace Invertus\SaferPay\Service;

use Invertus\SaferPay\Adapter\Configuration;
use Invertus\SaferPay\Config\SaferPayConfig;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Asking Saferpay to register an alias only makes sense when the shop can offer that card back to
 * the shopper later. The conditions below mirror the ones the checkout uses to display saved cards
 * in SaferPayOfficial::hookPaymentOptions, so the shop never tokenises a card it cannot reuse.
 */
class CardAliasRegistrationGuard
{
    /** @var Configuration */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param mixed $selectedCard value posted by the checkout, 0 means the shopper opted in
     *
     * @return bool
     */
    public function shouldRegister($selectedCard): bool
    {
        if ((int) $selectedCard !== SaferPayConfig::CREDIT_CARD_OPTION_SAVE) {
            return false;
        }

        if (!$this->configuration->getAsBoolean(SaferPayConfig::CREDIT_CARD_SAVE)) {
            return false;
        }

        // Saved cards are re-used through an alias transaction, which requires a business licence.
        if (!$this->configuration->getAsBoolean(SaferPayConfig::BUSINESS_LICENSE . SaferPayConfig::getConfigSuffix())) {
            return false;
        }

        return true;
    }
}
