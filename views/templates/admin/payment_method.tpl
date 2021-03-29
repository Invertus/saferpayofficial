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

<div class="form-group saferpay-form-group">
    <div class="col-lg-2">
        <div class="checkbox">
            <label class="container-checkbox">
                <input type="checkbox"
                       id="{$paymentMethod|cat:'_enable'|escape:'html':'UTF-8'}_on"
                       name="{$paymentMethod|cat:'_enable'|escape:'html':'UTF-8'}"
                       value="1"
                       {if $is_active}checked="checked"{/if}>
                <span class="checkmark"></span>

            </label>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="checkbox">
            <label class="container-checkbox">
                <input type="checkbox"
                       id="{$paymentMethod|cat:'_logo'|escape:'html':'UTF-8'}_on"
                       name="{$paymentMethod|cat:'_logo'|escape:'html':'UTF-8'}"
                       value="1"
                       {if $is_logo_active}checked="checked"{/if}>
                <span class="checkmark"></span>

            </label>
        </div>
    </div>

        <div class="col-lg-2">
            <div class="checkbox">
                {if $paymentMethod|in_array:$supported_field_payments}

                <label class="container-checkbox">
                    <input type="checkbox"
                           id="{$paymentMethod|cat:'_field'|escape:'htmlall':'UTF-8'}_on"
                           name="{$paymentMethod|cat:'_field'|escape:'htmlall':'UTF-8'}"
                           value="1"
                           {if $is_field_active}checked="checked"{/if}>
                    <span class="checkmark"></span>

                </label>
                {/if}

            </div>
        </div>

    <div class="col-lg-3">
        {html_options
        name=$paymentMethod|cat:'_countries[]'
        id=$paymentMethod|cat:'_countries'
        class='chosen js-chosen'
        options=$countryOptions
        selected=$countrySelect
        multiple='multiple'
        }
    </div>
    <div class="col-lg-3">
        {html_options
        name=$paymentMethod|cat:'_currencies[]'
        id=$paymentMethod|cat:'_currencies'
        class='chosen js-chosen'
        options=$currencyOptions
        selected=$currencySelect
        multiple='multiple'
        }
    </div>
</div>