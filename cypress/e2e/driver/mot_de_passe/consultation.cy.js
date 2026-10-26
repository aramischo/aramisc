// cypress/e2e/driver/mot_de_passe/consultation.cy.js
// Rôle Driver — Changement de mot de passe : affichage du formulaire (sans modifier).
// Le chauffeur n'a aucune permission de module → surface limitée au tableau de bord et au profil.

describe('Driver · Mot de passe · consultation', () => {
  const shot = 'driver/mot_de_passe/consultation';
  beforeEach(() => cy.loginAsDriver());

  it('affiche le formulaire de changement de mot de passe', () => {
    cy.visit('/change-password');
    cy.url().should('include', '/change-password');
    cy.url().should('not.include', '/login');
    cy.get('form').should('exist');
    cy.get('input[type="password"]').its('length').should('be.gte', 1);
    // cy.screenshot(`${shot}/01-formulaire`);
  });
});
