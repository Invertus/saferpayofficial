describe('Admin journey - part 1', () => {
  it('1 - PrestaShop environment', () => {
    cy.viewport(1920,1080)
    cy.LogInFO()
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
  it.skip('2 - Worldline environment', () => {
    // todo
  })
  it.only('3 - Back to PrestaShop environment', () => {
    cy.LogInBO()
    cy.viewport(1920,1080)
    cy.LogInFO()
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
    cy.wait(2000)
    // Validate that the current URLs matches a specific pattern
    cy.url().should('include', '/module/worldlineop/redirect?action=redirectReturnHosted&RETURNMAC=')
    cy.url().should('include', '&hostedCheckoutId=')
    cy.contains('Your order is confirmed').should('exist').should('be.visible')
    // Validate that the current URLs matches a specific pattern
    cy.url().should('include', '/order-confirmation?id_cart=')
    cy.url().should('include', '&id_module=')
    cy.url().should('include', '&key=')
    
  })
  it('4 - Check order history', () => {
    cy.viewport(1920,1080)
    cy.LogInFO()
    cy.visit('/en/order-history')
    cy.get('[scope="row"]').eq(0).should('exist').should('be.visible')
    cy.contains('Details').eq(0).click()
    cy.get('#order-products > tbody > tr > :nth-child(4)')
    cy.get('.line-products > :nth-child(2)')
    cy.get('.line-shipping > :nth-child(2)')
    cy.get('.line-total > :nth-child(2)')
  })
})