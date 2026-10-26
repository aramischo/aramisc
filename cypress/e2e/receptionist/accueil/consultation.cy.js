// cypress/e2e/receptionist/accueil/consultation.cy.js
// Rôle Receptionist — Front office : visiteurs, plaintes, journal des appels.

describe('Receptionist · Accueil · consultation', () => {
  const shot = 'receptionist/accueil/consultation';
  beforeEach(() => cy.loginAsReceptionist());

  const pages = [
    { url: '/visitor', nom: 'visiteurs' },
    { url: '/complaint', nom: 'plaintes' },
    { url: '/phone-call', nom: 'appels-telephoniques' },
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
