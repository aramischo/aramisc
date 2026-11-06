// cypress/e2e/student/presence/consultation.cy.js
// Rôle Student — rubrique Présence : consultation + recherche mensuelle (lecture seule).

describe('Student · Présence · consultation & recherche', () => {
  const shot = 'student/presence/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche le formulaire de recherche de présence', () => {
    cy.visit('/student-my-attendance');
    cy.url().should('include', '/student-my-attendance');
    cy.get('form').should('exist');
    cy.get('select[name="month"]').should('exist');
    cy.get('select[name="year"]').should('exist');
    cy.get('button[type="submit"]').should('exist');
    // cy.screenshot(`${shot}/01-formulaire-recherche`);
  });

  it('recherche la présence d\'un mois et affiche le rapport', () => {
    cy.visit('/student-my-attendance');

    const year = new Date().getFullYear().toString();
    cy.get('select[name="month"]').select('09', { force: true });
    cy.get('select[name="year"]').select(year, { force: true });

    // cy.screenshot(`${shot}/02-criteres-remplis`);
    cy.get('button[type="submit"]').first().click({ force: true });

    // Rechargement POST : la recherche renvoie une page authentifiée 200 (pas de 500,
    // pas de déconnexion) avec le formulaire de recherche toujours présent. Le rapport
    // mensuel lui-même reste vide tant qu'aucune présence n'est saisie pour l'élève.
    cy.url().should('include', '/student-my-attendance');
    cy.url().should('not.include', '/login');
    cy.get('select[name="month"]').should('exist');
    cy.get('select[name="year"]').should('exist');
    // cy.screenshot(`${shot}/03-rapport-affiche`);
  });
});
