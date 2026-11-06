// cypress/e2e/parent/matieres/consultation.cy.js
// Rôle Parent — rubrique Matières de l'enfant.

import { parentSetup } from '../_support';

describe('Parent · Matières · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/matieres/consultation';

  it('affiche les matières de l\'enfant', () => {
    cy.visit(`/parent-subjects/${ctx.childId}`);
    cy.url().should('match', /\/parent-subjects\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-matieres`);
  });
});
