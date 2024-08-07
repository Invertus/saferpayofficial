//Checking the console for errors
let windowConsoleError;
Cypress.on('window:before:load', (win) => {
    windowConsoleError = cy.spy(win.console, 'error');
})
afterEach(() => {
    expect(windowConsoleError).to.not.be.called;
})
describe('PS817 Tests Suite -> Authorization + Before auth', {
    failFast: {
        enabled: false,
    },
}, () => {
    beforeEach(() => {
        cy.viewport(1920, 1080)
        cy.CachingBOFO()

    })

    it('Change the setting to Authorize', () => {
        cy.visit('/admin1/')
        cy.OpeningModuleDashboardURL()
        cy.get('#SAFERPAY_PAYMENT_BEHAVIOR_1').click()
        cy.get('#configuration_fieldset_2 > .panel-footer > .btn').click()
    })
    it('Change the setting create order before authorization', () => {
        cy.visit('/admin1/')
        cy.OpeningModuleDashboardURL()
        cy.get('#SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION_0').click()
        cy.get('#configuration_fieldset_2 > .panel-footer > .btn').click()
    })


    it('A2A guest PM visible', () => {
        cy.clearCookies()
        cy.visit('/en/women/2-9-brown-bear-printed-sweater.html#/1-size-s', { headers: {"Accept-Encoding": "gzip, deflate"}})
        cy.guestCheckout()
        cy.contains('Accounttoaccount').should('be.visible')
    })

    it('Twint guest success', () => {
        cy.clearCookies()
        cy.visit('/en/women/2-9-brown-bear-printed-sweater.html#/1-size-s')
        cy.guestCheckoutCHF()
        cy.contains('Twint').click({ force: true })
        cy.get('.condition-label > .js-terms').click({ force: true })
        cy.contains('Place order').click()
        cy.get('.saferpay-paymentpage').should('be.visible')
        cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
    })

    it('Wechatpay Checkouting', () => {
        cy.navigatingToThePayment()
        //Payment method choosing
        cy.contains('Wechatpay').click({ force: true })
        cy.get('.condition-label > .js-terms').click({ force: true })
        cy.contains('Place order').click({ force: true })
        cy.get('.saferpay-paymentpage').should('be.visible')
        cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
    })

    it('MC Checkouting', () => {
        cy.navigatingToThePayment()
        //Payment method choosing
        cy.contains('Mastercard').click({ force: true })
        cy.get('.condition-label > .js-terms').click({ force: true })
        cy.contains('Place order').click({ force: true })
        cy.get('[class="btn btn-next pay-button"]').click()
        cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
    })

    it('Sofort Checkouting', () => {
        cy.navigatingToThePayment()
        //Payment method choosing
        cy.contains('Sofort').should('be.visible')
    })

    it('Visa Checkouting', () => {
        cy.navigatingToThePayment()
        //Payment method choosing
        cy.contains('Visa').click({ force: true })
        cy.get('.condition-label > .js-terms').click({ force: true })
        cy.contains('Visa').should('be.visible')
    })

    it('AmericanExpressCheckouting', () => {
        cy.navigatingToThePayment()
        //Payment method choosing
        cy.contains('AmericanExpress').click({ force: true })
        cy.get('.condition-label > .js-terms').click({ force: true })
        cy.contains('Place order').click({ force: true })
        cy.FillAmex()
        cy.get('[class="btn btn-next pay-button"]').click()
        cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
    })

    it('DinersClub Checkouting', () => {
        cy.navigatingToThePayment()
        //Payment method choosing
        cy.contains('DinersClub').click({ force: true })
        cy.get('.condition-label > .js-terms').click({ force: true })
        cy.contains('Place order').click({ force: true })
        cy.FillDiners()
        cy.get('[class="btn btn-next pay-button"]').click()
        cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
    })

    it('Jcb Checkouting', () => {
        cy.navigatingToThePayment()
        //Payment method choosing
        cy.contains('Jcb').click({ force: true })
        cy.get('.condition-label > .js-terms').click({ force: true })
        cy.contains('Place order').click({ force: true })
        cy.FillJcb()
        cy.get('[class="btn btn-next pay-button"]').click()
        cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
    })

    it('Apple Pay Checkouting', () => {
        cy.navigatingToThePayment()
        //Payment method choosing
        cy.contains('Applepay').click({ force: true })
        cy.get('.condition-label > .js-terms').click({ force: true })
        cy.contains('Applepay').should('be.visible')
        // cy.contains('Place order').click({ force: true })
        // cy.get('.btn-wallet-applepay').click()
        // cy.get('#payButtonText').click()
        // cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
    })

    it('Myone Checkouting', () => {
        cy.visit('/en/order-history')
        cy.changeCurrencyCHF()
        cy.navigatingToThePaymentCHF()
        cy.contains('Myone').should('be.visible')
    })

    it('BonusCard Checkouting', () => {
        cy.visit('/en/order-history')
        cy.changeCurrencyCHF()
        cy.navigatingToThePaymentCHF()
        //Payment method choosing
        cy.contains('BonusCard').should('be.visible')
    })

    it('Paypal Checkouting', () => {
        cy.navigatingToThePayment()
        //Payment method choosing
        cy.contains('Paypal').click({ force: true })
        cy.contains('Paypal').should('be.visible')
    })

    it('Unionpay Checkouting', () => {
        cy.navigatingToThePayment()
        //Payment method choosing
        cy.contains('Unionpay').click({ force: true })
        cy.get('.condition-label > .js-terms').click({ force: true })
        cy.contains('Unionpay').should('be.visible')
    })

    it('Postfinancepay Checkouting', () => {
        cy.visit('/en/order-history')
        cy.changeCurrencyCHF()
        cy.navigatingToThePaymentCHF()
        //Payment method choosing
        cy.contains('Postfinancepay').click({ force: true })
        cy.get('.condition-label > .js-terms').click({ force: true })
        cy.contains('Postfinancepay').should('be.visible')
    })
})