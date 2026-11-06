// cypress/e2e/admin/examen/configuration_examen.cy.js
//
// Scénario utilisateur (mode « examen multiple ») :
//  1. L'admin ouvre Examen > Configuration d'examen, choisit « Examen multiple » (formulaire chargé en AJAX),
//     coche le type d'examen, choisit Class1 (matières chargées en AJAX), coche Math, note 100,
//     répartition « Écrit » = 100 → un examen est créé pour Class1 / sec1 / Math avec sa répartition
//  2. La liste affiche l'examen (type, classe, section, matière, note)
//  3. « Sélectionner > Modifier » : il passe la note à 80 et la répartition à 80
//     (la note de passage n'est affichée que si le type de résultat est « note », non configuré dans la base de test)
//  4. « Sélectionner > Supprimer », confirme → l'examen et sa répartition disparaissent (contrôle en base)
//
// Type d'examen de test créé en before() (tinker) ; after() supprime type, examens et répartitions restants.
// Class1 / sec1 a des matières affectées dans la base de test (Math = matière 1). Aucune notification.

import { php, findRow, rowAction } from '../administration/helpers';

describe("Admin – Examen : Configuration d'examen (CRUD, mode multiple)", () => {
  const shot = 'admin/examen/configuration_examen';
  const typeTitle = `CyExamConf_${Date.now()}`;
  const CLASS_ID = '1';
  const SUBJECT_ID = '1'; // Math
  let typeId;

  before(() => {
    php(`
      $id = DB::table('aramisc_exam_types')->insertGetId([
        'title' => '${typeTitle}', 'active_status' => 1, 'school_id' => 1, 'academic_id' => getAcademicId(),
        'created_at' => now(), 'updated_at' => now(),
      ]);
      echo 'CY<<'.base64_encode(json_encode($id)).'>>CY';
    `).then((id) => { typeId = id; });
  });

  after(() => {
    php(`
      $type = DB::table('aramisc_exam_types')->where('title', '${typeTitle}')->value('id');
      DB::table('aramisc_exam_setups')->where('exam_term_id', $type)->delete();
      DB::table('aramisc_exams')->where('exam_type_id', $type)->delete();
      DB::table('aramisc_exam_types')->where('id', $type)->delete();
    `);
  });

  const state = () =>
    php(`echo 'CY<<'.base64_encode(json_encode([
      'exams' => DB::table('aramisc_exams')->where('exam_type_id', ${typeId})->get(['id', 'class_id', 'section_id', 'subject_id', 'exam_mark', 'pass_mark']),
      'setups' => DB::table('aramisc_exam_setups')->where('exam_term_id', ${typeId})->get(['exam_title', 'exam_mark']),
    ])).'>>CY';`);

  it("configure un examen multiple pour Class1 / Math, le modifie puis le supprime", () => {
    cy.loginAsAdmin();
    cy.intercept('GET', '**/return_exam_view*').as('examView');
    cy.intercept('GET', '**/get-class-subjects*').as('subjects');
    cy.visit('/exam');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('#exam_system').select('multi', { force: true });
    cy.wait('@examView');
    cy.get(`#exams_types_${typeId}`).check({ force: true });
    cy.get('#exam_class').select(CLASS_ID, { force: true });
    cy.wait('@subjects');
    cy.get(`#subjects_${SUBJECT_ID}`).check({ force: true });
    cy.get('#exam_mark_main').clear().type('100');
    cy.get('#productTable input[name="exam_title[]"]').first().clear().type('Écrit');
    cy.get('#productTable input[name="exam_mark[]"]').first().clear().type('100');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('form[action$="/exam"] button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    state().then((s) => {
      expect(s.exams, 'un examen (Class1 / sec1 / Math)').to.have.length(1);
      expect(s.exams[0]).to.include({ class_id: 1, section_id: 1, subject_id: 1 });
      expect(Number(s.exams[0].exam_mark)).to.eq(100);
      expect(s.setups, 'répartition').to.have.length(1);
      expect(s.setups[0].exam_title).to.eq('Écrit');
    });

    // 1b. Même configuration soumise une 2e fois → aucun doublon (anomalie 49, corrigée)
    cy.visit('/exam');
    cy.get('#exam_system').select('multi', { force: true });
    cy.wait('@examView');
    cy.get(`#exams_types_${typeId}`).check({ force: true });
    cy.get('#exam_class').select(CLASS_ID, { force: true });
    cy.wait('@subjects');
    cy.get(`#subjects_${SUBJECT_ID}`).check({ force: true });
    cy.get('#exam_mark_main').clear().type('100');
    cy.get('#productTable input[name="exam_title[]"]').first().clear().type('Écrit');
    cy.get('#productTable input[name="exam_mark[]"]').first().clear().type('100');
    cy.get('form[action$="/exam"] button.primary-btn.submit').click();
    state().then((s) => {
      expect(s.exams, 'toujours un seul examen').to.have.length(1);
      expect(s.setups, 'toujours une seule répartition').to.have.length(1);
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/exam$/);
    findRow(typeTitle).should('contain', 'Class1').and('contain', 'Math').and('contain', '100');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(typeTitle, 'a.dropdown-item[href*="/exam/"]');

    cy.url().should('match', /\/exam\/\d+$/);
    cy.get('#exam_mark_main').invoke('val').then((v) => expect(Number(v)).to.eq(100));
    cy.get('#exam_mark_main').clear().type('80');
    cy.get('#productTable input[name="exam_title[]"]').first().should('have.value', 'Écrit');
    cy.get('#productTable input[name="exam_mark[]"]').first().clear().type('80');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('form[action*="/exam/"] button.primary-btn.fix-gr-bg').last().click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    state().then((s) => {
      expect(Number(s.exams[0].exam_mark)).to.eq(80);
      expect(s.setups).to.have.length(1);
      expect(Number(s.setups[0].exam_mark)).to.eq(80);
    });
    cy.url().should('match', /\/exam$/);
    findRow(typeTitle).should('contain', '80');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(typeTitle, 'a.dropdown-item[data-target^="#deleteExamModal"]');
    cy.get('.modal.show[id^="deleteExamModal"]').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    state().then((s) => {
      expect(s.exams, 'examen supprimé').to.have.length(0);
      expect(s.setups, 'répartition supprimée').to.have.length(0);
    });
  });
});
