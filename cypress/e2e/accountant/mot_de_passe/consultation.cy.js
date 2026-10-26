// cypress/e2e/accountant/mot_de_passe/consultation.cy.js
// Rôle Accountant — Changement de mot de passe : affichage du formulaire (sans modifier).

describe('Accountant · Mot de passe · consultation', () => {
  const shot = 'accountant/mot_de_passe/consultation';
  beforeEach(() => cy.loginAsAccountant());

  it('affiche le formulaire de changement de mot de passe', () => {
    cy.visit('/change-password');
    cy.url().should('include', '/change-password');
    cy.url().should('not.include', '/login');
    cy.get('form').should('exist');
    cy.get('input[type="password"]').its('length').should('be.gte', 1);
    // cy.screenshot(`${shot}/01-formulaire`);
  });
});
