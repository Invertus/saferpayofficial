describe('Admin journey - part 1', () => {
  it('1 - Configure plugin credentials', () => {
    cy.LogInBO()
    cy.get('[name="worldlineopAccountSettings[testPspid]"]')
    cy.get('[name="worldlineopAccountSettings[testApiKey]"]')
    cy.get('[name="worldlineopAccountSettings[testApiSecret]"]')
    cy.get('[name="worldlineopAccountSettings[testWebhooksKey]"]')
    cy.get('[name="worldlineopAccountSettings[testWebhooksSecret]"]')
    cy.get('[name="submitTestCredentialsForm"]').click()
    cy.contains('Account credentials are valid. Account settings saved successfully.').should('be.visible')
    // other actions to be confirmed with WL Steen
  })
  it.only('2 - Configure plugin for test', () => {
    cy.LogInBO()
    cy.get('#worldlineopAdvancedSettings_advancedSettingsEnabled_on').click()
  });
})