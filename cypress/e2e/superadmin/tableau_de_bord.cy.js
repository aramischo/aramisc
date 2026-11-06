// cypress/e2e/superadmin/tableau_de_bord.cy.js
//
// Super admin (role_id 1) — baseline / smoke.
//
// Contexte : dans cette installation AUCUNE route n'est exclusive au Super admin
// (le module Saas est désactivé, la route superadmin-dashboard n'existe pas). La
// seule spécificité GARANTIE du role 1 est le court-circuit du système de permissions
// dans userPermission() (Helper.php:426 : role_id==1 && is_administrator=='yes' => true).
// Ce spec vérifie que le chemin de connexion role 1 fonctionne et que le Super admin
// atteint le tableau de bord et un échantillon d'écrans de gestion.
//
// ⚠ NB : l'enforcement des permissions par-route est de toute façon neutralisé pour
//   TOUS les rôles (bug pré-existant : le singleton app('permission') d'AppServiceProvider
//   ne retourne rien). Voir NOTES_PASSATION.md. Un rôle restreint atteint donc aussi ces
//   pages ; ce spec documente l'accès du Super admin, pas une différence 403/200.

describe('Super admin – Tableau de bord & accès', () => {
  beforeEach(() => {
    cy.loginAsSuperAdmin();
  });

  it('se connecte en role 1 et affiche le tableau de bord admin', () => {
    cy.visit('/admin-dashboard');
    cy.url().should('include', '/admin-dashboard');
    cy.get('body').should('be.visible');
    cy.screenshot('superadmin/tableau_de_bord/dashboard');
  });

  // Échantillon représentatif d'écrans de gestion : le Super admin les atteint tous (200).
  const PAGES = [
    '/global-section',
    '/global-class',
    '/department',
    '/designation',
    '/bank-account',
    '/add-expense',
    '/add-income',
  ];

  PAGES.forEach((route) => {
    it(`atteint ${route} (200)`, () => {
      cy.request({ url: route, failOnStatusCode: false }).its('status').should('eq', 200);
    });
  });
});
