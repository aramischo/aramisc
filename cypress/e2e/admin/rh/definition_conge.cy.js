// cypress/e2e/admin/rh/definition_conge.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Congés > Définir les congés
//  2. Il choisit le rôle « Teacher », l'enseignant teacher1, un type de congé et 5 jours → la définition apparaît dans la liste
//  3. « Sélectionner > Modifier » : formulaire pré-rempli, il passe à 7 jours → la liste affiche 7
//  4. « Sélectionner > Ajouter des jours » : il ajoute 3 jours → la liste affiche 10
//  5. « Sélectionner > Supprimer », confirme → la définition disparaît (contrôle en base)
//
// Pour ne pas toucher aux congés existants, le test crée son propre type de congé en before() (tinker)
// et le supprime en after() avec toute définition restante. Aucune notification (AramiscLeaveDefineController).

import { php, findRow, dtSearch } from './helpers';

describe('Admin – RH : Définition des congés (CRUD + ajout de jours)', () => {
  const shot = 'admin/rh/definition_conge';
  const typeName = `CyTypeDefini_${Date.now()}`;
  const TEACHER_ROLE = '4';
  const teacherName = 'teacher1 teacher1';
  let typeId;
  let teacherId;

  before(() => {
    php(`
      $id = DB::table('aramisc_leave_types')->insertGetId([
        'type' => '${typeName}', 'total_days' => 0, 'active_status' => 1, 'school_id' => 1,
        'academic_id' => getAcademicId(), 'created_at' => now(), 'updated_at' => now(),
      ]);
      echo 'CY<<'.base64_encode(json_encode(['type' => $id, 'teacher' => DB::table('users')->where('email', 'teacher1@aramisc.com')->value('id')])).'>>CY';
    `).then((r) => {
      expect(r.teacher, 'teacher1 existe').to.be.a('number');
      typeId = r.type;
      teacherId = r.teacher;
    });
  });

  after(() => {
    php(`
      $id = DB::table('aramisc_leave_types')->where('type', '${typeName}')->value('id');
      DB::table('aramisc_leave_defines')->where('type_id', $id)->delete();
      DB::table('aramisc_leave_types')->where('id', $id)->delete();
    `);
  });

  const days = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_leave_defines')->where('type_id', ${typeId})->pluck('days'))).'>>CY';`);

  // La liste n'est pas filtrable sur le type (colonne calculée) : on filtre sur l'enseignant puis on prend la ligne du type
  const defineRow = () => findRow(teacherName).parents('tbody').contains('tr', typeName);

  it("définit un congé pour un enseignant, le modifie, ajoute des jours puis le supprime", () => {
    cy.loginAsAdmin();
    cy.intercept('GET', '**/staffNameByRole*').as('staffs');
    cy.visit('/leave-define');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('#member_type').select(TEACHER_ROLE, { force: true });
    cy.wait('@staffs');
    cy.get(`#selectStaffs option[value="${teacherId}"]`).should('exist');
    cy.get('#selectStaffs').select(String(teacherId), { force: true });
    cy.get('select[name="leave_type"]').select(String(typeId), { force: true });
    cy.get('input[name="days"]:not(#showDays)').clear().type('5');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/leave-define$/);
    defineRow().should('contain', 'Teacher').and('contain', '5');
    days().should('deep.equal', [5]);

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    defineRow().within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[href*="leave-define/"]').click({ force: true });
    });

    cy.url().should('match', /\/leave-define\/\d+$/);
    cy.get('select[name="leave_type"]').should('have.value', String(typeId));
    cy.get('input[name="days"]:not(#showDays)').should('have.value', '5').clear().type('7');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    days().should('deep.equal', [7]);
    cy.visit('/leave-define');
    defineRow().should('contain', '7');

    // ─────────────────────── 4. AJOUT DE JOURS ───────────────────────
    defineRow().within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[data-target="#addLeaveDayModal"]').click({ force: true });
    });
    cy.get('#addLeaveDayModal.show').should('be.visible').within(() => {
      cy.get('#showDays').should('have.value', '7').clear().type('3');
      // cy.screenshot(`${shot}/05-ajout-jours-formulaire`);
      cy.get('button[type="submit"]').click();
    });
    // cy.screenshot(`${shot}/06-ajout-jours-apres-submit`);

    days().should('deep.equal', [10]);
    defineRow().should('contain', '10');

    // ─────────────────────────── 5. DELETE ───────────────────────────
    defineRow().within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[data-target="#deleteLeaveDefineModal"]').click({ force: true });
    });
    cy.get('#deleteLeaveDefineModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/07-delete-apres-confirmation`);

    days().should('deep.equal', []);
    dtSearch(teacherName);
    cy.get('#table_id tbody', { timeout: 20000 }).should('not.contain', typeName);
  });
});
