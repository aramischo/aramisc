// cypress/e2e/admin/imprime_en_vrac/certificat_vrac.cy.js
//
// Scénario utilisateur :
//  1. Imprimé en vrac > Certificat : « Recherche » sans certificat → erreur de validation
//  2. Classe (1re de la liste) + 1er certificat + espacement 10 px → « Recherche »
//     → l'aperçu d'impression affiche un certificat par étudiant de la classe
//
// Pas de CRUD : page « critères → aperçu ». Aucune donnée créée, rien à nettoyer.
// Prérequis base de test : au moins un certificat (/student-certificate) et une classe avec des étudiants.
//
// Captures d'écran : en commentaire pour l'instant.

describe('Admin – Imprimé en vrac : Certificat vrac', () => {
  const shot = 'admin/imprime_en_vrac/certificat_vrac';
  const form = 'form[action$="/bulkprint/certificate-bulk-print"]';

  beforeEach(() => {
    cy.loginAsAdmin();
    // Les aperçus lancent window.print() au chargement : on le neutralise
    cy.on('window:before:load', (win) => {
      win.print = () => {};
    });
  });

  it('valide les critères puis affiche l\'aperçu des certificats d\'une classe', () => {
    // ─────────────── 1. Validation ───────────────
    cy.visit('/bulkprint/certificate-bulk-print');
    cy.get(form).contains('button[type="submit"]', /recherch|search/i).click();
    cy.url().should('include', '/bulkprint/certificate-bulk-print');
    cy.get('select#certificateList').should('have.class', 'is-invalid');
    // cy.screenshot(`${shot}/01-validation`);

    // ─────────────── 2. Recherche → aperçu d'impression ───────────────
    cy.get('select#multipleClass option').first().then(($o) => {
      cy.get('select#multipleClass').select([$o.val()], { force: true });
    });
    cy.get('select#certificateList option').eq(1).then(($o) => {
      cy.get('select#certificateList').select($o.val(), { force: true });
    });
    cy.get(`${form} input[name="grid_gap"]`).clear().type('10');
    // cy.screenshot(`${shot}/02-criteres-formulaire`);
    cy.get(form).contains('button[type="submit"]', /recherch|search/i).click();
    cy.get('.DivBody', { timeout: 60000 }).should('have.length.at.least', 1);
    // cy.screenshot(`${shot}/03-apercu-impression`);
  });
});
