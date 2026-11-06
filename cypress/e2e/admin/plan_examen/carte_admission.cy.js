// cypress/e2e/admin/plan_examen/carte_admission.cy.js
//
// Scénario utilisateur :
//  0. Préparation (tinker) : la base de test n'a aucun type d'examen → création d'un examen temporaire ;
//     coupure Email/SMS/App de la notification Exam_Admit_Card (sinon e-mails / SMS / push réels).
//  1. Plan d'examen > Carte d'admission : « Recherche » sans critère → erreurs de validation
//  2. Examen → classe → section (AJAX) → « Recherche » → liste des élèves de la section
//  3. Cocher 2 élèves → « Générer la carte d'admission » → aperçu d'impression avec leurs cartes ;
//     2 lignes créées dans admit_cards
//  4. Nettoyage (after) : cartes, notifications Web créées, examen temporaire ; réglages de notification rétablis
//
// Le formulaire « Générer » s'ouvre normalement dans un nouvel onglet (target=_blank) : retiré pour rester dans Cypress.
// Captures d'écran : en commentaire pour l'instant.

import { php } from '../portefeuille/helpers';

const CLASS_ID = '2'; // Class2 (5 élèves)
const SECTION_ID = '2';
const EVENT = 'Exam_Admit_Card';

describe("Admin – Plan d'examen : Carte d'admission", () => {
  const shot = 'admin/plan_examen/carte_admission';
  const examTitle = `Examen_CY_${Date.now()}`;
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
      DB::table('admit_cards')->where('exam_type_id', ${backup.examId})->delete();
      DB::table('aramisc_exam_schedules')->where('exam_term_id', ${backup.examId})->delete();
      DB::table('aramisc_exam_types')->where('id', ${backup.examId})->delete();
      DB::table('aramisc_notifications')->where('id', '>', ${backup.maxNotif})->delete();
      DB::table('aramisc_notification_settings')->where('id', ${backup.settingId})
        ->update(['destination' => base64_decode('${Cypress.Buffer.from(backup.dest).toString('base64')}')]);
      echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_notification_settings')->where('id', ${backup.settingId})->value('destination'))).'>>CY';
    `).then((dest) => expect(dest, 'réglage de notification rétabli').to.eq(backup.dest));
  });

  it("valide les critères, liste les élèves puis génère leurs cartes d'admission", () => {
    const searchForm = 'form[action$="/examplan/admitcard"]';

    // ─────────────── 1. Validation ───────────────
    cy.visit('/examplan/admitcard');
    cy.get(searchForm).contains('button[type="submit"]', /recherch|search/i).click();
    cy.url().should('match', /\/examplan\/admitcard$/);
    cy.get(`${searchForm} .text-danger, ${searchForm} .is-invalid`).should('exist');
    // cy.screenshot(`${shot}/01-validation`);

    // ─────────────── 2. Recherche ───────────────
    const generateForm = 'form[action$="/examplan/admitcard/generate"]';
    const search = () => {
      cy.get('#exam').select(String(backup.examId), { force: true });
      cy.get('#select_class').select(CLASS_ID, { force: true });
      cy.get(`#select_section option[value="${SECTION_ID}"]`, { timeout: 20000 }).should('exist');
      cy.get('#select_section').select(SECTION_ID, { force: true });
      cy.get(searchForm).contains('button[type="submit"]', /recherch|search/i).click();
    };

    // 2a. Sans planning d'examen → avertissement, pas de génération (anomalie 17)
    // Le contrôleur redirige vers l'index (302) au lieu d'afficher la liste. Le toast n'est pas vérifié : une requête AJAX
    // de l'ancienne page (ajaxSelectStudent, chat/check/notification) passe parfois avant le rechargement et consomme le flash.
    cy.intercept('POST', '**/examplan/admitcard').as('searchNoSchedule');
    search();
    cy.wait('@searchNoSchedule').its('response.statusCode').should('eq', 302);
    cy.url().should('match', /\/examplan\/admitcard$/);
    cy.get(generateForm).should('not.exist');

    // 2b. Avec une épreuve au planning → liste des élèves
    php(`DB::table('aramisc_exam_schedules')->insert(['exam_term_id' => ${backup.examId}, 'class_id' => ${CLASS_ID}, 'section_id' => ${SECTION_ID},
      'subject_id' => 1, 'date' => '2026-12-15', 'start_time' => '09:00:00', 'end_time' => '10:00:00', 'active_status' => 1,
      'school_id' => 1, 'academic_id' => 1, 'created_at' => now(), 'updated_at' => now()]);`);
    cy.visit('/examplan/admitcard');
    search();
    // cy.screenshot(`${shot}/02-recherche-formulaire`);
    cy.get(`${generateForm} tbody tr`, { timeout: 20000 }).should('have.length', 5);
    // cy.screenshot(`${shot}/03-recherche-resultat`);

    // ─────────────── 3. Génération ───────────────
    const names = [];
    cy.get(`${generateForm} tbody tr`).each(($tr, i) => {
      if (i < 2) {
        names.push($tr.find('td').eq(1).text().trim());
        cy.wrap($tr).find('input[type="checkbox"]').check({ force: true });
      }
    });
    // cy.screenshot(`${shot}/04-generation-formulaire`);
    cy.get(generateForm).invoke('removeAttr', 'target');
    cy.get(generateForm).find('button.submit').click();
    // cy.screenshot(`${shot}/05-apercu-impression`);

    cy.get('body', { timeout: 60000 }).should(($body) => {
      names.forEach((n) => expect($body.text()).to.contain(n));
    });
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('admit_cards')->where('exam_type_id', ${backup.examId})->count())).'>>CY';`)
      .should('eq', 2);

    // Notifications : uniquement les 2 élèves concernés et leurs parents, une fois chacun (avant : toute la section × N)
    php(`
      $students = DB::table('student_records')->whereIn('id', DB::table('admit_cards')->where('exam_type_id', ${backup.examId})->pluck('student_record_id'))->pluck('student_id');
      $allowed = DB::table('aramisc_students')->whereIn('id', $students)->pluck('user_id')
        ->merge(DB::table('aramisc_parents')->whereIn('id', DB::table('aramisc_students')->whereIn('id', $students)->pluck('parent_id'))->pluck('user_id'));
      $sent = DB::table('aramisc_notifications')->where('id', '>', ${backup.maxNotif})->pluck('user_id');
      echo 'CY<<'.base64_encode(json_encode(['outsiders' => $sent->diff($allowed)->count(), 'duplicates' => $sent->count() - $sent->unique()->count()])).'>>CY';
    `).should('deep.equal', { outsiders: 0, duplicates: 0 });
  });
});
