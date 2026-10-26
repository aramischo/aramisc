// cypress/e2e/accountant/eleves/consultation.cy.js
// Rôle Accountant — Élèves : liste des élèves (accessible au comptable).

describe('Accountant · Élèves · consultation', () => {
  const shot = 'accountant/eleves/consultation';
  beforeEach(() => cy.loginAsAccountant());

  it('affiche la liste des élèves', () => {
    cy.visit('/student-list');
    cy.url().should('include', '/student-list');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-liste-eleves`);
  });
});
