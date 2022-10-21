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

use Configuration;
use Context;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Enum\PaymentType;
use Invertus\SaferPay\Repository\SaferPayFieldRepository;

class PaymentRedirectionProvider
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var string
     */
    private $moduleName;

    /** @var PaymentTypeProvider */
    private $paymentTypeProvider;

    public function __construct(Context $context, $moduleName, PaymentTypeProvider $paymentTypeProvider)
    {
        $this->context = $context;
        $this->moduleName = $moduleName;
        $this->paymentTypeProvider = $paymentTypeProvider;
    }

    /**
     * @param string $paymentMethod
     *
     * @return string
     */
    public function provideRedirectionLinkByPaymentMethod($paymentMethod)
    {
        $paymentType = $this->paymentTypeProvider->get($paymentMethod);

        if ($paymentType === PaymentType::HOSTED_IFRAME) {
            return $this->context->link->getModuleLink(
                $this->moduleName,
                ControllerName::HOSTED_IFRAME,
                ['saved_card_method' => $paymentMethod, SaferPayConfig::IS_BUSINESS_LICENCE => true],
                true
            );
        }

        if ($paymentType === PaymentType::IFRAME) {
            return $this->context->link->getModuleLink(
                $this->moduleName,
                ControllerName::IFRAME,
                ['saved_card_method' => $paymentMethod, SaferPayConfig::IS_BUSINESS_LICENCE => true],
                true
            );
        }

        return $this->context->link->getModuleLink(
            $this->moduleName,
            ControllerName::VALIDATION,
            ['saved_card_method' => $paymentMethod, SaferPayConfig::IS_BUSINESS_LICENCE => false],
            true
        );
    }
}
