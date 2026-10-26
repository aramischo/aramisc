// cypress/e2e/teacher/centre_telechargement/consultation.cy.js
// Rôle Teacher — Centre de téléchargement / contenu de cours.

describe('Teacher · Centre de téléchargement · consultation', () => {
  const shot = 'teacher/centre_telechargement/consultation';
  beforeEach(() => cy.loginAsTeacher());

  it('affiche la page de contenu à téléverser', () => {
    cy.visit('/upload-content');
    cy.url().should('include', '/upload-content');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box, form').should('exist');
    // cy.screenshot(`${shot}/01-upload-content`);
  });
});
