# Change log

## [1.0.0] - 2019-09-13

- BO : Module compatible with PS 1.6.* - 1.7.*
- FO : Module compatible with PS 1.6.* - 1.7.*

## [1.0.2] - 2020-07-03

- FO : Fixed issue with cart disappearance after declined transaction

## [1.0.3] - 2020-11-12

- BO : Added ApplePay and Klarna payment settings
- BO : Added SaferPay Fields settings with possibility to show custom payment form template
- FO : Added ApplePay and Klarna payment options

## [1.0.4] - 2020-11-20

- BO : Added possibility to change Awaiting SaferPay Payment default order state

## [1.0.5] - 2020-12-03

- BO : Removed brands setting from payments to not send other payment with wallets

## [1.0.6] - 2020-12-31

- BO : Order page bootstrap templates upgraded to new version
- BO : Hooks added for prestashop 1.7.7 order page
- BO : Admin controllers refactored to work with new prestashop version

## [1.0.7] - 2021-01-20

- BO : Added gender information to Payer.BillingAddress and Payer.DeliveryAddress
- BO : Added shipping fee information to Order.Items
- BO : Added order reference information to Refund request

## [1.0.8] - 2021-05-31

- BO : 3DS capture fix
- BO : Updates from bitbucket
- BO : Module install fix

## [1.0.9] - 2021-07-15

- BO : Fixed invoice_date not being set on order when using module as payment option with CAPTURE default payment method behaviour.

## [1.0.10] - 2021-08-12

- FO : Fixed issue with maintenance mode and notification controller.
- BO : Fixed status issue with Bancontact payment.

## [1.0.11] - 2021-08-26

- FO : Added Belgium for Klarna payment

## [1.0.12] - 2021-08-26

- FO : Added missing countries for Klarna payment

## [1.0.13] - 2021-10-29

- FO : Updated payment images

## [1.0.14] - 2022-02-11

- FO : Updated payment methods loading functionality

## [1.0.15] - 2022-05-31

- BO : "Invalid credentials" exception catcher added. 

## [1.0.16] - 2022-06-07

- BO : Changed mastercard config name from MASTERCARD to MasterCard. 

## [1.0.17] - 2022-06-14

- FO : Fixed issue where API missing response would throw 500 error in checkout page.

## [1.0.18] - 2022-06-21

- BO: Added new switch to control when new order mail is sent to merchant.
- BO : Fixed issue where on older PS version capture order would send wrong price.
- 
## [1.0.19] - *

- FO: added ability to save and use saved cards with hosted fields payment

## [1.0.20] - *

- FO: Fixed issue when payment was cancelled due to 3DS failure but Order confirmation was still shown
- FO: Fixed issue when 3DS failed, but it still captured/authorized payment

## [1.0.21] - *

- FO: Fixed issue with payments not being displayed when currency option was not set to "ALL"
- BO: Fixed issue with "Maestro Intl." not being enabled in BO

## [1.0.22] - *

- BO: Fixed issue with Bancontact payment being captured twice, thus causing an error from API.

## [1.0.23] - *

- BO: Fixed release script.

## [1.0.24] - *

- BO: Fixed uninstall functionality not working for later versions of PrestaShop.

## [1.1.0] - *

- BO : Module compatible with PS 1.6.* - 8.0.*
- FO : Module compatible with PS 1.6.* - 8.0.*

## [1.1.1] - *

- FO : Fixed ApplePay payment method was not displayed on Macintosh PC's. 

## [1.1.2] - *

- BO : Fixed problems with install and tab display.

## [1.1.3] - *

- BO : Increased API version from 1.23 => 1.32
- BO : Fixed compatibility with Notification Container for SaferPay API version lower than 1.35.
- BO : ReturnSuccessUrl and ReturnFailUrl replaced by ReturnUrl container
- BO : NotificationUrl container replaced by SuccessNotificationUrl and FailNotificationUrl,
- BO : Display from BillingAddressForm and DeliveryAddressForm containers replaced by AddressSource

## [1.1.4] - *

- BO : Fixed API version issue

## [1.1.5] - *

- BO : Fixed the redirect issue when payment was aborted.

## [1.1.6] - *
- BO : Added additional check for confirmation email regarding payment status and customer behavior

## [1.1.7] - *
- BO : Added PrestaShop module security validations
- FO : Added PrestaShop module security validations

## [1.1.8] - *
- BO : Added a toggle setting for Saferpay email sending option
- BO : Added a configuration field for the customization of the description parameter
- BO : Increased module's API version
- BO : Added a more descriptive payment method name in invoices
- BO : Additional improvements and fixes

- ## [1.2.0] - *
- BO : Added order creation after authorization functionality

- ## [1.2.1] - *
- FO : Increased compatibility with PrestaShop 1.6

- ## [1.2.2] - *
- FO : Confirmation email after order authorization fix
- BO : Order confirmation email setting removed 
- BO : Security improvements

- ## [1.2.3] - *
- FO : Increased API version to 1.40
- FO : WeChat Pay payment method added
- FO : AccountToAccount Pay payment method added
- BO : Security improvements
- BO : Bug fixes and improvements

## [1.2.4]
- Fixed credit card saving
- Implemented code logging
- Requiring card holder name when entering card details
- Removed depreciated feature for custom CSS
- Compatibility with most popular OPC modules (The Checkout, Super Checkout, One Page Checkout PS)

## [1.2.5]
- Fixed issue with JSON API Password escaping HTML entities
- Added new payment methods: Blik, ClickToPay
- Fixed performance issues when loading "Payments" tab in Back office.
- Fixed issue when saferpay logic is executing on other payment methods which is not saferpay

## [2.0.0]
- Fixed issues with default awaiting status on installation
- Fixed issues with double same statuses in order
- Fixed issue with failure after user come back after initializing payment
- PrestaShop 9 compatibility

## [2.0.1]
- Fixed issue when user is redirected to payment page in Back office when module is disabled
- Fixed issue when payment is showing but it is disabled