// cypress/e2e/librarian/eleves/consultation.cy.js
// Rôle Librarian — Élèves : liste des élèves.

describe('Librarian · Élèves · consultation', () => {
  const shot = 'librarian/eleves/consultation';
  beforeEach(() => cy.loginAsLibrarian());

  it('affiche la liste des élèves', () => {
    cy.visit('/student-list');
    cy.url().should('include', '/student-list');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-liste-eleves`);
  });
});
