// cypress/e2e/teacher/planning_examen/consultation.cy.js
// Rôle Teacher — Planning des examens.

describe('Teacher · Planning d\'examen · consultation', () => {
  const shot = 'teacher/planning_examen/consultation';
  beforeEach(() => cy.loginAsTeacher());

  it('affiche le planning des examens', () => {
    cy.visit('/exam-schedule');
    cy.url().should('include', '/exam-schedule');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box, form').should('exist');
    // cy.screenshot(`${shot}/01-planning-examen`);
  });
});
