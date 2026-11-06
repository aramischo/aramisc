// cypress/e2e/parent/dortoir/consultation.cy.js
// Rôle Parent — rubrique Dortoir de l'enfant.

import { parentSetup } from '../_support';

describe('Parent · Dortoir · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/dortoir/consultation';

  it('affiche les informations de dortoir de l\'enfant', () => {
    cy.visit(`/parent-dormitory/${ctx.childId}`);
    cy.url().should('match', /\/parent-dormitory\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-dortoir`);
  });
});
