// cypress/e2e/admin/plan_de_cours/sujet.cy.js
//
// Scénario utilisateur (validé manuellement) :
//  0. Préparation : création d'une leçon
//  1. Plan de cours > Sujet : classe → section → sujet → leçon (chargements AJAX en cascade), titre → « Sauver le sujet »
//     → le sujet apparaît dans la liste
//  2. Sélectionner > Modifier : formulaire pré-rempli → nouveau titre → « Mettre à jour le sujet »
//  3. Sélectionner > Supprimer → modale de confirmation → le sujet disparaît
//  4. Nettoyage : suppression de la leçon (base de test)
// Captures d'écran : en commentaire pour l'instant.

import { selectValue, selectAjax, createLesson, deleteLesson } from './helpers';

describe('Admin – Plan de cours : Sujet / Topic (CRUD)', () => {
  const shot = 'admin/plan_de_cours/sujet';
  const stamp = Date.now();
  const lessonTitle = `Lecon_Topic_${stamp}`;
  const topic = `Sujet_${stamp}`;
  const updated = `${topic}_Updated`;

  beforeEach(() => {
    cy.loginAsAdmin();
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un sujet", () => {
    createLesson(lessonTitle).then(({ classId, subjectId }) => {
      // ─────────────── 1. CREATE ───────────────
      cy.visit('/lesson/topic');
      selectValue('#select_class', classId);
      selectAjax('#select_section');
      selectAjax('#select_subject', subjectId);
      cy.get('#lesson_from_subject option', { timeout: 20000 }).contains(lessonTitle).then(($o) => {
        selectValue('#lesson_from_subject', $o.val());
      });
      cy.get('form[action$="/lesson/topic"] input[name="topic[]"]').first().type(topic);
      // cy.screenshot(`${shot}/01-create-formulaire`);
      cy.get('form[action$="/lesson/topic"]').contains('button', /sauver|save/i).click();
      // cy.screenshot(`${shot}/02-create-apres-submit`);

      // ─────────────── 2. READ ───────────────
      cy.get('#table_id', { timeout: 20000 }).contains('tr', topic).should('contain', lessonTitle);

      // ─────────────── 3. UPDATE ───────────────
      cy.get('#table_id').contains('tr', topic).within(() => {
        cy.get('.dropdown-toggle').click({ force: true });
        cy.get('a.dropdown-item[href*="topic-edit/"]').click({ force: true });
      });
      cy.url().should('include', '/lesson/topic-edit/');
      cy.get('input[name="topic[]"]').filter((i, el) => el.value === topic).should('have.length', 1)
        .clear().type(updated);
      // cy.screenshot(`${shot}/03-update-formulaire`);
      cy.get('form[action*="topic-update"]').contains('button', /mettre à jour|update/i).click();
      // cy.screenshot(`${shot}/04-update-apres-submit`);
      cy.url().should('include', '/lesson/topic');
      cy.get('#table_id', { timeout: 20000 }).contains('tr', updated).should('exist');

      // ─────────────── 4. DELETE ───────────────
      cy.get('#table_id').contains('tr', updated).find('a[onclick^="deleteTopic"]').click({ force: true });
      cy.get('#deleteTopicModal').should('be.visible').find('button[type="submit"]').click();
      // cy.screenshot(`${shot}/05-delete-apres-confirmation`);
      cy.get('#table_id', { timeout: 20000 }).should('not.contain', updated);

      // ─────────────── 5. NETTOYAGE ───────────────
      deleteLesson(lessonTitle);
    });
  });
});
