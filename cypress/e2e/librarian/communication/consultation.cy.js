// cypress/e2e/librarian/communication/consultation.cy.js
// Rôle Librarian — Communication : événements, tableau d'affichage.

describe('Librarian · Communication · consultation', () => {
  const shot = 'librarian/communication/consultation';
  beforeEach(() => cy.loginAsLibrarian());

  const pages = [
    { url: '/event', nom: 'evenements' },
    { url: '/notice-list', nom: 'annonces' },
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
