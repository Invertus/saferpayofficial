describe('PS1786 Saferpay module test suite', () => {
  it('Logging in and filling the Test Credentials', () => {
    cy.viewport(1920, 1024)
    cy.visit('https://sp1786.eu.ngrok.io/admin1/')
    cy.get('#email').type('demo@demo.com')
    cy.get('#passwd').type('demodemo')
    cy.get('#submit_login').click()
  })
})