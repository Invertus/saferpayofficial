describe('Admin journey - part 2', () => {
  it('1 - Check order', () => {
    cy.LogInBO()
    cy.get('#subtab-AdminOrders > .link').highlightElement().click({force:true})
    cy.get(':nth-child(1) > .column-reference').highlightElement()
    cy.get(':nth-child(1) > .choice-type').highlightElement().contains('Awaiting payment capture').should('be.visible').should('exist')
    cy.get(':nth-child(1) > .order_price-type').highlightElement().contains('€115.20').should('be.visible').should('exist') // hardcoded price verifying
    cy.get(':nth-child(1) > .column-payment').highlightElement().click() // navigating into the last Order
    cy.get('#worldlineop-admin-order').highlightElement().scrollIntoView()
    cy.get('[id="worldlineop-admin-order"]').highlightElement().contains('115.20 EUR').should('be.visible').should('exist') // hardcoded price verifying
    cy.get(':nth-child(1) > .col-md-12 > .info-block > .row > :nth-child(2) > :nth-child(2)').highlightElement() // todo transaction ID
  })
  it('2 - Fully capture transaction', () => {
    cy.LogInBO()
    cy.get('#subtab-AdminOrders > .link').highlightElement().click({force:true})
    cy.get(':nth-child(1) > .column-reference').highlightElement()
    cy.get(':nth-child(1) > .column-payment').highlightElement().click() // navigating into the last Order
    cy.get('#worldlineop-admin-order').highlightElement().scrollIntoView()
    cy.get('#worldlineop-btn-capture').highlightElement().click()
    cy.contains('Capture requested successfully').highlightElement().should('exist').should('be.visible')
    cy.get(':nth-child(1) > .card > .card-body > :nth-child(1) > .col > :nth-child(2) > :nth-child(2)').highlightElement().contains('0.00 EUR') // hardcoded so far - Amount captured
    cy.get(':nth-child(1) > .card > .card-body > :nth-child(1) > .col > :nth-child(3) > :nth-child(2)').highlightElement().contains('115.20 EUR') // hardcoded so far - Amount pending capture
    cy.get(':nth-child(1) > .card > .card-body > :nth-child(1) > .col > :nth-child(4) > :nth-child(2)').highlightElement().contains('0.00 EUR') // hardcoded so far - Amount that can be captured
    cy.get('#update_order_status_action_input').contains('Awaiting payment capture').should('be.visible').highlightElement().should('exist')
  })
})