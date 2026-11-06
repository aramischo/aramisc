// cypress/e2e/admin/reports.cy.js

describe('Admin – Reports', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  it('affiche le rapport global des élèves', () => {
    cy.visit('/student-report');
    cy.url().should('include', '/student-report');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_reports_student_report');
  });

  it('affiche le rapport des tuteurs / parents', () => {
    cy.visit('/guardian-report');
    cy.url().should('include', '/guardian-report');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_reports_guardian_report');
  });

  it('affiche l\'historique des élèves', () => {
    cy.visit('/student-history');
    cy.url().should('include', '/student-history');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_reports_student_history');
  });

  it('affiche le rapport de connexion des élèves', () => {
    cy.visit('/student-login-report');
    cy.url().should('include', '/student-login-report');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_reports_student_login');
  });

  it('affiche le rapport de feuille de tabulation', () => {
    cy.visit('/tabulation-sheet-report');
    cy.url().should('include', '/tabulation-sheet-report');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_reports_tabulation_sheet');
  });

  it('affiche le rapport des bulletins de progrès', () => {
    cy.visit('/progress-card-report');
    cy.url().should('include', '/progress-card-report');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_reports_progress_card');
  });

  it('affiche le rapport de feuille de notes par matière (Subject Mark Sheet)', () => {
    cy.visit('/subject_mark_sheet');
    cy.url().should('include', '/subject_mark_sheet');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_reports_subject_mark_sheet');
  });

  it('affiche le rapport de feuille de notes finale (Final Mark Sheet)', () => {
    cy.visit('/final_mark_sheet');
    cy.url().should('include', '/final_mark_sheet');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_reports_final_mark_sheet');
  });

  it('affiche le bulletin de notes final de l\'élève (Student Final Mark Sheet)', () => {
    cy.visit('/student_mark_sheet_final');
    cy.url().should('include', '/student_mark_sheet_final');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_reports_student_final_mark_sheet');
  });
});
