// cypress/e2e/admin/comptes/compte_bancaire.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Comptes > Compte bancaire : banque, titulaire, n° de compte, type, solde d'ouverture 1000, note
//     → le compte apparaît dans la liste (solde courant 1000) et un revenu « Opening Balance » est enregistré
//  2. « Sélectionner > Transaction » : la page des mouvements du compte s’ouvre
//  3. « Sélectionner > Modifier » : titulaire et solde d’ouverture 1200 → solde courant et revenu d’ouverture à 1200
//  4. « Sélectionner > Supprimer », confirme → le compte disparaît (contrôle en base)
//
// Aucune notification (AramiscBankAccountController). after() supprime le compte et son revenu d'ouverture s'ils restent.

import { php, findRow, rowAction, shouldBeGone, deleteBanks } from './helpers';

describe('Admin – Comptes : Compte bancaire (CRUD)', () => {
  const shot = 'admin/comptes/compte_bancaire';
  const prefix = 'CyBanque_';
  const bankName = `${prefix}${Date.now()}`;
  let bankId = 0;

  after(() => {
    php(`DB::table('aramisc_add_incomes')->where('name', 'Opening Balance')->where('item_sell_id', ${bankId})->whereIn('amount', [1000, 1200])->delete();`);
    deleteBanks(prefix);
  });

  const dbState = () =>
    php(`
      $bank = DB::table('aramisc_bank_accounts')->where('bank_name', '${bankName}')->first(['id', 'account_name', 'opening_balance', 'current_balance', 'note']);
      echo 'CY<<'.base64_encode(json_encode([
        'bank' => $bank,
        'opening' => $bank ? DB::table('aramisc_add_incomes')->where('name', 'Opening Balance')->where('item_sell_id', $bank->id)->count() : null,
        'openingAmount' => $bank ? DB::table('aramisc_add_incomes')->where('name', 'Opening Balance')->where('item_sell_id', $bank->id)->value('amount') : null,
      ])).'>>CY';
    `);

  it("crée un compte bancaire, consulte ses transactions, le modifie puis le supprime", () => {
    cy.loginAsAdmin();
    cy.visit('/bank-account');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="bank_name"]').clear().type(bankName);
    cy.get('input[name="account_name"]').clear().type('Ecole Cypress');
    cy.get('input[name="account_number"]').clear().type(String(Date.now()).slice(-10));
    cy.get('input[name="account_type"]').clear().type('Courant');
    cy.get('input[name="opening_balance"]').clear().type('1000');
    cy.get('textarea[name="note"]').clear().type('Compte de test');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    dbState().then((s) => {
      expect(s.bank, 'compte enregistré').to.not.be.null;
      expect(Number(s.bank.current_balance), 'solde courant').to.eq(1000);
      expect(s.opening, "revenu « Opening Balance »").to.eq(1);
      bankId = s.bank.id;
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/bank-account$/);
    findRow(bankName).should('contain', 'Ecole Cypress').and('contain', '1000').and('contain', 'Compte de test');

    // ─────────────────────────── 3. TRANSACTIONS ───────────────────────────
    rowAction(bankName, 'a.dropdown-item[href*="bank-transaction"]');
    cy.url().should('include', '/bank-transaction/');
    cy.contains(bankName).should('exist');
    // cy.screenshot(`${shot}/03-transactions`);
    cy.visit('/bank-account');

    // ──────────── 4. UPDATE (anomalie 33, corrigée : même n° de compte accepté, solde décalé) ────────────
    rowAction(bankName, 'a.dropdown-item[href*="/bank-account/"]');
    cy.url().should('match', /\/bank-account\/\d+$/);
    cy.get('input[name="account_name"]').should('have.value', 'Ecole Cypress').clear().type('Ecole Cypress M');
    cy.get('input[name="opening_balance"]').clear().type('1200');
    // cy.screenshot(`${shot}/04-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);
    cy.url().should('match', /\/bank-account$/);
    dbState().then((s) => {
      expect(s.bank.account_name).to.eq('Ecole Cypress M');
      expect(Number(s.bank.opening_balance), "solde d'ouverture").to.eq(1200);
      expect(Number(s.bank.current_balance), 'solde courant décalé de +200').to.eq(1200);
      expect(Number(s.openingAmount), "revenu « Opening Balance » mis à jour").to.eq(1200);
    });
    findRow(bankName).should('contain', 'Ecole Cypress M');

    // ─────────────────────────── 5. DELETE ───────────────────────────
    rowAction(bankName, 'a.dropdown-item[data-target="#deleteBankAccountModal"]');
    cy.get('#deleteBankAccountModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    dbState().its('bank').should('be.null');
    shouldBeGone(bankName);
  });
});
