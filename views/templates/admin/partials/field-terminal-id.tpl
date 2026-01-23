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
<div class="saferpay-terminal-selector" data-environment="{$field['environment']|escape:'htmlall':'UTF-8'}">
    <div class="input-group fixed-width-xl">
        <select
            name="{$key|escape:'htmlall':'UTF-8'}"
            id="{$key|escape:'htmlall':'UTF-8'}"
            class="saferpay-terminal-select form-control"
            data-current-value="{$field['value']|escape:'htmlall':'UTF-8'}"
            {if !isset($field['terminals']) || count($field['terminals']) == 0}disabled{/if}>
            <option value="">{l s='-- Select Terminal --' mod='saferpayofficial'}</option>
            {if isset($field['terminals']) && count($field['terminals']) > 0}
                {foreach from=$field['terminals'] item=terminal}
                    <option value="{$terminal['TerminalId']|escape:'htmlall':'UTF-8'}"
                        {if $field['value'] == $terminal['TerminalId']}selected{/if}>
                        {$terminal['TerminalId']|escape:'htmlall':'UTF-8'}{if $terminal['Description']} - {$terminal['Description']|escape:'htmlall':'UTF-8'}{/if}
                    </option>
                {/foreach}
            {/if}
        </select>
        <span class="input-group-btn">
            <button type="button" class="btn btn-default saferpay-refresh-terminals" title="{l s='Refresh Terminals' mod='saferpayofficial'}">
                <i class="icon-refresh"></i>
            </button>
        </span>
    </div>
    <input
        type="text"
        name="{$key|escape:'htmlall':'UTF-8'}_manual"
        id="{$key|escape:'htmlall':'UTF-8'}_manual"
        class="saferpay-terminal-manual-input form-control fixed-width-xl"
        placeholder="{l s='Or enter Terminal ID manually' mod='saferpayofficial'}"
        value="{if (!isset($field['terminals']) || count($field['terminals']) == 0) && $field['value']}{$field['value']|escape:'htmlall':'UTF-8'}{/if}"
        style="margin-top: 5px; {if isset($field['terminals']) && count($field['terminals']) > 0}display: none;{/if}"
    />
    <p class="help-block" data-empty-text="{l s='Please configure Customer ID, Username, and Password to load terminals' mod='saferpayofficial'}">
        {if !isset($field['terminals']) || count($field['terminals']) == 0}
            {l s='Please configure Customer ID, Username, and Password to load terminals' mod='saferpayofficial'}
        {else}
            {l s='Select a terminal from the list' mod='saferpayofficial'}
        {/if}
    </p>
</div>
