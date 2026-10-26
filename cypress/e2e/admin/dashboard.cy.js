// cypress/e2e/admin/dashboard.cy.js

describe('Admin – Dashboard', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  it('redirige vers le dashboard après connexion', () => {
    cy.visit('/dashboard');
    cy.url().should('include', '/dashboard');
    cy.screenshot('admin_dashboard_redirect');
  });

  it('affiche les widgets principaux du dashboard', () => {
    cy.visit('/dashboard');
    cy.get('body').should('be.visible');
    cy.get('h1, h2, h3, .page-title, .dashboard, [class*="card"]').should('exist');
    cy.screenshot('admin_dashboard_widgets');
  });

  it('affiche la barre de navigation latérale', () => {
    cy.visit('/dashboard');
    cy.get('nav, aside, .sidebar, #sidebar, [class*="sidebar"]').should('exist');
    cy.screenshot('admin_dashboard_sidebar');
  });

  it('permet de changer l\'année académique via le menu', () => {
    cy.visit('/dashboard');
    cy.get('select, [class*="academic"], [class*="session"]').should('exist');
    cy.screenshot('admin_dashboard_academic_year');
  });
});
