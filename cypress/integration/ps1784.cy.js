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
      cy.visit('https://sp1784.eu.ngrok.io/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('https://sp1784.eu.ngrok.io/index.php?controller=my-account')
      cy.get('#login-form [name="email"]').eq(0).type('demo@demo.com')
      cy.get('#login-form [name="password"]').eq(0).type('demodemo')
      cy.get('#login-form [type="submit"]').eq(0).click({force:true})
      cy.get('#history-link > .link-item').click()
      })
      }
describe('PS1784 Saferpay Tests Suite', () => {
  beforeEach(() => {
      cy.viewport(1920,1080)
      login('SaferpayBOFOLoggingIn')
  })
it.only('01 Connecting the Test API information to module', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/')
      cy.get('#subtab-AdminParentModulesSf > :nth-child(1)').click()
      cy.get('.pstaggerAddTagInput').type('saferpay')
      cy.get('#module-search-button').click()
      cy.get('.btn-group > .btn-primary-reverse').click()  //clicking the Congifure
      cy.get('[name="SAFERPAY_USERNAME_TEST"]').type((Cypress.env('SAFERPAY_USERNAME_TEST')),{delay: 0, log: false})
      cy.get('[name="SAFERPAY_PASSWORD_TEST"]').type((Cypress.env('SAFERPAY_PASSWORD_TEST')),{delay: 0, log: false})
      cy.get('[name="SAFERPAY_CUSTOMER_ID_TEST"]').type((Cypress.env('SAFERPAY_CUSTOMER_ID_TEST')),{delay: 0, log: false})
      cy.get('[name="SAFERPAY_TERMINAL_ID_TEST"]').type((Cypress.env('SAFERPAY_TERMINAL_ID_TEST')),{delay: 0, log: false})
      cy.get('[name="SAFERPAY_MERCHANT_EMAILS_TEST"]').type((Cypress.env('SAFERPAY_MERCHANT_EMAILS_TEST')),{delay: 0, log: false})
      cy.get('[name="SAFERPAY_FIELDS_ACCESS_TOKEN_TEST"]').type((Cypress.env('SAFERPAY_FIELDS_ACCESS_TOKEN_TEST')),{delay: 0, log: false})
      cy.get('#configuration_fieldset_1 > .panel-footer > .btn').click()
      cy.get(':nth-child(4) > .alert').should('exist')
})
it.only('02 Enabling Saferpay carriers and countries successfully', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/')
      cy.get('[id="subtab-AdminPaymentPreferences"]').find('[href]').eq(0).click({force:true})
      cy.get('[class="js-multiple-choice-table-select-column"]').eq(7).click()
      cy.get('[class="btn btn-primary"]').eq(3).click()
})
it.only('03 Enabling All payments in Module BO', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/')
      cy.get('#subtab-AdminParentModulesSf > :nth-child(1)').click()
      cy.get('.pstaggerAddTagInput').type('saferpay')
      cy.get('#module-search-button').click()
      cy.get('.btn-group > .btn-primary-reverse').click()  //clicking the Congifure
      cy.get('#subtab-AdminSaferPayOfficialPayment').click()
      //todo update selectors
      cy.get('.saferpay-group.all-payments > .col-lg-8 > .form-group > :nth-child(1) > .checkbox > .container-checkbox > .checkmark').click()
      cy.get('.saferpay-group.all-payments > .col-lg-8 > .form-group > :nth-child(2) > .checkbox > .container-checkbox > .checkmark').click()
      cy.get('.saferpay-group.all-payments > .col-lg-8 > .form-group > :nth-child(3) > .checkbox > .container-checkbox > .checkmark').click()
      cy.get('#all_countries_chosen > .chosen-choices > .search-field > .default').click()
      cy.get('.highlighted').click()
      cy.get('#all_currencies_chosen > .chosen-choices > .search-field > .default').click()
      cy.get('.highlighted').click()
      cy.get('#configuration_form_submit_btn').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('04 TWINT Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
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
it('05 TWINT BO Order Refunding', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('06 LASTSCHRIFT Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
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
it('07 LASTSCHRIFT BO Order Refunding', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('08 VISA Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Visa').click({force:true})
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
it('09 VISA BO Order Refunding', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('10 MASTERCARD Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Mastercard').click({force:true})
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
it('11 MASTERCARD BO Order Refunding', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('12 AMERICAN EXPRESS Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
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
it('13 AMERICAN EXPRESS BO Order Refunding and Capturing', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Capturing action
      cy.get('[name="submitCaptureOrder"]').should('be.visible').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('14 DINERS CLUB Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
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
it('15 DINERS CLUB BO Order Refunding and Capturing', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Capturing action
      cy.get('[name="submitCaptureOrder"]').should('be.visible').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('16 JCB Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-8').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Jcb').click({force:true})
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
it('17 JCB BO Order Refunding and Capturing', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Capturing action
      cy.get('[name="submitCaptureOrder"]').should('be.visible').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('18 MYONE Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
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
it('19 MYONE BO Order Refunding', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('20 BONUSCARD Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
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
it('21 BONUSCARD BO Order Refunding', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
// it('22 PAYPAL Checkouting', () => { // should be checked how to how to overcome the security in simulator
//       cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
//       cy.get('a').click()
//       cy.contains('Reorder').click()
//       cy.get('#id-address-delivery-address-8').click()
//       //Billing country LT, DE etc.
//       cy.get('.clearfix > .btn').click()
//       cy.get('#js-delivery > .continue').click()
//       //Payment method choosing
//       cy.contains('PayPal').click({force:true})
//       cy.get('.condition-label > .js-terms').click({force:true})
//       cy.get('.ps-shown-by-js > .btn').click()
//       //todo fix the paypal cross-origin 
//       prepareCookie();
//       cy.origin('https://test.saferpay.com/Simulators/PayPalRestApi/**', () => {
//       cy.visit('https://test.saferpay.com/Simulators/PayPalRestApi/')
//       cy.get('[id="pay"]').click()
//       })
//       //cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
//       })
// it('23 PAYPAL BO Order Refunding', () => {
//       cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
//       cy.get(':nth-child(1) > .column-payment').click()
//       cy.contains('Payment completed by Saferpay').should('be.visible')
//       //Refunding action
//       cy.get('[name="saferpay_refund_amount"]').should('be.visible')
//       cy.get('[class="saferpay-refund-button"]').click()
//       cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
//       cy.contains('Order Refunded by Saferpay').should('be.visible')
// })
it('24 POSTEFINANCE Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
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
      cy.origin('https://test.saferpay.com', () => {
      cy.get('[title="UnionPay"]').click({force:true})
      cy.get('[input-id="CardNumber"]').type('9100100052000005')
      cy.get('[class="btn btn-next"]').click()
      cy.get('[id="UnionPayButton"]').click()
      })
      cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it('25 POSTEFINANCE BO Order Refunding', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('26 POSTCARD Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
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
      cy.origin('https://test.saferpay.com', () => {
      cy.get('[title="UnionPay"]').click({force:true})
      cy.get('[input-id="CardNumber"]').type('9100100052000005')
      cy.get('[class="btn btn-next"]').click()
      cy.get('[id="UnionPayButton"]').click()
      })
      cy.get('[id="content-hook_order_confirmation"]').should('exist') //verification of Success Screen
      })
it('27 POSTCARD BO Order Refunding', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('28 BANCONTACT Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
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
it('29 BANCONTACT BO Order Refunding', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('30 UNIONPAY Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
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
it('31 UNIONPAY BO Order Refunding', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('32 KLARNA Checkouting', () => {
      cy.visit('https://sp1784.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
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
it('33 KLARNA BO Order Refunding', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Capturing action
      cy.get('[name="submitCaptureOrder"]').should('be.visible').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it('34 GOOGLEPAY Checkouting', () => {//TODO to finish
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
it('35 GOOGLEPAY BO Order Refunding', () => {
      cy.visit('https://sp1784.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Capturing action
      cy.get('[name="submitCaptureOrder"]').should('be.visible').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
})
