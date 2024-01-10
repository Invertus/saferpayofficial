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
    // validation of the PMs
    cy.contains('Pay with Worldline Online Payments').should('exist').should('be.visible')
    cy.contains('Pay with MasterCard').should('exist').should('be.visible')
    cy.contains('Pay with VISA').should('exist').should('be.visible')
    // missing PM selection in the scenario, temporary taking MasterCard
    cy.contains('Pay with MasterCard').click()
    cy.get('.condition-label > .js-terms').click()
    cy.contains('Place order').click()
    cy.get('#payment-cardnumber').type('5137009801943438')
    cy.get('#payment-cardholdername').type('MR TEST')
    cy.get('#payment-cvc').type('123')
    cy.get('#submit-all').click()
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