// cypress/e2e/admin/imprime_en_vrac/paie_vrac.cy.js
//
// Scénario utilisateur :
//  1. Imprimé en vrac > Impression en vrac de paie : rôle Teacher, janvier 2000 → « Recherche »
//     → aucune paie : message « Not Found ! Generate Payroll » et on reste sur la page
//  2. Rôle Teacher, mai 2025 → « Recherche » → l'aperçu affiche les fiches de paie de la période
//
// Pas de CRUD : page « critères → aperçu ». Aucune donnée créée, rien à nettoyer.
// Prérequis base de test : une paie générée pour un enseignant en mai 2025 (présente dans la copie de la prod).
//
// Captures d'écran : en commentaire pour l'instant.

describe('Admin – Imprimé en vrac : Paie vrac', () => {
  const shot = 'admin/imprime_en_vrac/paie_vrac';
  const form = 'form[action$="/bulkprint/payroll-bulk-print"]';
  const TEACHER_ROLE = '4';

  const search = (month, year) => {
    cy.visit('/bulkprint/payroll-bulk-print');
    cy.get('select#role_id').select(TEACHER_ROLE, { force: true });
    cy.get('select#payroll_month').select(month, { force: true });
    cy.get('select#payroll_year').select(year, { force: true });
    // cy.screenshot(`${shot}/criteres-${month}-${year}`);
    cy.get(form).contains('button[type="submit"]', /recherch|search/i).click();
  };

  beforeEach(() => {
    cy.loginAsAdmin();
    // Les aperçus lancent window.print() au chargement : on le neutralise
    cy.on('window:before:load', (win) => {
      win.print = () => {};
    });
  });

  it('signale une période sans paie puis affiche les fiches de paie d\'une période existante', () => {
    // ─────────────── 1. Période sans paie ───────────────
    search('January', '2000');
    cy.url().should('include', '/bulkprint/payroll-bulk-print');
    cy.get('.toast-message').should('contain', 'Not Found');
    // cy.screenshot(`${shot}/01-aucune-paie`);

    // ─────────────── 2. Période avec paie → aperçu d'impression ───────────────
    search('May', '2025');
    cy.contains(/payslip pour la période de|payslip for the period of/i, { timeout: 60000 })
      .should('contain', 'May')
      .and('contain', '2025');
    // cy.screenshot(`${shot}/02-apercu-impression`);
  });
});
