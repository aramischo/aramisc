// cypress/e2e/admin/inventaire/vente.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Inventaire > Vendre des articles : rubrique de revenu, paiement « Cash », acheteur « Teacher » (teacher1),
//     référence, date, article (prix 20 × quantité 3 = 60), payé 0 (reste dû 60) → la vente apparaît dans la liste ;
//     le stock de l'article passe de 10 à 7 ; un revenu est enregistré
//  2. « Sélectionner > Modifier » : la quantité passe à 4 → le stock doit valoir 6 (10 − 4)
//  3. « Sélectionner > Supprimer » (proposé seulement sans paiement ; sinon « Annuler »), confirme → la vente disparaît, le stock revient à 10, le revenu est supprimé
//
// Article de test (stock 10) créé en before() (tinker), supprimé en after() avec ventes, lignes et revenus liés.
// ⚠ Notification Item_sell : muteMessaging() coupe Email/SMS/App (+ réglages e-mail / SMS), restoreMessaging() rétablit.
// Date volontairement > 12 (15/09/2026) : cas réel d'un utilisateur FR (format j/m/aaaa).

import { php, findRow, rowAction, shouldBeGone, muteMessaging, restoreMessaging, toastSuccess, numberShouldBe } from '../administration/helpers';

describe("Admin – Inventaire : Vente d'articles (stock, revenu)", () => {
  const shot = 'admin/inventaire/vente';
  const stamp = Date.now();
  const reference = `CyVente_${stamp}`;
  const CASH = '1';
  const INCOME_HEAD = '1';
  const TEACHER_ROLE = '4';
  let ctx; // { item, cat, teacher } — teacher = id aramisc_staffs (la page Vente propose les membres par id de personnel)
  let backup;

  before(() => {
    muteMessaging(['Item_sell'], ['admin@aramisc.com']).then((b) => { backup = b; });
    php(`
      $base = ['school_id' => 1, 'academic_id' => getAcademicId(), 'created_at' => now(), 'updated_at' => now()];
      $cat = DB::table('aramisc_item_categories')->insertGetId(['category_name' => 'CyCatVente_${stamp}'] + $base);
      $item = DB::table('aramisc_items')->insertGetId(['item_name' => 'CyArticleVente_${stamp}', 'item_category_id' => $cat, 'total_in_stock' => 10] + $base);
      echo 'CY<<'.base64_encode(json_encode(['item' => $item, 'cat' => $cat, 'teacher' => DB::table('aramisc_staffs')->where('email', 'teacher1@aramisc.com')->value('id')])).'>>CY';
    `).then((r) => { ctx = r; });
  });

  after(() => {
    if (ctx) {
      php(`
        $ids = DB::table('aramisc_item_sells')->where('reference_no', '${reference}')->pluck('id');
        DB::table('aramisc_item_sell_children')->whereIn('item_sell_id', $ids)->delete();
        DB::table('aramisc_add_incomes')->whereIn('item_sell_id', $ids)->where('name', '!=', 'Opening Balance')->delete();
        DB::table('aramisc_item_sells')->whereIn('id', $ids)->delete();
        DB::table('aramisc_item_sell_children')->where('item_id', ${ctx.item})->delete();
        DB::table('aramisc_items')->where('id', ${ctx.item})->delete();
        DB::table('aramisc_item_categories')->where('id', ${ctx.cat})->delete();
      `);
    }
    restoreMessaging(backup);
  });

  const state = () =>
    php(`
      $s = DB::table('aramisc_item_sells')->where('reference_no', '${reference}')->first(['id', 'grand_total', 'total_quantity', 'sell_date']);
      echo 'CY<<'.base64_encode(json_encode([
        'sell' => $s,
        'stock' => (int) DB::table('aramisc_items')->where('id', ${ctx.item})->value('total_in_stock'),
        'incomes' => $s ? DB::table('aramisc_add_incomes')->where('item_sell_id', $s->id)->where('name', '!=', 'Opening Balance')->count() : 0,
        'children' => DB::table('aramisc_item_sell_children')->where('item_id', ${ctx.item})->count(),
      ])).'>>CY';
    `);

  it('vend des articles, modifie la quantité puis supprime la vente en tenant le stock à jour', () => {
    cy.loginAsAdmin();
    cy.intercept('GET', '**/staffNameByRole*').as('staffs');
    cy.visit('/item-sell');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('#income_head_id').select(INCOME_HEAD, { force: true });
    cy.get('#item_sell_payment_method_id').select(CASH, { force: true });
    cy.get('#buyer_type').select(TEACHER_ROLE, { force: true });
    cy.wait('@staffs');
    cy.get(`#selectStaffs option[value="${ctx.teacher}"]`).should('exist');
    cy.get('#selectStaffs').select(String(ctx.teacher), { force: true });
    cy.get('input[name="reference_no"]').clear().type(reference);
    cy.get('#sell_date').invoke('val', '15/09/2026');
    cy.get('#productName1').select(String(ctx.item), { force: true });
    cy.get('#unit_price1').clear().type('20');
    cy.get('#quantity1').clear().type('3');
    numberShouldBe('#subTotal', 60, 'total calculé');
    // Payé 0 : la liste ne propose « Supprimer » que pour une vente sans paiement (sinon « Annuler »)
    cy.get('input[name="totalPaid"]').clear().type('0');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('form[action$="save-item-sell-data"] button.primary-btn.fix-gr-bg').last().click();
    toastSuccess();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    state().then((s) => {
      expect(s.sell, 'vente enregistrée').to.not.be.null;
      expect(Number(s.sell.grand_total)).to.eq(60);
      expect(s.sell.sell_date).to.eq('2026-09-15');
      expect(s.stock, 'stock après vente').to.eq(7);
      expect(s.incomes, 'revenu lié').to.eq(1);
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.visit('/item-sell-list');
    findRow(reference).should('contain', '60');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(reference, 'a.dropdown-item[href*="edit-item-sell"]');

    cy.url().should('include', '/edit-item-sell/');
    cy.get('input[name="reference_no"]').should('have.value', reference);
    numberShouldBe('#quantity1', 3, 'quantité vendue');
    cy.get('#quantity1').clear().type('4');
    numberShouldBe('#subTotal', 80, 'total recalculé');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('form[action$="update-item-sell-data"] button.primary-btn.fix-gr-bg').last().click();
    toastSuccess();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    state().then((s) => {
      expect(Number(s.sell.total_quantity)).to.eq(4);
      expect(s.stock, 'stock après modification (10 − 4)').to.eq(6);
      expect(s.incomes, 'un seul revenu lié').to.eq(1);
    });

    // ─────────────────────────── 4. DELETE ───────────────────────────
    cy.visit('/item-sell-list');
    rowAction(reference, 'a.dropdown-item.deleteUrl[href*="delete-item-sale-view"]');
    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible')
      .find('a[href*="delete-item-sale/"] button').click();
    toastSuccess();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    state().then((s) => {
      expect(s.sell, 'vente supprimée').to.be.null;
      expect(s.stock, 'stock revenu à 10').to.eq(10);
      expect(s.children, 'lignes de vente supprimées').to.eq(0);
    });
    shouldBeGone(reference);
  });
});
