// cypress/e2e/teacher/eleves/consultation.cy.js
// Rôle Teacher — Élèves : liste des élèves.

describe('Teacher · Élèves · consultation', () => {
  const shot = 'teacher/eleves/consultation';
  beforeEach(() => cy.loginAsTeacher());

  it('affiche la liste des élèves', () => {
    cy.visit('/student-list');
    cy.url().should('include', '/student-list');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-liste-eleves`);
  });
});
