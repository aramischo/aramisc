// cypress/e2e/parent/planning_examen/consultation.cy.js
// Rôle Parent — rubrique Planning d'examen de l'enfant : consultation + recherche.

import { parentSetup } from '../_support';

describe('Parent · Planning d\'examen · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/planning_examen/consultation';

  it('affiche le planning d\'examen de l\'enfant', () => {
    cy.visit(`/parent-examination-schedule/${ctx.childId}`);
    cy.url().should('match', /\/parent-examination-schedule\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box, form').should('exist');
    // cy.screenshot(`${shot}/01-planning`);
  });
});
