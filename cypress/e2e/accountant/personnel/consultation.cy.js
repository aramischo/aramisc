// cypress/e2e/accountant/personnel/consultation.cy.js
// Rôle Accountant — Personnel : répertoire du personnel (accessible au comptable).

describe('Accountant · Personnel · consultation', () => {
  const shot = 'accountant/personnel/consultation';
  beforeEach(() => cy.loginAsAccountant());

  it('affiche le répertoire du personnel', () => {
    cy.visit('/staff-directory');
    cy.url().should('include', '/staff-directory');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box, form').should('exist');
    // cy.screenshot(`${shot}/01-personnel`);
  });
});
