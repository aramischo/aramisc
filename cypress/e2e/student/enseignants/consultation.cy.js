// cypress/e2e/student/enseignants/consultation.cy.js
// Rôle Student — rubrique Enseignants : consultation de la liste des enseignants.

describe('Student · Enseignants · consultation', () => {
  const shot = 'student/enseignants/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche la liste des enseignants de l\'élève', () => {
    cy.visit('/student-teacher');
    cy.url().should('include', '/student-teacher');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-enseignants`);
  });
});
