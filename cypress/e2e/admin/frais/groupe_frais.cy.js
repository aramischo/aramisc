// cypress/e2e/admin/frais/groupe_frais.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Frais > Groupe de frais : nom, description → le groupe apparaît dans la liste
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il renomme → la liste est à jour
//  3. « Sélectionner > Supprimer », confirme → le groupe disparaît (contrôle en base)
//
// Remplace l'ancien bloc « Fees Group » de fees.cy.js (il soumettait le 1er formulaire de la page, celui de la recherche
// de l'en-tête). Aucune notification. after() supprime le groupe s'il reste.

import { php, findRow, rowAction, shouldBeGone } from '../administration/helpers';

describe('Admin – Frais : Groupe de frais (CRUD)', () => {
  const shot = 'admin/frais/groupe_frais';
  const prefix = 'CyGroupeFrais_';
  const name = `${prefix}${Date.now()}`;
  const updatedName = `${name}_M`;

  after(() => {
    php(`DB::table('aramisc_fees_groups')->where('name', 'like', '${prefix}%')->delete();`);
  });

  const group = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_fees_groups')->whereIn('name', ['${name}', '${updatedName}'])->first(['name', 'description']))).'>>CY';`);

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un groupe de frais", () => {
    cy.loginAsAdmin();
    cy.visit('/fees-group');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="name"]').clear().type(name);
    cy.get('textarea[name="description"]').clear().type('Groupe Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    group().should('deep.equal', { name, description: 'Groupe Cypress' });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/fees-group$/);
    findRow(name).should('contain', 'Groupe Cypress');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="fees-group-edit"]');

    cy.url().should('include', '/fees-group-edit/');
    cy.get('input[name="name"]').should('have.value', name).clear().type(updatedName);
    cy.get('textarea[name="description"]').clear().type('Groupe modifie');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    group().should('deep.equal', { name: updatedName, description: 'Groupe modifie' });
    findRow(updatedName).should('contain', 'Groupe modifie');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedName, 'a.dropdown-item.deleteFeesGroupModal');
    cy.get('#deleteFeesGroupModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    group().should('be.null');
    shouldBeGone(updatedName);
  });
});
