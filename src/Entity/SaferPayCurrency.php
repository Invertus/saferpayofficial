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
 * Class SaferPayCurrency
 */
class SaferPayCurrency extends ObjectModel
{
    /**
     * @var string
     */
    public $payment_name;

    /**
     * @var int
     */
    public $id_currency;

    /**
     * @var bool
     */
    public $all_currencies;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'saferpay_currency',
        'primary' => 'id_saferpay_currency',
        'fields' => [
            'payment_name' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'id_currency' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'all_currencies' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
        ],
    ];
}
