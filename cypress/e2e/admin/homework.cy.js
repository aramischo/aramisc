// cypress/e2e/admin/homework.cy.js

describe('Admin – Homework', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // Liste / ajout / modification / suppression d'un devoir : voir devoirs/devoir.cy.js

  it('affiche le rapport d\'évaluation des devoirs', () => {
    cy.visit('/evaluation-report');
    cy.url().should('include', '/evaluation-report');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_homework_evaluation_report');
  });

  it('affiche le rapport synthétique des devoirs', () => {
    cy.visit('/homework-report');
    cy.url().should('include', '/homework-report');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_homework_report');
  });
});
