// cypress/e2e/receptionist/admissions/consultation.cy.js
// Rôle Receptionist — Admissions : requêtes d'admission.

describe('Receptionist · Admissions · consultation', () => {
  const shot = 'receptionist/admissions/consultation';
  beforeEach(() => cy.loginAsReceptionist());

  it('affiche les requêtes d\'admission', () => {
    cy.visit('/admission-query');
    cy.url().should('include', '/admission-query');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box, form').should('exist');
    // cy.screenshot(`${shot}/01-requetes-admission`);
  });
});
