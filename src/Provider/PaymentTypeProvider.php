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

namespace Invertus\SaferPay\Provider;

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Enum\PaymentType;
use Invertus\SaferPay\Repository\SaferPayFieldRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentTypeProvider
{
    /** @var SaferPayFieldRepository */
    private $saferPayFieldRepository;

    public function __construct(
        SaferPayFieldRepository $saferPayFieldRepository
    ) {
        $this->saferPayFieldRepository = $saferPayFieldRepository;
    }

    /**
     * @param string $paymentMethod
     * @return string
     */
    public function get(string $paymentMethod): string
    {
        // Custom Form ON (Saferpay Fields, Business licence) => Saferpay Fields.
        // Anything else (Custom Form OFF, non-Business) => Saferpay Payment Page.
        // The legacy Transaction Interface (IFRAME) is no longer selectable (SL-374).
        if ($this->isHostedIframeRedirect($paymentMethod)) {
            return PaymentType::HOSTED_IFRAME;
        }

        return PaymentType::BASIC;
    }

    /**
     * @param string $paymentMethod
     * @return bool
     */
    private function isHostedIframeRedirect(string $paymentMethod): bool
    {
        if (!\Configuration::get(SaferPayConfig::BUSINESS_LICENSE . SaferPayConfig::getConfigSuffix())) {
            return false;
        }

        // Grouped cards render a single inline Fields form under the "Cards" option.
        if ($paymentMethod === SaferPayConfig::PAYMENT_CARDS
            && \Configuration::get(SaferPayConfig::SAFERPAY_GROUP_CARDS)
        ) {
            return true;
        }

        // Individual cards use Fields when their "Custom form" toggle is on.
        if (!$this->saferPayFieldRepository->isActiveByName($paymentMethod)) {
            return false;
        }

        return true;
    }
}
