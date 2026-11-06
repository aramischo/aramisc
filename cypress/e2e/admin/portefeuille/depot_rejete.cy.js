// cypress/e2e/admin/portefeuille/depot_rejete.cy.js
//
// Scénario utilisateur :
//  1. Élève (student1) : Mon portefeuille > Ajouter un solde → montant, méthode « Bank », banque, note → Soumettre
//  2. Admin : Portefeuille > Dépôt en attente → Action > Rejeter → note de rejet → Soumettre
//     → le dépôt disparaît des dépôts en attente
//  3. Rejeter le dépôt : il apparaît avec le statut « Rejeté » ; « Voir » la note de rejet l'affiche
//  4. Le solde de l'élève est inchangé
//  5. Nettoyage (after) : transactions, notifications et solde remis à l'état de départ, notifications rétablies
//
// Voir helpers.js : Email/SMS/App des notifications wallet sont coupés pendant le test.
// Captures d'écran : en commentaire pour l'instant.

import { setupWallet, teardownWallet, studentBalance, studentDeposit, adminDecideDeposit, rowByNote } from './helpers';

describe('Admin – Portefeuille : Dépôt en attente → Rejeter le dépôt', () => {
  const shot = 'admin/portefeuille/depot_rejete';
  const stamp = Date.now();
  const note = `Depot_Rejete_${stamp}`;
  const rejectNote = `Rejet_${stamp}`;
  const amount = 100 + (stamp % 800) === 500 ? 501 : 100 + (stamp % 800);
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

  it("rejette le dépôt d'un élève avec une note et le retrouve dans les dépôts rejetés", () => {
    // ─────────────── 1. Dépôt par l'élève ───────────────
    studentDeposit(amount, note, shot);

    // ─────────────── 2. Rejet ───────────────
    adminDecideDeposit(note, 'reject', rejectNote, shot);
    // cy.screenshot(`${shot}/01-apres-rejet`);

    // ─────────────── 3. Rejeter le dépôt ───────────────
    cy.visit('/wallet/reject-diposit');
    rowByNote(note).within(() => {
      cy.get('button.bg-danger').should('exist');
      cy.get(`[data-rejectnote="${rejectNote}"]`).click({ force: true });
    });
    cy.get('#rejectNote').should('be.visible').and('contain', rejectNote);
    // cy.screenshot(`${shot}/02-note-de-rejet`);

    // ─────────────── 4. Solde inchangé ───────────────
    studentBalance().should('eq', initialBalance);
  });
});
