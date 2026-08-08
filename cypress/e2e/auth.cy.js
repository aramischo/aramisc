// cypress/e2e/auth.cy.js

describe('Authentification', () => {
  beforeEach(() => {
    // Aucun reset : la base de test est la copie de la prod, on l'utilise telle quelle.
  });

  it("affiche la page de connexion", () => {
    cy.visit('/login');
    cy.get('input[name="email"]').should('be.visible');
    cy.get('input[name="password"]').should('be.visible');
    cy.screenshot('login_page');
  });

  it('refuse un mauvais mot de passe', () => {
    cy.visit('/login');
    cy.get('input[name="email"]').type('admin@aramisc.com');
    cy.get('input[name="password"]').type('mauvais-mot-de-passe');
    cy.get('form').submit();

    cy.url().should('include', '/login');
    cy.screenshot('login_failed');
  });

  it('permet à un utilisateur valide de se connecter', () => {
    cy.login('admin@aramisc.com', 'hachak1984');

    cy.visit('/dashboard');
    cy.url().should('include', '/dashboard');
    cy.screenshot('login_success_dashboard');
  });
});
