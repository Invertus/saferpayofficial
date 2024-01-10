describe('Admin journey - part 2', () => {
  it('1 - Check order', () => {
    cy.LogInBO()
    cy.get('#subtab-AdminOrders > .link').click({force:true})
    cy.get(':nth-child(1) > .column-reference')
    cy.get(':nth-child(1) > .choice-type').contains('Awaiting payment capture').should('be.visible').should('exist')
    cy.get(':nth-child(1) > .order_price-type').contains('€115.20').should('be.visible').should('exist') // hardcoded price verifying
    cy.get(':nth-child(1) > .column-payment').click() // navigating into the last Order
    cy.get('#worldlineop-admin-order').scrollIntoView()
    cy.get('[id="worldlineop-admin-order"]').contains('115.20 EUR').should('be.visible').should('exist') // hardcoded price verifying
    cy.get(':nth-child(1) > .col-md-12 > .info-block > .row > :nth-child(2) > :nth-child(2)') // todo transaction ID
  })
  it('2 - Fully capture transaction', () => {
    cy.LogInBO()
    cy.get('#subtab-AdminOrders > .link').click({force:true})
    cy.get(':nth-child(1) > .column-reference')
    cy.get(':nth-child(1) > .column-payment').click() // navigating into the last Order
    cy.get('#worldlineop-admin-order').scrollIntoView()
    cy.get('#worldlineop-btn-capture').click()
  })
})