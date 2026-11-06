// cypress/e2e/parent/tableau_affichage/consultation.cy.js
// Rôle Parent — rubrique Tableau d'affichage.

import { parentSetup } from '../_support';

describe('Parent · Tableau d\'affichage · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/tableau_affichage/consultation';

  it('affiche le tableau d\'affichage du parent', () => {
    cy.visit('/parent-noticeboard');
    cy.url().should('include', '/parent-noticeboard');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box, .notice').should('exist');
    // cy.screenshot(`${shot}/01-annonces`);
  });
});
