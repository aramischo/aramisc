// cypress/e2e/student/emploi_du_temps/consultation.cy.js
// Rôle Student — rubrique Emploi du temps : consultation de l'emploi du temps de classe.

describe('Student · Emploi du temps · consultation', () => {
  const shot = 'student/emploi_du_temps/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche l\'emploi du temps de la classe', () => {
    cy.visit('/student-class-routine');
    cy.url().should('include', '/student-class-routine');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-emploi-du-temps`);
  });
});
