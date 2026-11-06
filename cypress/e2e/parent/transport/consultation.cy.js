// cypress/e2e/parent/transport/consultation.cy.js
// Rôle Parent — rubrique Transport de l'enfant.

import { parentSetup } from '../_support';

describe('Parent · Transport · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/transport/consultation';

  it('affiche les informations de transport de l\'enfant', () => {
    cy.visit(`/parent-transport/${ctx.childId}`);
    cy.url().should('match', /\/parent-transport\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-transport`);
  });
});
