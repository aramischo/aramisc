// cypress/e2e/admin/plan_de_cours/lecon.cy.js
//
// Scénario utilisateur (validé manuellement) :
//  1. Plan de cours > Leçon : classe, sujet (chargé en AJAX), nom de la leçon → « Sauver la leçon »
//     → la leçon apparaît dans la liste (Classe / Section / Sujet / Leçon)
//  2. Sélectionner > Modifier : la page d'édition est pré-remplie → nouveau nom → « Mettre à jour »
//  3. Suppression de la leçon (icône corbeille de la page d'édition, confirmation)
//
// ⚠ Une ligne de la liste regroupe toutes les leçons de la même classe/section/sujet : son bouton « Supprimer »
//   les supprime toutes (AramiscLessonController::deleteLesson). Le test utilise donc la suppression unitaire
//   pour ne pas toucher aux autres données de la base de test.
// Captures d'écran : en commentaire pour l'instant.

import { selectValue, selectAjax } from './helpers';

describe('Admin – Plan de cours : Leçon (CRUD)', () => {
  const shot = 'admin/plan_de_cours/lecon';
  const title = `Lecon_${Date.now()}`;
  const updated = `${title}_Updated`;

  beforeEach(() => {
    cy.loginAsAdmin();
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'une leçon", () => {
    // ─────────────── 1. CREATE ───────────────
    cy.visit('/lesson');
    cy.get('#select_class_lesson option').eq(1).then(($o) => selectValue('#select_class_lesson', $o.val()));
    selectAjax('#select_subject');
    cy.get('form[action$="/lesson/lesson"] input[name="lesson[]"]').first().type(title);
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('form[action$="/lesson/lesson"]').contains('button', /sauver|save/i).click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────── 2. READ ───────────────
    cy.url().should('match', /\/lesson\/?$/);
    cy.get('#table_id').contains('tr', title).should('exist');

    // ─────────────── 3. UPDATE ───────────────
    cy.get('#table_id').contains('tr', title).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[href*="lesson-edit/"]').click({ force: true });
    });
    cy.url().should('include', '/lesson/lesson-edit/');
    cy.get('input[name="lesson[]"]').filter((i, el) => el.value === title).should('have.length', 1)
      .clear().type(updated);
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('form[action$="/lesson/lesson-update"]').contains('button', /mettre à jour|update/i).click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);
    cy.url().should('match', /\/lesson\/?$/);
    cy.get('#table_id').should('contain', updated);

    // ─────────────── 4. DELETE (unitaire) ───────────────
    cy.get('#table_id').contains('tr', updated).find('a[href*="lesson-edit/"]').invoke('attr', 'href').then((href) => {
      cy.visit(href);
    });
    cy.get('input[name="lesson[]"]').filter((i, el) => el.value === updated)
      .closest('tr')
      .find('a[href*="lesson-item-delete"]')
      .click({ force: true });
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);
    cy.url().should('match', /\/lesson\/?$/);
    cy.get('#table_id').should('not.contain', updated);
  });
});
