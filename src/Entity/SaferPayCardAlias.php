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

class SaferPayCardAlias extends ObjectModel
{
    public $id_customer;
    public $success;
    public $alias_id;
    public $lifetime;
    public $valid_till;
    public $date_add;
    public $date_upd;
    public $card_number;
    public $payment_method;

    public static $definition = [
        'table' => 'saferpay_card_alias',
        'primary' => 'id_saferpay_card_alias',
        'fields' => [
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'success' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'alias_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'lifetime' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'card_number' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'payment_method' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'valid_till' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
