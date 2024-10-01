<?php

namespace Invertus\SaferPay\Logger\Formatter;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LogFormatter implements LogFormatterInterface
{
    const SAFERPAY_LOG_PREFIX = 'SAFERPAY_MODULE_LOG:';

    public function getMessage(string $message): string
    {
        return self::SAFERPAY_LOG_PREFIX . ' ' . $message;
    }
}