describe('Admin journey - part 1', () => {
  it.only('1 - PrestaShop environment', () => {
    cy.viewport(1920,1080)
    cy.LogInFO()
    cy.visit('/')
    cy.contains('Hummingbird printed t-shirt').highlightElement().click()
    cy.contains('Add to cart').highlightElement().click()
    cy.contains('Proceed to checkout').highlightElement().click().should('exist').should('be.visible')
    cy.contains('Proceed to checkout').highlightElement().click().should('exist').should('be.visible')
    cy.contains('LT').highlightElement() // choosing carriers
    cy.get('.clearfix > .btn').highlightElement().click()
    cy.get('#js-delivery > .continue').highlightElement().click()
    // validation of the PMs
    cy.contains('Pay with Worldline Online Payments').highlightElement().should('exist').should('be.visible')
    cy.contains('Pay with MasterCard').should('exist').highlightElement().should('be.visible')
    cy.contains('Pay with VISA').should('exist').highlightElement().should('be.visible')
    // missing PM selection in the scenario, temporary taking MasterCard
    cy.contains('Pay with MasterCard').highlightElement().click()
    cy.get('.condition-label > .js-terms').highlightElement().click()
    cy.contains('Place order').highlightElement().click()
    cy.get('#payment-cardnumber').highlightElement().type('5137009801943438')
    cy.get('#payment-cardholdername').highlightElement().type('MR TEST')
    cy.get('#payment-cvc').highlightElement().type('123')
    cy.get('#submit-all').highlightElement().click()

    // starting the validation of the values
    // Create an object to store multiple values
    const dataToSave = {}
    cy.get('.total-value > :nth-child(2)').highlightElement() // getting Total Value price
      .invoke('text')
      .then((textValue) => {
        dataToSave.totalValuePrice = textValue;
      })
    cy.get('tbody > :nth-child(1) > :nth-child(2)').highlightElement() // getting Subtotal price
      .invoke('text')
      .then((textValue) => {
        dataToSave.subtotalValuePrice = textValue;
      })

    // Save the values to a fixture
    cy.writeFile('cypress/fixtures/multipleValues.json', dataToSave)


  })
  it.skip('2 - Worldline environment', () => {
    // todo
  })
  it('3 - Back to PrestaShop environment', () => {
    cy.viewport(1920,1080)
    cy.LogInFO()
    cy.visit('/')
    cy.contains('Hummingbird printed t-shirt').highlightElement().click()
    cy.contains('Add to cart').highlightElement().click()
    cy.contains('Proceed to checkout').highlightElement().click().should('exist').should('be.visible')
    cy.contains('Proceed to checkout').highlightElement().click().should('exist').should('be.visible')
    cy.contains('LT') // choosing carriers
    cy.get('.clearfix > .btn').highlightElement().click()
    cy.get('#js-delivery > .continue').highlightElement().click()
    // validation of the PMs
    cy.contains('Pay with Worldline Online Payments').highlightElement().should('exist').should('be.visible')
    cy.contains('Pay with MasterCard').highlightElement().should('exist').should('be.visible')
    cy.contains('Pay with VISA').should('exist').highlightElement().should('be.visible')
    // missing PM selection in the scenario, temporary taking MasterCard
    cy.contains('Pay with MasterCard').highlightElement().click()
    cy.get('.condition-label > .js-terms').highlightElement().click()
    cy.contains('Place order').highlightElement().click()
    cy.get('#payment-cardnumber').highlightElement().type('5137009801943438')
    cy.get('#payment-cardholdername').highlightElement().type('MR TEST')
    cy.get('#payment-cvc').highlightElement().type('123')
    cy.get('#submit-all').highlightElement().click()
    cy.wait(2000)
    // Validate that the current URLs matches a specific pattern
    cy.url().highlightElement().should('include', '/module/worldlineop/redirect?action=redirectReturnHosted&RETURNMAC=')
    cy.url().highlightElement().should('include', '&hostedCheckoutId=')
    cy.wait(2000)
    cy.contains('Your order is confirmed').highlightElement().should('exist').should('be.visible')
    // Validate that the current URLs matches a specific pattern
    cy.url().highlightElement().should('include', '/order-confirmation?id_cart=')
    cy.url().highlightElement().should('include', '&id_module=')
    cy.url().highlightElement().should('include', '&key=')
    // empty cart validating
    cy.contains('Cart (0)').highlightElement().should('be.visible').should('exist')
    
  })
  it('4 - Check order history', () => {
    cy.viewport(1920,1080)
    cy.LogInFO()
    cy.visit('/en/order-history')
    cy.get('[scope="row"]').eq(0).highlightElement().should('exist').should('be.visible')
    cy.contains('Details').eq(0).highlightElement().click()
    cy.get('#order-products > tbody > tr > :nth-child(4)').highlightElement()
    cy.get('.line-products > :nth-child(2)').highlightElement()
    cy.get('.line-shipping > :nth-child(2)').highlightElement()
    cy.get('.line-total > :nth-child(2)').highlightElement()
  })
})