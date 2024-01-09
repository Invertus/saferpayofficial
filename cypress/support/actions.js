Cypress.Commands.add("configuration_01", () => {
    cy.get('[class="environment-switch sandbox"]').click()
    cy.get('#KLARNA_PAYMENT_API_USERNAME').clear().type('FAIL',{delay: 0, log: false})
    cy.get('#KLARNA_PAYMENT_API_PASSWORD').clear().type('FAIL',{delay: 0, log: false})
    cy.get('[name="submit_credential_settings"]').click()
    cy.contains('Failed').should('be.visible') //checking if the fake api submition error appears
  })
Cypress.Commands.add("configuration_02", () => {
    cy.get('[class="environment-switch sandbox"]').click()
    cy.get('#KLARNA_PAYMENT_API_USERNAME').clear().type((Cypress.env('KLARNA_API_USERNAME')),{delay: 0, log: false})
    cy.get('#KLARNA_PAYMENT_API_PASSWORD').clear().type((Cypress.env('KLARNA_API_PASSWORD')),{delay: 0, log: false})
    cy.get('[name="submit_credential_settings"]').click() // saving
    cy.contains('Connect Klarna Payments (connected)').should('be.visible')
    cy.get('#KLARNA_PAYMENT_DEBUG_MODE_on').click() //enabling Debug mode for logs
    cy.get('[name="submit_additional_settings"]').click()
    cy.contains('Settings updated.') //checking the alert if Debug mode is saved
  })
Cypress.Commands.add("configuration_03", () => {
    cy.get('[id="subtab-AdminPaymentPreferences"]').find('[href]').eq(0).click({force:true})
    cy.get('[class="js-multiple-choice-table-select-column"]').each(($element) => { // checks all the checkboxes in the loop, if not checked
      cy.wrap($element).click()
    })
    cy.get('[id="form-carrier-restrictions-save-button"]').click()
  })
Cypress.Commands.add("configuration_04", () => {
    cy.get('#subtab-AdminKlarnaPaymentStyling').click()
    cy.get('#color_0').clear().type('#000000',{force:true})
    cy.get('#color_1').clear().type('#000000',{force:true})
    cy.get('#color_2').clear().type('#000000',{force:true})
    cy.get('#color_3').clear().type('#000000',{force:true})
    cy.get('#KLARNA_PAYMENT_RADIUS_BORDER').clear().type('1')
    //checking the color box
    cy.get('#icp_color_3').click()
    cy.get('#mColorPickerImg').should('be.visible')
    cy.get('#cell5').click()
    cy.get('[name="submit_styling_settings"]').click() //saving
    cy.contains('Settings updated.') //verifying the success alert
  })
Cypress.Commands.add("configuration_05", () => {
    cy.get('ol').should('be.visible') //severity badges check
    cy.get('#form-log > .panel').should('be.visible') //logs panel is shown
    //Making additional API calls for Logs testing
    cy.get('#subtab-AdminKlarnaPaymentSettings').click() //Back to Settings
    cy.get('#KLARNA_PAYMENT_API_USERNAME').clear().type((Cypress.env('KLARNA_API_USERNAME')),{delay: 0, log: false})
    cy.get('#KLARNA_PAYMENT_API_PASSWORD').clear().type((Cypress.env('KLARNA_API_PASSWORD')),{delay: 0, log: false})
    cy.get('[name="submit_credential_settings"]').click()
    cy.contains('Connect Klarna Payments (connected)')
    //Back to Logs tab
    cy.get('#subtab-AdminKlarnaPaymentLogs').click()
    cy.get('[name="logFilter_message"]').clear().type(',--{enter}') //checking the filtering fields, if they not crash
    cy.get('[id="table-log"]').should('be.visible') //asserting that Logs table is existing after filtering
    cy.get('[name="submitResetlog"]').click()
    cy.contains('Severity levels:').should('be.visible')
    //Request, Response and Context buttons checking
    cy.get('[data-information-type="request"]').first().click()
    cy.get('[class="log-modal-content-data"]').should('be.visible').should('contain','{','}')
    cy.get('body').click(100, 100);
    cy.get('[data-information-type="response"]').first().click()
    cy.get('[class="log-modal-content-data"]').should('be.visible').should('contain','{','}')
    cy.get('body').click(100, 100);
    cy.get('[data-information-type="context"]').first().click()
    cy.get('[class="log-modal-content-data"]').should('be.visible').should('contain','{','}')
    cy.get('body').click(100, 100);
  })
  Cypress.Commands.add("configuration_06", () => {
    cy.visit('/de/index.php?controller=history')
    cy.contains('Reorder').click()
    cy.contains('DE').click()
    //Billing country LT, DE etc.
    cy.get('[name="confirm-addresses"]').click()
    cy.get('[name="confirmDeliveryOption"]').click()
    // Payment method choosing (different naming in different languages)
    // Select and click on <span>Text</span> with case-sensitive search
    cy.get('span').then(($span) => {
      const matchingElement = $span.filter((index, element) => {
        // Use strict equality for case-sensitive match
        return Cypress.$(element).text() === 'Rechnung'
      })
      // Click on the matching element
      cy.wrap(matchingElement).click()
    })
    cy.get('[name="conditions_to_approve[terms-and-conditions]"]').click({force:true})
    cy.contains('Place order').click()
    cy.wait(1000)
    // temporary crappy selector, looking for optimization
    cy.get('[style="position: fixed; top: 0px; left: 0px; width: 100%; height: 100%; z-index: 2147483647; background-image: radial-gradient(rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0.8)); animation: 0.3s linear 0s 1 normal none running klarnaApfShow; display: flex; justify-content: center; align-items: center;"] > div > button').click()
    cy.wait(1000)
    cy.iframe('[id="klarna-apf-iframe"]').find('[id="onContinue__text"]').click().should('exist').wait(3000)
    cy.iframe('[id="klarna-apf-iframe"]').find('[id="otp_field"]').clear().type('123456').should('exist').wait(3000)
    cy.iframe('[id="klarna-apf-iframe"]').find('[id="buy_button"]').click().should('exist').wait(3000)
    cy.contains('confirmed').should('be.visible') // checking if success screen is seen in UI
  })