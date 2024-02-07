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

{include file="./partials/all_errors_16.tpl"}

<div class="card card-block">

    <div id="main">

        <div class="input-container">
            <div class="form-group row col-sm-7 input-box">
                <div class="col col-sm-12 col-md-6">
                    <input type="input" class="saferpay-input form-control" id="fields-card-number""
                    placeholder="{l s='Card number' mod='saferpayofficial'}" readonly>
                </div>

                <div class="col col-sm-12 col-md-3">
                    <input type="input" class="saferpay-input form-control" id="fields-expiration"
                           placeholder="{l s='Exp' mod='saferpayofficial'}" readonly>
                </div>

                <div class="col col-sm-12 col-md-3">
                    <input type="input" class="saferpay-input form-control" id="fields-cvc"
                           placeholder="{l s='Cvc' mod='saferpayofficial'}" readonly>
                </div>
            </div>
        </div>

        <div class="button-container">
            <div class="form-group row button-box col-sm-7">
                <button class="btn btn-primary submit-button" id="submit_hosted_field"
                        disabled="disabled">{l s='Pay' mod='saferpayofficial'}</button>
            </div>
        </div>

        <div class="credit-card-container">
            <div class="image-container">
                <div id="credit-card" class="credit-card-image img-fluid"></div>
            </div>
        </div>

    </div>

    <input type="hidden" name="saferpay_selected_card" value="{$saferpay_selected_card|escape:'htmlall':'UTF-8'}" />
</div>