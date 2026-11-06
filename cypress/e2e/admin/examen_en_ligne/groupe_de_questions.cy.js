// cypress/e2e/admin/examen_en_ligne/groupe_de_questions.cy.js
//
// Scénario utilisateur :
//  1. Examen en ligne > Groupe de questions : titre → « Groupe de sauvegarde » → le groupe apparaît dans la liste
//  2. Sélectionner > Modifier : formulaire pré-rempli → nouveau titre → « Mettre à jour le groupe »
//  3. Sélectionner > Supprimer → modale de confirmation → le groupe disparaît
// Aucune notification n'est envoyée par ce contrôleur.
// Captures d'écran : en commentaire pour l'instant.

import { php } from '../portefeuille/helpers';

describe('Admin – Examen en ligne : Groupe de questions (CRUD)', () => {
  const shot = 'admin/examen_en_ligne/groupe_de_questions';
  const stamp = Date.now();
  const title = `Groupe_${stamp}`;
  const updated = `${title}_Updated`;

  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // Filet de sécurité : supprime le groupe s'il existe encore (test interrompu)
  after(() => {
    php(`DB::table('aramisc_question_groups')->whereIn('title', ['${title}', '${updated}'])->delete();`);
  });

  /** Ouvre le menu « Sélectionner » de la ligne et clique sur l'action voulue. */
  const rowAction = (text, selector) => {
    cy.get('#table_id', { timeout: 20000 }).contains('tr', text).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get(selector).click({ force: true });
    });
  };

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un groupe de questions", () => {
    // ─────────────── 1. CREATE ───────────────
    cy.visit('/question-group');
    cy.get('form[action$="/question-group"] input[name="title"]').type(title);
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('form[action$="/question-group"] button.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────── 2. READ ───────────────
    cy.get('#table_id', { timeout: 20000 }).contains('td', title).should('exist');

    // ─────────────── 3. UPDATE ───────────────
    rowAction(title, 'a.dropdown-item[href*="/question-group/"]');
    cy.url().should('match', /\/question-group\/\d+$/);
    cy.get('input[name="title"]').should('have.value', title).clear().type(updated);
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('form[action*="/question-group/"] button.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);
    cy.url().should('match', /\/question-group$/);
    cy.get('#table_id', { timeout: 20000 }).contains('td', updated).should('exist');

    // ─────────────── 4. DELETE ───────────────
    rowAction(updated, 'a.dropdown-item[data-target^="#deleteQuestionGroupModal"]');
    cy.get('.modal.show', { timeout: 10000 }).find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);
    cy.url().should('match', /\/question-group$/);
    cy.get('#table_id', { timeout: 20000 }).should('not.contain', updated);
  });
});
