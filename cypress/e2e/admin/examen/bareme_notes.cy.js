// cypress/e2e/admin/examen/bareme_notes.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Examen > Barème de notes : nom, GPA, % de / à, note de / à, description → la ligne apparaît dans la liste
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il renomme et change la description → la liste est à jour
//  3. « Sélectionner > Supprimer », confirme → la ligne disparaît (contrôle en base)
//
// GPA unique par année : le test utilise 9.75 (absent du barème de la base de test). La création est refusée par
// l'application si des résultats sont déjà enregistrés pour l'année (aramisc_result_stores) — vérifié en before().
// Aucune notification (AramiscMarksGradeController). after() supprime la ligne si elle reste.

import { php, findRow, rowAction, shouldBeGone } from '../administration/helpers';

describe('Admin – Examen : Barème de notes (CRUD)', () => {
  const shot = 'admin/examen/bareme_notes';
  const name = `CyG${String(Date.now()).slice(-6)}`;
  const updatedName = `${name}M`;
  const GPA = '9.75';

  before(() => {
    php(`echo 'CY<<'.base64_encode(json_encode([
      'results' => DB::table('aramisc_result_stores')->where('academic_id', getAcademicId())->count(),
      'gpa' => DB::table('aramisc_marks_grades')->where('academic_id', getAcademicId())->where('gpa', ${GPA})->count(),
    ])).'>>CY';`).then((r) => {
      expect(r.results, "aucun résultat d'examen enregistré (sinon création refusée)").to.eq(0);
      expect(r.gpa, 'GPA de test libre').to.eq(0);
    });
  });

  after(() => {
    php(`DB::table('aramisc_marks_grades')->whereIn('grade_name', ['${name}', '${updatedName}'])->delete();`);
  });

  const grade = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_marks_grades')->whereIn('grade_name', ['${name}', '${updatedName}'])->first(['grade_name', 'gpa', 'percent_from', 'percent_upto', 'description']))).'>>CY';`);

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'une ligne de barème", () => {
    cy.loginAsAdmin();
    cy.visit('/marks-grade');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="grade_name"]').clear().type(name);
    cy.get('input[name="gpa"]').clear().type(GPA);
    cy.get('input[name="percent_from"]').clear().type('95');
    cy.get('input[name="percent_upto"]').clear().type('99');
    cy.get('input[name="grade_from"]').clear().type('9.5');
    cy.get('input[name="grade_upto"]').clear().type('9.99');
    cy.get('textarea[name="description"]').clear().type('Barème Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    grade().then((g) => {
      expect(g, 'ligne enregistrée').to.not.be.null;
      expect(Number(g.gpa)).to.eq(9.75);
      expect(Number(g.percent_from)).to.eq(95);
      expect(Number(g.percent_upto)).to.eq(99);
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/marks-grade$/);
    findRow(name).should('contain', '9.75');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="marks-grade/"]');

    cy.url().should('match', /\/marks-grade\/\d+$/);
    cy.get('input[name="grade_name"]').should('have.value', name).clear().type(updatedName);
    cy.get('textarea[name="description"]').clear().type('Barème modifié');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    grade().then((g) => {
      expect(g.grade_name).to.eq(updatedName);
      expect(g.description).to.eq('Barème modifié');
    });
    findRow(updatedName).should('exist');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedName, 'a.dropdown-item[data-target^="#deleteMarksGradeModal"]');
    cy.get('.modal.show[id^="deleteMarksGradeModal"]').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    grade().should('be.null');
    shouldBeGone(updatedName);
  });
});
