// cypress/e2e/admin/administration/requete_admission.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Administration > Requête d'admission et clique « Ajouter »
//  2. Il remplit la fenêtre (nom, téléphone, e-mail, adresse, description, dates, assigné, référence, source, classe,
//     nombre d'enfants) → la requête apparaît dans la liste
//  3. Il clique « Sélectionner > Modifier » (fenêtre chargée en AJAX), le formulaire est pré-rempli, il modifie → la liste est à jour
//  4. Il clique « Sélectionner > Ajouter une requête » (suivi) : il saisit réponse + note → le suivi apparaît, puis il le supprime
//  5. Il clique « Sélectionner > Supprimer », confirme dans la modale → la requête disparaît (avec ses suivis)
//
// Dates volontairement > 12 (15/09/2026 → 20/09/2026) : cas réel d'un utilisateur FR (format j/m/aaaa).
// Données de la base de test utilisées : référence id 3 (« ref 1 »), source id 1 (« source 1 »), classe id 1 (Class1).
// Aucune notification (AramiscAdmissionQueryController). Le after() supprime requête + suivis s'ils restent.
// Remplace les anciens tests de admission.cy.js (création non vérifiée, suppression vide).

import { php, findRow, rowAction, shouldBeGone } from './helpers';

describe("Admin – Administration : Requête d'admission (CRUD + suivi)", () => {
  const shot = 'admin/administration/requete_admission';
  const prefix = 'CyRequete_';

  after(() => {
    php(`
      $ids = DB::table('aramisc_admission_queries')->where('name', 'like', '${prefix}%')->pluck('id');
      DB::table('aramisc_admission_query_followups')->whereIn('admission_query_id', $ids)->delete();
      DB::table('aramisc_admission_queries')->whereIn('id', $ids)->delete();
    `);
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suivi, Suppression) d'une requête d'admission", () => {
    const name = `${prefix}${Date.now()}`;
    const updatedName = `${name}_Modifie`;

    cy.loginAsAdmin();
    cy.visit('/admission-query');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('[data-target="#addQuery"]').click();
    cy.get('#addQuery.show').should('be.visible').within(() => {
      cy.get('input[name="name"]').type(name);
      cy.get('input[name="phone"]').type('0612121212');
      cy.get('input[name="email"]').type(`${name.toLowerCase()}@exemple.ma`);
      cy.get('textarea[name="address"]').type('5 avenue Hassan II, Rabat');
      cy.get('textarea[name="description"]').type('Demande pour la rentrée');
      cy.get('input[name="date"]').invoke('val', '15/09/2026');
      cy.get('input[name="next_follow_up_date"]').invoke('val', '20/09/2026');
      cy.get('input[name="assigned"]').type('Secrétariat');
      cy.get('select[name="reference"]').select('3', { force: true });
      cy.get('select[name="source"]').select('1', { force: true });
      cy.get('select[name="class"]').select('1', { force: true });
      cy.get('input[name="no_of_child"]').type('2');
    });
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('#save_button_query').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('include', '/admission-query');
    findRow(name).should('contain', '0612121212').and('contain', 'source 1').and('contain', '15/09/2026').and('contain', '20/09/2026');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item.modalLink[href*="admission-query-edit"]');

    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible').within(() => {
      cy.get('input[name="name"]').should('have.value', name);
      cy.get('input[name="phone"]').should('have.value', '0612121212');
      cy.get('select[name="source"]').should('have.value', '1');
      cy.get('input[name="date"]').should('have.value', '15/09/2026');

      cy.get('input[name="name"]').clear().type(updatedName);
      cy.get('input[name="phone"]').clear().type('0634343434');
      cy.get('select[name="source"]').select('2', { force: true });
      cy.get('input[name="no_of_child"]').clear().type('3');
    });
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('#showDetaildModal #update_button_query').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    findRow(updatedName).should('contain', '0634343434').and('contain', 'source 2');

    // ───────────────────── 4. SUIVI (ajout + suppression) ─────────────────────
    rowAction(updatedName, 'a.dropdown-item[href*="add-query"]');

    cy.get('input[name="follow_up_date"]').invoke('val', '16/09/2026');
    cy.get('input[name="next_follow_up_date"]').invoke('val', '25/09/2026');
    cy.get('textarea[name="response"]').type('Rappel effectué, dossier en cours');
    cy.get('textarea[name="note"]').type('Note Cypress');
    // cy.screenshot(`${shot}/05-suivi-formulaire`);
    cy.get('form[action$="query-followup-store"] button.submit').click();
    // cy.screenshot(`${shot}/06-suivi-apres-submit`);

    cy.get('#table_id').contains('tr', 'Rappel effectué, dossier en cours').should('contain', 'Note Cypress');
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_admission_queries')->where('name', '${updatedName}')->value('next_follow_up_date'))).'>>CY';`)
      .should('eq', '2026-09-25');

    cy.get('#table_id').contains('tr', 'Rappel effectué, dossier en cours').within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[data-target^="#deletefollowUpQuery"]').click({ force: true });
    });
    cy.get('.modal.show[id^="deletefollowUpQuery"]').should('be.visible').find('a[href*="delete-follow-up"]').click();
    // cy.screenshot(`${shot}/07-suivi-delete-apres-confirmation`);
    cy.get('#table_id').should('not.contain', 'Rappel effectué, dossier en cours');

    // ─────────────────────────── 5. DELETE ───────────────────────────
    cy.visit('/admission-query');
    rowAction(updatedName, 'a.dropdown-item[data-target="#deleteAdmissionQueryModal"]');
    cy.get('#deleteAdmissionQueryModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/08-delete-apres-confirmation`);

    cy.url().should('include', '/admission-query');
    shouldBeGone(updatedName);
  });
});
