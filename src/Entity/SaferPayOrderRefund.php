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

/**
 * Class SaferPayPayment
 */
class SaferPayOrderRefund extends ObjectModel
{
    /**
     * @var Int
     */
    public $id_saferpay_order;

    /**
     * @var Int
     */
    public $id_order;

    /**
     * @var string
     */
    public $transaction_id;

    /**
     * @var string
     */
    public $status;

    /**
     * @var int
     */
    public $amount;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'saferpay_order_refund',
        'primary' => 'id_saferpay_order_refund',
        'fields' => [
            'id_saferpay_order' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'transaction_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'status' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'amount' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'currency' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
        ],
    ];
}
