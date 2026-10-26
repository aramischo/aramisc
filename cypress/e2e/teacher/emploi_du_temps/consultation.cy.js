// cypress/e2e/teacher/emploi_du_temps/consultation.cy.js
// Rôle Teacher — Emploi du temps (route /class-routine).

describe('Teacher · Emploi du temps · consultation', () => {
  const shot = 'teacher/emploi_du_temps/consultation';
  beforeEach(() => cy.loginAsTeacher());

  it('affiche l\'emploi du temps', () => {
    cy.visit('/class-routine');
    cy.url().should('include', '/class-routine');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-emploi-du-temps`);
  });
});
