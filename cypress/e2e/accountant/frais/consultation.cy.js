// cypress/e2e/accountant/frais/consultation.cy.js
// Rôle Accountant — Frais : collecte, groupes/types/barèmes, rapports de frais.

describe('Accountant · Frais · consultation', () => {
  const shot = 'accountant/frais/consultation';
  beforeEach(() => cy.loginAsAccountant());

  const pages = [
    { url: '/collect-fees', nom: 'collecte-frais' },
    { url: '/fees-group', nom: 'groupes-frais' },
    { url: '/fees-type', nom: 'types-frais' },
    { url: '/fees-master', nom: 'baremes-frais' },
    { url: '/balance-fees-report', nom: 'rapport-solde-frais' },
    { url: '/student-fine-report', nom: 'rapport-amendes' },
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
