// cypress/e2e/student/devoirs/consultation.cy.js
// Rôle Student — rubrique Devoirs : consultation des devoirs assignés.
// L'ajout de contenu (upload) dépend d'un devoir assigné à la classe de l'élève ;
// couvert séparément quand les données s'y prêtent.

describe('Student · Devoirs · consultation', () => {
  const shot = 'student/devoirs/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche la liste des devoirs de l\'élève', () => {
    cy.visit('/student-homework');
    cy.url().should('include', '/student-homework');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-devoirs`);
  });
});
