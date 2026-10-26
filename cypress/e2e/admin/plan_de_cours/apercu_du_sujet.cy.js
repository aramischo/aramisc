// cypress/e2e/admin/plan_de_cours/apercu_du_sujet.cy.js
//
// Page de consultation (pas de CRUD propre) : on crée les données, on vérifie l'affichage, on nettoie.
// Scénario utilisateur (validé manuellement) :
//  0. Préparation : une leçon + un sujet
//  1. Plan de cours > Aperçu du sujet : la liste affiche la leçon et son sujet
//  2. Filtre classe → section → sujet → « Rechercher » → la leçon et le sujet sont retrouvés
//  3. Nettoyage : suppression du sujet puis de la leçon
// Captures d'écran : en commentaire pour l'instant.

import { selectValue, selectAjax, createLesson, createTopic, deleteTopic, deleteLesson } from './helpers';

describe('Admin – Plan de cours : Aperçu du sujet', () => {
  const shot = 'admin/plan_de_cours/apercu_du_sujet';
  const stamp = Date.now();
  const lessonTitle = `Lecon_Apercu_${stamp}`;
  const topic = `Sujet_Apercu_${stamp}`;

  beforeEach(() => {
    cy.loginAsAdmin();
  });

  it('affiche et filtre la progression des sujets', () => {
    createLesson(lessonTitle).then(({ classId, subjectId }) => {
      createTopic(topic, lessonTitle, classId, subjectId);

      // ─────────────── 1. LECTURE ───────────────
      cy.visit('/lesson/topic-overview');
      cy.get('table').should('contain', lessonTitle).and('contain', topic);
      // cy.screenshot(`${shot}/01-liste`);

      // ─────────────── 2. RECHERCHE ───────────────
      selectValue('#select_class', classId);
      selectAjax('#select_section');
      selectAjax('#select_subject', subjectId);
      // cy.screenshot(`${shot}/02-recherche-formulaire`);
      cy.get('form[action*="topic-overview"] button').last().click();
      // cy.screenshot(`${shot}/03-recherche-apres-submit`);
      cy.get('table').should('contain', lessonTitle).and('contain', topic);

      // ─────────────── 3. NETTOYAGE ───────────────
      deleteTopic(topic);
      deleteLesson(lessonTitle);
    });
  });
});
