// cypress/e2e/admin/info_etudiant/categorie_etudiant.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Info-étudiant > Catégorie d'étudiant
//  2. Il saisit un nom de catégorie → la catégorie apparaît dans la liste
//  3. Il clique « Sélectionner > Modifier », le champ est pré-rempli, il renomme → la liste affiche le nouveau nom
//  4. Il clique « Sélectionner > Supprimer », confirme dans la modale → la catégorie disparaît
//
// Aucune notification (AramiscStudentCategoryController). Le after() supprime la catégorie si elle reste.

import { php, findRow, rowAction, shouldBeGone } from '../administration/helpers';

describe("Admin – Info-étudiant : Catégorie d'étudiant (CRUD)", () => {
  const shot = 'admin/info_etudiant/categorie_etudiant';
  const prefix = 'CyCategorie_';

  after(() => {
    php(`DB::table('aramisc_student_categories')->where('category_name', 'like', '${prefix}%')->delete();`);
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'une catégorie d'étudiant", () => {
    const name = `${prefix}${Date.now()}`;
    const updatedName = `${name}_M`;

    cy.loginAsAdmin();
    cy.visit('/student-category');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="category"]').clear().type(name);
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/student-category$/);
    findRow(name).should('exist');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="student-category-edit"]');

    cy.url().should('include', '/student-category-edit/');
    cy.get('input[name="category"]').should('have.value', name).clear().type(updatedName);
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    cy.url().should('match', /\/student-category$/);
    findRow(updatedName).should('exist');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedName, 'a.dropdown-item[data-target^="#deleteStudentTypeModal"]');
    cy.get('.modal.show[id^="deleteStudentTypeModal"]').should('be.visible')
      .find('a[href*="student-category-delete"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    cy.url().should('include', '/student-category');
    shouldBeGone(updatedName);
  });
});
