// cypress/e2e/parent/mes_enfants/consultation.cy.js
// Rôle Parent — rubrique Mes enfants : consultation de la fiche de l'enfant.
// La mise à jour (my-children-update) réutilise le formulaire d'admission complet
// (destructeur, déjà couvert côté admin) → non testé ici.

import { parentSetup } from '../_support';

describe('Parent · Mes enfants · consultation', () => {
  const ctx = parentSetup();
  const shot = 'parent/mes_enfants/consultation';

  it('affiche la fiche de l\'enfant', () => {
    cy.visit(`/my-children/${ctx.childId}`);
    cy.url().should('match', /\/my-children\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box, .single-info-details').should('exist');
    // cy.screenshot(`${shot}/01-fiche-enfant`);
  });
});
