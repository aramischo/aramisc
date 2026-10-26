// cypress/e2e/admin/plan_de_cours/plan_de_cours.cy.js
//
// Scénario utilisateur (validé manuellement) :
//  0. Préparation : on cherche un enseignant qui a un emploi du temps, on lit la classe/section/sujet
//     de son 1er créneau, puis on crée une leçon et un sujet pour ce couple classe/sujet
//  1. Plan de cours > Plan de cours : enseignant → Rechercher → bouton « + » d'un créneau →
//     modale : leçon, sujet, sous-sujet, note → « Enregistrer » → le plan apparaît dans le créneau
//  2. Icône « voir » : la modale affiche leçon, sujet, sous-sujet et note
//  3. Icône « modifier » : sous-sujet et note modifiés → « Mettre à jour » → la vue reflète les changements
//  4. Icône « supprimer » → confirmation → le plan disparaît
//  5. Nettoyage : suppression du sujet et de la leçon
// Captures d'écran : en commentaire pour l'instant.

import {
  createLesson, createTopic, deleteTopic, deleteLesson,
  findTeacherWithRoutine, readFirstSlot, createLessonPlan, deleteLessonPlan,
  muteLessonPlanNotifications, restoreLessonPlanNotifications,
} from './helpers';

describe('Admin – Plan de cours : Plan de cours (CRUD)', () => {
  const shot = 'admin/plan_de_cours/plan_de_cours';
  const stamp = Date.now();
  const lessonTitle = `Lecon_Plan_${stamp}`;
  const topic = `Sujet_Plan_${stamp}`;
  const subTopic = `SousSujet_${stamp}`;
  const note = `Note_${stamp}`;

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

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un plan de leçon", () => {
    findTeacherWithRoutine().then((teacherId) => {
      readFirstSlot(teacherId).then(({ addHref, classId, subjectId }) => {
        createLesson(lessonTitle, classId, subjectId);
        createTopic(topic, lessonTitle, classId, subjectId);

        // ─────────────── 1. CREATE ───────────────
        // (recherche par enseignant faite par l'URL /lesson/lesson-plan/{teacher} = résultat du formulaire « Rechercher »)
        createLessonPlan({ teacherId, addHref, lessonTitle, topicTitle: topic, subTopic, note }).then((planId) => {
          // cy.screenshot(`${shot}/01-create-apres-submit`);

          // ─────────────── 2. READ ───────────────
          cy.get(`a[href$="view-lesson-plan-lesson/${planId}"]`).click({ force: true });
          cy.get('#showDetaildModal', { timeout: 20000 })
            .should('be.visible')
            .and('contain', lessonTitle)
            .and('contain', topic)
            .and('contain', subTopic)
            .and('contain', note);
          // cy.screenshot(`${shot}/02-read-detail`);

          // ─────────────── 3. UPDATE ───────────────
          cy.visit(`/lesson/lesson-plan/${teacherId}`);
          cy.get(`a[href$="edit-lesson-plan-lesson/${planId}"]`).click({ force: true });
          cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible').within(() => {
            cy.get('input[name="sub_topic[]"]').first().should('have.value', subTopic).clear().type(`${subTopic}_Updated`);
            cy.get('textarea[name="note"]').should('have.value', note).clear().type(`${note}_Updated`);
            // cy.screenshot(`${shot}/03-update-formulaire`);
            cy.contains('button', /mettre à jour|update/i).click();
          });
          // cy.screenshot(`${shot}/04-update-apres-submit`);
          cy.visit(`/lesson/lesson-plan/${teacherId}`);
          cy.get(`a[href$="view-lesson-plan-lesson/${planId}"]`).click({ force: true });
          cy.get('#showDetaildModal', { timeout: 20000 })
            .should('contain', `${subTopic}_Updated`)
            .and('contain', `${note}_Updated`);

          // ─────────────── 4. DELETE ───────────────
          deleteLessonPlan(teacherId, planId);
          // cy.screenshot(`${shot}/05-delete-apres-confirmation`);
        });

        // ─────────────── 5. NETTOYAGE ───────────────
        deleteTopic(topic);
        deleteLesson(lessonTitle);
      });
    });
  });
});
