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

namespace Invertus\SaferPay\Infrastructure\Hook\Action;

use Context;
use Invertus\SaferPay\Infrastructure\Hook\HookInterface;
use Invertus\SaferPay\Presentation\Loader\PaymentFormAssetLoader;
use Invertus\SaferPay\Validation\ValidateIsAssetsRequired;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Hook handler for hookActionFrontControllerSetMedia
 * Registers JS/CSS assets on checkout/payment pages
 */
class FrontControllerSetMediaHook implements HookInterface
{
    /**
     * @var ValidateIsAssetsRequired
     */
    private $validateIsAssetsRequired;

    /**
     * @var PaymentFormAssetLoader
     */
    private $paymentFormAssetsLoader;

    /**
     * @var Context
     */
    private $context;

    public function __construct(
        ValidateIsAssetsRequired $validateIsAssetsRequired,
        PaymentFormAssetLoader $paymentFormAssetsLoader,
        Context $context
    ) {
        $this->validateIsAssetsRequired = $validateIsAssetsRequired;
        $this->paymentFormAssetsLoader = $paymentFormAssetsLoader;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $params)
    {
        // Check if controller is available
        if (!$this->context->controller) {
            return;
        }

        if (!$this->validateIsAssetsRequired->run($this->context->controller)) {
            return;
        }

        $this->paymentFormAssetsLoader->register($this->context->controller);
        $this->paymentFormAssetsLoader->registerErrorBags();
    }
}
