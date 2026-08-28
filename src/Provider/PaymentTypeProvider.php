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

use Invertus\SaferPay\Adapter\Configuration;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Enum\PaymentType;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentTypeProvider
{
    /** @var Configuration */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $paymentMethod
     * @return string
     */
    public function get(string $paymentMethod): string
    {
        // Saferpay Fields prerequisites met => inline card form (Saferpay Fields).
        // Anything else => Saferpay Payment Page (redirect).
        // The legacy Transaction Interface (IFRAME) is no longer selectable (SL-374).
        if ($this->isSaferPayFieldsPayment($paymentMethod)) {
            return PaymentType::HOSTED_IFRAME;
        }

        return PaymentType::BASIC;
    }

    /**
     * Resolves the flow on the return leg from how the payment was initialized, not from the brand
     * Saferpay reports back. A field token means the shopper paid through Saferpay Fields, whatever
     * card they ended up typing into it.
     *
     * @param string $paymentMethod
     * @param string|null $fieldToken
     * @param bool $usingSavedCard
     *
     * @return string
     */
    public function getForReturn(string $paymentMethod, $fieldToken = null, bool $usingSavedCard = false): string
    {
        if (!empty($fieldToken) || $usingSavedCard) {
            return PaymentType::HOSTED_IFRAME;
        }

        return $this->get($paymentMethod);
    }

    /**
     * Card payments use Saferpay Fields only when every prerequisite holds: a Business licence,
     * the "Use Saferpay Fields" setting, and a Fields access token. A missing prerequisite falls
     * back to the Payment Page, so the checkout never offers a card form it cannot render.
     *
     * @param string $paymentMethod
     * @return bool
     */
    private function isSaferPayFieldsPayment(string $paymentMethod): bool
    {
        if (!$this->isCardPaymentMethod($paymentMethod)) {
            return false;
        }

        $suffix = $this->configuration->getAsBoolean(SaferPayConfig::TEST_MODE)
            ? SaferPayConfig::TEST_SUFFIX
            : '';

        if (!$this->configuration->getAsBoolean(SaferPayConfig::BUSINESS_LICENSE . $suffix)) {
            return false;
        }

        if (!$this->configuration->getAsBoolean(SaferPayConfig::SAFERPAY_USE_FIELDS)) {
            return false;
        }

        if (empty($this->configuration->get(SaferPayConfig::FIELDS_ACCESS_TOKEN . $suffix))) {
            return false;
        }

        return true;
    }

    /**
     * @param string $paymentMethod
     * @return bool
     */
    private function isCardPaymentMethod(string $paymentMethod): bool
    {
        return $paymentMethod === SaferPayConfig::PAYMENT_CARDS
            || in_array($paymentMethod, SaferPayConfig::FIELD_SUPPORTED_PAYMENT_METHODS, true);
    }
}
