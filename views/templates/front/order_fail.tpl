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
{extends file='checkout/order-confirmation.tpl'}
{block name='page_content_container'}
    <section id="content-hook_order_confirmation" class="card">
        <div class="card-block">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="h1 card-title">
                        <i class="material-icons rtl-no-flip text-danger">cancel</i>{l s='Your order is canceled' mod='saferpayofficial'}
                    </h3>
                </div>
            </div>
        </div>
    </section>
{/block}
{block name='hook_order_confirmation'}
{/block}
{block name='hook_payment_return'}
{/block}
{block name='customer_registration_form'}
{/block}