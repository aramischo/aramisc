// cypress/e2e/admin/inventaire/magasin.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Inventaire > Magasin d'articles : nom, n° de magasin, description → le magasin apparaît dans la liste
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il renomme et change le n° → la liste est à jour
//  3. « Sélectionner > Supprimer » (fenêtre chargée en AJAX), confirme → le magasin disparaît (contrôle en base)
//
// Aucune notification (AramiscItemStoreController). after() supprime le magasin s'il reste.

import { php, findRow, rowAction, shouldBeGone } from '../administration/helpers';

describe("Admin – Inventaire : Magasin d'articles (CRUD)", () => {
  const shot = 'admin/inventaire/magasin';
  const prefix = 'CyMagasin_';
  const name = `${prefix}${Date.now()}`;
  const updatedName = `${name}_M`;

  after(() => {
    php(`DB::table('aramisc_item_stores')->where('store_name', 'like', '${prefix}%')->delete();`);
  });

  const store = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_item_stores')->whereIn('store_name', ['${name}', '${updatedName}'])->first(['store_name', 'store_no']))).'>>CY';`);

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un magasin", () => {
    cy.loginAsAdmin();
    cy.visit('/item-store');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="store_name"]').clear().type(name);
    cy.get('input[name="store_no"]').clear().type('MAG-01');
    cy.get('textarea[name="description"]').clear().type('Magasin Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    store().should('deep.equal', { store_name: name, store_no: 'MAG-01' });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/item-store$/);
    findRow(name).should('contain', 'MAG-01').and('contain', 'Magasin Cypress');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="item-store/"]');

    cy.url().should('match', /\/item-store\/\d+$/);
    cy.get('input[name="store_name"]').should('have.value', name).clear().type(updatedName);
    cy.get('input[name="store_no"]').should('have.value', 'MAG-01').clear().type('MAG-02');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    store().should('deep.equal', { store_name: updatedName, store_no: 'MAG-02' });
    cy.url().should('match', /\/item-store$/);
    findRow(updatedName).should('contain', 'MAG-02');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedName, 'a.dropdown-item.deleteUrl');
    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible')
      .find('a[href*="delete-store/"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    store().should('be.null');
    shouldBeGone(updatedName);
  });
});
