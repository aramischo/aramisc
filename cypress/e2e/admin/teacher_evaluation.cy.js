// cypress/e2e/admin/teacher_evaluation.cy.js
// Tests for Teacher Evaluation Settings & Reports

describe('Admin – Teacher Evaluation', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // ─────────────────── SETTINGS ─────────────────────────────────
  describe('Teacher Evaluation Settings', () => {
    it('affiche la page de configuration de l\'évaluation enseignant', () => {
      cy.visit('/teacher-evaluation-setting');
      cy.url().should('include', '/teacher-evaluation-setting');
      cy.get('form, .card').should('exist');
      cy.screenshot('admin_teacher_evaluation_setting');
    });
  });

  // ─────────────────── APPROVED EVALUATIONS ─────────────────────
  describe('Approved Evaluation Report', () => {
    it('affiche le rapport des évaluations approuvées', () => {
      cy.visit('/teacher-approved-evaluation-report');
      cy.url().should('include', '/teacher-approved-evaluation-report');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_teacher_approved_evaluation');
    });
  });

  // ─────────────────── PENDING EVALUATIONS ──────────────────────
  describe('Pending Evaluation Report', () => {
    it('affiche le rapport des évaluations en attente', () => {
      cy.visit('/teacher-pending-evaluation-report');
      cy.url().should('include', '/teacher-pending-evaluation-report');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_teacher_pending_evaluation');
    });
  });

  // ─────────────────── TEACHER-WISE REPORT ──────────────────────
  describe('Teacher-Wise Evaluation Report', () => {
    it('affiche le rapport par enseignant', () => {
      cy.visit('/teacher-wise-evaluation-report');
      cy.url().should('include', '/teacher-wise-evaluation-report');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_teacher_wise_evaluation');
    });
  });

  // ─────────────────── TEACHER PANEL REPORT ─────────────────────
  describe('Teacher Panel Evaluation Report', () => {
    it('affiche le rapport du panel des enseignants', () => {
      cy.visit('/teacher-panel-evaluation-report');
      cy.url().should('include', '/teacher-panel-evaluation-report');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_teacher_panel_evaluation');
    });
  });
});
