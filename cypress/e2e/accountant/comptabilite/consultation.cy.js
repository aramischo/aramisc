// cypress/e2e/accountant/comptabilite/consultation.cy.js
// Rôle Accountant — Comptabilité : revenus, dépenses, plan comptable, comptes bancaires, bénéfice.
// Pages accessibles d'après les permissions du rôle (lecture seule ; aucune écriture).

describe('Accountant · Comptabilité · consultation', () => {
  const shot = 'accountant/comptabilite/consultation';
  beforeEach(() => cy.loginAsAccountant());

  const pages = [
    { url: '/add-income', nom: 'revenus' },
    { url: '/add-expense', nom: 'depenses' },
    { url: '/chart-of-account', nom: 'plan-comptable' },
    { url: '/bank-account', nom: 'comptes-bancaires' },
    { url: '/profit', nom: 'benefice' },
    // NB : /payment-method est une route web retirée (commentée dans admin_tenant.php), remplacée
    // par /payment-method-settings. Il ne restait qu'une permission orpheline (aramisc_role_permissions,
    // lien 152) pour les rôles 1/5/6, non rendue dans les menus actifs → nettoyée (voir NOTES_PASSATION).
    // Exclu du test car la route n'existe plus (404).
  ];

  pages.forEach(({ url, nom }, i) => {
    it(`accède à « ${nom} »`, () => {
      cy.visit(url);
      cy.url().should('include', url);
      cy.url().should('not.include', '/login');
      cy.get('table, .card, .white-box, form').should('exist');
      // cy.screenshot(`${shot}/0${i + 1}-${nom}`);
    });
  });
});
