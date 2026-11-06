// cypress/e2e/accountant/tableau_de_bord/consultation.cy.js
// Rôle Accountant (comptable) — back-office admin restreint par permissions.

describe('Accountant · Tableau de bord · consultation', () => {
  const shot = 'accountant/tableau_de_bord/consultation';
  beforeEach(() => cy.loginAsAccountant());

  it('affiche le tableau de bord du comptable', () => {
    cy.visit('/admin-dashboard');
    cy.url().should('include', '/admin-dashboard');
    cy.url().should('not.include', '/login');
    cy.get('.card, .white-box, [class*="widget"], h2, h3').should('exist');
    // cy.screenshot(`${shot}/01-tableau-de-bord`);
  });
});
