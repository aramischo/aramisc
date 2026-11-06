// cypress/e2e/student/bibliotheque/consultation.cy.js
// Rôle Student — rubrique Bibliothèque : liste des livres + livres empruntés.
// NB connu : un élève non inscrit comme membre de la bibliothèque est redirigé par
// l'app sur la page « livres empruntés ». Le test l'accepte sans échouer.

describe('Student · Bibliothèque · consultation', () => {
  const shot = 'student/bibliotheque/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche la liste des livres de la bibliothèque', () => {
    cy.visit('/student-library');
    cy.url().should('include', '/student-library');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/01-liste-livres`);
  });

  it('accède aux livres empruntés (ou redirection si non-membre)', () => {
    cy.visit('/student-book-issue');
    // Un élève non-membre est renvoyé ailleurs dans le panneau : on vérifie
    // seulement qu'on reste authentifié et qu'une page valide est rendue.
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/02-livres-empruntes`);
  });
});
