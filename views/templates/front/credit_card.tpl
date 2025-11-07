{**
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
 *}

<tr>
    <td>
        <img src="{$card_img|escape:'htmlall':'UTF-8'}">
    </td>
    <td>
        {$payment_method|escape:'htmlall':'UTF-8'}
    </td>
    <td>
        {$date_add|escape:'htmlall':'UTF-8'}
    </td>
    <td>
        {$date_ends|escape:'htmlall':'UTF-8'}
    </td>
    <td>
        {$card_number|escape:'htmlall':'UTF-8'}
    </td>
    <td>
        <a class="button lnk_view btn btn-default"
           href="{$link->getModuleLink('saferpayofficial', {$controller|escape:'htmlall':'UTF-8'}, ['saved_card_id' => $saved_card_id|escape:'htmlall':'UTF-8'])|escape:'htmlall':'UTF-8' }">
            <span>
                {l s='Remove' mod='saferpayofficial'}
            </span>
        </a>
    </td>
</tr>