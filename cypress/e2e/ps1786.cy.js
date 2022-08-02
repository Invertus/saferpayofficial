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
it.only('04 TWINT Checkouting', () => {
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
it('05 Bancontact Order BO Shiping, Refunding [Orders API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('06 iDEAL Checkouting [Orders API]', () => {
      cy.visit('/SHOP2/de/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('iDEAL').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('.payment-method-list > :nth-child(1)').click()
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('[id="mollie-ok"]').should('be.visible')
})
it('07 iDEAL Order BO Shiping, Refunding [Orders API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('08 Klarna Slice It Checkouting [Orders API]', () => {
      cy.visit('/SHOP2/de/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.contains('DE').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Ratenkauf.').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="authorized"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('[id="mollie-ok"]').should('be.visible')
})
it('09 Klarna Slice It Order BO Shiping, Refunding [Orders API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('10 Klarna Pay Later Checkouting [Orders API]', () => {
      cy.visit('/SHOP2/de/index.php?controller=history')
      cy.get('a').click()
      //
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.contains('DE').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Rechnung.').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="authorized"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('[id="mollie-ok"]').should('be.visible')
})
it('11 Klarna Pay Later Order BO Shiping, Refunding [Orders API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('12 Credit Card Checkouting [Orders API]', () => {
      //Enabling the Single-Click for now
      cy.visit('/admin1/')
      cy.get('#subtab-AdminMollieModule > .link').click()
      cy.get('#MOLLIE_SINGLE_CLICK_PAYMENT_on').click()
      cy.get('[type="submit"]').first().click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      cy.visit('/SHOP2/en/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Credit card').click({force:true})
      //Credit card inputing
      cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
      cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
      getBody().find('#cardHolder').clear({force: true}).type('TEST TEEESSSTT')
      })
      cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
      getBody().find('#cardNumber').clear({force: true}).type('5555555555554444')
      })
      cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
      getBody().find('#expiryDate').clear({force: true}).type('1222')
      })
      cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
      getBody().find('#verificationCode').clear({force: true}).type('222')
      })
      cy.get('.condition-label > .js-terms').click({force:true})
      cy.get('#mollie-save-card').check()
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('[id="mollie-ok"]').should('be.visible')
})
it('13 Check if customerId is passed during the 2nd payment using Single Click Payment [Orders API]', () => {
  cy.visit('/SHOP2/en/index.php?controller=history')
  cy.get('a').click()
  cy.contains('Reorder').click()
  //Billing country LT, DE etc.
  cy.get('.clearfix > .btn').click()
  cy.get('#js-delivery > .continue').click()
  //Payment method choosing
  cy.contains('Credit card').click({force:true})
  cy.get('[for="mollie-use-saved-card"]').should('exist')
  cy.get('.condition-label > .js-terms').click({force:true})
  prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('[id="mollie-ok"]').should('be.visible')
      cy.visit('/admin1/')
      //Disabling the single-click - no need again
      cy.get('#subtab-AdminMollieModule > .link').click()
      cy.get('#MOLLIE_SINGLE_CLICK_PAYMENT_off').click()
      cy.get('[type="submit"]').first().click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('14 Credit Card Order BO Shiping, Refunding [Orders API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('15 IN3 Checkouting [Orders API]', () => {
  cy.visit('/SHOP2/de/index.php?controller=history')
  cy.get('a').click()
  cy.contains('Reorder').click()
  cy.contains('NL').click()
  //Billing country LT, DE etc.
  cy.get('.clearfix > .btn').click()
  cy.get('#js-delivery > .continue').click()
  //Payment method choosing
  // waiting for enabling IN3 payment
  cy.contains('in3').click({force:true})
  cy.get('.condition-label > .js-terms').click({force:true})
  prepareCookie();
  cy.get('.ps-shown-by-js > .btn').click()
  cy.setCookie(
    'SESSIONID',
    "cypress-dummy-value",
    {
        domain: '.www.mollie.com',
        sameSite: 'None',
        secure: true,
        httpOnly: true
    }
  );    // reload current page to activate cookie
  cy.reload();
  cy.get('[value="paid"]').click()
  cy.get('[class="button form__button"]').click()
  cy.get('[id="mollie-ok"]').should('be.visible')
})
it('16 IN3 Order BO Shiping, Refunding [Orders API]', () => {
  cy.visit('/admin1/index.php?controller=AdminOrders')
  cy.get(':nth-child(1) > .column-payment').click()
  //Refunding dropdown in React
  cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
  cy.get('[role="button"]').eq(2).click()
  cy.get('[class="swal-button swal-button--confirm"]').click()
  cy.get('[class="alert alert-success"]').should('be.visible')
  //Shipping button in React
  cy.get('.btn-group > [title=""]').eq(0).click()
  cy.get('[class="swal-button swal-button--confirm"]').click()
  cy.get('.swal-modal').should('exist')
  cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
  cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
  cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
  cy.get(':nth-child(2) > .swal-button').click()
  cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
  cy.get('[class="alert alert-success"]').should('be.visible')
})
it('17 IN3 should not be shown under 5000 EUR [Orders API]', () => {
  cy.visit('/SHOP2/de/')
  cy.contains('Hummingbird printed sweater').click()
  cy.get('[class="btn btn-primary add-to-cart"]').click()
  cy.get('.cart-content-btn > .btn-primary').click()
  cy.get('.text-sm-center > .btn').click()
  cy.contains('NL').click()
  //Billing country LT, DE etc.
  cy.get('.clearfix > .btn').click()
  cy.get('#js-delivery > .continue').click()
  //Payment method choosing
  cy.contains('in3').should('not.exist')
  cy.get('.logo').click()
  cy.get('.blockcart').click()
  cy.get('.remove-from-cart > .material-icons').click()
})
it('18 IN3 Checking that IN3 logo exists OK [Orders API]', () => {
  cy.visit('/admin1/')
  cy.get('#subtab-AdminMollieModule > .link').click()
  cy.get('[href="#advanced_settings"]').click()
  cy.get('[name="MOLLIE_IMAGES"]').select('big')
  cy.get('[type="submit"]').first().click()
  cy.get('[class="alert alert-success"]').should('be.visible')
  cy.visit('/SHOP2/de/index.php?controller=history')
  cy.get('a').click()
  cy.contains('Reorder').click()
  cy.contains('NL').click()
  //Billing country LT, DE etc.
  cy.get('.clearfix > .btn').click()
  cy.get('#js-delivery > .continue').click()
  //asserting i3 image
  cy.get('html').should('contain.html','src="https://www.mollie.com/external/icons/payment-methods/in3%402x.png"')
  //todo finish
  cy.visit('/admin1/')
  cy.get('#subtab-AdminMollieModule > .link').click()
  cy.get('[href="#advanced_settings"]').click()
  cy.get('[name="MOLLIE_IMAGES"]').select('hide')
  cy.get('[type="submit"]').first().click()
  cy.get('[class="alert alert-success"]').should('be.visible')
})
it('19 Enabling All payments in Module BO [Payments API]', () => {
      cy.visit('/admin1/')
      cy.get('#subtab-AdminMollieModule > .link').click()
      cy.ConfPaymentsAPI1784()
      cy.get('[type="submit"]').first().click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('20 Check if Bancontact QR payment dropdown exists [Payments API]', () => {
  cy.visit('/admin1/')
  cy.get('#subtab-AdminMollieModule > .link').click()
  cy.get('[name="MOLLIE_BANCONTACT_QR_CODE_ENABLED"]').should('exist')
})
it('21 Bancontact Checkouting [Payments API]', () => {
      cy.visit('/SHOP2/de/index.php?controller=history')
      cy.get('a').click()
      //
      cy.contains('Reorder').click()
      cy.contains('LT').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Bancontact').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('[id="mollie-ok"]').should('be.visible')
})
it('22 Bancontact Order BO Shiping, Refunding [Payments API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.get('#mollie_order > :nth-child(1)').should('exist')
      cy.get('.form-inline > :nth-child(1) > .btn').should('exist')
      cy.get('.input-group-btn > .btn').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
      cy.get('.card-body > :nth-child(6)').should('exist')
      cy.get('.card-body > :nth-child(9)').should('exist')
      cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body').should('exist')
      cy.get('.sc-bxivhb > .panel > .panel-heading').should('exist')
      cy.get('.sc-bxivhb > .panel > .card-body').should('exist')
      //check partial refunding on Payments API
      cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
      cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
      cy.get('.form-inline > :nth-child(1) > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
})
it('23 iDEAL Checkouting [Payments API]', () => {
      cy.visit('/SHOP2/en/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('iDEAL').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('.payment-method-list > :nth-child(1)').click()
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('[id="mollie-ok"]').should('be.visible')
})
it('24 iDEAL Order BO Shiping, Refunding [Payments API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.get('#mollie_order > :nth-child(1)').should('exist')
      cy.get('.form-inline > :nth-child(1) > .btn').should('exist')
      cy.get('.input-group-btn > .btn').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
      cy.get('.card-body > :nth-child(6)').should('exist')
      cy.get('.card-body > :nth-child(9)').should('exist')
      cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body').should('exist')
      cy.get('.sc-bxivhb > .panel > .panel-heading').should('exist')
      cy.get('.sc-bxivhb > .panel > .card-body').should('exist')
      //check partial refunding on Payments API
      cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
      cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
      cy.get('.form-inline > :nth-child(1) > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
})
it('25 Credit Card Checkouting [Payments API]', () => {
      cy.visit('/SHOP2/en/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Credit card').click({force:true})
      //Credit card inputing
      cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
      cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
      getBody().find('#cardHolder').clear({force: true}).type('TEST TEEESSSTT')
      })
      cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
      getBody().find('#cardNumber').clear({force: true}).type('5555555555554444')
      })
      cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
      getBody().find('#expiryDate').clear({force: true}).type('1222')
      })
      cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
      getBody().find('#verificationCode').clear({force: true}).type('222')
      })
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('[id="mollie-ok"]').should('be.visible')
})
it('26 Credit Card Order BO Shiping, Refunding [Payments API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.get('#mollie_order > :nth-child(1)').should('exist')
      cy.get('.form-inline > :nth-child(1) > .btn').should('exist')
      cy.get('.input-group-btn > .btn').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
      cy.get('.card-body > :nth-child(6)').should('exist')
      cy.get('.card-body > :nth-child(9)').should('exist')
      cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body').should('exist')
      cy.get('.sc-bxivhb > .panel > .panel-heading').should('exist')
      cy.get('.sc-bxivhb > .panel > .card-body').should('exist')
      //check partial refunding on Payments API
      cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
      cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
      cy.get('.form-inline > :nth-child(1) > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
})
it('27 Credit Card Guest Checkouting [Payments API]', () => {
      cy.clearCookies()
      //Payments API item
      cy.visit('/SHOP2/en/', { headers: {"Accept-Encoding": "gzip, deflate"}})
      cy.get('[class="h3 product-title"]').eq(0).click()
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      // Creating random user all the time
      cy.get(':nth-child(1) > .custom-radio > input').check()
      cy.get('#field-firstname').type('AUT',{delay:0})
      cy.get(':nth-child(3) > .col-md-6 > .form-control').type('AUT',{delay:0})
      const uuid = () => Cypress._.random(0, 1e6)
      const id = uuid()
      const testname = `testemail${id}@testing.com`
      cy.get(':nth-child(4) > .col-md-6 > .form-control').type(testname, {delay: 0})
      cy.get(':nth-child(6) > .col-md-6 > .input-group > .form-control').type('123456',{delay:0})
      cy.get(':nth-child(9) > .col-md-6 > .custom-checkbox > label > input').check()
      cy.get('#customer-form > .form-footer > .continue').click()
      cy.reload()
      cy.get(':nth-child(6) > .col-md-6 > .form-control').type('123456',{delay:0})
      cy.get(':nth-child(7) > .col-md-6 > .form-control').type('123456',{delay:0}).as('vat number')
      cy.get(':nth-child(8) > .col-md-6 > .form-control').type('ADDR',{delay:0}).as('address')
      cy.get(':nth-child(10) > .col-md-6 > .form-control').type('54469',{delay:0}).as('zip')
      cy.get(':nth-child(11) > .col-md-6 > .form-control').type('CIT',{delay:0}).as('city')
      cy.get(':nth-child(12) > .col-md-6 > .form-control').select('Lithuania').as('country')
      cy.get(':nth-child(13) > .col-md-6 > .form-control').type('+370 000',{delay:0}).as('telephone')
      cy.get('.form-footer > .continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Credit card').click({force:true})
      //Credit card inputing
      cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
      cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
      getBody().find('#cardHolder').clear({force: true}).type('TEST TEEESSSTT')
      })
      cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
      getBody().find('#cardNumber').clear({force: true}).type('5555555555554444')
      })
      cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
      getBody().find('#expiryDate').clear({force: true}).type('1222')
      })
      cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
      getBody().find('#verificationCode').clear({force: true}).type('222')
      })
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('[id="mollie-ok"]').should('be.visible')
})
})