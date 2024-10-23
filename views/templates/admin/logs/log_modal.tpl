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
<div
        class="btn btn-default button js-log-button"
        data-toggle="modal"
        data-log-id="{$log_id|escape:'htmlall':'UTF-8'}"
        data-information-type="{$log_information_type|escape:'htmlall':'UTF-8'}"
        data-target="log-modal-{$log_id|escape:'htmlall':'UTF-8'}-{$log_information_type|escape:'htmlall':'UTF-8'}"
>
    {l s='View' mod='saferpayofficial'}
</div>

<div id="log-modal-{$log_id|escape:'htmlall':'UTF-8'}-{$log_information_type|escape:'htmlall':'UTF-8'}" class="modal">
    <div class="log-modal-overlay"></div>

    <div class="log-modal-window">
        <div class="log-modal-title">
            <h4>
                {if $log_information_type === 'request'}
                    {$log_id|escape:'htmlall':'UTF-8'}: {l s='Request data' mod='saferpayofficial'}
                {elseif $log_information_type === 'response'}
                    {$log_id|escape:'htmlall':'UTF-8'}: {l s='Response data' mod='saferpayofficial'}
                {elseif $log_information_type === 'context'}
                    {$log_id|escape:'htmlall':'UTF-8'}: {l s='Context data' mod='saferpayofficial'}
                {/if}
            </h4>
        </div>

        <div class="log-modal-content">
            <div class="log-modal-content-spinner hidden"></div>
            <pre class="log-modal-content-data hidden"></pre>
        </div>
    </div>
</div>