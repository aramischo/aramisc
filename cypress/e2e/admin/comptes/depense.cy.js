// cypress/e2e/admin/comptes/depense.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Comptes > Ajouter une dépense : nom, rubrique « Head 1 », moyen « Bank », compte, date, montant 100
//     → la dépense apparaît dans la liste ; le compte passe de 1000 à 900 avec un relevé
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il corrige le montant à 150 → le compte doit valoir 850 (1000 − 150)
//  3. « Sélectionner > Supprimer », confirme → la dépense disparaît, le compte revient à 1000 sans relevé
//
// Compte bancaire de test créé en before() (1000) et supprimé en after() avec dépenses et relevés.
// Date volontairement > 12 (15/09/2026) : cas réel d'un utilisateur FR (format j/m/aaaa). Aucune notification.

import { php, findRow, rowAction, shouldBeGone, BANK_METHOD, createBank, deleteBanks, bankState } from './helpers';

describe('Admin – Comptes : Dépense payée par banque (CRUD + solde du compte)', () => {
  const shot = 'admin/comptes/depense';
  const prefix = 'CyDepenseBanque_';
  const name = `CyDepense_${Date.now()}`;
  const EXPENSE_HEAD = '2'; // Head 1 (type E)
  let bankId;

  before(() => {
    createBank(`${prefix}${Date.now()}`, 1000).then((id) => { bankId = id; });
  });

  after(() => {
    deleteBanks(prefix);
  });

  it('ajoute une dépense par banque, la modifie puis la supprime en tenant le solde du compte à jour', () => {
    cy.loginAsAdmin();
    cy.visit('/add-expense');

    // ──────────── 0. « Bank » sans compte bancaire → refus de validation (anomalie 39, corrigée) ────────────
    cy.get('input[name="name"]').clear().type(name);
    cy.get('select[name="expense_head"]').select(EXPENSE_HEAD, { force: true });
    cy.get('select#payment_method').select(BANK_METHOD, { force: true });
    cy.get('input[name="date"]').invoke('val', '15/09/2026');
    cy.get('input[name="amount"]').clear().type('100');
    cy.get('button.primary-btn.submit').click();
    cy.get('select[name="accounts"].is-invalid', { timeout: 20000 }).should('exist');
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_add_expenses')->where('name', '${name}')->count())).'>>CY';`).should('eq', 0);
    bankState(bankId).should('deep.equal', { balance: 1000, statements: 0 });

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.visit('/add-expense');
    cy.get('input[name="name"]').clear().type(name);
    cy.get('select[name="expense_head"]').select(EXPENSE_HEAD, { force: true });
    cy.get('select#payment_method').select(BANK_METHOD, { force: true });
    cy.get('select[name="accounts"]').select(String(bankId), { force: true });
    cy.get('input[name="date"]').invoke('val', '15/09/2026');
    cy.get('input[name="amount"]').clear().type('100');
    cy.get('textarea[name="description"]').clear().type('Dépense Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    bankState(bankId).should('deep.equal', { balance: 900, statements: 1 });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/add-expense$/);
    // La liste affiche la date au format de la langue (anomalie 34 corrigée via dateConvert)
    findRow(name).should('contain', 'Bank').and('contain', '15/09/2026').and('contain', '100');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="add-expense/"]');

    cy.url().should('match', /\/add-expense\/\d+$/);
    cy.get('input[name="name"]').should('have.value', name);
    cy.get('input[name="amount"]').should('have.value', '100');
    cy.get('input[name="date"]').should('have.value', '15/09/2026');
    cy.get('select[name="accounts"]').should('have.value', String(bankId));

    cy.get('input[name="amount"]').clear().type('150');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    findRow(name).should('contain', '150');
    bankState(bankId).should('deep.equal', { balance: 850, statements: 1 });

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(name, 'a.dropdown-item[onclick^="deleteExpense"]');
    cy.get('#deleteExpenseModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    shouldBeGone(name);
    bankState(bankId).should('deep.equal', { balance: 1000, statements: 0 });
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_add_expenses')->where('name', '${name}')->count())).'>>CY';`).should('eq', 0);
  });
});
