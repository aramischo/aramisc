// cypress/e2e/admin/fees.cy.js

describe('Admin – Fees Collection', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // ─────────────────── COLLECT FEES ─────────────────────────────
  describe('Collect Fees', () => {
    it('affiche la page de collecte des frais', () => {
      cy.visit('/collect-fees');
      cy.url().should('include', '/collect-fees');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_collect_fees');
    });
  });

  // ─────────────────── SEARCH FEES PAYMENT ──────────────────────
  describe('Search Fees Payment', () => {
    it('affiche la page de recherche de paiements', () => {
      cy.visit('/search-fees-payment');
      cy.url().should('include', '/search-fees-payment');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_search_fees_payment');
    });
  });

  // ─────────────────── FEES DUE ─────────────────────────────────
  describe('Search Fees Due', () => {
    it('affiche la page des frais impayés', () => {
      cy.visit('/search-fees-due');
      cy.url().should('include', '/search-fees-due');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_search_fees_due');
    });
  });

  // ─────────────────── FEES STATEMENT ───────────────────────────
  describe('Fees Statement', () => {
    it('affiche le relevé de frais', () => {
      cy.visit('/fees-statement');
      cy.url().should('include', '/fees-statement');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_fees_statement');
    });
  });

  // ─────────────────── BALANCE FEES REPORT ──────────────────────
  describe('Balance Fees Report', () => {
    it('affiche le rapport de solde des frais', () => {
      cy.visit('/balance-fees-report');
      cy.url().should('include', '/balance-fees-report');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_balance_fees_report');
    });
  });

  // ─────────────────── TRANSACTION REPORT ───────────────────────
  describe('Transaction Report', () => {
    it('affiche le rapport des transactions', () => {
      cy.visit('/transaction-report');
      cy.url().should('include', '/transaction-report');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_transaction_report');
    });
  });

  // ─────────────────── BANK PAYMENT SLIP ────────────────────────
  describe('Bank Payment Slip', () => {
    it('affiche la page des virements bancaires', () => {
      cy.visit('/bank-payment-slip');
      cy.url().should('include', '/bank-payment-slip');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_bank_payment_slip');
    });
  });

  // ─────────────────── FEES CARRY FORWARD ───────────────────────
  describe('Fees Carry Forward', () => {
    it('affiche la page de report des frais', () => {
      cy.visit('/fees-forward');
      cy.url().should('include', '/fees-forward');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_fees_forward');
    });

    it('affiche la configuration du report de frais (Carry Forward Settings)', () => {
      cy.visit('/fees-carry-forward-settings-view');
      cy.url().should('include', '/fees-carry-forward-settings-view');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_fees_carry_forward_settings');
    });

    it('affiche la recherche de report de frais (Carry Forward View)', () => {
      cy.visit('/fees-carry-forward-view');
      cy.url().should('include', '/fees-carry-forward-view');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_fees_carry_forward_view');
    });

    it('affiche le journal de report de frais (Carry Forward Log)', () => {
      cy.visit('/fees-carry-forward-log-view');
      cy.url().should('include', '/fees-carry-forward-log-view');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_fees_carry_forward_log');
    });
  });

  // ─────────────────── FINE REPORT ──────────────────────────────
  describe('Fine Report', () => {
    it('affiche le rapport des amendes', () => {
      cy.visit('/fine-report');
      cy.url().should('include', '/fine-report');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_fine_report');
    });
  });
});
