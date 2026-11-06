// cypress/e2e/teacher/conge/demande.cy.js
// Rôle Teacher — Congé : formulaire de demande + liste en attente (routes admin /apply-leave, /pending-leave).

describe('Teacher · Congé · demande', () => {
  const shot = 'teacher/conge/demande';
  beforeEach(() => cy.loginAsTeacher());

  it('affiche le formulaire de demande de congé', () => {
    cy.visit('/apply-leave');
    cy.url().should('include', '/apply-leave');
    cy.url().should('not.include', '/login');
    cy.get('form, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-formulaire`);
  });

  it('affiche les demandes de congé en attente', () => {
    cy.visit('/pending-leave');
    cy.url().should('include', '/pending-leave');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/02-en-attente`);
  });
});
