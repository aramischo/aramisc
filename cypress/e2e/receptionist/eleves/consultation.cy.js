// cypress/e2e/receptionist/eleves/consultation.cy.js
// Rôle Receptionist — Élèves : liste des élèves.

describe('Receptionist · Élèves · consultation', () => {
  const shot = 'receptionist/eleves/consultation';
  beforeEach(() => cy.loginAsReceptionist());

  it('affiche la liste des élèves', () => {
    cy.visit('/student-list');
    cy.url().should('include', '/student-list');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-liste-eleves`);
  });
});
