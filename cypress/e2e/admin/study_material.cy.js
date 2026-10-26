// cypress/e2e/admin/study_material.cy.js

describe('Admin – Study Material / Download Center', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  it('affiche la page d\'envoi de contenu / matériel pédagogique', () => {
    cy.visit('/upload-content');
    cy.url().should('include', '/upload-content');
    cy.get('form, .card').should('exist');
    cy.screenshot('admin_study_material_upload');
  });

  it('affiche la liste des devoirs / assignments déposés', () => {
    cy.visit('/assignment-list');
    cy.url().should('include', '/assignment-list');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_study_material_assignment_list');
  });

  it('affiche la liste des supports de cours', () => {
    cy.visit('/study-metarial-list');
    cy.url().should('include', '/study-metarial-list');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_study_material_list');
  });

  it('affiche la liste du programme (syllabus)', () => {
    cy.visit('/syllabus-list');
    cy.url().should('include', '/syllabus-list');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_study_material_syllabus_list');
  });

  it('affiche la liste des autres téléchargements', () => {
    cy.visit('/other-download-list');
    cy.url().should('include', '/other-download-list');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_study_material_other_download_list');
  });
});
