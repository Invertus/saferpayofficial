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

declare(strict_types=1);

namespace Invertus\SaferPay\Service;

use Configuration;
use Invertus\SaferPay\Config\SaferPayConfig;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayEmailTemplateControlService implements SaferPayEmailTemplateControlServiceInterface
{
    private const CONTROLLED_TEMPLATES = [
        'new_order' => SaferPayConfig::SAFERPAY_SEND_NEW_ORDER_MAIL,
        'order_conf' => SaferPayConfig::SAFERPAY_SEND_ORDER_CONF_MAIL,
    ];

    public function shouldSendEmail(array $params): bool
    {
        if (!$this->isSaferPayOrder($params)) {
            return true;
        }

        if (!$this->isControlledTemplate($params['template'])) {
            return true;
        }

        return $this->isTemplateEnabled($params['template']);
    }

    private function isSaferPayOrder(array $params): bool
    {
        if (!isset($params['cart'])) {
            return false;
        }

        $order = Order::getByCartId($params['cart']->id);

        return $order && $order->module === 'saferpayofficial';
    }

    private function isControlledTemplate(string $template): bool
    {
        return isset(self::CONTROLLED_TEMPLATES[$template]);
    }

    private function isTemplateEnabled(string $template): bool
    {
        $configKey = self::CONTROLLED_TEMPLATES[$template];

        return (bool) Configuration::get($configKey);
    }
}
