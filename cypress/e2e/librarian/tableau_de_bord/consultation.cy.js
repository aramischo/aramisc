// cypress/e2e/librarian/tableau_de_bord/consultation.cy.js
// Rôle Librarian (bibliothécaire) — back-office admin restreint par permissions.

describe('Librarian · Tableau de bord · consultation', () => {
  const shot = 'librarian/tableau_de_bord/consultation';
  beforeEach(() => cy.loginAsLibrarian());

  it('affiche le tableau de bord du bibliothécaire', () => {
    cy.visit('/admin-dashboard');
    cy.url().should('include', '/admin-dashboard');
    cy.url().should('not.include', '/login');
    cy.get('.card, .white-box, [class*="widget"], h2, h3').should('exist');
    // cy.screenshot(`${shot}/01-tableau-de-bord`);
  });
});
