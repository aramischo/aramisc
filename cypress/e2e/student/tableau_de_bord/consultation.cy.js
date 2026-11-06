// cypress/e2e/student/tableau_de_bord/consultation.cy.js
// Rôle Student — rubrique Tableau de bord : page d'accueil de l'élève.

describe('Student · Tableau de bord · consultation', () => {
  const shot = 'student/tableau_de_bord/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche le tableau de bord de l\'élève', () => {
    cy.visit('/student-dashboard');
    cy.url().should('include', '/student-dashboard');
    cy.url().should('not.include', '/login');
    cy.get('.card, .white-box, [class*="widget"], h2, h3').should('exist');
    // cy.screenshot(`${shot}/01-tableau-de-bord`);
  });
});
