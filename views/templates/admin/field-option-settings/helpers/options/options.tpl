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

{extends file="helpers/options/options.tpl"}

{block name="input" append}
    {if $field['type'] == 'desc'}
        <div class="col-lg-5 {if isset($field['class'])}{$field['class']|escape:'htmlall':'UTF-8'}{/if}">
            {if $field['template'] == 'field-javascript-library-desc.tpl'}
                {include file="../../../partials/field-javascript-library-desc.tpl"}
            {/if}

            {if $field['template'] == 'field-access-token-desc.tpl'}
                {include file="../../../partials/field-access-token-desc.tpl"}
            {/if}

            {if $field['template'] == 'field-hosted-field-template-desc.tpl'}
                {include file="../../../partials/field-hosted-field-template-desc.tpl"}
            {/if}
            {if $field['template'] == 'field-new-order-mail-desc.tpl'}
                {include file="../../../partials/field-new-order-mail-desc.tpl"}
            {/if}
        </div>
    {/if}

    {if $field['type'] == 'select-template'}

        <div class="field-container">
            {foreach from=$field['templateOptions'] key=key item=templateUrl}
                {assign var='key' value=$key + 1} {* To have normal keys without 0 *}
                <label class="field-label">
                    <input type="radio" name="{$field['name']|escape:'htmlall':'UTF-8'}" value="{$key|escape:'htmlall':'UTF-8'}" {if $key == $field['value']}checked{/if}>
                    <img src="{$templateUrl|escape:'htmlall':'UTF-8'}">
                </label>
            {/foreach}
        </div>

    {/if}
{/block}
