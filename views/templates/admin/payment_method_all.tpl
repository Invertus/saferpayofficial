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

<div class="form-group saferpay-form-group all-payments">
    <div class="col-lg-2">
        <div class="checkbox">
            <label class="container-checkbox">
                <input type="checkbox"
                       id="all_enalbe_on"
                       value="1">
                <span class="checkmark"></span>

            </label>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="checkbox">
            <label class="container-checkbox">
                <input type="checkbox"
                       id="all_logo_on"
                       value="1">
                <span class="checkmark"></span>

            </label>
        </div>
    </div>
    <div class="col-lg-3">
        {html_options
        name='all_countries[]'
        id='all_countries'
        class='chosen js-chosen'
        options=$countryOptions
        selected=$countrySelect
        multiple='multiple'
        }
    </div>
    <div class="col-lg-3">
        {html_options
        name='all_currencies[]'
        id='all_currencies'
        class='chosen js-chosen'
        options=$currencyOptions
        selected=$currencySelect
        multiple='multiple'
        }
    </div>
</div>