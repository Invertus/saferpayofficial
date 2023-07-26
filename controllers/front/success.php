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

use Invertus\SaferPay\Api\Enum\TransactionStatus;
use Invertus\SaferPay\Controller\AbstractSaferPayController;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAssertion;

class SaferPayOfficialSuccessModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'success';

    public function postProcess()
    {
        $cartId = Tools::getValue('cartId');
        $moduleId = Tools::getValue('moduleId');
        $orderId = Tools::getValue('orderId');
        $secureKey = Tools::getValue('secureKey');

        $cart = new Cart($cartId);

        if ($cart->secure_key !== $secureKey) {
            $redirectLink = $this->context->link->getPageLink(
                'order',
                true,
                null,
                [
                    'step' => 1,
                ]
            );

            Tools::redirect($redirectLink);
        }

        try {
            // TODO The shopping cart should be locked to prevent problems with the notify.php process running at the same time.
            // $this->assertTransaction($orderId);

            Tools::redirect($this->context->link->getPageLink(
                'order-confirmation',
                true,
                null,
                [
                    'id_cart' => $cartId,
                    'id_module' => $moduleId,
                    'id_order' => $orderId,
                    'key' => $secureKey,
                ]
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
                    'secureKey' => $secureKey
                ],
                true
            ));
        }
    }

    private function assertTransaction($orderId)
    {
        /** @var SaferPayTransactionAssertion $transactionAssert */
        $transactionAssert = $this->module->getService(SaferPayTransactionAssertion::class);
        $assertionResponse = $transactionAssert->assert($orderId);

        return $assertionResponse;
    }
}
