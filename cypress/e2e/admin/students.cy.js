// cypress/e2e/admin/students.cy.js

describe('Admin – Student Information', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // ─────────────────── PREVIOUS RECORD ──────────────────────────
  describe('Previous Record', () => {
    it('affiche la page des anciens dossiers', () => {
      cy.visit('/previous-record');
      cy.url().should('include', '/previous-record');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_students_previous_record');
    });
  });

  // ─────────────────── STUDENT ATTENDANCE ───────────────────────
  describe('Student Attendance', () => {
    it('affiche la page de présence des élèves', () => {
      cy.visit('/student-attendance');
      cy.url().should('include', '/student-attendance');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_students_attendance');
    });

    it('affiche le rapport de présence', () => {
      cy.visit('/student-attendance-report');
      cy.url().should('include', '/student-attendance-report');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_students_attendance_report');
    });
  });

  // ─────────────────── SUBJECT ATTENDANCE ───────────────────────
  describe('Subject-wise Attendance', () => {
    it('affiche la page de présence par matière', () => {
      cy.visit('/subject-wise-attendance');
      cy.url().should('include', '/subject-wise-attendance');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_students_subject_attendance');
    });

    it('affiche le rapport de présence par matière', () => {
      cy.visit('/subject-attendance-report');
      cy.url().should('include', '/subject-attendance-report');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_students_subject_attendance_report');
    });
  });
});
