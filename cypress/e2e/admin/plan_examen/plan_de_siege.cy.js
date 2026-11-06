// cypress/e2e/admin/plan_examen/plan_de_siege.cy.js
//
// Scénario utilisateur :
//  0. Préparation (tinker) : la base de test n'a aucun type d'examen → création d'un examen temporaire ;
//     coupure Email/SMS/App de la notification Exam_Seat_Plan (sinon e-mails / SMS / push réels).
//  1. Plan d'examen > Plan de siège : « Recherche » sans critère → erreurs de validation
//  2. Examen → classe → section (AJAX) → « Recherche » → liste des élèves de la section
//  3. Cocher 2 élèves → « Générer le plan de siège » → aperçu d'impression avec leurs places ;
//     2 lignes créées dans seat_plans
//  4. Nouvelle recherche : les 2 élèves qui ont déjà une place sont pré-cochés
//  5. Nettoyage (after) : places, notifications Web créées, examen temporaire ; réglages de notification rétablis
//
// Le formulaire « Générer » s'ouvre normalement dans un nouvel onglet (target=_blank) : retiré pour rester dans Cypress.
// Captures d'écran : en commentaire pour l'instant.

import { php } from '../portefeuille/helpers';

const CLASS_ID = '2'; // Class2 (5 élèves)
const SECTION_ID = '2';
const EVENT = 'Exam_Seat_Plan';

describe("Admin – Plan d'examen : Plan de siège", () => {
  const shot = 'admin/plan_examen/plan_de_siege';
  const examTitle = `Examen_CY_${Date.now()}`;
  const searchForm = 'form[action$="/examplan/seatplan/search"]';
  const generateForm = 'form[action$="/examplan/seatplan/generate"]';
  let backup;

  before(() => {
    php(`
      $setting = DB::table('aramisc_notification_settings')->where('event', '${EVENT}')->first();
      $examId = DB::table('aramisc_exam_types')->insertGetId([
        'title' => '${examTitle}', 'active_status' => 1, 'school_id' => 1, 'academic_id' => 1,
        'created_at' => now(), 'updated_at' => now(),
      ]);
      $x = json_decode($setting->destination, true);
      $x['Email'] = 0; $x['SMS'] = 0; $x['App'] = 0;
      DB::table('aramisc_notification_settings')->where('id', $setting->id)->update(['destination' => json_encode($x)]);
      echo 'CY<<'.base64_encode(json_encode([
        'settingId' => $setting->id, 'dest' => $setting->destination, 'examId' => $examId,
        'maxNotif' => (int) DB::table('aramisc_notifications')->max('id'),
      ])).'>>CY';
    `).then((b) => {
      expect(b, 'préparation').to.have.property('examId');
      backup = b;
    });
  });

  beforeEach(() => {
    cy.loginAsAdmin();
    // Les aperçus d'impression peuvent appeler window.print() : on le neutralise
    cy.on('window:before:load', (win) => {
      win.print = () => {};
    });
  });

  after(() => {
    if (!backup) return;
    php(`
      DB::table('seat_plans')->where('exam_type_id', ${backup.examId})->delete();
      DB::table('aramisc_exam_schedules')->where('exam_term_id', ${backup.examId})->delete();
      DB::table('aramisc_exam_types')->where('id', ${backup.examId})->delete();
      DB::table('aramisc_notifications')->where('id', '>', ${backup.maxNotif})->delete();
      DB::table('aramisc_notification_settings')->where('id', ${backup.settingId})
        ->update(['destination' => base64_decode('${Cypress.Buffer.from(backup.dest).toString('base64')}')]);
      echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_notification_settings')->where('id', ${backup.settingId})->value('destination'))).'>>CY';
    `).then((dest) => expect(dest, 'réglage de notification rétabli').to.eq(backup.dest));
  });

  /** Examen → classe → section (AJAX) → « Recherche ». `step` : préfixe des captures (ex. « 02 »). */
  const search = (step) => {
    cy.get('#exam').select(String(backup.examId), { force: true });
    cy.get('#select_class').select(CLASS_ID, { force: true });
    cy.get(`#select_section option[value="${SECTION_ID}"]`, { timeout: 20000 }).should('exist');
    cy.get('#select_section').select(SECTION_ID, { force: true });
    // cy.screenshot(`${shot}/${step}a-recherche-formulaire`);
    cy.get(searchForm).contains('button[type="submit"]', /recherch|search/i).click();
    cy.get(`${generateForm} tbody tr`, { timeout: 20000 }).should('have.length', 5);
    // cy.screenshot(`${shot}/${step}b-recherche-resultat`);
  };

  it('valide les critères, liste les élèves puis génère leur plan de siège', () => {
    // ─────────────── 1. Validation ───────────────
    cy.visit('/examplan/seatplan');
    cy.get(searchForm).contains('button[type="submit"]', /recherch|search/i).click();
    cy.url().should('match', /\/examplan\/seatplan$/);
    cy.get(`${searchForm} .text-danger, ${searchForm} .is-invalid`).should('exist');
    // cy.screenshot(`${shot}/01-validation`);

    // ─────────────── 2. Recherche ───────────────
    // 2a. Sans planning d'examen → avertissement, pas de génération (anomalie 17)
    cy.get('#exam').select(String(backup.examId), { force: true });
    cy.get('#select_class').select(CLASS_ID, { force: true });
    cy.get(`#select_section option[value="${SECTION_ID}"]`, { timeout: 20000 }).should('exist');
    cy.get('#select_section').select(SECTION_ID, { force: true });
    // Le contrôleur redirige vers l'index (302) au lieu d'afficher la liste. Le toast n'est pas vérifié : une requête AJAX
    // de l'ancienne page (ajaxSelectStudent, chat/check/notification) passe parfois avant le rechargement et consomme le flash.
    cy.intercept('POST', '**/examplan/seatplan/search').as('searchNoSchedule');
    cy.get(searchForm).contains('button[type="submit"]', /recherch|search/i).click();
    cy.wait('@searchNoSchedule').its('response.statusCode').should('eq', 302);
    cy.url().should('match', /\/examplan\/seatplan$/);
    cy.get(generateForm).should('not.exist');

    // 2b. Avec une épreuve au planning → liste des élèves
    php(`DB::table('aramisc_exam_schedules')->insert(['exam_term_id' => ${backup.examId}, 'class_id' => ${CLASS_ID}, 'section_id' => ${SECTION_ID},
      'subject_id' => 1, 'date' => '2026-12-15', 'start_time' => '09:00:00', 'end_time' => '10:00:00', 'active_status' => 1,
      'school_id' => 1, 'academic_id' => 1, 'created_at' => now(), 'updated_at' => now()]);`);
    cy.visit('/examplan/seatplan');
    search('02');
    cy.get(`${generateForm} tbody input[type="checkbox"]:checked`).should('have.length', 0);

    // ─────────────── 3. Génération ───────────────
    const names = [];
    cy.get(`${generateForm} tbody tr`).each(($tr, i) => {
      if (i < 2) {
        names.push($tr.find('td').eq(1).text().trim());
        cy.wrap($tr).find('input[type="checkbox"]').check({ force: true });
      }
    });
    // cy.screenshot(`${shot}/03-generation-formulaire`);
    cy.get(generateForm).invoke('removeAttr', 'target');
    cy.get(generateForm).find('button.submit').click();
    // cy.screenshot(`${shot}/04-apercu-impression`);
    cy.get('h2', { timeout: 60000 }).should(($h2) => {
      const shown = [...$h2].map((el) => el.innerText.trim());
      names.forEach((n) => expect(shown).to.include(n));
    });
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('seat_plans')->where('exam_type_id', ${backup.examId})->count())).'>>CY';`)
      .should('eq', 2);

    // Notifications : uniquement les 2 élèves concernés et leurs parents, une fois chacun (avant : toute la section × N)
    php(`
      $students = DB::table('student_records')->whereIn('id', DB::table('seat_plans')->where('exam_type_id', ${backup.examId})->pluck('student_record_id'))->pluck('student_id');
      $allowed = DB::table('aramisc_students')->whereIn('id', $students)->pluck('user_id')
        ->merge(DB::table('aramisc_parents')->whereIn('id', DB::table('aramisc_students')->whereIn('id', $students)->pluck('parent_id'))->pluck('user_id'));
      $sent = DB::table('aramisc_notifications')->where('id', '>', ${backup.maxNotif})->pluck('user_id');
      echo 'CY<<'.base64_encode(json_encode(['outsiders' => $sent->diff($allowed)->count(), 'duplicates' => $sent->count() - $sent->unique()->count()])).'>>CY';
    `).should('deep.equal', { outsiders: 0, duplicates: 0 });

    // ─────────────── 4. Relecture : élèves déjà placés pré-cochés ───────────────
    cy.visit('/examplan/seatplan');
    search('05');
    cy.get(`${generateForm} tbody tr`).each(($tr, i) => {
      cy.wrap($tr).find('input[type="checkbox"]').should(i < 2 ? 'be.checked' : 'not.be.checked');
    });
    // cy.screenshot(`${shot}/05c-relecture-pre-coches`);
  });
});
