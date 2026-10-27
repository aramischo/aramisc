// cypress/e2e/admin/examination.cy.js

describe('Admin – Examination', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // ─────────────────── MARKS REGISTER ───────────────────────────
  describe('Marks Register', () => {
    it('affiche le registre des notes', () => {
      cy.visit('/marks-register');
      cy.url().should('include', '/marks-register');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_marks_register');
    });
  });

  // NOTE: /online-exam et /online-exam-report (module Online Exam) renvoient 500
  // (anomalie 16, bug applicatif non corrigé — voir NOTES_PASSATION.md).
  // Tests retirés de la suite sur demande.

  // ─────────────────── QUESTION GROUP ───────────────────────────
  describe('Question Group', () => {
    it('affiche la liste des groupes de questions', () => {
      cy.visit('/question-group');
      cy.url().should('include', '/question-group');
      cy.get('table, .card').should('exist');
      cy.screenshot('admin_question_group_list');
    });
  });

  // ─────────────────── QUESTION BANK ────────────────────────────
  describe('Question Bank', () => {
    it('affiche la banque de questions', () => {
      cy.visit('/question-bank');
      cy.url().should('include', '/question-bank');
      cy.get('table, .card').should('exist');
      cy.screenshot('admin_question_bank_list');
    });
  });

  // ─────────────────── EXAM ROUTINE REPORT ──────────────────────
  describe('Exam Routine Report', () => {
    it('affiche le rapport de routine d\'examen', () => {
      cy.visit('/exam-routine-report');
      cy.url().should('include', '/exam-routine-report');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_exam_routine_report');
    });
  });

  // ─────────────────── MERIT LIST ───────────────────────────────
  describe('Merit List Report', () => {
    it('affiche le rapport de liste de mérite', () => {
      cy.visit('/merit-list-report');
      cy.url().should('include', '/merit-list-report');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_merit_list_report');
    });
  });

  // ─────────────────── MARK SHEET REPORT ────────────────────────
  describe('Mark Sheet Report', () => {
    it('affiche la page du bulletin de notes', () => {
      cy.visit('/mark-sheet-report');
      cy.url().should('include', '/mark-sheet-report');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_mark_sheet_report');
    });

    it('affiche le bulletin de notes par élève', () => {
      cy.visit('/mark-sheet-report-student');
      cy.url().should('include', '/mark-sheet-report-student');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_mark_sheet_report_student');
    });
  });

  // ─────────────────── EXAM SETTINGS ────────────────────────────
  describe('Exam Settings', () => {
    it('affiche les paramètres d\'examen', () => {
      cy.visit('/exam-settings');
      cy.url().should('include', '/exam-settings');
      cy.get('form, .card').should('exist');
      cy.screenshot('admin_exam_settings');
    });
  });

  // NOTE: /online-exam-report retiré — voir note Online Exam ci-dessus (anomalie 16, 500).

  // ─────────────────── RESULTS ARCHIVE ──────────────────────────
  describe('Results Archive', () => {
    it('affiche l\'archive des résultats', () => {
      cy.visit('/results-archive');
      cy.url().should('include', '/results-archive');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_results_archive');
    });
  });
});
