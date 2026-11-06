// cypress/e2e/receptionist/tableau_de_bord/consultation.cy.js
// Rôle Receptionist (réception) — back-office admin restreint par permissions.

describe('Receptionist · Tableau de bord · consultation', () => {
  const shot = 'receptionist/tableau_de_bord/consultation';
  beforeEach(() => cy.loginAsReceptionist());

  it('affiche le tableau de bord de la réception', () => {
    cy.visit('/admin-dashboard');
    cy.url().should('include', '/admin-dashboard');
    cy.url().should('not.include', '/login');
    cy.get('.card, .white-box, [class*="widget"], h2, h3').should('exist');
    // cy.screenshot(`${shot}/01-tableau-de-bord`);
  });
});
