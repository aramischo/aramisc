// cypress/e2e/parent/examen/consultation.cy.js
// Rôle Parent — rubrique Examen : résultats d'examen de l'enfant (lecture seule).

import { parentSetup } from '../_support';

describe('Parent · Examen · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/examen/consultation';

  it('affiche les résultats d\'examen de l\'enfant', () => {
    cy.visit(`/parent-examination/${ctx.childId}`);
    cy.url().should('match', /\/parent-examination\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-resultats-examen`);
  });
});
