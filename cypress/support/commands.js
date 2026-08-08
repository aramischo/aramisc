// cypress/support/commands.js

/**
 * Connecte un utilisateur via le vrai formulaire de login (UI),
 * et met en cache la session pour éviter de se reconnecter à chaque test.
 *
 * cy.login('admin@aramisc.com', 'password');
 */
Cypress.Commands.add('login', (email, password) => {
  cy.session(
    [email, password],
    () => {
      cy.visit('/login');
      cy.get('input[name="email"]').type(email);
      cy.get('input[name="password"]').type(password);
      cy.get('form').submit();

      // Vérifie qu'on n'est plus sur la page de login = connexion réussie
      cy.url().should('not.include', '/login');
    },
    {
      cacheAcrossSpecs: true,
    }
  );
});

/**
 * Exécute une commande artisan arbitraire depuis un test.
 *
 * cy.artisan('db:seed --class=SchoolSeeder');
 */
Cypress.Commands.add('artisan', (command) => {
  return cy.task('artisan', command, { timeout: 30000 });
});

/**
 * Login helper — Admin.
 * Credentials pulled from cypress/fixtures/users.json
 */
Cypress.Commands.add('loginAsAdmin', () => {
  cy.fixture('users').then((u) => {
    cy.login(u.admin.email, u.admin.password);
  });
});

/**
 * Login helper — Student.
 * Update fixtures/users.json with real student credentials.
 */
Cypress.Commands.add('loginAsStudent', () => {
  cy.fixture('users').then((u) => {
    cy.login(u.student.email, u.student.password);
  });
});

/**
 * Login helper — Parent.
 * Update fixtures/users.json with real parent credentials.
 */
Cypress.Commands.add('loginAsParent', () => {
  cy.fixture('users').then((u) => {
    cy.login(u.parent.email, u.parent.password);
  });
});

/**
 * Login helper — Teacher.
 * Update fixtures/users.json with real teacher credentials.
 */
Cypress.Commands.add('loginAsTeacher', () => {
  cy.fixture('users').then((u) => {
    cy.login(u.teacher.email, u.teacher.password);
  });
});

/**
 * Assert a success toast/alert is visible.
 * Adjust the selector to match your theme's alert class.
 */
Cypress.Commands.add('assertSuccess', () => {
  cy.get('.alert-success, .toast-success, [class*="success"]', { timeout: 10000 })
    .should('be.visible');
});
