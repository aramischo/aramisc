// cypress/e2e/admin/accounts.cy.js

describe('Admin – Accounts', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // ─────────────────── TRANSACTION ──────────────────────────────
  describe('Transaction', () => {
    it('affiche la liste des transactions', () => {
      cy.visit('/transaction');
      cy.url().should('include', '/transaction');
      // Le tableau n'apparaît qu'après une recherche : on contrôle le formulaire de recherche
      cy.get('form[action$="transaction-search"]').should('exist');
      cy.screenshot('admin_accounts_transaction_list');
    });
  });

  // ─────────────────── SEARCH ACCOUNT ───────────────────────────
  describe('Search Account', () => {
    it('affiche la page de recherche de compte', () => {
      cy.visit('/search-account');
      cy.url().should('include', '/search-account');
      cy.get('form, .card').should('exist');
      cy.screenshot('admin_accounts_search_account');
    });
  });

  // ─────────────────── PROFIT ───────────────────────────────────
  describe('Profit', () => {
    it('affiche la page de profit', () => {
      cy.visit('/profit');
      cy.url().should('include', '/profit');
      cy.get('form, .card').should('exist');
      cy.screenshot('admin_accounts_profit');
    });
  });

  // ─────────────────── ACCOUNTS PAYROLL REPORT ──────────────────
  describe('Accounts Payroll Report', () => {
    it('affiche le rapport de paie comptable', () => {
      cy.visit('/accounts-payroll-report');
      cy.url().should('include', '/accounts-payroll-report');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_accounts_payroll_report');
    });
  });
});
