/// <reference types="Cypress" />
///<reference types="cypress-iframe" />
import 'cypress-iframe'
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
      cy.visit('https://sp1786.eu.ngrok.io/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('https://sp1786.eu.ngrok.io/index.php?controller=my-account')
      cy.get('#login-form [name="email"]').eq(0).type('demo@demo.com')
      cy.get('#login-form [name="password"]').eq(0).type('demodemo')
      cy.get('#login-form [type="submit"]').eq(0).click({force:true})
      cy.get('#history-link > .link-item').click()
      })
      }
describe('PS1786 Saferpay Tests Suite', () => {
  beforeEach(() => {
      cy.viewport(1920,1080)
      login('SaferpayBOFOLoggingIn')
  })
it('01 Connecting the Test API information to module', () => {
      cy.visit('https://sp1786.eu.ngrok.io/admin1/')
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
it('02 Enabling Saferpay carriers and countries successfully', () => {
      cy.visit('https://sp1786.eu.ngrok.io/admin1/')
      cy.get('[id="subtab-AdminPaymentPreferences"]').find('[href]').eq(0).click({force:true})
      cy.get('[class="js-multiple-choice-table-select-column"]').eq(7).click()
      cy.get('[class="btn btn-primary"]').eq(3).click()
})
it('03 Enabling All payments in Module BO', () => {
      cy.visit('https://sp1786.eu.ngrok.io/admin1/')
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
      cy.visit('https://sp1786.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-2').click()
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
      cy.visit('https://sp1786.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
it.only('06 LASTSCHRIFT Checkouting', () => {
      cy.visit('https://sp1786.eu.ngrok.io/en/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      cy.get('#id-address-delivery-address-2').click()
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
it.only('07 LASTSCHRIFT BO Order Refunding', () => {
      cy.visit('https://sp1786.eu.ngrok.io/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.contains('Payment completed by Saferpay').should('be.visible')
      //Refunding action
      cy.get('[name="saferpay_refund_amount"]').should('be.visible')
      cy.get('[class="saferpay-refund-button"]').click()
      cy.get('[class="alert alert-success d-print-none"]').should('be.visible') //visible success message
      cy.contains('Order Refunded by Saferpay').should('be.visible')
})
})
