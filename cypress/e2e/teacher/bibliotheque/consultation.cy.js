// cypress/e2e/teacher/bibliotheque/consultation.cy.js
// Rôle Teacher — Bibliothèque : liste des livres.

describe('Teacher · Bibliothèque · consultation', () => {
  const shot = 'teacher/bibliotheque/consultation';
  beforeEach(() => cy.loginAsTeacher());

  it('affiche la liste des livres de la bibliothèque', () => {
    cy.visit('/book-list');
    cy.url().should('include', '/book-list');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-liste-livres`);
  });
});
