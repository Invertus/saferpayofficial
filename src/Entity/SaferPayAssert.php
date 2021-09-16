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

class SaferPayAssert extends ObjectModel
{
    public $id_saferpay_order;
    public $amount;
    public $status;
    public $exp_year;
    public $exp_month;
    public $currency_code;
    public $uncertain;
    public $brand;
    public $payment_method;
    public $transaction_paid;
    public $merchant_reference;
    public $payment_id;
    public $acceptance;
    public $is_test;
    public $card_number;
    public $refunded_amount;
    public $pending_refund_amount;
    public $liability_shift;
    public $liability_entity;
    public $dcc_value;
    public $dcc_currency_code;
    public $authorized;

    public static $definition = [
        'table' => 'saferpay_assert',
        'primary' => 'id_saferpay_assert',
        'fields' => [
            'id_saferpay_order' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'amount' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'status' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'exp_year' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'exp_month' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'currency_code' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'uncertain' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'brand' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'payment_method' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'transaction_paid' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'merchant_reference' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'payment_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'acceptance' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'is_test' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'liability_shift' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'liability_entity' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'card_number' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'refunded_amount' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'pending_refund_amount' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'dcc_value' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'dcc_currency_code' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'authorized' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
        ],
    ];
}
