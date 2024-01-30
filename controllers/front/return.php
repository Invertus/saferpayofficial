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

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Controller\AbstractSaferPayController;
use Invertus\SaferPay\Core\Payment\DTO\CheckoutData;
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAssertion;
use Invertus\SaferPay\Processor\CheckoutProcessor;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialReturnModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'return';

    /**
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $cartId = Tools::getValue('cartId');
        $secureKey = Tools::getValue('secureKey');
        $isBusinessLicence = (int) Tools::getValue(SaferPayConfig::IS_BUSINESS_LICENCE);
        $fieldToken = Tools::getValue('fieldToken');
        $moduleId = $this->module->id;
        $selectedCard = Tools::getValue('selectedCard');
        $orderId =  Tools::getValue('orderId');

        $cart = new Cart($cartId);

        if (!Validate::isLoadedObject($cart)) {
            $this->ajaxDie(json_encode([
                'error_type' => 'unknown_error',
                'error_text' => $this->module->l('An unknown error error occurred. Please contact support', self::FILENAME),
            ]));
        }

        if ($cart->secure_key !== $secureKey) {
            $this->ajaxDie(json_encode([
                'error_type' => 'unknown_error',
                'error_text' => $this->module->l('An unknown error error occurred. Please contact support', self::FILENAME),
            ]));
        }

        // At this point notify controller possibly already created and order
        if (Order::getIdByCartId($cartId)) {
            Tools::redirect($this->context->link->getModuleLink(
                $this->module->name,
                $this->getSuccessControllerName($isBusinessLicence, $fieldToken),
                [
                    'cartId' => $cartId,
                    'orderId' => $orderId,
                    'moduleId' => $moduleId,
                    'secureKey' => $secureKey,
                    'selectedCard' => $selectedCard,
                ],
                true
            ));
        }

        try {
            /** @var SaferPayTransactionAssertion $transactionAssert */
            $transactionAssert = $this->module->getService(SaferPayTransactionAssertion::class);
            $assertionResponse = $transactionAssert->assert($cartId);

            $checkoutData = CheckoutData::create(
                (int) $cartId,
                $assertionResponse->getPaymentMeans()->getBrand()->getPaymentMethod(),
                (int) $isBusinessLicence
            );

            $checkoutData->setIsAuthorizedOrder(true);
            $checkoutData->setOrderStatus($assertionResponse->getTransaction()->getStatus());

            /** @var CheckoutProcessor $checkoutProcessor **/
            $checkoutProcessor = $this->module->getService(CheckoutProcessor::class);
            $checkoutProcessor->run($checkoutData);

            $orderId = \Order::getIdByCartId($cartId);

            Tools::redirect($this->context->link->getModuleLink(
                $this->module->name,
                $this->getSuccessControllerName($isBusinessLicence, $fieldToken),
                [
                    'cartId' => $cartId,
                    'orderId' => $orderId,
                    'moduleId' => $moduleId,
                    'secureKey' => $secureKey,
                    'selectedCard' => $selectedCard,
                ],
                true
            ));
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                sprintf(
                    'Failed to assert transaction. Message: %s. File name: %s',
                    $e->getMessage(),
                    self::FILENAME
                )
            );

            Tools::redirect($this->context->link->getModuleLink(
                $this->module->name,
                'failValidation',
                [
                    'cartId' => $cartId,
                    'orderId' => $orderId,
                    'secureKey' => $secureKey,
                ],
                true
            ));
        }
    }

    private function getSuccessControllerName($isBusinessLicence, $fieldToken)
    {
        $successController = ControllerName::SUCCESS;

        if ($isBusinessLicence) {
            $successController = ControllerName::SUCCESS_IFRAME;
        }

        if ($fieldToken) {
            $successController = ControllerName::SUCCESS_HOSTED;
        }

        return $successController;
    }
}
