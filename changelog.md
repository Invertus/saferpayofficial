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

- ## [1.0.22] - *

- BO: Fixed issue with Bancontact payment being captured twice, thus causing an error from API.

- ## [1.0.23] - *

- BO: Fixed release script.
