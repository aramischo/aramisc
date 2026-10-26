// cypress/e2e/receptionist/courrier/consultation.cy.js
// Rôle Receptionist — Courrier : réception et envoi postal.

describe('Receptionist · Courrier · consultation', () => {
  const shot = 'receptionist/courrier/consultation';
  beforeEach(() => cy.loginAsReceptionist());

  const pages = [
    { url: '/postal-receive', nom: 'reception-postale' },
    { url: '/postal-dispatch', nom: 'envoi-postal' },
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
