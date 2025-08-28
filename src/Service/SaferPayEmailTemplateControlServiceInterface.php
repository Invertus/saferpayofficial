<?php

declare(strict_types=1);

namespace Invertus\SaferPay\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface SaferPayEmailTemplateControlServiceInterface
{
    public function shouldSendEmail(array $params): bool;
}
