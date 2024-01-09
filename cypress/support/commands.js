/**
 * NOTICE OF LICENSE
 *
 * @author    Klarna Bank AB www.klarna.com
 * @copyright Copyright (c) permanent, Klarna Bank AB
 * @license   ISC
 *
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Klarna Bank AB
 */

//import 'cypress-file-upload';
import 'cypress-iframe';
// or
//require('cypress-iframe');

//const compareSnapshotCommand = require('cypress-visual-regression/dist/command');
//compareSnapshotCommand({
//  capture: 'fullPage'
//});
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
// Cypress.Commands.add("login", (email, password) => { ... })

import './actions';

Cypress.Commands.add("OrderRefundingShippingOrdersAPI", () => {
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
    cy.get('#klarnapayment_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
    cy.get('[class="alert alert-success"]').should('be.visible')
})
Cypress.Commands.add("EnablingModuleMultistore", () => {
  cy.get('#subtab-AdminParentModulesSf > :nth-child(1)').click()
  cy.get('#subtab-AdminModulesSf').click().wait(1000)
  // enable or upgrade the module
  cy.get('[data-tech-name="klarnapayment"]').then(($body) => {
    if ($body.text().includes('Upgrade')) {
      // yup, module needs to be upgraded
      cy.get('[data-tech-name="klarnapayment"]').contains('Upgrade').click()
      cy.get('.btn-secondary').click()
      cy.get('.growl').should('have.text','succeeded.')
    } else if ($body.text().includes('Enable')) {
      // or just enable the module first
      cy.get('[data-tech-name="klarnapayment"]').contains('Enable').click()
    } else {
      // nop, just enter the module configuration
      cy.get('[data-tech-name="klarnapayment"]').contains('Configure').click()
    }
    })
  // back to dashboard
  cy.get('#tab-AdminDashboard > .link').click({force:true})
})
Cypress.Commands.add("OpenModuleDashboard", () => {
    cy.get('#subtab-AdminParentModulesSf > :nth-child(1)').click()
    cy.get('#subtab-AdminModulesSf').click().wait(1000)
    cy.get('[data-tech-name="klarnapayment"]').contains('Configure').click()
})
Cypress.Commands.add("LoggingInPS1784BO", () => {
  //Caching the BO session
  const login = (KlarnaPaymentBOFOLoggingIn) => {
  cy.session(KlarnaPaymentBOFOLoggingIn, () => {
  cy.visit('/admin1/')
  cy.url().should('contain', 'https').as('Check if HTTPS exists')
  cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
  cy.get('#passwd').type('demodemo',{delay: 0, log: false})
  cy.get('#submit_login').click().wait(1000).as('Connection successsful')
  })
  }
  login('KlarnaPaymentBOFOLoggingIn')
})
Cypress.Commands.add("LoggingInPS8BO", () => {
  //Caching the BO session
  const login = (KlarnaPaymentBOFOLoggingIn) => {
  cy.session(KlarnaPaymentBOFOLoggingIn, () => {
  cy.visit('/admin1/')
  cy.get('#email').type('demo@prestashop.com',{delay: 0, log: false})
  cy.get('#passwd').type('prestashop_demo',{delay: 0, log: false})
  cy.get('#submit_login').click().wait(1000).as('Connection successsful')
  })
  }
  login('KlarnaPaymentBOFOLoggingIn')
})
Cypress.Commands.add("LoggingInPS1784FO", () => {
  //Caching the FO session
  const login = (KlarnaPaymentBOFOLoggingIn) => {
  cy.session(KlarnaPaymentBOFOLoggingIn, () => {
  cy.visit('/index.php?controller=my-account')
  cy.get('#login-form [name="email"]').eq(0).type('demo@demo.com')
  cy.get('#login-form [name="password"]').eq(0).type('demodemo')
  cy.get('#login-form [type="submit"]').eq(0).click({force:true})
  cy.get('#history-link > .link-item').click()
  })
  }
  login('KlarnaPaymentBOFOLoggingIn')
})
Cypress.Commands.add("LoggingInPS8FO", () => {
  //Caching the FO session
  const login = (KlarnaPaymentBOFOLoggingIn) => {
  cy.session(KlarnaPaymentBOFOLoggingIn, () => {
  cy.visit('/index.php?controller=my-account')
  cy.get('#login-form [name="email"]').eq(0).type('demo@prestashop.com')
  cy.get('#login-form [name="password"]').eq(0).type('demo_prestashop')
  cy.get('#login-form [type="submit"]').eq(0).click({force:true})
  cy.get('#history-link > .link-item').click()
  })
  }
  login('KlarnaPaymentBOFOLoggingIn')
})
Cypress.Commands.add("SwitchMultistore", () => {
  cy.get('#header_shop').within(($header_shop) => {
    cy.contains('PrestaShop').click({force:true})
  })
})
Cypress.Commands.add("acceptToken", () => {
  cy.contains('I understand').click()
})