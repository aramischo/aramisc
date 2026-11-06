// cypress/e2e/admin/inventaire/article.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Inventaire > Liste d'articles : nom, catégorie, description → l'article apparaît (stock 0)
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il renomme et change la description → la liste est à jour
//  3. « Sélectionner > Supprimer » (fenêtre chargée en AJAX), confirme → l'article disparaît (contrôle en base)
//
// La base de test n'a aucune catégorie d'article : before() en crée une (tinker), after() la supprime avec l'article.
// Aucune notification (AramiscItemController).

import { php, findRow, rowAction, shouldBeGone } from '../administration/helpers';

describe("Admin – Inventaire : Article (CRUD)", () => {
  const shot = 'admin/inventaire/article';
  const prefix = 'CyArticle_';
  const name = `${prefix}${Date.now()}`;
  const updatedName = `${name}_M`;
  const categoryName = `CyCategorieArticle_${Date.now()}`;
  let categoryId;

  before(() => {
    php(`
      $id = DB::table('aramisc_item_categories')->insertGetId([
        'category_name' => '${categoryName}', 'school_id' => 1, 'academic_id' => getAcademicId(),
        'created_at' => now(), 'updated_at' => now(),
      ]);
      echo 'CY<<'.base64_encode(json_encode($id)).'>>CY';
    `).then((id) => { categoryId = id; });
  });

  after(() => {
    php(`
      DB::table('aramisc_items')->where('item_name', 'like', '${prefix}%')->delete();
      DB::table('aramisc_item_categories')->where('category_name', '${categoryName}')->delete();
    `);
  });

  const item = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_items')->whereIn('item_name', ['${name}', '${updatedName}'])->first(['item_name', 'item_category_id', 'description']))).'>>CY';`);

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un article", () => {
    cy.loginAsAdmin();
    cy.visit('/item-list');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="item_name"]').clear().type(name);
    cy.get('select[name="category_name"]').select(String(categoryId), { force: true });
    cy.get('textarea[name="description"]').clear().type('Article Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    item().should('deep.equal', { item_name: name, item_category_id: categoryId, description: 'Article Cypress' });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/item-list$/);
    findRow(name).should('contain', categoryName).and('contain', 'Article Cypress');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="item-list/"]');

    cy.url().should('match', /\/item-list\/\d+$/);
    cy.get('input[name="item_name"]').should('have.value', name).clear().type(updatedName);
    cy.get('select[name="category_name"]').should('have.value', String(categoryId));
    cy.get('textarea[name="description"]').clear().type('Article modifie');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    item().should('deep.equal', { item_name: updatedName, item_category_id: categoryId, description: 'Article modifie' });
    cy.url().should('match', /\/item-list$/);
    findRow(updatedName).should('contain', 'Article modifie');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedName, 'a.dropdown-item.deleteUrl');
    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible')
      .find('a[href*="delete-item/"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    item().should('be.null');
    shouldBeGone(updatedName);
  });
});
