// cypress/e2e/student/examen_en_ligne/consultation.cy.js
// Rôle Student — rubrique Examen en ligne : liste des examens disponibles + résultats passés.
// On ne lance pas réellement un examen (take-online-exam) pour ne pas consommer une tentative.

describe('Student · Examen en ligne · consultation', () => {
  const shot = 'student/examen_en_ligne/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche la liste des examens en ligne disponibles', () => {
    cy.visit('/student-online-exam');
    cy.url().should('include', '/student-online-exam');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-liste-examens`);
  });

  it('affiche les résultats des examens en ligne passés', () => {
    cy.visit('/student_view_result');
    cy.url().should('include', '/student_view_result');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/02-resultats-examens`);
  });
});
