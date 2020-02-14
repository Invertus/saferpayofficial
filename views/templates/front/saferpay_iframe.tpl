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

{extends file='checkout/checkout.tpl'}

<head>
    {block name='head'}
        {include file='_partials/head.tpl'}
    {/block}
</head>

{block name='content'}
    <div>
        <iframe id="saferpay-iframe" src="{{$redirect}}"></iframe>
    </div>
{/block}

{block name='footer'}
    <div class="footer-container">
        <div class="container">
            <div class="row">
                {hook h='displayFooter'}
            </div>
        </div>
    </div>
{/block}
