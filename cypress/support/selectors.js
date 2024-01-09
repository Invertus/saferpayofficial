Cypress.Commands.add('selectProductHomepage', () => {
  const targetSelector = '[class="h3 product-title"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[class="h3 product-title"]').should('exist').first()
    }
    else {
        // Selector 2
        cy.contains('Hummingbird printed t-shirt').should('exist').first()
    }
  })
})
Cypress.Commands.add('clickAddToCart', () => {
  const targetSelector = '[data-button-action="add-to-cart"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[data-button-action="add-to-cart"]').should('exist').first()
    }
    else {
        // Selector 2
        cy.contains('Add to cart').should('exist').first()
    }
  })
})
Cypress.Commands.add('modalPopupExistence', () => {
  const targetSelector = '[class="modal-dialog"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[class="modal-dialog"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[class="modal-content"]').should('exist')
    }
    else if (condition) {
        // Selector 3
        cy.get('[class="modal-body"]').should('exist')
    }
    else if (condition) {
        // Selector 4
        cy.get('[class="cart-content"]').should('exist')
    }
    else if (condition) {
        // Selector 5
        cy.get('[class="cart-content-btn"]').should('exist')
    }
  })
})
// clicks on Proceed to Checkout in the modal popup
Cypress.Commands.add('proceedToCheckoutModalPopup', () => {
  const targetSelector = '[class="btn btn-primary"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[class="modal-dialog"]').find('[class="btn btn-primary"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[class="modal-content"]').find('Proceed to checkout').should('exist')
    }
    else if (condition) {
        // Selector 3
        cy.get('[class="modal-body"]').find('Checkout').should('exist')
    }
    else if (condition) {
        // Selector 4
        cy.get('[class="modal-body"]').find('[class="btn btn-primary btn-block btn-lg mb-2"]').should('exist')
    }
  })
})
Cypress.Commands.add('proceedToCheckout', () => {
  const targetSelector = '[class="btn btn-primary"]:visible' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[class="btn btn-primary"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.contains('Proceed to checkout').should('exist')
    }
    else if (condition) {
        // Selector 3
        cy.contains('Checkout').should('exist')
    }
    else if (condition) {
        // Selector 4
        cy.contains('[class="btn btn-primary btn-block btn-lg mb-2"]').should('exist')
    }
  })
})
Cypress.Commands.add('firstNameInput', () => {
  const targetSelector = '[name="firstname"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="firstname"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.contains('Proceed to checkout').should('exist')
    }
    else if (condition) {
        // Selector 3
        cy.contains('Checkout').should('exist')
    }
    else if (condition) {
        // Selector 4
        cy.contains('[class="btn btn-primary btn-block btn-lg mb-2"]').should('exist')
    }
  })
})
Cypress.Commands.add('lastNameInput', () => {
  const targetSelector = '[name="lastname"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="lastname"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.contains('Proceed to checkout').should('exist')
    }
    else if (condition) {
        // Selector 3
        cy.contains('Checkout').should('exist')
    }
    else if (condition) {
        // Selector 4
        cy.contains('[class="btn btn-primary btn-block btn-lg mb-2"]').should('exist')
    }
  })
})
Cypress.Commands.add('emailInput', () => {
  const targetSelector = '[name="email"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="email"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[type="email"]').should('exist')
    }
  })
})
Cypress.Commands.add('passwordInput', () => {
  const targetSelector = '[name="password"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="password"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[type="password"]').should('exist')
    }
  })
})
Cypress.Commands.add('birthdayInput', () => {
  const targetSelector = '[name="birthday"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="birthday"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[type="birthday"]').should('exist')
    }
  })
})
Cypress.Commands.add('continueButton', () => {
  const targetSelector = '[name="continue"]:visible' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="continue"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[data-link-action="register-new-customer"]').should('exist')
    }
    else if (condition) {
        // Selector 3
        cy.get('[type="submit"]').should('exist')
    }
    else if (condition) {
        // Selector 4
        cy.contains('Continue').should('exist')
    }
  })
})
Cypress.Commands.add('continueAddressButton', () => {
  const targetSelector = '[name="confirm-addresses"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="confirm-addresses"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[class="continue btn btn-primary float-xs-right"]').should('exist')
    }
    else if (condition) {
        // Selector 3
        cy.get('[type="submit"]').should('exist')
    }
    else if (condition) {
        // Selector 4
        cy.contains('Continue').should('exist')
    }
  })
})
Cypress.Commands.add('continueDeliveryButton', () => {
  const targetSelector = '[name="confirmDeliveryOption"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="confirmDeliveryOption"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[class="continue btn btn-primary float-xs-right"]').should('exist')
    }
    else if (condition) {
        // Selector 3
        cy.get('[type="submit"]').should('exist')
    }
    else if (condition) {
        // Selector 4
        cy.contains('Continue').should('exist')
    }
  })
})
Cypress.Commands.add('companyInput', () => {
  const targetSelector = '[name="company"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="company"]').should('exist')
    }
  })
})
Cypress.Commands.add('vatInput', () => {
  const targetSelector = '[name="vat_number"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="vat_number"]').should('exist')
    }
  })
})
Cypress.Commands.add('addressInput1', () => {
  const targetSelector = '[name="address1"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="address1"]').should('exist')
    }
  })
})
Cypress.Commands.add('addressInput2', () => {
  const targetSelector = '[name="address2"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="address2"]').should('exist')
    }
  })
})
Cypress.Commands.add('cityInput', () => {
  const targetSelector = '[name="city"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="city"]').should('exist')
    }
  })
})
Cypress.Commands.add('zipInput', () => {
  const targetSelector = '[name="postcode"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="postcode"]').should('exist')
    }
  })
})
Cypress.Commands.add('countrySelect', () => {
  const targetSelector = '[name="id_country"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="id_country"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[class="form-control form-control-select js-country"]').should('exist')
    }
  })
})
Cypress.Commands.add('phoneInput', () => {
  const targetSelector = '[name="phone"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[name="phone"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[type="tel"]').should('exist')
    }
  })
})
// choosing the Payment option
Cypress.Commands.add('selectPaymentCheckbox', () => {
  const targetSelector = '[id="payment-option-1"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[id="payment-option-1"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[data-module-name="ps_checkpayment"]').should('exist')
    }
    else if (condition) {
        // Selector 3
        cy.get('[name="payment-option"]').should('exist')
    }
  })
})
// clicking on Terms and Conditions checkbox
Cypress.Commands.add('conditionsCheckbox', () => {
  const targetSelector = '[id="conditions_to_approve[terms-and-conditions]"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[id="conditions_to_approve[terms-and-conditions]"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[name="conditions_to_approve[terms-and-conditions]"]').should('exist')
    }
    else if (condition) {
        // Selector 3
        cy.get('[type="checkbox"]').should('exist')
    }
    else if (condition) {
        // Selector 4
        cy.get('[class="custom-checkbox"]').should('exist')
    }
  })
})
// clicking on Obligation to Pay in FO checkout
Cypress.Commands.add('confirmOrderButton', () => {
  const targetSelector = '[class="btn btn-primary center-block"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[class="btn btn-primary center-block"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[id="payment-confirmation"]').should('exist')
    }
    else if (condition) {
        // Selector 3
        cy.contains('Order with an obligation to pay').should('exist')
    }
  })
})
// verifying the FO Thank You page
Cypress.Commands.add('verifySuccessPageExistence', () => {
  const targetSelector = '[class="h1 card-title"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[class="h1 card-title"]').should('exist')
    }
    else if (condition) {
        // Selector 2
        cy.get('[class="material-icons rtl-no-flip done"]').should('exist')
    }
    else if (condition) {
        // Selector 3
        cy.contains('Your order is confirmed').should('exist')
    }
  })
})
// clicking on BO > Orders > Orders
Cypress.Commands.add('clickOrderMenu', () => {
  const targetSelector = '[id="subtab-AdminParentOrders"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[id="subtab-AdminParentOrders"]').click()
        cy.get('[id="subtab-AdminOrders"]')
    }
    else {
        // Selector 2
        cy.get('[class="link-levelone has_submenu"]').click()
        cy.get('[id="subtab-AdminOrders"]')
    }
  })
})
// clicking on the latest tr of BO > Orders
Cypress.Commands.add('takeLatestOrder', () => {
  const targetSelector = '[id="order_grid_table"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[id="order_grid_table"]')
          .within(() => {
          // finds the latest Order <tr>
          cy.get('tr').eq(2).should('exist').click()
         })
    }
    else {
        // Selector 2
        cy.get('[class="grid-table js-grid-table table  "]')
          .within(() => {
          // finds the latest Order <tr>
          cy.get('tr').eq(2).should('exist').click()
         })
    }
  })
})
Cypress.Commands.add('checkAllRequiredCheckboxes', () => {
  const targetSelector = '[type="checkbox"]' // the targeted selector is here
  cy.get('body').then((body) => {
    const condition = body.find(targetSelector).length > 0
    if (condition) {
        // Selector 1
        cy.get('[type="checkbox"]').should('exist')
    }
    else {
        // Selector 2
        cy.contains('[class="material-icons rtl-no-flip checkbox-checked"]:visible').should('exist').first()
    }
  })
})
Cypress.Commands.add('visitingCart', () => {
  cy.request({ url: '/cart', failOnStatusCode: false }).then((response) => {
    if (response.status === 404) {
      // If the initial URL returns a 404 status code, visit the alternative URL
      cy.visit('/index.php?controller=cart&action=show');
    } else {
      // Handle other status codes or perform assertions for the initial URL
      // For example: cy.expect(response.status).to.eq(200);
      // Add your assertions or test logic here
    }
  })
})