// cypress/e2e/admin/portefeuille/depot_approuve.cy.js
//
// Scénario utilisateur :
//  1. Élève (student1) : Mon portefeuille > Ajouter un solde → montant, méthode « Bank », banque, note → Soumettre
//  2. Admin : Portefeuille > Dépôt en attente → le dépôt apparaît (note horodatée)
//  3. Action > Approuver → confirmation → il disparaît des dépôts en attente
//  4. Approuver le dépôt : il apparaît avec le statut « Approuvé » ; le solde de l'élève a augmenté du montant
//  5. Transaction de portefeuille : la transaction apparaît avec le montant approuvé
//  6. Nettoyage (after) : transactions, notifications et solde remis à l'état de départ, notifications rétablies
//
// Voir helpers.js : Email/SMS/App des notifications wallet sont coupés pendant le test.
// Captures d'écran : en commentaire pour l'instant.

import { setupWallet, teardownWallet, studentBalance, studentDeposit, adminDecideDeposit, rowByNote } from './helpers';

describe('Admin – Portefeuille : Dépôt en attente → Approuver le dépôt → Transaction', () => {
  const shot = 'admin/portefeuille/depot_approuve';
  const stamp = Date.now();
  const note = `Depot_Approuve_${stamp}`;
  const amount = 100 + (stamp % 800) === 500 ? 501 : 100 + (stamp % 800); // montant entier ≠ du dépôt existant (500)
  let backup;
  let initialBalance;

  before(() => {
    setupWallet().then((b) => {
      backup = b;
      initialBalance = Number(b.balance);
    });
  });

  after(() => {
    teardownWallet(backup);
  });

  it("approuve le dépôt d'un élève et le retrouve dans les dépôts approuvés et les transactions", () => {
    // ─────────────── 1. Dépôt par l'élève ───────────────
    studentDeposit(amount, note, shot);

    // ─────────────── 2. Dépôt en attente ───────────────
    cy.loginAsAdmin();
    cy.visit('/wallet/pending-diposit');
    rowByNote(note).should('contain', 'Bank');
    // cy.screenshot(`${shot}/01-depot-en-attente`);

    // ─────────────── 3. Approbation ───────────────
    adminDecideDeposit(note, 'approve', null, shot);
    // cy.screenshot(`${shot}/02-apres-approbation`);

    // ─────────────── 4. Approuver le dépôt + solde ───────────────
    cy.visit('/wallet/approve-diposit');
    rowByNote(note).find('button.bg-success').should('exist');
    // cy.screenshot(`${shot}/03-depot-approuve`);
    studentBalance().should('eq', initialBalance + amount);

    // ─────────────── 5. Transaction de portefeuille ───────────────
    cy.visit('/wallet/wallet-transaction');
    // Colonne « Approuver » : « Dhs 137 » (symbole + montant brut)
    cy.contains('#table_id td', new RegExp(`\\s${amount}(\\.00)?\\s*$`), { timeout: 20000 }).should('exist');
    // cy.screenshot(`${shot}/04-transaction`);
  });
});
