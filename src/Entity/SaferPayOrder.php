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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class SaferPayPayment
 */
class SaferPayOrder extends ObjectModel
{
    /**
     * @var Int|null
     */
    public $id_order;

    /**
     * @var Int|null
     */
    public $id_cart;

    /**
     * @var Int
     */
    public $id_customer;

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $redirect_url;

    /**
     * @var bool
     */
    public $captured;

    /**
     * @var bool
     */
    public $canceled;

    /**
     * @var bool
     */
    public $is_transaction;

    /**
     * @var
     */
    public $transaction_id;

    /**
     * @var
     */
    public $refunded;

    /**
     * @var
     */
    public $refund_id;

    /**
     * @var
     */
    public $authorized;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'saferpay_order',
        'primary' => 'id_saferpay_order',
        'fields' => [
            'id_order' => ['type' => self::TYPE_NOTHING, 'allow_null' => true],
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'transaction_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'refund_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'token' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'redirect_url' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'is_transaction' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'captured' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'canceled' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'refunded' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'authorized' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
        ],
    ];
}
