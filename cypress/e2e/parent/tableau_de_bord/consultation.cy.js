// cypress/e2e/parent/tableau_de_bord/consultation.cy.js
// Rôle Parent — rubrique Tableau de bord.

import { parentSetup } from '../_support';

describe('Parent · Tableau de bord · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/tableau_de_bord/consultation';

  it('affiche le tableau de bord du parent', () => {
    cy.visit('/parent-dashboard');
    cy.url().should('include', '/parent-dashboard');
    cy.url().should('not.include', '/login');
    cy.get('.card, .white-box, [class*="widget"], h2, h3').should('exist');
    // cy.screenshot(`${shot}/01-tableau-de-bord`);
  });
});
