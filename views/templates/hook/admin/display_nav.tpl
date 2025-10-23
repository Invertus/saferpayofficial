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

<div id="configuration_form" class="defaultForm form-horizontal AdminPatterns">
    <div class="panel">
        <div class="form-wrapper">
            <div class="clearfix">
                <ul class="nav nav-tabs col-lg-11">
                    {foreach from=$menu_tabs key=numStep item=tab}
                        <li{if $tab.active} class="active"{/if}>
                            <a id="{$tab.short|escape:'htmlall':'UTF-8'}" href="{$tab.href|escape:'htmlall':'UTF-8'}">
                                <span class="{$tab.imgclass|escape:'htmlall':'UTF-8'}"></span>
                                {$tab.desc|escape:'htmlall':'UTF-8'}
                            </a>
                        </li>
                    {/foreach}
                </ul>
            </div>
        </div>
    </div>
</div>
