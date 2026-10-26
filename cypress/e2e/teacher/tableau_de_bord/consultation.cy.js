// cypress/e2e/teacher/tableau_de_bord/consultation.cy.js
// Rôle Teacher — Tableau de bord (l'enseignant utilise le back-office admin, restreint par permissions).

describe('Teacher · Tableau de bord · consultation', () => {
  const shot = 'teacher/tableau_de_bord/consultation';
  beforeEach(() => cy.loginAsTeacher());

  it('affiche le tableau de bord de l\'enseignant', () => {
    cy.visit('/admin-dashboard');
    cy.url().should('include', '/admin-dashboard');
    cy.url().should('not.include', '/login');
    cy.get('.card, .white-box, [class*="widget"], h2, h3').should('exist');
    // cy.screenshot(`${shot}/01-tableau-de-bord`);
  });
});
