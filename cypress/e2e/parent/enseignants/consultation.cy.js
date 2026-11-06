// cypress/e2e/parent/enseignants/consultation.cy.js
// Rôle Parent — rubrique Enseignants de l'enfant.

import { parentSetup } from '../_support';

describe('Parent · Enseignants · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/enseignants/consultation';

  it('affiche la liste des enseignants de l\'enfant', () => {
    cy.visit(`/parent-teacher-list/${ctx.childId}`);
    cy.url().should('match', /\/parent-teacher-list\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-enseignants`);
  });
});
