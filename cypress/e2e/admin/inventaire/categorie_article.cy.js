// cypress/e2e/admin/inventaire/categorie_article.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Inventaire > Catégorie d'articles : nom → la catégorie apparaît dans la liste
//  2. « Sélectionner > Modifier » : le champ est pré-rempli, il renomme → la liste est à jour
//  3. « Sélectionner > Supprimer » (fenêtre chargée en AJAX), confirme → la catégorie disparaît (contrôle en base)
//
// Remplace l'ancien inventory.cy.js (il soumettait le 1er formulaire de la page, celui de la recherche de l'en-tête).
// Aucune notification (AramiscItemCategoryController). after() supprime la catégorie si elle reste.

import { php, findRow, rowAction, shouldBeGone } from '../administration/helpers';

describe("Admin – Inventaire : Catégorie d'articles (CRUD)", () => {
  const shot = 'admin/inventaire/categorie_article';
  const prefix = 'CyCategorieArt_';
  const name = `${prefix}${Date.now()}`;
  const updatedName = `${name}_M`;

  after(() => {
    php(`DB::table('aramisc_item_categories')->where('category_name', 'like', '${prefix}%')->delete();`);
  });

  const category = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_item_categories')->whereIn('category_name', ['${name}', '${updatedName}'])->value('category_name'))).'>>CY';`);

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'une catégorie d'articles", () => {
    cy.loginAsAdmin();
    cy.visit('/item-category');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="category_name"]').clear().type(name);
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    category().should('eq', name);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/item-category$/);
    findRow(name).should('exist');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="item-category/"]');

    cy.url().should('match', /\/item-category\/\d+$/);
    cy.get('input[name="category_name"]').should('have.value', name).clear().type(updatedName);
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    category().should('eq', updatedName);
    findRow(updatedName).should('exist');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedName, 'a.dropdown-item.deleteUrl');
    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible')
      .find('a[href*="delete-item-category/"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    category().should('be.null');
    shouldBeGone(updatedName);
  });
});
