// cypress/e2e/teacher/presence_matiere/consultation.cy.js
// Rôle Teacher — Présence par matière : page de saisie/consultation.
// Route réelle : /subject-wise-attendance (et non /subject-attendance, qui n'existe pas → 404).

describe('Teacher · Présence par matière · consultation', () => {
  const shot = 'teacher/presence_matiere/consultation';
  beforeEach(() => cy.loginAsTeacher());

  it('affiche la page de présence par matière', () => {
    cy.visit('/subject-wise-attendance');
    cy.url().should('include', '/subject-wise-attendance');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box, form').should('exist');
    // cy.screenshot(`${shot}/01-presence-matiere`);
  });
});
