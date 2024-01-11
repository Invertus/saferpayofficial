describe('Admin journey - part 1', () => {
  it.only('1 - Configure plugin credentials', () => {
    cy.LogInBO()
    cy.get('[name="worldlineopAccountSettings[testPspid]"]').clear().type(Cypress.env('TEST_PSPID')).highlightElement()
    cy.get('[name="worldlineopAccountSettings[testApiKey]"]').clear().type(Cypress.env('TEST_API_KEY')).highlightElement()
    cy.get('[name="worldlineopAccountSettings[testApiSecret]"]').clear().type(Cypress.env('TEST_API_SECRET')).highlightElement()
    cy.get('[name="worldlineopAccountSettings[testWebhooksKey]"]').clear().type(Cypress.env('TEST_WEBHOOKS_KEY')).highlightElement()
    cy.get('[name="worldlineopAccountSettings[testWebhooksSecret]"]').clear().type(Cypress.env('TEST_WEBHOOKS_SECRET')).highlightElement()
    cy.get('[name="submitTestCredentialsForm"]').click().highlightElement()
    cy.contains('Account credentials are valid. Account settings saved successfully.').should('exist').should('be.visible').highlightElement()
    cy.wait(2000)
    // other actions to be confirmed with WL Steen
  })
  it('2 - Configure plugin for test', () => {
    cy.LogInBO()
    cy.get('[id="worldlineopAdvancedSettings_advancedSettingsEnabled_on"]').click({force:true}).highlightElement()
    cy.contains('Advanced Settings').click().highlightElement()
    cy.get('#worldlineop-type-auth').check().highlightElement()
    cy.get('[id="worldlineopAdvancedSettings_logsEnabled_on"]').click({force:true}).highlightElement()
    cy.get('[id="worldlineopAdvancedSettings_groupCardPaymentOptions_on"]').click({force:true}).highlightElement()
    cy.get('[id="worldlineopAdvancedSettings_force3DsV2_on"]').click({force:true}).highlightElement()
    cy.get('[name="submitSaveAdvancedSettingsForm"]').click().highlightElement() // saving the form
    cy.get('[class="icon icon-credit-card"]').click().highlightElement() // going into Payment Methods
    cy.get('[id="worldlineopPaymentMethodsSettings_displayGenericOption_on"]').click({force:true}).highlightElement()
    cy.get('[id="worldlineopPaymentMethodsSettings_displayRedirectPaymentOptions_on"]').click({force:true}).highlightElement()
    cy.contains('Refresh list of available payment methods').click().highlightElement()
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
    cy.get('[class="payment-product panel"]').highlightElement()
      .contains('VISA').highlightElement()
      .parent() // Assuming the parent contains the toggle switch
      .find('[class="switch prestashop-switch fixed-width-md"]').highlightElement()
      .click().highlightElement()
    cy.get('[class="payment-product panel"]').highlightElement()
      .contains('MasterCard').highlightElement()
      .parent() // Assuming the parent contains the toggle switch
      .find('[class="switch prestashop-switch fixed-width-md"]').highlightElement()
      .click().highlightElement()
    cy.get('[name="submitPaymentMethodsSettingsForm"]').click().highlightElement() // saving the form
  })
  it('3 - Configure PrestaShop (if needed)', () => {
    cy.LogInBO()
    cy.get('[id="subtab-AdminParentOrderPreferences"]').find('a').click({force:true}).highlightElement()
    cy.get('[name="gift_options[enable_gift_wrapping]"]').click({multiple:true, force:true}).highlightElement()
    cy.get('[id="form-gift-save-button"]').click().highlightElement() // saving the form
  })
})