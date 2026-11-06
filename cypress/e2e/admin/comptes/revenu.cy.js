// cypress/e2e/admin/comptes/revenu.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Comptes > Ajouter un revenu : nom, rubrique « Fees Collection », moyen « Bank », compte, date, montant 200
//     → le revenu apparaît dans la liste ; le compte passe de 1000 à 1200 avec un relevé
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il corrige le montant à 300 → le compte doit valoir 1300 (1000 + 300)
//  3. « Sélectionner > Supprimer », confirme → le revenu disparaît, le compte revient à 1000 sans relevé
//
// Compte bancaire de test créé en before() (1000) et supprimé en after() avec revenus et relevés.
// Date volontairement > 12 (15/09/2026) : cas réel d'un utilisateur FR (format j/m/aaaa). Aucune notification.

import { php, findRow, rowAction, shouldBeGone, BANK_METHOD, createBank, deleteBanks, bankState } from './helpers';

describe('Admin – Comptes : Revenu payé par banque (CRUD + solde du compte)', () => {
  const shot = 'admin/comptes/revenu';
  const prefix = 'CyRevenuBanque_';
  const name = `CyRevenu_${Date.now()}`;
  const INCOME_HEAD = '1'; // Fees Collection (type I)
  let bankId;

  before(() => {
    createBank(`${prefix}${Date.now()}`, 1000).then((id) => { bankId = id; });
  });

  after(() => {
    deleteBanks(prefix);
  });

  const fill = (amount) => {
    cy.get('select[name="income_head"]').select(INCOME_HEAD, { force: true });
    cy.get('select#payment_method').select(BANK_METHOD, { force: true });
    cy.get('select[name="accounts"]').select(String(bankId), { force: true });
    cy.get('input[name="date"]').invoke('val', '15/09/2026');
    cy.get('input[name="amount"]').clear().type(String(amount));
  };

  it('ajoute un revenu par banque, le modifie puis le supprime en tenant le solde du compte à jour', () => {
    cy.loginAsAdmin();
    cy.visit('/add-income');

    // ──────────── 0. « Bank » sans compte bancaire → refus de validation (anomalie 39, corrigée) ────────────
    cy.get('input[name="name"]').clear().type(name);
    fill(200);
    cy.get('select[name="accounts"]').select('', { force: true });
    cy.get('input[name="name"]').parents('form').find('button.primary-btn.fix-gr-bg').last().click();
    cy.get('select[name="accounts"].is-invalid', { timeout: 20000 }).should('exist');
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_add_incomes')->where('name', '${name}')->count())).'>>CY';`).should('eq', 0);
    bankState(bankId).should('deep.equal', { balance: 1000, statements: 0 });

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.visit('/add-income');
    cy.get('input[name="name"]').clear().type(name);
    fill(200);
    cy.get('textarea[name="description"]').clear().type('Revenu Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('input[name="name"]').parents('form').find('button.primary-btn.fix-gr-bg').last().click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    bankState(bankId).should('deep.equal', { balance: 1200, statements: 1 });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/add-income$/);
    // La liste affiche la date au format de la langue (anomalie 34 corrigée via dateConvert)
    findRow(name).should('contain', 'Bank').and('contain', '15/09/2026').and('contain', '200');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="add-income-edit"]');

    cy.url().should('include', '/add-income-edit/');
    cy.get('input[name="name"]').should('have.value', name);
    cy.get('input[name="amount"]').should('have.value', '200');
    cy.get('input[name="date"]').should('have.value', '15/09/2026');
    cy.get('select[name="accounts"]').should('have.value', String(bankId));

    cy.get('input[name="amount"]').clear().type('300');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('input[name="name"]').parents('form').find('button.primary-btn.fix-gr-bg').last().click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    findRow(name).should('contain', '300');
    bankState(bankId).should('deep.equal', { balance: 1300, statements: 1 });

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(name, 'a.dropdown-item[onclick^="deleteIncome"]');
    cy.get('#deleteIncomeModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    shouldBeGone(name);
    bankState(bankId).should('deep.equal', { balance: 1000, statements: 0 });
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_add_incomes')->where('name', '${name}')->count())).'>>CY';`).should('eq', 0);
  });
});
