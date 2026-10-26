// cypress/e2e/teacher/teacher_panel.cy.js

describe('Teacher Panel', () => {
  beforeEach(() => {
    cy.loginAsTeacher();
  });

  // ─────────────────── DASHBOARD ────────────────────────────────
  it('affiche le tableau de bord de l\'enseignant', () => {
    cy.visit('/admin-dashboard');
    cy.url().should('include', '/admin-dashboard');
    cy.get('body').should('be.visible');
    cy.get('[class*="card"], [class*="widget"], h2, h3').should('exist');
    cy.screenshot('teacher_dashboard');
  });

  // ─────────────────── STUDENT LIST ─────────────────────────────
  it('affiche la liste des élèves pour l\'enseignant', () => {
    cy.visit('/student-list');
    cy.url().should('include', '/student-list');
    cy.get('body').should('be.visible');
    cy.screenshot('teacher_student_list');
  });

  // ─────────────────── ATTENDANCE ───────────────────────────────
  it('affiche la prise de présence par matière', () => {
    cy.visit('/subject-attendance');
    cy.url().should('include', '/subject-attendance');
    cy.get('body').should('be.visible');
    cy.screenshot('teacher_subject_attendance');
  });

  // ─────────────────── HOMEWORK ─────────────────────────────────
  it('affiche la liste des devoirs créés par l\'enseignant', () => {
    cy.visit('/homework-list');
    cy.url().should('include', '/homework-list');
    cy.get('body').should('be.visible');
    cy.screenshot('teacher_homework_list');
  });

  it('affiche le formulaire d\'ajout de devoir', () => {
    cy.visit('/add-homework');
    cy.url().should('include', '/add-homework');
    cy.get('form, .card').should('exist');
    cy.screenshot('teacher_add_homework');
  });

  // ─────────────────── STUDY MATERIAL / UPLOAD CONTENT ──────────
  it('affiche le centre de téléchargement / cours', () => {
    cy.visit('/upload-content');
    cy.url().should('include', '/upload-content');
    cy.get('body').should('be.visible');
    cy.screenshot('teacher_upload_content');
  });

  // ─────────────────── MARKS REGISTER ───────────────────────────
  it('affiche le registre de saisie des notes', () => {
    cy.visit('/marks-register');
    cy.url().should('include', '/marks-register');
    cy.get('body').should('be.visible');
    cy.screenshot('teacher_marks_register');
  });

  // ─────────────────── CLASS ROUTINE ────────────────────────────
  it('affiche l\'emploi du temps de l\'enseignant', () => {
    cy.visit('/class-routine-new');
    cy.url().should('include', '/class-routine');
    cy.get('body').should('be.visible');
    cy.screenshot('teacher_class_routine');
  });

  // ─────────────────── EXAM SCHEDULE ────────────────────────────
  it('affiche le calendrier des examens', () => {
    cy.visit('/exam-schedule');
    cy.url().should('include', '/exam-schedule');
    cy.get('body').should('be.visible');
    cy.screenshot('teacher_exam_schedule');
  });

  // ─────────────────── NOTICEBOARD ──────────────────────────────
  it('affiche le tableau d\'affichage pour l\'enseignant', () => {
    cy.visit('/notice-board');
    cy.url().should('include', '/notice');
    cy.get('body').should('be.visible');
    cy.screenshot('teacher_noticeboard');
  });

  // ─────────────────── LEAVE MANAGEMENT ─────────────────────────
  it('affiche le formulaire de demande de congé', () => {
    cy.visit('/apply-leave');
    cy.url().should('include', '/apply-leave');
    cy.get('form, .card').should('exist');
    cy.screenshot('teacher_apply_leave');
  });

  it('affiche les demandes de congé en attente', () => {
    cy.visit('/pending-leave');
    cy.url().should('include', '/pending-leave');
    cy.get('body').should('be.visible');
    cy.screenshot('teacher_pending_leave');
  });

  // ─────────────────── EVALUATION REPORT ────────────────────────
  it('affiche le rapport d\'évaluation de l\'enseignant', () => {
    cy.visit('/teacher-panel-evaluation-report');
    cy.url().should('include', '/teacher-panel-evaluation-report');
    cy.get('body').should('be.visible');
    cy.screenshot('teacher_evaluation_report');
  });

  // ─────────────────── LIBRARY ──────────────────────────────────
  it('affiche la liste des livres de la bibliothèque', () => {
    cy.visit('/book-list');
    cy.url().should('include', '/book-list');
    cy.get('body').should('be.visible');
    cy.screenshot('teacher_book_list');
  });

  // ─────────────────── CHANGE PASSWORD ──────────────────────────
  it('affiche la page de changement de mot de passe', () => {
    cy.visit('/change-password');
    cy.url().should('include', '/change-password');
    cy.get('form').should('exist');
    cy.screenshot('teacher_change_password');
  });
});
