// cypress/e2e/admin/communicate.cy.js

describe('Admin – Communicate', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // ─────────────────── NEWS ─────────────────────────────────────
  describe('News', () => {
    it('affiche la liste des actualités', () => {
      cy.visit('/news');
      cy.url().should('include', '/news');
      cy.get('table, .card').should('exist');
      cy.screenshot('admin_communicate_news');
    });
  });

  // ─────────────────── SEND SMS / EMAIL ─────────────────────────
  // Anciennes URL /send-sms et /send-email inexistantes (404) → page unique « Envoyer SMS / e-mail » et son journal
  describe('Send SMS', () => {
    it('affiche la page d\'envoi de SMS / e-mail', () => {
      cy.visit('/send-email-sms-view');
      cy.url().should('include', '/send-email-sms-view');
      cy.get('form, .card').should('exist');
      cy.screenshot('admin_communicate_send_sms');
    });
  });

  describe('Send Email', () => {
    it('affiche le journal des e-mails / SMS envoyés', () => {
      cy.visit('/email-sms-log');
      cy.url().should('include', '/email-sms-log');
      cy.get('table').should('exist');
      cy.screenshot('admin_communicate_send_email');
    });
  });

  // ─────────────────── ACADEMIC CALENDAR ────────────────────────
  describe('Academic Calendar', () => {
    it('affiche la vue calendrier académique', () => {
      cy.visit('/academic-calendar');
      cy.url().should('include', '/academic-calendar');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_communicate_academic_calendar');
    });
  });
});
