// cypress/e2e/parent/emploi_du_temps/consultation.cy.js
// Rôle Parent — rubrique Emploi du temps de l'enfant.

import { parentSetup } from '../_support';

describe('Parent · Emploi du temps · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/emploi_du_temps/consultation';

  it('affiche l\'emploi du temps de l\'enfant', () => {
    cy.visit(`/parent-class-routine/${ctx.childId}`);
    cy.url().should('match', /\/parent-class-routine\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-emploi-du-temps`);
  });
});
