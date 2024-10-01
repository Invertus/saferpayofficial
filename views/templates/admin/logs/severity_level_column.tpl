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
{if $log_severity_level == $log_severity_level_informative}
    <span class="badge badge-pill badge-success" style="margin-bottom: 5px">{l s='Informative only' mod='klarnapayment'} ({$log_severity_level|intval})</span>
{elseif $log_severity_level == $log_severity_level_warning}
    <span class="badge badge-pill badge-warning" style="margin-bottom: 5px">{l s='Warning' mod='klarnapayment'} ({$log_severity_level|intval})</span>
{elseif $log_severity_level == $log_severity_level_error}
    <span class="badge badge-pill badge-danger" style="margin-bottom: 5px">{l s='Error' mod='klarnapayment'} ({$log_severity_level|intval})</span>
{elseif $log_severity_level == $log_severity_level_major}
    <span class="badge badge-pill badge-critical" style="margin-bottom: 5px">{l s='Major issue (crash)!' mod='klarnapayment'} ({$log_severity_level|intval})</span>
{else}
    <span class="badge badge-pill">{$log_severity_level|escape:'htmlall':'UTF-8'}</span>
{/if}