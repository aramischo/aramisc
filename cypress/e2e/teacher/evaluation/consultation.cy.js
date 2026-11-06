// cypress/e2e/teacher/evaluation/consultation.cy.js
// Rôle Teacher — Rapport d'évaluation de l'enseignant.

describe('Teacher · Évaluation · consultation', () => {
  const shot = 'teacher/evaluation/consultation';
  beforeEach(() => cy.loginAsTeacher());

  it('affiche le rapport d\'évaluation de l\'enseignant', () => {
    cy.visit('/teacher-panel-evaluation-report');
    cy.url().should('include', '/teacher-panel-evaluation-report');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-evaluation`);
  });
});
