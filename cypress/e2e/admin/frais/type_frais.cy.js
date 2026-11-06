// cypress/e2e/admin/frais/type_frais.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Frais > Type de frais : nom, groupe, description → le type apparaît dans la liste
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il renomme → la liste est à jour
//  3. « Sélectionner > Supprimer », confirme → le type disparaît (contrôle en base)
//
// Groupe de frais de test créé en before() (tinker), supprimé en after() avec le type.
// Remplace l'ancien bloc « Fees Type » de fees.cy.js (il soumettait le 1er formulaire de la page, celui de la recherche
// de l'en-tête). Aucune notification.

import { php, findRow, rowAction, shouldBeGone } from '../administration/helpers';

describe('Admin – Frais : Type de frais (CRUD)', () => {
  const shot = 'admin/frais/type_frais';
  const prefix = 'CyTypeFrais_';
  const name = `${prefix}${Date.now()}`;
  const updatedName = `${name}_M`;
  const groupName = `CyGroupePourType_${Date.now()}`;
  let groupId;

  before(() => {
    php(`
      $id = DB::table('aramisc_fees_groups')->insertGetId([
        'name' => '${groupName}', 'active_status' => 1, 'school_id' => 1, 'academic_id' => getAcademicId(),
        'created_at' => now(), 'updated_at' => now(),
      ]);
      echo 'CY<<'.base64_encode(json_encode($id)).'>>CY';
    `).then((id) => { groupId = id; });
  });

  after(() => {
    php(`
      DB::table('aramisc_fees_types')->where('name', 'like', '${prefix}%')->delete();
      DB::table('aramisc_fees_groups')->where('name', '${groupName}')->delete();
    `);
  });

  const type = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_fees_types')->whereIn('name', ['${name}', '${updatedName}'])->first(['name', 'fees_group_id']))).'>>CY';`);

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un type de frais", () => {
    cy.loginAsAdmin();
    cy.visit('/fees-type');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="name"]').clear().type(name);
    cy.get('select[name="fees_group"]').select(String(groupId), { force: true });
    cy.get('textarea[name="description"]').clear().type('Type Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    type().should('deep.equal', { name, fees_group_id: groupId });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/fees-type$/);
    findRow(name).should('contain', groupName);

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="fees-type-edit"]');

    cy.url().should('include', '/fees-type-edit/');
    cy.get('input[name="name"]').should('have.value', name).clear().type(updatedName);
    cy.get('select[name="fees_group"]').should('have.value', String(groupId));
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    type().should('deep.equal', { name: updatedName, fees_group_id: groupId });
    findRow(updatedName).should('contain', groupName);

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedName, 'a.dropdown-item[data-target^="#deleteFeesTypeModal"]');
    cy.get('.modal.show[id^="deleteFeesTypeModal"]').should('be.visible')
      .find('a[href*="fees-type-delete"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    type().should('be.null');
    shouldBeGone(updatedName);
  });
});
