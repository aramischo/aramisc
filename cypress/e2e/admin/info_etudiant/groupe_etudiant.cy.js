// cypress/e2e/admin/info_etudiant/groupe_etudiant.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Info-étudiant > Groupe d'étudiants
//  2. Il saisit un nom de groupe → le groupe apparaît dans la liste (0 élève)
//  3. Il clique « Sélectionner > Modifier », le champ est pré-rempli, il renomme → la liste affiche le nouveau nom
//  4. Il clique « Sélectionner > Supprimer », confirme dans la modale → le groupe disparaît
//
// Aucune notification (AramiscStudentGroupController). Le after() supprime le groupe s'il reste.

import { php, findRow, rowAction, shouldBeGone } from '../administration/helpers';

describe("Admin – Info-étudiant : Groupe d'étudiants (CRUD)", () => {
  const shot = 'admin/info_etudiant/groupe_etudiant';
  const prefix = 'CyGroupe_';

  after(() => {
    php(`DB::table('aramisc_student_groups')->where('group', 'like', '${prefix}%')->delete();`);
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un groupe d'étudiants", () => {
    const name = `${prefix}${Date.now()}`;
    const updatedName = `${name}_M`;

    cy.loginAsAdmin();
    cy.visit('/student-group');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="group"]').clear().type(name);
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/student-group$/);
    findRow(name).find('td').eq(1).should('contain', '0');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="student-group-edit"]');

    cy.url().should('include', '/student-group-edit/');
    cy.get('input[name="group"]').should('have.value', name).clear().type(updatedName);
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    cy.url().should('match', /\/student-group$/);
    findRow(updatedName).should('exist');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedName, 'a.dropdown-item[data-target^="#deleteStudentGroupModal"]');
    cy.get('.modal.show[id^="deleteStudentGroupModal"]').should('be.visible')
      .find('a[href*="student-group-delete"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    cy.url().should('include', '/student-group');
    shouldBeGone(updatedName);
  });
});
