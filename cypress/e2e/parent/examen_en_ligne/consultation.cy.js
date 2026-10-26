// cypress/e2e/parent/examen_en_ligne/consultation.cy.js
// Rôle Parent — rubrique Examen en ligne de l'enfant : liste + résultats (lecture seule).

import { parentSetup } from '../_support';

describe('Parent · Examen en ligne · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/examen_en_ligne/consultation';

  it('affiche les examens en ligne de l\'enfant', () => {
    cy.visit(`/parent-online-examination/${ctx.childId}`);
    cy.url().should('match', /\/parent-online-examination\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-liste-examens`);
  });

  it('affiche les résultats des examens en ligne de l\'enfant', () => {
    cy.visit(`/parent-online-examination-result/${ctx.childId}`);
    cy.url().should('match', /\/parent-online-examination-result\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/02-resultats`);
  });
});
