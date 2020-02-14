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

<p class="payment_module">
    <a
            class="saferpay_method js_credit_card saferpay_credit_card_{$method}"
            href="#"
            data-saferpay-method="{$method}"
    >
        <img src="{$imgUrl}" alt="{$method}"/>
        {$method}
    </a>
</p>
<div class="saferpay_additional_info {$method}" style="display: none">
    <form action="{$redirect}">
        {$additional_info}
        <input type="hidden" class="saved_card_method" name="isBusinessLicence" value="1">
        <a type="submit" class="btn btn-default button button-medium" onclick='this.parentNode.submit()'>
            <span>{l s='Pay' mod='saferpayofficial'}<i class="icon-chevron-right right"></i></span>
        </a>
    </form>
</div>
