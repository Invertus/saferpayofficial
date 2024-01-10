describe('Admin journey - part 1', () => {
  it.skip('1 - Configure plugin credentials', () => {
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
  })
})