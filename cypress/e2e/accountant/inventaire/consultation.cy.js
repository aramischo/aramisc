// cypress/e2e/accountant/inventaire/consultation.cy.js
// Rôle Accountant — Inventaire : articles, réceptions, ventes, fournisseurs.

describe('Accountant · Inventaire · consultation', () => {
  const shot = 'accountant/inventaire/consultation';
  beforeEach(() => cy.loginAsAccountant());

  const pages = [
    { url: '/item-list', nom: 'articles' },
    { url: '/item-category', nom: 'categories-articles' },
    { url: '/item-receive-list', nom: 'receptions' },
    { url: '/item-sell-list', nom: 'ventes' },
    { url: '/suppliers', nom: 'fournisseurs' },
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
