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

class SaferPayLog extends ObjectModel
{
    public $id_saferpay_log;

    public $id_log;

    public $id_shop;

    public $request;

    public $response;

    public $context;

    public $date_add;

    public static $definition = [
        'table' => 'saferpay_log',
        'primary' => 'id_saferpay_log',
        'fields' => [
            'id_log' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'request' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'response' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'context' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
