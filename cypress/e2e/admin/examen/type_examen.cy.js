// cypress/e2e/admin/examen/type_examen.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Examen > Type d'examen : titre, « Examen moyen » non coché → le type apparaît dans la liste (Non, 0.00)
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il renomme, coche « Examen moyen » avec une moyenne de 40
//     → la liste affiche Oui / 40.00
//  3. « Sélectionner > Supprimer », confirme → le type disparaît (contrôle en base)
//
// Aucune notification (AramiscExaminationController). after() supprime le type s'il reste.

import { php, findRow, rowAction, shouldBeGone } from '../administration/helpers';

describe("Admin – Examen : Type d'examen (CRUD)", () => {
  const shot = 'admin/examen/type_examen';
  const prefix = 'CyTypeExamen_';
  const name = `${prefix}${Date.now()}`;
  const updatedName = `${name}_M`;

  after(() => {
    php(`DB::table('aramisc_exam_types')->where('title', 'like', '${prefix}%')->delete();`);
  });

  const examType = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_exam_types')->whereIn('title', ['${name}', '${updatedName}'])->first(['title', 'is_average', 'average_mark']))).'>>CY';`);

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un type d'examen", () => {
    cy.loginAsAdmin();
    cy.visit('/exam-type');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="exam_type_title"]').clear().type(name);
    cy.get('#averageExam').should('not.be.checked');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    examType().then((t) => {
      expect(t, "type d'examen enregistré").to.not.be.null;
      expect(t.is_average).to.eq(0);
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/exam-type$/);
    findRow(name).should('exist');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="exam-type-edit"]');

    cy.url().should('include', '/exam-type-edit/');
    cy.get('input[name="exam_type_title"]').should('have.value', name).clear().type(updatedName);
    cy.get('#averageExam').check({ force: true });
    cy.get('input[name="average_mark"]').clear({ force: true }).type('40', { force: true });
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    examType().then((t) => {
      expect(t.title).to.eq(updatedName);
      expect(t.is_average).to.eq(1);
      expect(Number(t.average_mark)).to.eq(40);
    });
    cy.url().should('match', /\/exam-type$/);
    findRow(updatedName).should('contain', '40.00');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedName, 'a.dropdown-item[data-target^="#deleteSubjectModal"]');
    cy.get('.modal.show[id^="deleteSubjectModal"]').should('be.visible')
      .find('a[href*="exam-type-delete"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    examType().should('be.null');
    shouldBeGone(updatedName);
  });
});
