// cypress/e2e/admin/inventaire/reception.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Inventaire > Recevoir des articles : rubrique de dépense, paiement « Cash », fournisseur, magasin,
//     référence, date, article (prix 10 × quantité 5 = 50), payé 0 (reste dû 50) → « Recevoir »
//     → la réception apparaît dans la liste ; le stock de l'article passe à 5 ; une dépense est enregistrée
//  2. « Sélectionner > Modifier » : la quantité passe à 8 → stock 8, total 80
//  3. « Sélectionner > Supprimer » (proposé seulement sans paiement ; sinon « Annuler »), confirme → la réception disparaît, le stock revient à 0, la dépense est supprimée
//
// Article, fournisseur et magasin de test créés en before() (tinker), supprimés en after() avec réceptions et dépenses.
// ⚠ Notification Item_Recieved : muteMessaging() coupe Email/SMS/App (+ réglages e-mail / SMS), restoreMessaging() rétablit.
// Date volontairement > 12 (15/09/2026) : cas réel d'un utilisateur FR (format j/m/aaaa).

import { php, findRow, rowAction, shouldBeGone, muteMessaging, restoreMessaging, toastSuccess, numberShouldBe } from '../administration/helpers';

describe("Admin – Inventaire : Réception d'articles (stock, dépense)", () => {
  const shot = 'admin/inventaire/reception';
  const stamp = Date.now();
  const reference = `CyRecep_${stamp}`;
  const CASH = '1';
  const EXPENSE_HEAD = '2';
  let ctx; // { item, supplier, store }
  let backup;

  before(() => {
    muteMessaging(['Item_Recieved'], ['admin@aramisc.com']).then((b) => { backup = b; });
    php(`
      $base = ['school_id' => 1, 'academic_id' => getAcademicId(), 'created_at' => now(), 'updated_at' => now()];
      $cat = DB::table('aramisc_item_categories')->insertGetId(['category_name' => 'CyCatRecep_${stamp}'] + $base);
      $item = DB::table('aramisc_items')->insertGetId(['item_name' => 'CyArticleRecep_${stamp}', 'item_category_id' => $cat, 'total_in_stock' => 0] + $base);
      $supplier = DB::table('aramisc_suppliers')->insertGetId([
        'company_name' => 'CyFournRecep_${stamp}', 'company_address' => 'Cypress', 'contact_person_name' => 'Cypress',
        'contact_person_mobile' => '0500${String(stamp).slice(-6)}', 'contact_person_email' => 'cyrecep_${stamp}@exemple.ma',
      ] + $base);
      $store = DB::table('aramisc_item_stores')->insertGetId(['store_name' => 'CyMagRecep_${stamp}', 'store_no' => 'R1'] + $base);
      echo 'CY<<'.base64_encode(json_encode(['item' => $item, 'supplier' => $supplier, 'store' => $store, 'cat' => $cat])).'>>CY';
    `).then((r) => { ctx = r; });
  });

  after(() => {
    if (ctx) {
      php(`
        $ids = DB::table('aramisc_item_receives')->where('reference_no', '${reference}')->pluck('id');
        DB::table('aramisc_item_receive_children')->whereIn('item_receive_id', $ids)->delete();
        DB::table('aramisc_add_expenses')->whereIn('item_receive_id', $ids)->delete();
        DB::table('aramisc_bank_statements')->whereIn('item_receive_id', $ids)->delete();
        DB::table('aramisc_item_receives')->whereIn('id', $ids)->delete();
        DB::table('aramisc_items')->where('id', ${ctx.item})->delete();
        DB::table('aramisc_item_categories')->where('id', ${ctx.cat})->delete();
        DB::table('aramisc_suppliers')->where('id', ${ctx.supplier})->delete();
        DB::table('aramisc_item_stores')->where('id', ${ctx.store})->delete();
      `);
    }
    restoreMessaging(backup);
  });

  const state = () =>
    php(`
      $r = DB::table('aramisc_item_receives')->where('reference_no', '${reference}')->first(['id', 'grand_total', 'total_quantity', 'total_paid', 'total_due', 'receive_date']);
      echo 'CY<<'.base64_encode(json_encode([
        'receive' => $r,
        'stock' => (int) DB::table('aramisc_items')->where('id', ${ctx.item})->value('total_in_stock'),
        'expenses' => $r ? DB::table('aramisc_add_expenses')->where('item_receive_id', $r->id)->count() : 0,
      ])).'>>CY';
    `);

  const fillLine = (price, qty) => {
    cy.get('#productName1').select(String(ctx.item), { force: true });
    cy.get('#unit_price1').clear().type(String(price));
    cy.get('#quantity1').clear().type(String(qty));
  };

  it('reçoit des articles, modifie la quantité puis supprime la réception en tenant le stock à jour', () => {
    cy.loginAsAdmin();
    cy.visit('/item-receive');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('#expense_head_id').select(EXPENSE_HEAD, { force: true });
    cy.get('#item_receive_payment_method').select(CASH, { force: true });
    cy.get('#supplier_id').select(String(ctx.supplier), { force: true });
    cy.get('#store_id').select(String(ctx.store), { force: true });
    cy.get('input[name="reference_no"]').clear().type(reference);
    cy.get('#receive_date').invoke('val', '15/09/2026');
    fillLine(10, 5);
    numberShouldBe('#subTotal', 50, 'total calculé');
    // Payé 0 : la liste ne propose « Supprimer » que pour une réception sans paiement (sinon « Annuler »)
    cy.get('#totalPaid').clear().type('0');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('form[action$="save-item-receive-data"] button.primary-btn.fix-gr-bg').last().click();
    toastSuccess();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    state().then((s) => {
      expect(s.receive, 'réception enregistrée').to.not.be.null;
      expect(Number(s.receive.grand_total)).to.eq(50);
      expect(Number(s.receive.total_quantity)).to.eq(5);
      expect(s.receive.receive_date).to.eq('2026-09-15');
      expect(s.stock, 'stock après réception').to.eq(5);
      expect(s.expenses, 'dépense liée').to.eq(1);
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.visit('/item-receive-list');
    findRow(reference).should('contain', `CyFournRecep_${stamp}`);

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(reference, 'a.dropdown-item[href*="edit-item-receive"]');

    cy.url().should('include', '/edit-item-receive/');
    cy.get('input[name="reference_no"]').should('have.value', reference);
    numberShouldBe('#quantity1', 5, 'quantité reçue');
    // La rubrique de dépense enregistrée doit être présélectionnée (anomalie 43, corrigée)
    cy.get('#expense_head_id').should('have.value', EXPENSE_HEAD);
    cy.get('#quantity1').clear().type('8');
    numberShouldBe('#subTotal', 80, 'total recalculé');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('form[action*="update-edit-item-receive-data"] button.primary-btn.fix-gr-bg').last().click();
    toastSuccess();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    state().then((s) => {
      expect(Number(s.receive.grand_total)).to.eq(80);
      expect(Number(s.receive.total_quantity)).to.eq(8);
      expect(s.stock, 'stock après modification').to.eq(8);
      expect(s.expenses, 'une seule dépense liée').to.eq(1);
    });

    // ─────────────────────────── 4. DELETE ───────────────────────────
    cy.visit('/item-receive-list');
    rowAction(reference, 'a.dropdown-item.deleteUrl[href*="delete-item-receive-view"]');
    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible')
      .find('a[href*="delete-item-receive/"] button').click();
    toastSuccess();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    state().then((s) => {
      expect(s.receive, 'réception supprimée').to.be.null;
      expect(s.stock, 'stock revenu à 0').to.eq(0);
    });
    shouldBeGone(reference);
  });
});
