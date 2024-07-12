//Checking the console for errors
let windowConsoleError;
Cypress.on('window:before:load', (win) => {
    windowConsoleError = cy.spy(win.console, 'error');
})
afterEach(() => {
    expect(windowConsoleError).to.not.be.called;
})
describe('PS1786 Module Configuration', {
    failFast: {
        enabled: false,
    },
}, () => {
    beforeEach(() => {
        cy.viewport(1920, 1080)
        cy.CachingBOFOPS1789()
    })
    it('01 Connecting the Test API information to module', () => {
        cy.visit('/admin1/')
        cy.OpeningModuleDashboardURL()  //clicking the Congifure
        cy.get('[name="SAFERPAY_USERNAME_TEST"]').type((Cypress.env('SAFERPAY_USERNAME_TEST')), { delay: 0, log: false })
        cy.get('[name="SAFERPAY_PASSWORD_TEST"]').type((Cypress.env('SAFERPAY_PASSWORD_TEST')), { delay: 0, log: false })
        cy.get('[name="SAFERPAY_CUSTOMER_ID_TEST"]').type((Cypress.env('SAFERPAY_CUSTOMER_ID_TEST')), { delay: 0, log: false })
        cy.get('[name="SAFERPAY_TERMINAL_ID_TEST"]').type((Cypress.env('SAFERPAY_TERMINAL_ID_TEST')), { delay: 0, log: false })
        cy.get('[name="SAFERPAY_MERCHANT_EMAILS_TEST"]').type((Cypress.env('SAFERPAY_MERCHANT_EMAILS_TEST')), { delay: 0, log: false })
        cy.get('[name="SAFERPAY_FIELDS_ACCESS_TOKEN_TEST"]').type((Cypress.env('SAFERPAY_FIELDS_ACCESS_TOKEN_TEST')), { delay: 0, log: false })
        cy.get('#configuration_fieldset_1 > .panel-footer > .btn').click()
        cy.get(':nth-child(4) > .alert').should('exist')
    })
    it('02 Enabling Saferpay carriers and countries successfully', () => {
        cy.visit('/admin1/')
        cy.get('[id="subtab-AdminPaymentPreferences"]').find('[href]').eq(0).click({ force: true })
        cy.get('[class="js-multiple-choice-table-select-column"]').eq(7).click()
        cy.get('[class="btn btn-primary"]').eq(3).click()
    })
    it('03 Enabling All payments in Module BO', () => {
        cy.visit('/admin1/')
        cy.OpeningModuleDashboardURL()
        cy.get('#subtab-AdminSaferPayOfficialPayment').click()
        //todo update selectors
        cy.get('.saferpay-group.all-payments > .col-lg-8 > .form-group > :nth-child(1) > .checkbox > .container-checkbox > .checkmark').click()
        cy.get('.saferpay-group.all-payments > .col-lg-8 > .form-group > :nth-child(2) > .checkbox > .container-checkbox > .checkmark').click()
        //cy.get('.saferpay-group.all-payments > .col-lg-8 > .form-group > :nth-child(3) > .checkbox > .container-checkbox > .checkmark').click()
        cy.get('#all_countries_chosen > .chosen-choices > .search-field > .default').click()
        cy.get('.chosen-results > :first-child').click()
        cy.get('#all_currencies_chosen > .chosen-choices > .search-field > .default').click()
        cy.get('.chosen-results > :first-child').click()
        cy.get('#configuration_form_submit_btn').click()
        cy.get('[class="alert alert-success"]').should('be.visible')
    })
    it('04 Fields and Logs tabs are shown', () => {
        cy.visit('/admin1/')
        cy.OpeningModuleDashboardURL()
        cy.get('#subtab-AdminSaferPayOfficialFields').click()
        cy.get('[id="configuration_form"]').should('be.visible')
        cy.get('.field-container > :nth-child(1) > img').click()
        cy.get(':nth-child(2) > img').click()
        cy.get(':nth-child(3) > img').click()
        cy.get('[class="alert alert-info"]').should('be.visible')
        cy.get('[name="submitOptionsconfiguration"]').click()
        cy.get('[class="alert alert-success"]').should('be.visible')
        cy.get('#subtab-AdminSaferPayOfficialLogs').click()
        cy.get('[id="form-saferpay_log"]').should('be.visible')
    })
})