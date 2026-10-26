// cypress/e2e/parent/frais/consultation.cy.js
// Rôle Parent — rubrique Frais : consultation des frais de l'enfant (lecture seule).
// Aucun paiement réel déclenché.

import { parentSetup } from '../_support';

describe('Parent · Frais · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/frais/consultation';

  it('affiche les frais scolaires de l\'enfant', () => {
    cy.visit(`/parent-fees/${ctx.childId}`);
    cy.url().should('match', /\/parent-fees\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-frais`);
  });
});
