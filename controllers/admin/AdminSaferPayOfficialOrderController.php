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
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Service\SaferPayExceptionService;
use Invertus\SaferPay\Service\SaferPayOrderStatusService;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminSaferPayOfficialOrderController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function postProcess()
    {
        /** @var SaferPayOrderStatusService $orderStatusService */
        $orderStatusService = $this->module->getService(SaferPayOrderStatusService::class);

        if (SaferPayConfig::isVersionAbove177()) {
            $orderId = Tools::getValue('orderId');
        } else {
            $orderId = Tools::getValue('id_order');
        }
        $order = new Order($orderId);

        try {
            if (Tools::isSubmit('submitCaptureOrder')) {
                $orderStatusService->capture($order);
                $this->context->cookie->captured = true;
            } elseif (Tools::isSubmit('submitCancelOrder')) {
                $orderStatusService->cancel($order);
                $this->context->cookie->canceled = true;
            } elseif (Tools::isSubmit('submitRefundOrder')) {
                $refundAmount = Tools::getValue('saferpay_refund_amount');
                $orderStatusService->refund($order, $refundAmount);
                $this->context->cookie->refunded = true;
            }
        } catch (SaferPayApiException $e) {
            /** @var SaferPayExceptionService $exceptionService */
            $exceptionService = $this->module->getService(SaferPayExceptionService::class);
            $saferPayErrors = json_decode($this->context->cookie->saferPayErrors, true);
            $saferPayErrors[$orderId] = $exceptionService->getErrorMessageForException(
                $e,
                $exceptionService->getErrorMessages()
            );
            $this->context->cookie->saferPayErrors = json_encode($saferPayErrors);
        }

        if (SaferPayConfig::isVersionAbove177()) {
            $orderLink = $this->context->link->getAdminLink(
                'AdminOrders',
                true,
                [
                    'orderId' => $orderId,
                    'vieworder' => 1,
                ],
            );
        } else {
            $orderLink = $this->context->link->getAdminLink('AdminOrders') . '&id_order=' . $orderId . '&vieworder';
        }

        Tools::redirectAdmin($orderLink);
    }
}
