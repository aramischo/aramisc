// cypress/e2e/admin/comptes/transfert_fonds.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Comptes > Transfert de fonds : montant 200, motif, de « Bank » (compte A) vers « Bank » (compte B)
//  2. Le transfert apparaît dans la liste (motif, montant, Bank → Bank) ; A passe de 1000 à 800, B de 500 à 700,
//     un relevé sur chaque compte, une dépense et un revenu « Fund Transfer »
//
// La page ne propose ni modification ni suppression d'un transfert : création + lecture uniquement.
// Comptes A et B créés en before() et supprimés en after() avec transfert, relevés, revenu et dépense liés.
// ⚠ Notification Fund_Transfer : muteMessaging() coupe Email/SMS/App (et réglages e-mail / SMS), restoreMessaging() rétablit.

import { php, BANK_METHOD, createBank, deleteBanks, bankState } from './helpers';
import { muteMessaging, restoreMessaging } from '../administration/helpers';

describe('Admin – Comptes : Transfert de fonds entre deux comptes bancaires', () => {
  const shot = 'admin/comptes/transfert_fonds';
  const prefix = 'CyTransfertBanque_';
  const purpose = `CyTransfert_${Date.now()}`;
  let fromId;
  let toId;
  let backup;

  before(() => {
    muteMessaging(['Fund_Transfer'], ['admin@aramisc.com']).then((b) => { backup = b; });
    createBank(`${prefix}A_${Date.now()}`, 1000).then((id) => { fromId = id; });
    createBank(`${prefix}B_${Date.now()}`, 500).then((id) => { toId = id; });
  });

  after(() => {
    deleteBanks(prefix);
    restoreMessaging(backup);
  });

  it("transfère 200 d'un compte bancaire à un autre et met les deux soldes à jour", () => {
    cy.loginAsAdmin();
    cy.visit('/fund-transfer');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="amount"]').clear().type('200');
    cy.get('input[name="purpose"]').clear().type(purpose);
    cy.get(`#from_method${BANK_METHOD}`).check({ force: true });
    cy.get(`#from_bank${fromId}`).check({ force: true });
    cy.get(`#to_method${BANK_METHOD}`).check({ force: true });
    cy.get(`#tobank${toId}`).check({ force: true });
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('form[action$="fund-transfer-store"] button.primary-btn').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/fund-transfer$/);
    cy.get('#tableWithoutSort').contains('tr', purpose).should('contain', '200').and('contain', 'Bank');

    bankState(fromId).should('deep.equal', { balance: 800, statements: 1 });
    bankState(toId).should('deep.equal', { balance: 700, statements: 1 });
    php(`echo 'CY<<'.base64_encode(json_encode([
      'transfer' => DB::table('aramisc_amount_transfers')->where('purpose', '${purpose}')->count(),
      'expense' => DB::table('aramisc_add_expenses')->where('name', 'Fund Transfer')->where('account_id', ${fromId})->sum('amount'),
      'income' => DB::table('aramisc_add_incomes')->where('name', 'Fund Transfer')->where('account_id', ${toId})->sum('amount'),
    ])).'>>CY';`).then((r) => {
      expect(r.transfer, 'transfert enregistré').to.eq(1);
      expect(Number(r.expense), 'dépense « Fund Transfer » sur A').to.eq(200);
      expect(Number(r.income), 'revenu « Fund Transfer » sur B').to.eq(200);
    });
  });
});
