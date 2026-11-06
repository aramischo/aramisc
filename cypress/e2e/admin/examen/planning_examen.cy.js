// cypress/e2e/admin/examen/planning_examen.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Examen > Calendrier d'examen > créer (/exam-schedule-create) : type d'examen, Class1, sec1 → « Rechercher »
//  2. Il ajoute une ligne : Math, sec1, le 15/10/2026 de 09:00 à 11:00, salle → « Enregistrer »
//  3. Calendrier d'examen (/exam-schedule) : la recherche affiche l'épreuve (Math, 09:00 AM)
//  4. Modification : nouvelle recherche sur la page de création, il passe à 10:00 – 12:00 → enregistré
//  5. Suppression : bouton corbeille de la ligne, confirmation → l'épreuve disparaît (contrôle en base)
//
// Les matières proposées viennent de la configuration d'examen : before() crée (tinker) un type d'examen, l'examen
// Class1 / sec1 / Math et sa répartition ; after() supprime type, examen, répartition et planning restants.
// Date volontairement > 12 : cas réel d'un utilisateur FR (format j/m/aaaa). Aucune notification.

import { php, numberShouldBe } from '../administration/helpers';

describe("Admin – Examen : Calendrier d'examen (création, lecture, modification, suppression)", () => {
  const shot = 'admin/examen/planning_examen';
  const typeTitle = `CyPlanning_${Date.now()}`;
  let ctx; // { type, exam, room }

  before(() => {
    php(`
      $base = ['school_id' => 1, 'academic_id' => getAcademicId(), 'created_at' => now(), 'updated_at' => now()];
      $type = DB::table('aramisc_exam_types')->insertGetId(['title' => '${typeTitle}', 'active_status' => 1] + $base);
      $exam = DB::table('aramisc_exams')->insertGetId(['exam_type_id' => $type, 'class_id' => 1, 'section_id' => 1, 'subject_id' => 1, 'exam_mark' => 100, 'active_status' => 1] + $base);
      DB::table('aramisc_exam_setups')->insert(['exam_id' => $exam, 'class_id' => 1, 'section_id' => 1, 'subject_id' => 1, 'exam_term_id' => $type, 'exam_title' => 'Écrit', 'exam_mark' => 100, 'active_status' => 1] + $base);
      echo 'CY<<'.base64_encode(json_encode(['type' => $type, 'exam' => $exam, 'room' => DB::table('aramisc_class_rooms')->where('active_status', 1)->orderBy('id')->value('id')])).'>>CY';
    `).then((r) => { ctx = r; });
  });

  after(() => {
    php(`
      $type = DB::table('aramisc_exam_types')->where('title', '${typeTitle}')->value('id');
      DB::table('aramisc_exam_schedules')->where('exam_term_id', $type)->delete();
      DB::table('aramisc_exam_setups')->where('exam_term_id', $type)->delete();
      DB::table('aramisc_exams')->where('exam_type_id', $type)->delete();
      DB::table('aramisc_exam_types')->where('id', $type)->delete();
    `);
  });

  const schedules = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_exam_schedules')->where('exam_term_id', ${ctx.type})
      ->get(['subject_id', 'section_id', 'date', 'start_time', 'end_time', 'room_id']))).'>>CY';`);

  // Recherche (type, Class1, sec1) sur la page de création du calendrier
  const searchCreatePage = () => {
    cy.intercept('GET', '**/ajaxStudentPromoteSection*').as('sections');
    cy.visit('/exam-schedule-create');
    cy.get('select[name="exam_type"]').select(String(ctx.type), { force: true });
    cy.get('#select_class').select('1', { force: true });
    cy.wait('@sections');
    cy.get('#select_section option[value="1"]').should('exist');
    cy.get('#select_section').select('1', { force: true });
    cy.get('form[action$="exam-schedule-create"] button[type="submit"]').first().click();
    cy.get('#examRoutineTable', { timeout: 20000 }).should('exist');
  };

  const setTimes = (row, start, end) => {
    cy.get(`input[name="routine[${row}][start_time]"]`).invoke('val', start);
    cy.get(`input[name="routine[${row}][end_time]"]`).invoke('val', end);
  };

  it("planifie une épreuve, la modifie puis la supprime", () => {
    cy.loginAsAdmin();

    // ─────────────────────────── 1. CREATE ───────────────────────────
    searchCreatePage();
    cy.get('#addRowBtn').click();
    cy.get('select[name="routine[1][subject]"]').select('1', { force: true });
    cy.get('select[name="routine[1][section]"]').select('1', { force: true });
    cy.get('input[name="routine[1][date]"]').invoke('val', '15/10/2026');
    setTimes(1, '09:00', '11:00');
    cy.get('select[name="routine[1][room]"]').select(String(ctx.room), { force: true });
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('form[action$="add-exam-routine-store"] button.primary-btn.fix-gr-bg').last().click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    cy.url().should('include', '/exam-routine-view/'); // retour sur la grille de saisie après enregistrement
    schedules().then((s) => {
      expect(s, 'une épreuve').to.have.length(1);
      expect(s[0]).to.include({ subject_id: 1, section_id: 1, date: '2026-10-15', start_time: '09:00:00', end_time: '11:00:00' });
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.visit('/exam-schedule');
    cy.intercept('GET', '**/ajaxStudentPromoteSection*').as('sections2');
    cy.get('select[name="exam_type"]').select(String(ctx.type), { force: true });
    cy.get('#select_class').select('1', { force: true });
    cy.wait('@sections2');
    cy.get('#select_section').select('1', { force: true });
    cy.get('form[action$="exam-schedule-report-search"] button[type="submit"]').first().click();
    cy.get('#default_table').contains('tr', 'Math').should('contain', '15/10/2026').and('contain', '09:00 AM');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    searchCreatePage();
    cy.get('select[name="routine[0][subject]"]').should('have.value', '1');
    cy.get('input[name="routine[0][date]"]').should('have.value', '15/10/2026');
    setTimes(0, '10:00', '12:00');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('form[action$="add-exam-routine-store"] button.primary-btn.fix-gr-bg').last().click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    cy.url().should('include', '/exam-routine-view/'); // retour sur la grille de saisie après enregistrement
    schedules().then((s) => {
      expect(s, 'toujours une seule épreuve').to.have.length(1);
      expect(s[0]).to.include({ date: '2026-10-15', start_time: '10:00:00', end_time: '12:00:00' });
    });

    // ─────────────────────────── 4. DELETE ───────────────────────────
    searchCreatePage();
    cy.get('#row_0 .removeExamRoutineRowBtn').click();
    cy.get('#classRoutineDeleteModal.show').should('be.visible');
    cy.get('#classRoutineDeleteSubmitButton').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    cy.get('#row_0').should('not.exist');
    schedules().should('have.length', 0);
  });
});
