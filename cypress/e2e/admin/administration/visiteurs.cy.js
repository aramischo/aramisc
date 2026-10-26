// cypress/e2e/admin/administration/visiteurs.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Administration > Carnet de visiteurs
//  2. Il remplit le formulaire (motif, nom, téléphone, ID, nb de personnes, date, heures) → le visiteur apparaît dans la liste
//  3. Il clique « Sélectionner > Modifier », le formulaire est pré-rempli, il change nom et motif → la liste affiche les nouvelles valeurs
//  4. Il clique « Sélectionner > Supprimer », confirme dans la modale → le visiteur disparaît de la liste
//
// Aucune notification (AramiscVisitorController). Liste DataTables côté serveur : on filtre avec la recherche rapide.
// Base de test : le visiteur créé est supprimé par l'étape DELETE, et par le after() s'il reste (échec en cours de test).

import { php } from '../portefeuille/helpers';

describe('Admin – Administration : Carnet de visiteurs (CRUD)', () => {
  const shot = 'admin/administration/visiteurs';
  const prefix = 'CyVisiteur_';

  // Filtre la liste (DataTables serveur) puis renvoie la ligne contenant `text`
  const findRow = (text) => {
    cy.get('#table_id_filter input[type="search"], .dataTables_filter input').first().clear().type(text);
    return cy.get('#table_id tbody', { timeout: 20000 }).contains('tr', text);
  };

  const fillForm = (v) => {
    cy.get('input[name="purpose"]').clear().type(v.purpose);
    cy.get('input[name="name"]').clear().type(v.name);
    cy.get('input[name="phone"]').clear().type(v.phone);
    cy.get('input[name="visitor_id"]').clear().type(v.visitorId);
    cy.get('input[name="no_of_person"]').clear().type(v.persons);
    cy.get('input[name="in_time"]').invoke('val', v.inTime);
    cy.get('input[name="out_time"]').invoke('val', v.outTime);
  };

  after(() => {
    php(`DB::table('aramisc_visitors')->where('name', 'like', '${prefix}%')->delete();`);
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un visiteur", () => {
    const name = `${prefix}${Date.now()}`;
    const updatedName = `${name}_Modifie`;
    const visitor = { purpose: 'Rendez-vous direction', name, phone: '0612345678', visitorId: 'CIN123', persons: '2', inTime: '10:00', outTime: '11:30' };

    cy.loginAsAdmin();
    cy.visit('/visitor');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    fillForm(visitor);
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('form[action$="visitor-store"] button.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('include', '/visitor');
    findRow(name).should('contain', visitor.purpose).and('contain', visitor.phone).and('contain', '10:00');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    findRow(name).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[href*="visitor-edit"]').click({ force: true });
    });

    cy.url().should('include', '/visitor-edit/');
    cy.get('input[name="name"]').should('have.value', name);
    cy.get('input[name="purpose"]').should('have.value', visitor.purpose);
    cy.get('input[name="visitor_id"]').should('have.value', visitor.visitorId);

    cy.get('input[name="name"]').clear().type(updatedName);
    cy.get('input[name="purpose"]').clear().type('Réunion parents');
    cy.get('input[name="no_of_person"]').clear().type('3');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('form[action$="visitor-update"] button.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    cy.url().should('not.include', '/visitor-edit/');
    findRow(updatedName).should('contain', 'Réunion parents').and('contain', '3');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    findRow(updatedName).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[data-target="#deleteVisitorModal"]').click({ force: true });
    });

    cy.get('#deleteVisitorModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    cy.url().should('include', '/visitor');
    cy.get('.dataTables_filter input').first().clear().type(updatedName);
    cy.get('#table_id tbody', { timeout: 20000 }).should('not.contain', updatedName);
  });
});
