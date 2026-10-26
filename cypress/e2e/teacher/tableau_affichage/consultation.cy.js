// cypress/e2e/teacher/tableau_affichage/consultation.cy.js
// Rôle Teacher — Tableau d'affichage (route /notice-list ; /notice-board n'existe pas → 404).

describe('Teacher · Tableau d\'affichage · consultation', () => {
  const shot = 'teacher/tableau_affichage/consultation';
  beforeEach(() => cy.loginAsTeacher());

  it('affiche la liste des annonces', () => {
    cy.visit('/notice-list');
    cy.url().should('include', '/notice-list');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-annonces`);
  });
});
