// cypress/e2e/admin/plan_de_cours/helpers.js
//
// Fonctions partagées par les specs de la rubrique « Plan de cours ».
// (Ce fichier n'est pas un spec : il ne finit pas par .cy.js)
//
// Toutes les données créées ici sont supprimées par les fonctions delete*/cleanup* (base de test).

import { php } from '../portefeuille/helpers';

/**
 * La création d'un plan de leçon notifie enseignant, élèves et parents (événement Lesson_Plan :
 * Email + SMS + Web + App). On coupe Email/SMS/App le temps du spec (vrais e-mails / push sinon)
 * et on mémorise l'état pour le rétablir avec restoreLessonPlanNotifications().
 */
export const muteLessonPlanNotifications = () =>
  php(`
    $dest = DB::table('aramisc_notification_settings')->where('event', 'Lesson_Plan')->pluck('destination', 'id');
    foreach ($dest as $id => $d) {
      $x = json_decode($d, true);
      $x['Email'] = 0; $x['SMS'] = 0; $x['App'] = 0;
      DB::table('aramisc_notification_settings')->where('id', $id)->update(['destination' => json_encode($x)]);
    }
    echo 'CY<<'.base64_encode(json_encode(['dest' => $dest, 'maxNotif' => (int) DB::table('aramisc_notifications')->max('id')])).'>>CY';
  `);

/** Rétablit les réglages Lesson_Plan et supprime les notifications Web créées pendant le spec. */
export const restoreLessonPlanNotifications = (backup) => {
  if (!backup) return;
  const b64 = Cypress.Buffer.from(JSON.stringify(backup)).toString('base64');
  php(`
    $b = json_decode(base64_decode('${b64}'), true);
    foreach ($b['dest'] as $id => $d) {
      DB::table('aramisc_notification_settings')->where('id', $id)->update(['destination' => $d]);
    }
    DB::table('aramisc_notifications')->where('id', '>', $b['maxNotif'])->delete();
  `);
};

/** Sélectionne une option d'un <select> (habillé nice-select) par sa valeur. */
export const selectValue = (selector, value) => {
  cy.get(selector).select(String(value), { force: true });
};

/** Attend que le <select> ait au moins une option réelle (chargement AJAX) puis choisit la 1re ou celle demandée. */
export const selectAjax = (selector, value) => {
  cy.get(`${selector} option`, { timeout: 20000 }).should('have.length.greaterThan', 1);
  if (value !== undefined) {
    cy.get(`${selector} option[value="${value}"]`, { timeout: 20000 }).should('exist');
    return selectValue(selector, value);
  }
  return cy.get(`${selector} option`).eq(1).then(($o) => selectValue(selector, $o.val()));
};

/**
 * Crée une leçon (Plan de cours > Leçon).
 * Si classId/subjectId ne sont pas fournis, prend la 1re classe et le 1er sujet disponibles.
 * Renvoie (via cy.wrap) { classId, subjectId }.
 */
export const createLesson = (title, classId, subjectId) => {
  cy.visit('/lesson');
  if (classId) {
    selectValue('#select_class_lesson', classId);
  } else {
    cy.get('#select_class_lesson option').eq(1).then(($o) => selectValue('#select_class_lesson', $o.val()));
  }
  selectAjax('#select_subject', subjectId);
  cy.get('form[action$="/lesson/lesson"] input[name="lesson[]"]').first().clear().type(title);
  cy.get('form[action$="/lesson/lesson"]').contains('button', /sauver|save/i).click();
  cy.get('#table_id').contains('tr', title).should('exist');
  return cy.get('#select_class_lesson').invoke('val').then(() =>
    cy.get('#table_id').contains('tr', title).find('a[href*="lesson-edit/"]').invoke('attr', 'href').then((href) => {
      const [, cls, , sub] = href.match(/lesson-edit\/(\d+)\/(\d+)\/(\d+)/);
      return { classId: cls, subjectId: sub, editHref: href };
    })
  );
};

/** Supprime UNE leçon (icône corbeille de la page d'édition → ne touche pas aux autres leçons du groupe). */
export const deleteLesson = (title) => {
  cy.visit('/lesson');
  cy.get('#table_id').contains('tr', title).find('a[href*="lesson-edit/"]').invoke('attr', 'href').then((href) => {
    cy.visit(href);
  });
  cy.get(`input[name="lesson[]"]`).filter((i, el) => el.value === title)
    .closest('tr')
    .find('a[href*="lesson-item-delete"]')
    .click({ force: true }); // le confirm() est accepté automatiquement par Cypress
  cy.url().should('match', /\/lesson\/?$/);
  cy.get('#table_id').should('not.contain', title);
};

/** Crée un sujet (topic) rattaché à une leçon existante. */
export const createTopic = (topicTitle, lessonTitle, classId, subjectId) => {
  cy.visit('/lesson/topic');
  selectValue('#select_class', classId);
  selectAjax('#select_section');
  selectAjax('#select_subject', subjectId);
  cy.get('#lesson_from_subject option', { timeout: 20000 }).contains(lessonTitle).then(($o) => {
    selectValue('#lesson_from_subject', $o.val());
  });
  cy.get('form[action$="/lesson/topic"] input[name="topic[]"]').first().clear().type(topicTitle);
  cy.get('form[action$="/lesson/topic"]').contains('button', /sauver|save/i).click();
  cy.get('#table_id', { timeout: 20000 }).contains('tr', topicTitle).should('exist');
};

/** Supprime le sujet (groupe de topics) de la ligne qui contient topicTitle. */
export const deleteTopic = (topicTitle) => {
  cy.visit('/lesson/topic');
  cy.get('#table_id', { timeout: 20000 }).contains('tr', topicTitle).find('a[onclick^="deleteTopic"]').click({ force: true });
  cy.get('#deleteTopicModal').should('be.visible').find('button[type="submit"]').click();
  cy.get('#table_id', { timeout: 20000 }).should('not.contain', topicTitle);
};

/**
 * Trouve un enseignant qui a un emploi du temps (au moins un bouton « + » d'ajout de plan de leçon).
 * Renvoie (via cy.wrap) l'id de l'enseignant.
 */
export const findTeacherWithRoutine = () => {
  cy.visit('/lesson/lesson-plan');
  return cy.get('select[name="teacher"] option').then(($opts) => {
    const ids = [...$opts].map((o) => o.value).filter(Boolean);
    const tryNext = (i) => {
      if (i >= ids.length) {
        throw new Error("Aucun enseignant n'a d'emploi du temps : impossible de tester le plan de cours.");
      }
      return cy.request(`/lesson/lesson-plan/${ids[i]}`).then((res) =>
        res.body.includes('/lesson-plan-lesson/') ? ids[i] : tryNext(i + 1)
      );
    };
    return tryNext(0);
  });
};

/**
 * Ouvre la modale « Ajouter un plan de leçon » du 1er créneau de l'enseignant et renvoie
 * { addHref, classId, sectionId, subjectId } lus dans les champs cachés, puis ferme la modale.
 */
export const readFirstSlot = (teacherId) => {
  const slot = {};
  cy.visit(`/lesson/lesson-plan/${teacherId}`);
  cy.get('a.modalLink[href*="/lesson-plan-lesson/"]').first().then(($a) => {
    slot.addHref = $a.attr('href');
    cy.wrap($a).click({ force: true });
  });
  cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible');
  cy.get('#showDetaildModal input#class_id').invoke('val').then((v) => { slot.classId = v; });
  cy.get('#showDetaildModal input#section_id').invoke('val').then((v) => { slot.sectionId = v; });
  cy.get('#showDetaildModal input#subject_id').invoke('val').then((v) => { slot.subjectId = v; });
  cy.get('#showDetaildModal').contains('button', /annuler|cancel/i).click({ force: true });
  // cy.wrap est mis en file après les commandes ci-dessus : slot est rempli quand il est transmis
  return cy.wrap(slot);
};

/** Liste des liens « voir » des plans de leçon déjà affichés pour l'enseignant. */
export const viewLinks = (teacherId) => {
  cy.visit(`/lesson/lesson-plan/${teacherId}`);
  return cy.get('body').then(($b) =>
    [...$b.find('a[href*="view-lesson-plan-lesson/"]')].map((a) => a.getAttribute('href'))
  );
};

/** Crée un plan de leçon sur le créneau addHref et renvoie son id. */
export const createLessonPlan = ({ teacherId, addHref, lessonTitle, topicTitle, subTopic, note }) =>
  viewLinks(teacherId).then((before) => {
    cy.get(`a.modalLink[href="${addHref}"]`).click({ force: true });
    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible').within(() => {
      cy.get('#lessonTopic option').contains(lessonTitle).then(($o) => selectValue('#lessonTopic', $o.val()));
      cy.get('#select_topic option', { timeout: 20000 }).contains(topicTitle).then(($o) => selectValue('#select_topic', $o.val()));
      cy.get('input[name="sub_topic[]"]').first().type(subTopic);
      cy.get('textarea[name="note"]').type(note);
      cy.contains('button', /enregistrer|save/i).click();
    });
    return viewLinks(teacherId).then((after) => {
      const created = after.find((h) => !before.includes(h));
      expect(created, 'lien du plan de leçon créé').to.exist;
      return created.match(/view-lesson-plan-lesson\/(\d+)/)[1];
    });
  });

/** Supprime un plan de leçon via sa modale de suppression. */
export const deleteLessonPlan = (teacherId, planId) => {
  cy.visit(`/lesson/lesson-plan/${teacherId}`);
  cy.get(`a[href$="delete-lesson-plan-lesson/${planId}"]`).click({ force: true });
  cy.get('#showDetaildModal', { timeout: 20000 })
    .should('be.visible')
    .find(`a[href$="delete-lesson-plan/${planId}"] button`)
    .click();
  cy.get(`a[href$="view-lesson-plan-lesson/${planId}"]`).should('not.exist');
};
