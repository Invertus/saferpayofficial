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
use Invertus\SaferPay\DTO\Response\Assert\AssertBody;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAssertion;

class SaferPayOfficialReturnModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'return';

    /**
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $cartId = Tools::getValue('cartId');
        $orderId = Tools::getValue('orderId');
        $secureKey = Tools::getValue('secureKey');
        $isBusinessLicence = (int) Tools::getValue(SaferPayConfig::IS_BUSINESS_LICENCE);
        $fieldToken = Tools::getValue('fieldToken');

        $cart = new Cart($cartId);
        if ($cart->secure_key !== $secureKey) {
            $this->ajaxRender();
        }

        try {
            $assertResponseBody = $this->assertTransaction($orderId);
            $transactionStatus = $assertResponseBody->getTransaction()->getStatus();
            if ($transactionStatus === 'CANCELED'){
                $redirectLink = $this->context->link->getPageLink(
                    'failValidation',
                    true,
                    null,
                    [
                        'step' => 1,
                    ]
                );
            } else {
                $redirectLink = $this->context->link->getPageLink(
                    $this->getSuccessControllerName($isBusinessLicence, $fieldToken),
                    true,
                    null,
                    [
                        'step' => 1,
                    ]
                );
            }
            Tools::redirect($redirectLink);
        } catch (Exception $e) {
            $this->ajaxRender();
        }
    }

    /**
     * @param $cartId
     * @return AssertBody
     * @throws Exception
     */
    private function assertTransaction($orderId)
    {
        /** @var SaferPayTransactionAssertion $transactionAssert */
        $transactionAssert = $this->module->getService(SaferPayTransactionAssertion::class);
        $assertionResponse = $transactionAssert->assert($orderId);

        return $assertionResponse;
    }

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