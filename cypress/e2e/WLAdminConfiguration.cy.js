describe('Admin journey - part 1', () => {
  it('1 - Configure plugin credentials', () => {
    cy.LogInBO()
    cy.get('[name="worldlineopAccountSettings[testPspid]"]').highlightElement().clear().type(Cypress.env('TEST_PSPID'))
    cy.get('[name="worldlineopAccountSettings[testApiKey]"]').highlightElement().clear().type(Cypress.env('TEST_API_KEY'))
    cy.get('[name="worldlineopAccountSettings[testApiSecret]"]').highlightElement().clear().type(Cypress.env('TEST_API_SECRET'))
    cy.get('[name="worldlineopAccountSettings[testWebhooksKey]"]').highlightElement().clear().type(Cypress.env('TEST_WEBHOOKS_KEY'))
    cy.get('[name="worldlineopAccountSettings[testWebhooksSecret]"]').highlightElement().clear().type(Cypress.env('TEST_WEBHOOKS_SECRET'))
    cy.get('[name="submitTestCredentialsForm"]').highlightElement().click()
    cy.contains('Account credentials are valid. Account settings saved successfully.').should('exist').should('be.visible').highlightElement()
    cy.wait(2000)
    // other actions to be confirmed with WL Steen
  })
  it('2 - Configure plugin for test', () => {
    cy.LogInBO()
    cy.get('[id="worldlineopAdvancedSettings_advancedSettingsEnabled_on"]').highlightElement().click({force:true})
    cy.contains('Advanced Settings').highlightElement().click().wait(1000)
    cy.get('#worldlineop-type-auth').highlightElement().click()
    cy.get('[id="worldlineopAdvancedSettings_logsEnabled_on"]').highlightElement().click({force:true})
    cy.get('[id="worldlineopAdvancedSettings_groupCardPaymentOptions_on"]').highlightElement().click({force:true})
    cy.get('[id="worldlineopAdvancedSettings_force3DsV2_on"]').highlightElement().click({force:true})
    cy.get('[name="submitSaveAdvancedSettingsForm"]').highlightElement().click() // saving the form
    cy.get('[class="icon icon-credit-card"]').highlightElement().click() // going into Payment Methods
    cy.get('[id="worldlineopPaymentMethodsSettings_displayGenericOption_on"]').highlightElement().click({force:true})
    cy.get('[id="worldlineopPaymentMethodsSettings_displayRedirectPaymentOptions_on"]').highlightElement().click({force:true})
    cy.contains('Refresh list of available payment methods').highlightElement().click()
    // validation of PMs begins here
    cy.contains('American Express').should('be.visible').should('exist').highlightElement()
    cy.contains('BCMC').should('be.visible').should('exist').highlightElement()
    cy.contains('CB').should('be.visible').should('exist').highlightElement()
    cy.contains('Diners Club').should('be.visible').should('exist').highlightElement()
    cy.contains('MasterCard').should('be.visible').should('exist').highlightElement()
    cy.contains('GOOGLEPAY').should('be.visible').should('exist').highlightElement()
    cy.contains('iDeal').should('be.visible').should('exist').highlightElement()
    cy.contains('JCB').should('be.visible').should('exist').highlightElement()
    cy.contains('Maestro').should('be.visible').should('exist').highlightElement()
    cy.contains('PAYPAL').should('be.visible').should('exist').highlightElement()
    cy.contains('VISA').should('be.visible').should('exist').highlightElement()
    // enabling VISA, MasterCard only
    cy.get('[class="payment-product panel"]')
      .contains('VISA')
      .parent() // Assuming the parent contains the toggle switch
      .find('[class="switch prestashop-switch fixed-width-md"]')
      .highlightElement()
      .click()
    cy.get('[class="payment-product panel"]')
      .contains('MasterCard')
      .parent() // Assuming the parent contains the toggle switch
      .find('[class="switch prestashop-switch fixed-width-md"]')
      .highlightElement()
      .click()
    cy.get('[name="submitPaymentMethodsSettingsForm"]').highlightElement().click() // saving the form
  })
  it('3 - Configure PrestaShop (if needed)', () => {
    cy.LogInBO()
    cy.get('[id="subtab-AdminParentOrderPreferences"]').find('a').click({force:true}).highlightElement()
    cy.get('[name="gift_options[enable_gift_wrapping]"]').click({multiple:true, force:true}).highlightElement()
    cy.get('[id="form-gift-save-button"]').click().highlightElement() // saving the form
  })
})