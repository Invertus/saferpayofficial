describe('Admin journey - part 1', () => {
  it.only('1 - PrestaShop environment', () => {
    cy.viewport(1920,1080)
    cy.visit('/en/login?back=my-account')
    cy.get('#field-email').type('demo@demo.com')
    cy.get('#field-password').type('demodemo')
    cy.get('#submit-login').click()
    cy.visit('/')
    cy.contains('Hummingbird printed t-shirt').click()
    cy.contains('Add to cart').click()
    cy.contains('Proceed to checkout').click().should('exist').should('be.visible')
    cy.contains('Proceed to checkout').click().should('exist').should('be.visible')
    cy.contains('LT') // choosing carriers
    cy.get('.clearfix > .btn').click()
    cy.get('#js-delivery > .continue').click()
    cy.contains('Pay with Worldline Online Payments').click()
    cy.get('.condition-label > .js-terms').click()
    cy.contains('Place order').click()
  })
  it('2 - Worldline environment', () => {
    // todo
  })
  it('3 - Back to PrestaShop environment', () => {
    cy.LogInBO()
    // todo
  })
  it('4 - Check order history', () => {
    // todo
  })
})