describe('Admin journey - part 2', () => {
  it.only('1 - Check order', () => {
    cy.LogInBO()
    cy.get('#subtab-AdminOrders > .link').click({force:true})
  })
  it('2 - Fully capture transaction', () => {
    // todo
  })
})