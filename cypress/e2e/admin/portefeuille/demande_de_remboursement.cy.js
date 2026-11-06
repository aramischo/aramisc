// cypress/e2e/admin/portefeuille/demande_de_remboursement.cy.js
//
// Scénario utilisateur :
//  0. Préparation : l'élève (student1) dépose un montant par virement, l'admin l'approuve → solde > 0
//  1. Élève : Mon portefeuille > Demande de remboursement → le montant = tout le solde (lecture seule),
//     note → Soumettre
//  2. Admin : Portefeuille > Demande de remboursement → la demande apparaît « En attente »
//  3. Action > Approuver → confirmation → statut « Approuvé » ; le solde de l'élève retombe à 0
//  4. Nettoyage (after) : transactions, notifications et solde remis à l'état de départ, notifications rétablies
//
// Le REJET d'un remboursement n'est pas testé : walletRejectRefund() appelle send_mail() directement
// (hors réglages de notification), ce qui enverrait un vrai e-mail avec la base de test actuelle.
//
// Voir helpers.js : Email/SMS/App des notifications wallet sont coupés pendant le test.
// Captures d'écran : en commentaire pour l'instant.

import { setupWallet, teardownWallet, studentBalance, studentDeposit, adminDecideDeposit, rowByNote } from './helpers';

describe('Admin – Portefeuille : Demande de remboursement', () => {
  const shot = 'admin/portefeuille/demande_de_remboursement';
  const stamp = Date.now();
  const depositNote = `Depot_Rembours_${stamp}`;
  const refundNote = `Remboursement_${stamp}`;
  const amount = 100 + (stamp % 800) === 500 ? 501 : 100 + (stamp % 800);
  let backup;

  before(() => {
    setupWallet().then((b) => {
      backup = b;
    });
  });

  after(() => {
    teardownWallet(backup);
  });

  it("approuve la demande de remboursement d'un élève et remet son solde à zéro", () => {
    // ─────────────── 0. Préparation : solde positif ───────────────
    studentDeposit(amount, depositNote, shot);
    adminDecideDeposit(depositNote, 'approve', null, shot);
    studentBalance().then((balance) => {
      expect(balance, 'solde avant remboursement').to.be.greaterThan(0);

      // ─────────────── 1. Demande de remboursement (élève) ───────────────
      cy.loginAsStudent();
      cy.visit('/wallet/my-wallet');
      cy.get('[data-target="#refundRequest"]').click();
      cy.get('#refundRequest').should('be.visible').within(() => {
        cy.get('input[name="refund_amount"]').should('have.value', balance.toFixed(2));
        cy.get('#refundNote').type(refundNote);
        // cy.screenshot(`${shot}/01-demande-formulaire`);
      });
      cy.intercept('POST', '**/wallet/wallet-refund-request-store').as('refundStore');
      cy.get('form#refundAmount button.submit').click();
      cy.wait('@refundStore').its('response.statusCode').should('eq', 200);
      // cy.screenshot(`${shot}/02-demande-apres-submit`);
    });

    // ─────────────── 2. Demande en attente (admin) ───────────────
    cy.loginAsAdmin();
    cy.visit('/wallet/wallet-refund-request');
    rowByNote(refundNote).find('button.bg-warning').should('exist');
    // cy.screenshot(`${shot}/03-demande-en-attente`);

    // ─────────────── 3. Approbation ───────────────
    rowByNote(refundNote).find('a.dropdown-item[onclick^="approveRefund"]').click({ force: true });
    cy.get('#approveRefundModal').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/04-apres-approbation`);
    cy.url().should('include', '/wallet/wallet-refund-request');
    rowByNote(refundNote).find('button.bg-success').should('exist');
    // cy.screenshot(`${shot}/05-remboursement-approuve`);
    studentBalance().should('eq', 0);
  });
});
