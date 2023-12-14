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

use Context;
use Exception;
use Invertus\SaferPay\Adapter\Configuration;
use Invertus\SaferPay\Adapter\LegacyContext;
use Invertus\SaferPay\Api\Request\InitializeService;
use Invertus\SaferPay\DTO\Request\Initialize\InitializeRequest;
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Repository\SaferPayCardAliasRepository;
use Invertus\SaferPay\Factory\ModuleFactory;
use Invertus\SaferPay\Service\Request\InitializeRequestObjectCreator;
use Invertus\SaferPay\Config\SaferPayConfig;
use Order;
use SaferPayOfficial;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayInitialize
{
    /**
     * @var SaferPayOfficial
     */
    private $module;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var InitializeService
     */
    private $initializeService;

    /**
     * @var InitializeRequestObjectCreator
     */
    private $requestObjectCreator;

    /** @var SaferPayCardAliasRepository */
    private $saferPayCardAliasRepository;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(
        ModuleFactory $module,
        LegacyContext $context,
        InitializeService $initializeService,
        InitializeRequestObjectCreator $requestObjectCreator,
        SaferPayCardAliasRepository $saferPayCardAliasRepository,
        Configuration $configuration
    ) {
        $this->module = $module->getModule();
        $this->context = $context->getContext();
        $this->initializeService = $initializeService;
        $this->requestObjectCreator = $requestObjectCreator;
        $this->saferPayCardAliasRepository = $saferPayCardAliasRepository;
        $this->configuration = $configuration;
    }

    public function initialize(InitializeRequest $initializeRequest, $isBusinessLicence)
    {
        try {
            $initialize = $this->initializeService->initialize($initializeRequest, $isBusinessLicence);
        } catch (Exception $e) {
            throw new SaferPayApiException('Initialize API failed', SaferPayApiException::INITIALIZE);
        }

        return $initialize;
    }

    public function buildRequest(
        $paymentMethod,
        $isBusinessLicence,
        $selectedCard = -1,
        $fieldToken = null,
        $successController = null
    ) {
        $customerEmail = $this->context->customer->email;
        $cartId = $this->context->cart->id;
        $creationAfterInitialization = $this->configuration->getAsBoolean(SaferPayConfig::SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION);
        $alias = $this->saferPayCardAliasRepository->getSavedCardAliasFromId($selectedCard);

        $successUrl = $this->context->link->getModuleLink(
            $this->module->name,
            ControllerName::RETURN_URL,
            [
                'cartId' => $cartId,
                'secureKey' => $this->context->cart->secure_key,
                'orderId' => $creationAfterInitialization ? 0 :Order::getOrderByCartId($cartId),
                'moduleId' => $this->module->id,
                'selectedCard' => $selectedCard,
                'isBusinessLicence' => $isBusinessLicence,
                'fieldToken' => $fieldToken,
            ],
            true
        );

        $notifyUrl = $this->context->link->getModuleLink(
            $this->module->name,
            ControllerName::NOTIFY,
            [
                'success' => 1,
                'cartId' => $this->context->cart->id,
                'orderId' => $creationAfterInitialization ? 0 :Order::getOrderByCartId($cartId),
                'secureKey' => $this->context->cart->secure_key,
            ],
            true
        );

        $failUrl = $this->context->link->getModuleLink(
            $this->module->name,
            'failValidation',
            [
                'cartId' => $this->context->cart->id,
                'secureKey' => $this->context->cart->secure_key,
                'orderId' => $creationAfterInitialization ? 0 :Order::getOrderByCartId($cartId),
                'moduleId' => $this->module->id,
                'isBusinessLicence' => $isBusinessLicence,
            ],
            true
        );

        $initializeRequest = $this->requestObjectCreator->create(
            $this->context->cart,
            $customerEmail,
            $paymentMethod,
            $successUrl,
            $notifyUrl,
            $failUrl,
            $this->context->cart->id_address_delivery,
            $this->context->cart->id_address_invoice,
            $this->context->cart->id_customer,
            $alias,
            $fieldToken
        );

        return $initializeRequest;
    }

    /**
     * @param int $isBusinessLicence
     * @param string $fieldToken
     *
     * @return string
     */
    private function getSuccessControllerName($isBusinessLicence, $fieldToken)
    {
        $successController = 'success';
        if ($isBusinessLicence) {
            $successController = 'successIFrame';
        }
        if ($fieldToken) {
            $successController = 'successHosted';
        }
        return $successController;
    }
}
