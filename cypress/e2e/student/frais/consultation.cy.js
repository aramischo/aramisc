// cypress/e2e/student/frais/consultation.cy.js
// Rôle Student — rubrique Frais : consultation de l'état des frais (lecture seule).
// Le paiement réel (PayPal / Stripe / virement) n'est volontairement PAS déclenché.

describe('Student · Frais · consultation', () => {
  const shot = 'student/frais/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche l\'état des frais de l\'élève', () => {
    cy.visit('/student-fees');
    cy.url().should('include', '/student-fees');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-etat-frais`);
  });
});
