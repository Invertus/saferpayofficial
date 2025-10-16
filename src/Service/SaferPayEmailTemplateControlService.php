<?php

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
