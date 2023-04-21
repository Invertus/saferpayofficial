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
 * @author INVERTUS UAB www.invertus.eu  <support@invertus.eu>
 * @copyright SIX Payment Services
 * @license   SIX Payment Services
 */

namespace Invertus\SaferPay\Service\PaymentRestrictionValidation;

use Invertus\SaferPay\Adapter\LegacyContext;
use Invertus\SaferPay\Config\SaferPayConfig;

class ApplePayPaymentRestrictionValidation implements PaymentRestrictionValidationInterface
{
    /**
     * @var LegacyContext
     */
    private $context;

    public function __construct(LegacyContext $context)
    {
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function isValid($paymentName)
    {
        return $this->isIosDevice() || $this->isMacDesktop();
    }

    /**
     * @inheritdoc
     */
    public function supports($paymentName)
    {
        return \Tools::strtoupper($paymentName) == SaferPayConfig::PAYMENT_APPLEPAY;
    }

    /**
     * ApplePay works in Test mode with all browsers and devices
     *
     * @return bool
     */
    private function isIosDevice()
    {
        if (SaferPayConfig::isTestMode()) {
            return true;
        }

        return (bool) $this->context->getMobileDetect()->is('ios');
    }

    private function isMacDesktop()
    {
        if (SaferPayConfig::isTestMode()) {
            return true;
        }

        $device = $this->context->getDeviceDetect();

        return $device === 1 && strpos($_SERVER['HTTP_USER_AGENT'], 'Macintosh') !== false;
    }
}
