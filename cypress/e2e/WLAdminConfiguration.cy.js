describe('Admin journey - part 1', () => {
  it('1 - Configure plugin credentials', () => {
    cy.LogInBO()
    cy.get('[name="worldlineopAccountSettings[testPspid]"]')
    cy.get('[name="worldlineopAccountSettings[testApiKey]"]')
    cy.get('[name="worldlineopAccountSettings[testApiSecret]"]')
    cy.get('[name="worldlineopAccountSettings[testWebhooksKey]"]')
    cy.get('[name="worldlineopAccountSettings[testWebhooksSecret]"]')
    cy.get('[name="submitTestCredentialsForm"]').click()
    cy.contains('Account credentials are valid. Account settings saved successfully.').should('exist').should('be.visible')
    // other actions to be confirmed with WL Steen
  })
  it('2 - Configure plugin for test', () => {
    cy.LogInBO()
    cy.get('[id="worldlineopAdvancedSettings_advancedSettingsEnabled_on"]').click({force:true})
    cy.contains('Advanced Settings').click()
    cy.get('#worldlineop-type-auth').check()
    cy.get('[id="worldlineopAdvancedSettings_logsEnabled_on"]').click({force:true})
    cy.get('[id="worldlineopAdvancedSettings_groupCardPaymentOptions_on"]').click({force:true})
    cy.get('[id="worldlineopAdvancedSettings_force3DsV2_on"]').click({force:true})
    cy.get('[name="submitSaveAdvancedSettingsForm"]').click() // saving the form
    cy.get('[class="icon icon-credit-card"]').click() // going into Payment Methods
    cy.get('[id="worldlineopPaymentMethodsSettings_displayGenericOption_on"]').click({force:true})
    cy.get('[id="worldlineopPaymentMethodsSettings_displayRedirectPaymentOptions_on"]').click({force:true})
    cy.contains('Refresh list of available payment methods').click()
    // validation of PMs begins here
    cy.contains('American Express').should('be.visible').should('exist')
    cy.contains('BCMC').should('be.visible').should('exist')
    cy.contains('CB').should('be.visible').should('exist')
    cy.contains('Diners Club').should('be.visible').should('exist')
    cy.contains('MasterCard').should('be.visible').should('exist')
    cy.contains('GOOGLEPAY').should('be.visible').should('exist')
    cy.contains('iDeal').should('be.visible').should('exist')
    cy.contains('JCB').should('be.visible').should('exist')
    cy.contains('Maestro').should('be.visible').should('exist')
    cy.contains('PAYPAL').should('be.visible').should('exist')
    cy.contains('VISA').should('be.visible').should('exist')
    // enabling VISA, MasterCard only
    cy.get('[class="payment-product panel"]')
      .contains('VISA')
      .parent() // Assuming the parent contains the toggle switch
      .find('[class="switch prestashop-switch fixed-width-md"]')
      .click()
    cy.get('[class="payment-product panel"]')
      .contains('MasterCard')
      .parent() // Assuming the parent contains the toggle switch
      .find('[class="switch prestashop-switch fixed-width-md"]')
      .click()
    cy.get('[name="submitPaymentMethodsSettingsForm"]').click() // saving the form
  })
  it('3 - Configure PrestaShop (if needed)', () => {
    cy.LogInBO()
    cy.get('[id="subtab-AdminParentOrderPreferences"]').find('a').click({force:true})
    cy.get('[name="gift_options[enable_gift_wrapping]"]').click({multiple:true, force:true})
    cy.get('[id="form-gift-save-button"]').click() // saving the form
  })
})