<?php

declare(strict_types=1);

namespace Invertus\SaferPay\Service;

use Configuration;
use Invertus\SaferPay\Config\SaferPayConfig;

class CardPaymentGroupingService
{
    /**
     * @param array $paymentMethods Raw payment methods from API
     * @param array $allCurrencies List of all supported currencies (for CARD method)
     *
     * @return array Filtered/grouped payment methods
     */
    public function group(array $paymentMethods, array $allCurrencies): array
    {
        $result = [];

        foreach ($paymentMethods as $method) {
            if (!in_array($method['paymentMethod'], SaferPayConfig::CARD_BRANDS, true)) {
                $result[] = $method;
            }
        }

        $result[] = [
            'paymentMethod' => SaferPayConfig::PAYMENT_CARDS,
            'logoUrl' => '',
            'currencies' => $allCurrencies,
        ];

        return $result;
    }
}