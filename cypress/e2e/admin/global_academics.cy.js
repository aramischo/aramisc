// cypress/e2e/admin/global_academics.cy.js
// Tests for Super-Admin / Global Academic Management routes

describe('Admin – Global Academics (Multi-School)', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // ─────────────────── GLOBAL SECTION ───────────────────────────
  describe('Global Section', () => {
    it('affiche la liste des sections globales', () => {
      cy.visit('/global-section');
      cy.url().should('include', '/global-section');
      cy.get('table, .card').should('exist');
      cy.screenshot('admin_global_section_list');
    });
  });

  // ─────────────────── GLOBAL CLASS ─────────────────────────────
  describe('Global Class', () => {
    it('affiche la liste des classes globales', () => {
      cy.visit('/global-class');
      cy.url().should('include', '/global-class');
      cy.get('table, .card').should('exist');
      cy.screenshot('admin_global_class_list');
    });
  });

  // ─────────────────── GLOBAL SUBJECT ───────────────────────────
  describe('Global Subject', () => {
    it('affiche la liste des matières globales', () => {
      cy.visit('/global-subject');
      cy.url().should('include', '/global-subject');
      cy.get('table, .card').should('exist');
      cy.screenshot('admin_global_subject_list');
    });
  });

  // ─────────────────── GLOBAL ASSIGN SUBJECT ────────────────────
  describe('Global Assign Subject', () => {
    it('affiche la page d\'affectation de matières globales', () => {
      cy.visit('/global-assign-subject');
      cy.url().should('include', '/global-assign-subject');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_global_assign_subject');
    });

    it('affiche la page de création d\'affectation globale', () => {
      cy.visit('/global-assign-subject-create');
      cy.url().should('include', '/global-assign-subject-create');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_global_assign_subject_create');
    });
  });

  // ─────────────────── GLOBAL UPLOAD CONTENT (Study Material) ───
  describe('Global Upload Content', () => {
    it('affiche la liste du contenu pédagogique global', () => {
      cy.visit('/global-upload-content');
      cy.url().should('include', '/global-upload-content');
      cy.get('table, .card').should('exist');
      cy.screenshot('admin_global_upload_content_list');
    });
  });

  // ─────────────────── GLOBAL EXAM TYPE ─────────────────────────
  describe('Global Exam Type', () => {
    it('affiche la liste des types d\'examen globaux', () => {
      cy.visit('/global-exam-type');
      cy.url().should('include', '/global-exam-type');
      cy.get('table, .card').should('exist');
      cy.screenshot('admin_global_exam_type_list');
    });
  });

  // ─────────────────── GLOBAL EXAM ──────────────────────────────
  describe('Global Exam', () => {
    it('affiche la liste des examens globaux', () => {
      cy.visit('/global-exam');
      cy.url().should('include', '/global-exam');
      cy.get('table, .card').should('exist');
      cy.screenshot('admin_global_exam_list');
    });

    it('affiche le planning d\'examen global', () => {
      cy.visit('/return_global_exam_view');
      cy.url().should('include', '/return_global_exam_view');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_global_exam_view');
    });
  });

  // ─────────────────── GLOBAL ASSIGN (Subjects to Exam) ─────────
  describe('Global Assign', () => {
    it('affiche la page d\'affectation globale (matières → examen)', () => {
      cy.visit('/global-assign');
      cy.url().should('include', '/global-assign');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_global_assign');
    });
  });
});
