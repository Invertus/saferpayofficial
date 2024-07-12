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
// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })
Cypress.Commands.add(
    'iframeLoaded',
    {prevSubject: 'element'},
    ($iframe) => {
        const contentWindow = $iframe.prop('contentWindow');
        return new Promise(resolve => {
            if (
                contentWindow &&
                contentWindow.document.readyState === 'complete'
            ) {
                resolve(contentWindow)
            } else {
                $iframe.on('load', () => {
                    resolve(contentWindow)
                })
            }
        })
    });

    Cypress.Commands.add("CachingBOFOPS1789", () => {
        //Caching the BO and FO session
          const login = (SaferPayBOFOLoggingIn) => {
            cy.session(SaferPayBOFOLoggingIn,() => {
            cy.visit('/admin1/')
            cy.url().should('contain', 'https').as('Check if HTTPS exists')
            cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
            cy.get('#passwd').type('demodemo',{delay: 0, log: false})
            cy.get('#submit_login').click().wait(1000).as('Connection successsful')
            cy.visit('/en/my-account')
            cy.get('#login-form [name="email"]').eq(0).type((Cypress.env('SAFERPAY_EMAIL')),{delay: 0, log: false})
            cy.get('#login-form [name="password"]').eq(0).type((Cypress.env('SAFERPAY_PASSWORD')),{delay: 0, log: false})
            cy.get('#login-form [type="submit"]').eq(0).click({force:true})
            cy.get('#history-link > .link-item').click()
            })
            }
            login('SaferPayBOFOLoggingIn')
        })
        
        Cypress.Commands.add("OpeningModuleDashboardURL", () => {
            cy.visit('/admin1/index.php?controller=AdminModules&configure=saferpayofficial')
            cy.get('.btn-continue').click()
        })

        Cypress.Commands.add("navigatingToThePaymentCHF", () => {
            cy.visit('/en/order-history')
            cy.contains('Reorder').click()
            cy.contains('Switzerland').click()
            //Billing country LT, DE etc.
            cy.get('.clearfix > .btn').click()
            cy.get('#js-delivery > .continue').click()
        })

        Cypress.Commands.add("navigatingToThePayment", () => {
            cy.visit('/en/order-history')
            cy.contains('Reorder').click()
            cy.contains('Germany').click()
            //Billing country LT, DE etc.
            cy.get('.clearfix > .btn').click()
            cy.get('#js-delivery > .continue').click()
        })

        Cypress.Commands.add("guestCheckoutCHF", () => {
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
            cy.get('[name="email"]').first().type(testname, {delay: 0})
            cy.contains('Customer data privacy').click()
            cy.contains('I agree').click()
            cy.get('#customer-form > .form-footer > .continue').click()
            cy.get('#field-address1').type('ADDR',{delay:0}).as('address 1')
            cy.get('#field-address2').type('ADDR2',{delay:0}).as('address2')
            cy.get('#field-postcode').type('5446',{delay:0}).as('zip')
            cy.get('#field-city').type('Zurich',{delay:0}).as('city')
            cy.get('#field-id_country').select('Switzerland').as('country')
            cy.get('#field-phone').type('+370 000',{delay:0}).as('telephone')
            cy.get('[name="confirm-addresses"]').click();
            cy.get('#js-delivery > .continue').click()
        })

        Cypress.Commands.add("guestCheckout", () => {
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
            cy.get('[name="email"]').first().type(testname, {delay: 0})
            cy.contains('Customer data privacy').click()
            //cy.contains('I agree').click()
            cy.get('#customer-form > .form-footer > .continue').click()
            cy.get('#field-address1').type('ADDR',{delay:0}).as('address 1')
            cy.get('#field-address2').type('ADDR2',{delay:0}).as('address2')
            cy.get('#field-postcode').type('54460',{delay:0}).as('zip')
            cy.get('#field-city').type('Zurich',{delay:0}).as('city')
            cy.get('#field-id_country').select('Germany').as('country')
            cy.get('#field-phone').type('+370 000',{delay:0}).as('telephone')
            cy.get('[name="confirm-addresses"]').click();
            cy.get('#js-delivery > .continue').click()
        })

        Cypress.Commands.add("changeCurrencyCHF", () => {
            cy.get('[aria-label="Currency dropdown"]').click();
            cy.contains('a', 'CHF').click();
        })

        Cypress.Commands.add("FillAmex", () => {
            cy.get('[name="CardNumber"]').click().type('9070003150000008')
            cy.get('[name="Expiry"]').click().type('0525')
            cy.get('[name="VerificationCode"]').click().type('111')
        })

        Cypress.Commands.add("FillJcb", () => {
            cy.get('[name="CardNumber"]').click().type('9060003150000000')
            cy.get('[name="Expiry"]').click().type('0525')
            cy.get('[name="VerificationCode"]').click().type('111')
        })

        Cypress.Commands.add("FillDiners", () => {
            cy.get('[name="CardNumber"]').click().type('9050003150000002')
            cy.get('[name="Expiry"]').click().type('0525')
            cy.get('[name="VerificationCode"]').click().type('111')
        })

        Cypress.Commands.add("FillUnion", () => {
            cy.get('[name="CardNumber"]').click().type('9100104952000008')
            cy.get('[name="Expiry"]').click().type('0525')
            cy.get('[name="VerificationCode"]').click().type('111')
        })

Cypress.Commands.add(
    'getInDocument',
    {prevSubject: 'document'},
    (document, selector) => Cypress.$(selector, document)
);

Cypress.Commands.add(
    'getWithinIframe',
    (targetElement) => cy.get('iframe').iframeLoaded().its('document').getInDocument(targetElement)
);

Cypress.Commands.add('getIframe', (iframe) => {
    return cy.get(iframe)
        .its('0.contentDocument.body')
        .should('be.visible')
        .then(cy.wrap);
})

Cypress.Commands.add('iframe', { prevSubject: 'element' }, ($iframe, selector) => {
    Cypress.log({
      name: 'iframe',
      consoleProps() {
        return {
          iframe: $iframe,
        };
      },
    });
    return new Cypress.Promise(resolve => {
      resolve($iframe.contents().find(selector));
    });
  });