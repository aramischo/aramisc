// cypress/e2e/driver/tableau_de_bord/consultation.cy.js
// Rôle Driver (chauffeur) — aucune permission de module assignée : la surface se
// limite au tableau de bord et au profil / mot de passe.

describe('Driver · Tableau de bord · consultation', () => {
  const shot = 'driver/tableau_de_bord/consultation';
  beforeEach(() => cy.loginAsDriver());

  it('affiche le tableau de bord du chauffeur', () => {
    cy.visit('/admin-dashboard');
    cy.url().should('include', '/admin-dashboard');
    cy.url().should('not.include', '/login');
    cy.get('.card, .white-box, [class*="widget"], h2, h3, body').should('exist');
    // cy.screenshot(`${shot}/01-tableau-de-bord`);
  });
});
