// cypress/e2e/student/transport/consultation.cy.js
// Rôle Student — rubrique Transport : consultation des informations de transport.

describe('Student · Transport · consultation', () => {
  const shot = 'student/transport/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche les informations de transport de l\'élève', () => {
    cy.visit('/student-transport');
    cy.url().should('include', '/student-transport');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-transport`);
  });
});
