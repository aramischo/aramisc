// cypress/e2e/parent/bibliotheque/consultation.cy.js
// Rôle Parent — rubrique Bibliothèque : liste des livres + livres empruntés par l'enfant.

import { parentSetup } from '../_support';

describe('Parent · Bibliothèque · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/bibliotheque/consultation';

  it('affiche la liste des livres de la bibliothèque', () => {
    cy.visit('/parent-library');
    cy.url().should('include', '/parent-library');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-liste-livres`);
  });

  it('accède aux livres empruntés (ou redirection si non-membre)', () => {
    cy.visit('/parent-book-issue');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/02-livres-empruntes`);
  });
});
