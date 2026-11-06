// cypress/e2e/librarian/bibliotheque/consultation.cy.js
// Rôle Librarian — Bibliothèque : livres, catégories, membres, emprunts.

describe('Librarian · Bibliothèque · consultation', () => {
  const shot = 'librarian/bibliotheque/consultation';
  beforeEach(() => cy.loginAsLibrarian());

  const pages = [
    { url: '/book-list', nom: 'liste-livres' },
    { url: '/add-book', nom: 'ajouter-livre' },
    { url: '/book-category-list', nom: 'categories' },
    { url: '/library-member', nom: 'ajouter-membre' },
    { url: '/member-list', nom: 'membres' },
    { url: '/all-issed-book', nom: 'livres-emis' },
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
