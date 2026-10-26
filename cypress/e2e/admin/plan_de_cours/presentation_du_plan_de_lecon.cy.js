// cypress/e2e/admin/plan_de_cours/presentation_du_plan_de_lecon.cy.js
//
// Page de consultation (pas de CRUD propre) : on crée un plan de leçon, on vérifie la présentation, on nettoie.
// Scénario utilisateur (validé manuellement) :
//  0. Préparation : leçon + sujet + plan de leçon sur le 1er créneau d'un enseignant
//  1. Plan de cours > Présentation du plan de leçon : enseignant, classe, section, sujet → Rechercher
//     → la leçon et le sujet planifiés apparaissent
//  2. Nettoyage : suppression du plan, du sujet et de la leçon
// Captures d'écran : en commentaire pour l'instant.

import {
  selectValue, selectAjax,
  createLesson, createTopic, deleteTopic, deleteLesson,
  findTeacherWithRoutine, readFirstSlot, createLessonPlan, deleteLessonPlan,
  muteLessonPlanNotifications, restoreLessonPlanNotifications,
} from './helpers';

describe('Admin – Plan de cours : Présentation du plan de leçon', () => {
  const shot = 'admin/plan_de_cours/presentation_du_plan_de_lecon';
  const stamp = Date.now();
  const lessonTitle = `Lecon_Pres_${stamp}`;
  const topic = `Sujet_Pres_${stamp}`;

  // Notifications Lesson_Plan : Email/SMS/App coupés pendant le spec (voir helpers.js)
  let notifBackup;
  before(() => {
    muteLessonPlanNotifications().then((b) => {
      notifBackup = b;
    });
  });
  after(() => {
    restoreLessonPlanNotifications(notifBackup);
  });

  beforeEach(() => {
    cy.loginAsAdmin();
  });

  it('retrouve un plan de leçon via les filtres de la présentation', () => {
    findTeacherWithRoutine().then((teacherId) => {
      readFirstSlot(teacherId).then(({ addHref, classId, sectionId, subjectId }) => {
        createLesson(lessonTitle, classId, subjectId);
        createTopic(topic, lessonTitle, classId, subjectId);
        createLessonPlan({ teacherId, addHref, lessonTitle, topicTitle: topic, subTopic: 'Sous-sujet', note: 'Note' })
          .then((planId) => {
            // ─────────────── 1. RECHERCHE ───────────────
            cy.visit('/lesson/lessonPlan-overiew');
            selectValue('form[action*="lessonPlan-overiew"] select[name="teacher"]', teacherId);
            selectValue('#common_select_class', classId);
            selectAjax('#common_select_section', sectionId);
            selectAjax('#common_select_subject', subjectId);
            // cy.screenshot(`${shot}/01-recherche-formulaire`);
            cy.get('form[action*="lessonPlan-overiew"] button').last().click();
            // cy.screenshot(`${shot}/02-recherche-apres-submit`);
            cy.get('table').should('contain', lessonTitle).and('contain', topic);

            // ─────────────── 2. NETTOYAGE ───────────────
            deleteLessonPlan(teacherId, planId);
          });
        deleteTopic(topic);
        deleteLesson(lessonTitle);
      });
    });
  });
});
