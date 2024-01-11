Cypress.Commands.add("LogInBO", () => {
  cy.viewport(1920,1080)
  cy.visit('/admin1/index.php?controller=AdminWorldlineopConfiguration')
  cy.get('#email').type('demo@demo.com').highlightElement()
  cy.get('#passwd').type('demodemo').highlightElement()
  cy.get('#submit_login').click().highlightElement()
  cy.wait(1000)
})
Cypress.Commands.add("LogInFO", () => {
  cy.visit('en/login?back=my-account')
  cy.get('#field-email').type('demo@demo.com').highlightElement()
  cy.get('#field-password').type('demodemo').highlightElement()
  cy.get('#submit-login').click().highlightElement()
})