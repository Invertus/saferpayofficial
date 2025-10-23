/**
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
 */
/// <reference types="Cypress" />
///<reference types="cypress-iframe" />
function prepareCookie()
      {
            const name = 'PrestaShop-';

                   cy.request(
            {
                url: '/'
            }
        ).then((res) => {

            const cookies = res.requestHeaders.cookie.split(/; */);

            cookies.forEach(cookie => {

                const parts = cookie.split('=');
                const key = parts[0]
                const value = parts[1];

                if (key.startsWith(name)) {
                    cy.setCookie(
                        key,
                        value,
                        {
                            sameSite: 'None',
                            secure: true
                        }
                    );
                }
            });

        });
      }
      //Caching the BO and FO session
      const login = (SaferpayBOFOLoggingIn) => {
      cy.session(SaferpayBOFOLoggingIn,() => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.PSBOlogin()
      cy.visit('https://sp1764.eu.ngrok.io/index.php?controller=my-account')
      cy.PSFOlogin()
      cy.get('#history-link > .link-item').click()
      })
      }
describe('PS1764 Saferpay Tests Suite', () => {
  beforeEach(() => {
      cy.viewport(1920,1080)
      login('SaferpayBOFOLoggingIn')
  })
it.only('05 TWINT Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click() 
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Twint').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.origin('https://test.saferpay.com', () => {
      cy.get('[title="UnionPay"]').click({force:true})
      cy.get('[input-id="CardNumber"]').type('9100100052000005')
      cy.get('[class="btn btn-next"]').click()
      cy.get('[id="UnionPayButton"]').click()
      })
      cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it.only('06 TWINT BO Order Refunding', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('07 LASTSCHRIFT Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Lastschrift').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.origin('https://test.saferpay.com', () => {
      cy.get('[title="UnionPay"]').click({force:true})
      cy.get('[input-id="CardNumber"]').type('9100100052000005')
      cy.get('[class="btn btn-next"]').click()
      cy.get('[id="UnionPayButton"]').click()
      })
      cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it('08 LASTSCHRIFT BO Order Refunding', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('09 VISA Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Visa').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click().wait(1000)
      cy.iframe('#fields-card-number').find('[autocomplete="cc-number"]').type('9010003150000001')
      cy.iframe('#fields-expiration').find('[autocomplete="cc-exp"]').type('1299')
      cy.iframe('#fields-cvc').find('[autocomplete="off"]').type('123')
      cy.get('#submit').click()
      //todo to configure test account
      //cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it('10 VISA BO Order Refunding', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment completed by Saferpay').should('be.visible').as('@order status')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible').as('@order status')
})
it('11 MASTERCARD Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Mastercard').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click().wait(1000)
      cy.iframe('#fields-card-number').find('[autocomplete="cc-number"]').type('9010003150000001')
      cy.iframe('#fields-expiration').find('[autocomplete="cc-exp"]').type('1299')
      cy.iframe('#fields-cvc').find('[autocomplete="off"]').type('123')
      cy.get('#submit').click()
      //todo to finish test configuration
      //cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it('12 MASTERCARD BO Order Refunding', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it.only('13 AMERICAN EXPRESS Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('AmericanExpress').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.origin('https://test.saferpay.com', () => {
      cy.get('#CardNumber').type('9070003150000008')
      cy.get('#Expiry').type('1223')
      cy.get('#HolderName').type('TEST TEST')
      cy.get('#VerificationCode').type('123')
      cy.get('[class="btn btn-next"]').click()
      })
      cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it.only('14 AMERICAN EXPRESS BO Order Refunding and Capturing', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment authorized by Saferpay').should('be.visible')
      //Capturing action
      cy.get('[name="submitCaptureOrder"]').should('be.visible').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it.only('15 DINERS CLUB Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('DinersClub').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.origin('https://test.saferpay.com', () => {
      cy.get('#CardNumber').type('9050100052000005')
      cy.get('#Expiry').type('1223')
      cy.get('#HolderName').type('TEST TEST')
      cy.get('#VerificationCode').type('123')
      cy.get('[class="btn btn-next"]').click()
      cy.get('[class="btn btn-primary"]').click()
      })
      cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it.only('16 DINERS CLUB BO Order Refunding and Capturing', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment authorized by Saferpay').should('be.visible')
      //Capturing action
      cy.get('[name="submitCaptureOrder"]').should('be.visible').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('17 JCB Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Jcb').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click().wait(1000)
      cy.iframe('#fields-card-number').find('[autocomplete="cc-number"]').type('9060100052000003')
      cy.iframe('#fields-expiration').find('[autocomplete="cc-exp"]').type('1299')
      cy.iframe('#fields-cvc').find('[autocomplete="off"]').type('123')
      cy.get('#submit').click().wait(5000)
      //todo to finish test configuration
      //cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it('18 JCB BO Order Refunding and Capturing', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment authorized by Saferpay').should('be.visible')
      //Capturing action
      cy.get('[name="submitCaptureOrder"]').should('be.visible').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('19 MYONE Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('myOne').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.origin('https://test.saferpay.com', () => {
      cy.get('[title="UnionPay"]').click({force:true})
      cy.get('[input-id="CardNumber"]').type('9100100052000005')
      cy.get('[class="btn btn-next"]').click()
      cy.get('[id="UnionPayButton"]').click()
      })
      cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it('20 MYONE BO Order Refunding', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('21 BONUSCARD Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('BonusCard').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.origin('https://test.saferpay.com', () => {
      cy.get('[title="UnionPay"]').click({force:true})
      cy.get('[input-id="CardNumber"]').type('9100100052000005')
      cy.get('[class="btn btn-next"]').click()
      cy.get('[id="UnionPayButton"]').click()
      })
      cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it('22 BONUSCARD BO Order Refunding', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('23 PAYPAL Checkouting', () => { //TODO to finish overcoming the security
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('PayPal').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      cy.get('.ps-shown-by-js > .btn').click()
      //todo fix the paypal cross-origin 
      prepareCookie();
      cy.origin('https://test.saferpay.com/Simulators/PayPalRestApi/**', () => {
      cy.visit('https://test.saferpay.com/Simulators/PayPalRestApi/')
      cy.get('[id="pay"]').click()
      })
      //cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it('24 PAYPAL BO Order Refunding', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it.only('25 POSTEFINANCE Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('PostEFinance').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get(':nth-child(9) > .col-lg-6 > .simple-auth-response-button').click()
      cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it.only('26 POSTEFINANCE BO Order Refunding', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment authorized by Saferpay').should('be.visible')
      //Capturing action
      cy.get('[name="submitCaptureOrder"]').should('be.visible').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it.only('27 POSTCARD Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Postcard').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get(':nth-child(9) > .col-lg-6 > .simple-auth-response-button').click()
      cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it.only('28 POSTCARD BO Order Refunding', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment authorized by Saferpay').should('be.visible')
      //Capturing action
      cy.get('[name="submitCaptureOrder"]').should('be.visible').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it.only('29 BANCONTACT Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Bancontact').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.origin('https://test.saferpay.com', () => {
      cy.get('#CardNumber').type('91108000500000005')
      cy.get('#Expiry').type('1223')
      cy.get('#HolderName').type('TEST TEST')
      cy.get('[name="SubmitToNext"]').click()
      cy.get('[id="Submit"]').click()
      })
      cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it.only('30 BANCONTACT BO Order Refunding', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it.only('31 UNIONPAY Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('UnionPay').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.origin('https://test.saferpay.com', () => {
      cy.get('[title="UnionPay"]').click({force:true})
      cy.get('[input-id="CardNumber"]').type('9100100052000005')
      cy.get('[class="btn btn-next"]').click()
      cy.get('[id="UnionPayButton"]').click()
      })
      cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it.only('32 UNIONPAY BO Order Refunding', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it.only('33 KLARNA Checkouting', () => {
      cy.visit('https://sp1764.eu.ngrok.io/en/order-history')
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Klarna').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.origin('https://test.saferpay.com', () => {
      cy.get('[name="SubmitToNext"]').click()
      })
      cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it.only('34 KLARNA BO Order Capturing and Refunding', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment authorized by Saferpay').should('be.visible')
      //Capturing action
      cy.get('[name="submitCaptureOrder"]').should('be.visible').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('35 GOOGLEPAY Checkouting', () => {//TODO to finish
      Cypress.on('uncaught:exception', (err, runnable) => {
            // returning false here prevents Cypress from
            // failing the test
            return false
        })
      cy.visit('https://sp1786.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-2').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('GOOGLEPAY').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get('.paymentgroup > ul > li > .btn').click()
      cy.wait(1000)
      cy.get('[class="img img-selection-item"]').click({force:true})
      cy.wait(1000)
      const getIframeBody = () => {
            // get the iframe > document > body
            // and retry until the body element is not empty
            return cy
            .get('[id="popup-contentIframe"]')
            .its('0.contentDocument.body')
            // wraps "body" DOM element to allow
            // chaining more Cypress commands, like ".find(...)"
            // https://on.cypress.io/wrap
            .then(cy.wrap)
          }
      getIframeBody().find('[id="payWithout3DS"]').click()
      // const getIframeBodyProceed = () => {
      //       // get the iframe > document > body
      //       // and retry until the body element is not empty
      //       return cy
      //       .get('[class="resp-iframe"]')
      //       .its('0.contentDocument.body')
      //       // wraps "body" DOM element to allow
      //       // chaining more Cypress commands, like ".find(...)"
      //       // https://on.cypress.io/wrap
      //       .then(cy.wrap)
      //     }
      cy.wait(20000)
      cy.iframe('[class="resp-iframe"]').find('[id="submit"]')
      // cy.get('[class="resp-iframe"]').then($element => {
      //       const $body = $element.contents().find('body')
      //       let stripe = cy.wrap($body)
      //       stripe.find('[class="resp-iframe"]').click(150,150)
      //     })
      //       cy.origin('https://test.saferpay.com', () => {
      // cy.visit('https://test.saferpay.com/Simulators/ThreeDSv2/Acs/ChallengeProcess')
      // })
      //getIframeBodyProceed().find('body').click()
      })
it('36 GOOGLEPAY BO Order Refunding', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get('.btn-continue').click()
      cy.get('[class="btn-group pull-right"]').eq(0).click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Capturing action
      cy.get('[name="submitCaptureOrder"]').should('be.visible').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
})
