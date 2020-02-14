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

<div class="saved_cards">
    <input type="hidden" class="saved_card_method" name="saved_card_method" value="{$paymentMethod}">
    {$selected = 1}
    {foreach $savedCards as $savedCard}
        <div class="saved_credit_cards">
            <input type="radio" name="saved_card_{$paymentMethod}" value="{$savedCard['id_saferpay_card_alias']}"
                   {if $selected }checked="checked" {$selected = 0}{/if}
            >
            <span>{$savedCard['card_number']}</span>
        </div>
    {/foreach}
    <div class="saved_credit_cards">
        <input type="radio" name="saved_card_{$paymentMethod}" value="0"
               {if $selected }checked="checked"{/if}
        >
        <span>{l s='Use new card and save it' mod='saferpayofficial'}</span>
    </div>
    <div class="saved_credit_cards">
        <input type="radio" name="saved_card_{$paymentMethod}" value="-1">
        <span>{l s='Use new card once' mod='saferpayofficial'}</span>
    </div>
</div>