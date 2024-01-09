describe('Admin journey - part 1', () => {
  it('1 - Configure plugin credentials', () => {
    cy.viewport(1920,1080)
    cy.visit('/admin1/')
    cy.get('#email').type('demo@demo.com')
    cy.get('#passwd').type('demodemo')
    cy.get('#submit_login').click()
  })
})