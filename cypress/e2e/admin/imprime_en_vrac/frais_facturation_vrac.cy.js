// cypress/e2e/admin/imprime_en_vrac/frais_facturation_vrac.cy.js
//
// Scénario utilisateur :
//  1. Imprimé en vrac > Frais de facturation imprimé en vrac : classe « Class1 » → « Imprimer »
//  2. L'aperçu d'impression affiche au moins une facture (bloc « Numéro de facture »)
//
// Pas de CRUD : page « critères → aperçu ». Aucune donnée créée, rien à nettoyer.
// Prérequis base de test : une facture de frais dans la classe « Class1 » pour l'année académique courante.
//
// Captures d'écran : en commentaire pour l'instant.

describe('Admin – Imprimé en vrac : Frais de facturation vrac', () => {
  const shot = 'admin/imprime_en_vrac/frais_facturation_vrac';
  const form = 'form[action$="/bulkprint/fees-invoice-bulk-print-search"]';

  beforeEach(() => {
    cy.loginAsAdmin();
    // Les aperçus lancent window.print() au chargement : on le neutralise
    cy.on('window:before:load', (win) => {
      win.print = () => {};
    });
  });

  it('affiche l\'aperçu des factures de frais d\'une classe', () => {
    cy.visit('/bulkprint/fees-invoice-bulk-print');
    cy.get('select#feesSelectClass option').contains('Class1').then(($o) => {
      cy.get('select#feesSelectClass').select($o.val(), { force: true });
    });
    // cy.screenshot(`${shot}/01-criteres-formulaire`);
    cy.get(form).contains('button[type="submit"]', /imprimer|print/i).click();
    cy.url().should('include', '/bulkprint/fees-invoice-bulk-print-search');
    cy.contains(/numéro de facture|invoice number/i, { timeout: 60000 }).should('exist');
    // cy.screenshot(`${shot}/02-apercu-impression`);
  });
});
