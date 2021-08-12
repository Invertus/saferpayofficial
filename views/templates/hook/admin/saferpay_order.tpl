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

<div id="formAddPaymentPanel" class="panel card">
    <div class="panel-heading card-header">
        <i class="icon-money"></i>
        {l s='SaferPayOfficial' mod='saferpayofficial'} <span class="badge"></span>
    </div>
    {if !$liability_shift}
        <div class="alert alert-warning" role="alert">
            {l s='Payment status: Authorize. This payment failed security check (liability: false). If you want, you can still capture manually on your own responsibility.' mod='saferpayofficial'}
        </div>
    {/if}
    <div>
        <form id="saferpay-admin-form" class="ml-4 mt-4 mb-2" method="post" action="{$action|escape:'htmlall':'UTF-8'}">
            {if $transactionPaid == 'CAPTURED'}
                {if !$isSaferPayRefunded && !$isSaferPayCanceled}
                    <div class="mb-2 form-inline">
                        <div class="input-group money-type row">
                            <div class="input-group">
                                <span class=" input-group-addon input-group-text"> {$currencySign|escape:'htmlall':'UTF-8'}</span>
                                <input type="number"
                                       name="saferpay_refund_amount"
                                       class="form-control"
                                       step=".01"
                                       min="0"
                                       max="{($authAmount|escape:'htmlall':'UTF-8' - $refund_amount|escape:'htmlall':'UTF-8') / $amountMultiplier|escape:'htmlall':'UTF-8'}"
                                       value="{($authAmount|escape:'htmlall':'UTF-8' - $refund_amount|escape:'htmlall':'UTF-8') / $amountMultiplier|escape:'htmlall':'UTF-8'}">
                            </div>
                            <div class="saferpay-refund-button">
                                <button class="btn btn-primary" type="submit"
                                        name="submitRefundOrder">{l s='Refund' mod='saferpayofficial'}</button>
                            </div>
                        </div>
                    </div>
                {/if}
            {elseif $transactionPaid == 'AUTHORIZED'}
                <button class="btn btn-primary ml-4 mb-2" type="submit"
                        name="submitCaptureOrder">{l s='Capture' mod='saferpayofficial'}</button>
                <button class="btn btn-primary mb-2" type="submit"
                        name="submitCancelOrder">{l s='Cancel this order' mod='saferpayofficial'}</button>
            {/if}
        </form>
        <div>
            <h1 class="pl-4">{l s='Transaction details:' mod='saferpayofficial'}</h1>
        </div>
        <div class="row card-body">
            <div class="col-lg-4">
                <div>
                    <p>{l s='Authorisation Amount:' mod='saferpayofficial'} {$authAmount|escape:'htmlall':'UTF-8' / $amountMultiplier|escape:'htmlall':'UTF-8'}</p>
                </div>
                <div>
                    <p>{l s='Refunded Amount:' mod='saferpayofficial'} {$refund_amount|escape:'htmlall':'UTF-8' / $amountMultiplier|escape:'htmlall':'UTF-8'}</p>
                </div>
                <div>
                    <p>
                        {l s='Currency:' mod='saferpayofficial'} {$currency|escape:'htmlall':'UTF-8'}
                    </p>
                </div>
                {if $dcc_currency_code}
                    <div>
                        <p>{l s='Converted amount:' mod='saferpayofficial'} {$dcc_value|escape:'htmlall':'UTF-8'}</p>
                    </div>
                    <div>
                        <p>{l s='Converted currency:' mod='saferpayofficial'} {$dcc_currency_code|escape:'htmlall':'UTF-8'}</p>
                    </div>
                {/if}
            </div>
            <div class="col-lg-4">
                <div>
                    <p>
                        {l s='Transaction authorized:' mod='saferpayofficial'} {$transactionAuth|escape:'htmlall':'UTF-8'}
                    </p>
                </div>
                <div>
                    <p>
                        {l s='Card expiry date:' mod='saferpayofficial'} {$cardExpiryDate|escape:'htmlall':'UTF-8'}
                    </p>
                </div>
                <div>
                    <p>
                        {l s='Transaction uncertain:' mod='saferpayofficial'} {$transactionUncertain|escape:'htmlall':'UTF-8'}
                    </p>
                </div>
                <div>
                    <p>
                        {l s='Brand:' mod='saferpayofficial'} {$brand|escape:'htmlall':'UTF-8'}
                    </p>
                </div>
                <div>
                    <p>
                        {l s='Payment Method:' mod='saferpayofficial'} {$paymentMethod|escape:'htmlall':'UTF-8'}
                    </p>
                </div>
            </div>
            <div class="col-lg-4">
                <div>
                    <p>
                        {l s='Transaction paid:' mod='saferpayofficial'} {$transactionPaid|escape:'htmlall':'UTF-8'}
                    </p>
                </div>
                <div>
                    <p>
                        {l s='Payment ID:' mod='saferpayofficial'} {$paymentId|escape:'htmlall':'UTF-8'}
                    </p>
                </div>
                {if $liability_entity}
                    <div>
                        <p>
                            {l s='Liability entity:' mod='saferpayofficial'} {$liability_entity|escape:'htmlall':'UTF-8'}
                        </p>
                    </div>
                    <div>
                        <p>
                            {l s='Is Payment safe:' mod='saferpayofficial'} {$liability_shift|escape:'htmlall':'UTF-8'}
                        </p>
                    </div>
                {else}
                    <div>
                        <p>
                            {l s='Card on File:' mod='saferpayofficial'}
                        </p>
                    </div>
                {/if}
                <div>
                    <p>
                        {l s='Card number:' mod='saferpayofficial'} {$cardNumber|escape:'htmlall':'UTF-8'}
                    </p>
                </div>
                <div>
                    <p>
                        {l s='Canceled: ' mod='saferpayofficial'}
                        {if $isSaferPayCanceled}
                            {l s='Yes' mod='saferpayofficial'}
                        {else}
                            {l s='No' mod='saferpayofficial'}
                        {/if}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
