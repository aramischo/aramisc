// cypress/e2e/parent/devoirs/consultation.cy.js
// Rôle Parent — rubrique Devoirs de l'enfant.

import { parentSetup } from '../_support';

describe('Parent · Devoirs · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/devoirs/consultation';

  it('affiche les devoirs de l\'enfant', () => {
    cy.visit(`/parent-homework/${ctx.childId}`);
    cy.url().should('match', /\/parent-homework\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-devoirs`);
  });
});
