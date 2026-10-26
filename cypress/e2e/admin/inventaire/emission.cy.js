// cypress/e2e/admin/inventaire/emission.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Inventaire > Émettre un article : rôle « Teacher », teacher1, date d'émission 15/09/2026,
//     date de retour 20/09/2026, catégorie, article (chargé en AJAX), quantité 2 → l'émission apparaît dans la liste ;
//     le stock de l'article passe de 10 à 8
//  2. « Sélectionner > Retour », confirme → l'émission passe au statut « Retourné », le stock revient à 10
//
// La page ne propose ni modification ni suppression d'une émission : création, lecture et retour.
// Article de test (stock 10) créé en before() (tinker), supprimé en after() avec ses émissions. Aucune notification.
// Dates volontairement > 12 : cas réel d'un utilisateur FR (format j/m/aaaa).

import { php } from '../administration/helpers';

describe("Admin – Inventaire : Émission d'articles (émission, retour)", () => {
  const shot = 'admin/inventaire/emission';
  const stamp = Date.now();
  const itemName = `CyArticleEmis_${stamp}`;
  const TEACHER_ROLE = '4';
  let ctx; // { item, cat, teacher }

  before(() => {
    php(`
      $base = ['school_id' => 1, 'academic_id' => getAcademicId(), 'created_at' => now(), 'updated_at' => now()];
      $cat = DB::table('aramisc_item_categories')->insertGetId(['category_name' => 'CyCatEmis_${stamp}'] + $base);
      $item = DB::table('aramisc_items')->insertGetId(['item_name' => '${itemName}', 'item_category_id' => $cat, 'total_in_stock' => 10] + $base);
      echo 'CY<<'.base64_encode(json_encode(['item' => $item, 'cat' => $cat, 'teacher' => DB::table('users')->where('email', 'teacher1@aramisc.com')->value('id')])).'>>CY';
    `).then((r) => { ctx = r; });
  });

  after(() => {
    if (!ctx) return;
    php(`
      DB::table('aramisc_item_issues')->where('item_id', ${ctx.item})->delete();
      DB::table('aramisc_items')->where('id', ${ctx.item})->delete();
      DB::table('aramisc_item_categories')->where('id', ${ctx.cat})->delete();
    `);
  });

  const state = () =>
    php(`echo 'CY<<'.base64_encode(json_encode([
      'issue' => DB::table('aramisc_item_issues')->where('item_id', ${ctx.item})->first(['quantity', 'issue_status', 'issue_date', 'due_date', 'issue_to']),
      'stock' => (int) DB::table('aramisc_items')->where('id', ${ctx.item})->value('total_in_stock'),
    ])).'>>CY';`);

  it("émet un article à un enseignant puis enregistre son retour en tenant le stock à jour", () => {
    cy.loginAsAdmin();
    cy.intercept('GET', '**/staffNameByRole*').as('staffs');
    cy.intercept('GET', '**/getItemByCategory*').as('items');
    cy.visit('/item-issue');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('#member_type').select(TEACHER_ROLE, { force: true });
    cy.wait('@staffs');
    cy.get(`#selectStaffs option[value="${ctx.teacher}"]`).should('exist');
    cy.get('#selectStaffs').select(String(ctx.teacher), { force: true });
    cy.get('input[name="issue_date"]').invoke('val', '15/09/2026');
    cy.get('input[name="due_date"]').invoke('val', '20/09/2026');
    cy.get('#item_category_id').select(String(ctx.cat), { force: true });
    cy.wait('@items');
    cy.get(`#selectItems option[value="${ctx.item}"]`).should('exist');
    cy.get('#selectItems').select(String(ctx.item), { force: true });
    cy.get('input[name="quantity"]').clear().type('2');
    cy.get('textarea[name="description"]').clear().type('Émission Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.issuedSubmit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    state().then((s) => {
      expect(s.issue, 'émission enregistrée').to.not.be.null;
      expect(s.issue).to.include({ issue_status: 'I', issue_date: '2026-09-15', due_date: '2026-09-20' });
      expect(Number(s.issue.quantity)).to.eq(2);
      expect(s.stock, 'stock après émission').to.eq(8);
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('include', '/item-issue');
    cy.get('#table_id').contains('tr', itemName).should('contain', 'teacher1');

    // ─────────────────────────── 3. RETOUR ───────────────────────────
    cy.get('#table_id').contains('tr', itemName).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item.modalLink[href*="return-item-view"]').click({ force: true });
    });
    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible')
      .find('a[href*="return-item/"] button').click();
    // cy.screenshot(`${shot}/03-retour-apres-confirmation`);

    state().then((s) => {
      expect(s.issue.issue_status, 'statut retourné').to.eq('R');
      expect(s.stock, 'stock revenu à 10').to.eq(10);
    });
  });
});
