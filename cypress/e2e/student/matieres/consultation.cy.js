// cypress/e2e/student/matieres/consultation.cy.js
// Rôle Student — rubrique Matières : consultation des matières de l'élève.

describe('Student · Matières · consultation', () => {
  const shot = 'student/matieres/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche les matières de l\'élève', () => {
    cy.visit('/student-subject');
    cy.url().should('include', '/student-subject');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-matieres`);
  });
});
