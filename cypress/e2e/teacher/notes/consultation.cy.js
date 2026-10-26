// cypress/e2e/teacher/notes/consultation.cy.js
// Rôle Teacher — Registre de saisie des notes.

describe('Teacher · Notes · consultation', () => {
  const shot = 'teacher/notes/consultation';
  beforeEach(() => cy.loginAsTeacher());

  it('affiche le registre de saisie des notes', () => {
    cy.visit('/marks-register');
    cy.url().should('include', '/marks-register');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box, form').should('exist');
    // cy.screenshot(`${shot}/01-registre-notes`);
  });
});
