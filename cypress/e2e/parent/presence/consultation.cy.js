// cypress/e2e/parent/presence/consultation.cy.js
// Rôle Parent — rubrique Présence de l'enfant : consultation + recherche mensuelle.

import { parentSetup } from '../_support';

describe('Parent · Présence · consultation & recherche', () => {
  const ctx = parentSetup();
  const shot = 'parent/presence/consultation';

  it('affiche le formulaire de présence de l\'enfant', () => {
    cy.visit(`/parent-attendance/${ctx.childId}`);
    cy.url().should('match', /\/parent-attendance\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('select[name="month"]').should('exist');
    cy.get('select[name="year"]').should('exist');
    cy.get('button[type="submit"]').should('exist');
    // cy.screenshot(`${shot}/01-formulaire`);
  });

  it('recherche la présence d\'un mois et renvoie une page valide', () => {
    cy.visit(`/parent-attendance/${ctx.childId}`);

    // Choix d'une année réellement proposée par le <select> (la liste varie selon la config).
    cy.get('select[name="year"] option')
      .then(($o) => [...$o].map((o) => o.value).filter((v) => v && v !== '')[0])
      .then((year) => {
        cy.get('select[name="month"]').select('09', { force: true });
        cy.get('select[name="year"]').select(year, { force: true });
      });
    // cy.screenshot(`${shot}/02-criteres`);
    cy.get('button[type="submit"]').first().click({ force: true });

    // Page authentifiée 200 avec le formulaire de recherche toujours présent. Le rapport
    // mensuel reste vide tant qu'aucune présence n'est saisie pour l'enfant.
    cy.url().should('not.include', '/login');
    cy.get('select[name="month"]').should('exist');
    cy.get('select[name="year"]').should('exist');
    // cy.screenshot(`${shot}/03-resultat`);
  });
});
