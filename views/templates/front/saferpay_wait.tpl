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
<h2>{l s='Awaiting payment status' mod='saferpayofficial'}</h2>
<div class="saferpay-spinner">
    <div class="rect1"></div>
    <div class="rect2"></div>
    <div class="rect3"></div>
    <div class="rect4"></div>
    <div class="rect5"></div>
</div>
<style>
    .saferpay-spinner {
        margin:     100px auto;
        width:      50px;
        height:     40px;
        text-align: center;
        font-size:  10px;
    }

    .saferpay-spinner > div {
        background-color:  #333;
        height:            100%;
        width:             6px;
        display:           inline-block;

        -webkit-animation: sk-stretchdelay 1.2s infinite ease-in-out;
        animation:         sk-stretchdelay 1.2s infinite ease-in-out;
    }

    .saferpay-spinner .rect2 {
        -webkit-animation-delay: -1.1s;
        animation-delay:         -1.1s;
    }

    .saferpay-spinner .rect3 {
        -webkit-animation-delay: -1.0s;
        animation-delay:         -1.0s;
    }

    .saferpay-spinner .rect4 {
        -webkit-animation-delay: -0.9s;
        animation-delay:         -0.9s;
    }

    .saferpay-spinner .rect5 {
        -webkit-animation-delay: -0.8s;
        animation-delay:         -0.8s;
    }

    @-webkit-keyframes sk-stretchdelay {
        0%, 40%, 100% {
            -webkit-transform: scaleY(0.4)
        }
        20% {
            -webkit-transform: scaleY(1.0)
        }
    }

    @keyframes sk-stretchdelay {
        0%, 40%, 100% {
            transform:         scaleY(0.4);
            -webkit-transform: scaleY(0.4);
        }
        20% {
            transform:         scaleY(1.0);
            -webkit-transform: scaleY(1.0);
        }
    }
</style>
<script type="text/javascript">
    (function awaitSaferpayPaymentStatus() {
        var timeout = 3000;
        var request = new XMLHttpRequest();
        // nofilter is needed for url with variables
        request.open('GET', '{$checkStatusEndpoint|escape:'javascript':'UTF-8' nofilter}', true);

        request.onload = function() {
            if (request.status >= 200 && request.status < 400) {
                try {
                    var data = JSON.parse(request.responseText);
                    if (data.isFinished && data.href) {
                        window.location.href = data.href;
                        return;
                    }
                } catch (e) {
                }
            }

            setTimeout(awaitSaferpayPaymentStatus, timeout);
        };

        request.onerror = function() {
            setTimeout(awaitSaferpayPaymentStatus, timeout);
        };

        request.send();
    }());
</script>

