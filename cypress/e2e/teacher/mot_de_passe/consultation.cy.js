// cypress/e2e/teacher/mot_de_passe/consultation.cy.js
// Rôle Teacher — Changement de mot de passe : affichage du formulaire (sans modifier le mot de passe).

describe('Teacher · Mot de passe · consultation', () => {
  const shot = 'teacher/mot_de_passe/consultation';
  beforeEach(() => cy.loginAsTeacher());

  it('affiche le formulaire de changement de mot de passe', () => {
    cy.visit('/change-password');
    cy.url().should('include', '/change-password');
    cy.url().should('not.include', '/login');
    cy.get('form').should('exist');
    cy.get('input[type="password"]').its('length').should('be.gte', 1);
    // cy.screenshot(`${shot}/01-formulaire`);
  });
});
