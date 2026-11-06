// cypress/e2e/student/dortoir/consultation.cy.js
// Rôle Student — rubrique Dortoir : consultation des informations de dortoir.

describe('Student · Dortoir · consultation', () => {
  const shot = 'student/dortoir/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche les informations de dortoir de l\'élève', () => {
    cy.visit('/student-dormitory');
    cy.url().should('include', '/student-dormitory');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-dortoir`);
  });
});
