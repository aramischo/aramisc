// cypress/e2e/student/student_panel.cy.js

describe('Student Panel', () => {
  beforeEach(() => {
    cy.loginAsStudent();
  });

  // ─────────────────── DASHBOARD ────────────────────────────────
  it('affiche le tableau de bord élève', () => {
    cy.visit('/student-dashboard');
    cy.url().should('include', '/student-dashboard');
    cy.get('body').should('be.visible');
    cy.get('[class*="card"], [class*="widget"], h2, h3').should('exist');
    cy.screenshot('student_dashboard');
  });

  // ─────────────────── PROFILE ──────────────────────────────────
  it('affiche le profil de l\'élève', () => {
    cy.visit('/student-profile');
    cy.url().should('include', '/student-profile');
    cy.get('body').should('be.visible');
    cy.screenshot('student_profile');
  });

  // ─────────────────── FEES ─────────────────────────────────────
  it('affiche la page des frais de l\'élève', () => {
    cy.visit('/student-fees');
    cy.url().should('include', '/student-fees');
    cy.get('table, .card').should('exist');
    cy.screenshot('student_fees');
  });

  // ─────────────────── ONLINE EXAM ──────────────────────────────
  it('affiche la liste des examens en ligne disponibles', () => {
    cy.visit('/student-online-exam');
    cy.url().should('include', '/student-online-exam');
    cy.get('body').should('be.visible');
    cy.screenshot('student_online_exam');
  });

  it('affiche les résultats des examens en ligne passés', () => {
    cy.visit('/student_view_result');
    cy.url().should('include', '/student_view_result');
    cy.get('body').should('be.visible');
    cy.screenshot('student_online_exam_result');
  });

  // ─────────────────── ATTENDANCE ───────────────────────────────
  it('affiche la présence de l\'élève', () => {
    cy.visit('/student-my-attendance');
    cy.url().should('include', '/student-my-attendance');
    cy.get('body').should('be.visible');
    cy.screenshot('student_my_attendance');
  });

  // ─────────────────── RESULT ───────────────────────────────────
  it('affiche les résultats de l\'élève', () => {
    cy.visit('/student-result');
    cy.url().should('include', '/student-result');
    cy.get('body').should('be.visible');
    cy.screenshot('student_result');
  });

  // ─────────────────── CLASS ROUTINE ────────────────────────────
  it('affiche l\'emploi du temps de l\'élève', () => {
    cy.visit('/student-class-routine');
    cy.url().should('include', '/student-class-routine');
    cy.get('body').should('be.visible');
    cy.screenshot('student_class_routine');
  });

  // ─────────────────── EXAM SCHEDULE ────────────────────────────
  it('affiche le calendrier des examens de l\'élève', () => {
    cy.visit('/student-exam-schedule');
    cy.url().should('include', '/student-exam-schedule');
    cy.get('body').should('be.visible');
    cy.screenshot('student_exam_schedule');
  });

  // ─────────────────── HOMEWORK ─────────────────────────────────
  it('affiche les devoirs de l\'élève', () => {
    cy.visit('/student-homework');
    cy.url().should('include', '/student-homework');
    cy.get('body').should('be.visible');
    cy.screenshot('student_homework');
  });

  // ─────────────────── DOWNLOAD CENTER ──────────────────────────
  it('affiche la page des devoirs à télécharger', () => {
    cy.visit('/student-assignment');
    cy.url().should('include', '/student-assignment');
    cy.get('body').should('be.visible');
    cy.screenshot('student_assignment');
  });

  it('affiche le matériel pédagogique', () => {
    cy.visit('/student-study-material');
    cy.url().should('include', '/student-study-material');
    cy.get('body').should('be.visible');
    cy.screenshot('student_study_material');
  });

  it('affiche le programme de cours (syllabus)', () => {
    cy.visit('/student-syllabus');
    cy.url().should('include', '/student-syllabus');
    cy.get('body').should('be.visible');
    cy.screenshot('student_syllabus');
  });

  it('affiche les autres téléchargements', () => {
    cy.visit('/student-others-download');
    cy.url().should('include', '/student-others-download');
    cy.get('body').should('be.visible');
    cy.screenshot('student_others_download');
  });

  // ─────────────────── SUBJECT ──────────────────────────────────
  it('affiche les matières de l\'élève', () => {
    cy.visit('/student-subject');
    cy.url().should('include', '/student-subject');
    cy.get('body').should('be.visible');
    cy.screenshot('student_subject');
  });

  // ─────────────────── TRANSPORT ────────────────────────────────
  it('affiche les informations de transport de l\'élève', () => {
    cy.visit('/student-transport');
    cy.url().should('include', '/student-transport');
    cy.get('body').should('be.visible');
    cy.screenshot('student_transport');
  });

  // ─────────────────── DORMITORY ────────────────────────────────
  it('affiche les informations de dortoir de l\'élève', () => {
    cy.visit('/student-dormitory');
    cy.url().should('include', '/student-dormitory');
    cy.get('body').should('be.visible');
    cy.screenshot('student_dormitory');
  });

  // ─────────────────── LIBRARY ──────────────────────────────────
  it('affiche la liste des livres disponibles', () => {
    cy.visit('/student-library');
    cy.url().should('include', '/student-library');
    cy.get('body').should('be.visible');
    cy.screenshot('student_library');
  });

  it('affiche les livres empruntés par l\'élève', () => {
    cy.visit('/student-book-issue');
    cy.url().should('include', '/student-book-issue');
    cy.get('body').should('be.visible');
    cy.screenshot('student_book_issue');
  });

  // ─────────────────── NOTICEBOARD ──────────────────────────────
  it('affiche le tableau d\'affichage de l\'élève', () => {
    cy.visit('/student-noticeboard');
    cy.url().should('include', '/student-noticeboard');
    cy.get('body').should('be.visible');
    cy.screenshot('student_noticeboard');
  });

  // ─────────────────── TEACHER LIST ─────────────────────────────
  it('affiche la liste des enseignants de l\'élève', () => {
    cy.visit('/student-teacher');
    cy.url().should('include', '/student-teacher');
    cy.get('body').should('be.visible');
    cy.screenshot('student_teacher');
  });

  // ─────────────────── APPLY LEAVE ──────────────────────────────
  it('affiche le formulaire de demande de congé élève', () => {
    cy.visit('/student-apply-leave');
    cy.url().should('include', '/student-apply-leave');
    cy.get('form, .card').should('exist');
    cy.screenshot('student_apply_leave');
  });

  it('affiche la liste des congés en attente de l\'élève', () => {
    cy.visit('/student-pending-leave');
    cy.url().should('include', '/student-pending-leave');
    cy.get('table, .card').should('exist');
    cy.screenshot('student_pending_leave');
  });
});
