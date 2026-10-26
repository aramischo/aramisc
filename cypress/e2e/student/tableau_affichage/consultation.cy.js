// cypress/e2e/student/tableau_affichage/consultation.cy.js
// Rôle Student — rubrique Tableau d'affichage : consultation des annonces.

describe('Student · Tableau d\'affichage · consultation', () => {
  const shot = 'student/tableau_affichage/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche le tableau d\'affichage de l\'élève', () => {
    cy.visit('/student-noticeboard');
    cy.url().should('include', '/student-noticeboard');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box, .notice').should('exist');
    // cy.screenshot(`${shot}/01-annonces`);
  });
});
