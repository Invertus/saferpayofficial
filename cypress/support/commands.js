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

Cypress.Commands.add('PSFOlogin', (email, password) => {
    cy.get('#login-form [name="email"]').eq(0).type((Cypress.env('SAFERPAY_EMAIL')),{delay: 0, log: false})
    cy.get('#login-form [name="password"]').eq(0).type((Cypress.env('SAFERPAY_PASSWORD')),{delay: 0, log: false})
    cy.get('#login-form [type="submit"]').eq(0).click({force:true})
})
Cypress.Commands.add('PSBOlogin', (email, password) => {
    cy.get('#email').type((Cypress.env('SAFERPAY_EMAIL')),{delay: 0, log: false})
    cy.get('#passwd').type((Cypress.env('SAFERPAY_PASSWORD')),{delay: 0, log: false})
    cy.get('#submit_login').click().wait(1000).as('Connection successsful')
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