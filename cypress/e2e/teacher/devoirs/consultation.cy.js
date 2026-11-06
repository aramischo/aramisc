// cypress/e2e/teacher/devoirs/consultation.cy.js
// Rôle Teacher — Devoirs : liste + formulaire d'ajout.
// Route d'ajout réelle : /add-homeworks (pluriel ; /add-homework singulier → 404).

describe('Teacher · Devoirs · consultation', () => {
  const shot = 'teacher/devoirs/consultation';
  beforeEach(() => cy.loginAsTeacher());

  it('affiche la liste des devoirs', () => {
    cy.visit('/homework-list');
    cy.url().should('include', '/homework-list');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-liste-devoirs`);
  });

  it('affiche le formulaire d\'ajout de devoir', () => {
    cy.visit('/add-homeworks');
    cy.url().should('include', '/add-homeworks');
    cy.url().should('not.include', '/login');
    cy.get('form, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/02-ajout-devoir`);
  });
});
