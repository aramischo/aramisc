// cypress/e2e/student/resultat/consultation.cy.js
// Rôle Student — rubrique Résultat : consultation des résultats (lecture seule).

describe('Student · Résultat · consultation', () => {
  const shot = 'student/resultat/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche la page des résultats de l\'élève', () => {
    cy.visit('/student-result');
    cy.url().should('include', '/student-result');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box, form').should('exist');
    // cy.screenshot(`${shot}/01-resultats`);
  });
});
