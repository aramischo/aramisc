// cypress/e2e/admin/administration/courrier_recu.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Administration > Réception postale
//  2. Il remplit le formulaire (expéditeur, référence, adresse, note, destinataire, date) → le courrier apparaît dans la liste
//  3. Il clique « Sélectionner > Modifier », le formulaire est pré-rempli, il modifie → la liste affiche les nouvelles valeurs
//  4. Il clique « Sélectionner > Supprimer », confirme dans la modale → le courrier disparaît
//
// Date volontairement > 12 (15/09/2026) : cas réel d'un utilisateur FR (format j/m/aaaa).
// Aucune notification (AramiscPostalReceiveController). Le after() supprime le courrier s'il reste.

import { php, findRow, rowAction, shouldBeGone } from './helpers';

describe('Admin – Administration : Réception postale (CRUD)', () => {
  const shot = 'admin/administration/courrier_recu';
  const prefix = 'CyRecu_';

  after(() => {
    php(`DB::table('aramisc_postal_receives')->where('from_title', 'like', '${prefix}%')->delete();`);
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un courrier reçu", () => {
    const from = `${prefix}${Date.now()}`;
    const updatedFrom = `${from}_Modifie`;

    cy.loginAsAdmin();
    cy.visit('/postal-receive');

    // ──────────── 0. Champs obligatoires vides → refus de validation (anomalie 28, corrigée) ────────────
    cy.get('input[name="from_title"]').clear().type(from);
    cy.get('button.primary-btn.submit').click();
    cy.get('span.text-danger', { timeout: 20000 }).should('have.length.at.least', 3);
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_postal_receives')->where('from_title', '${from}')->count())).'>>CY';`)
      .should('eq', 0);

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.visit('/postal-receive');
    cy.get('input[name="from_title"]').clear().type(from);
    cy.get('input[name="reference_no"]').clear().type('REF-001');
    cy.get('input[name="address"]').clear().type('12 rue des Écoles, Rabat');
    cy.get('textarea[name="note"]').clear().type('Courrier reçu par Cypress');
    cy.get('input[name="to_title"]').clear().type('Direction');
    cy.get('input[name="date"]').invoke('val', '15/09/2026');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/postal-receive$/);
    findRow(from).should('contain', 'REF-001').and('contain', 'Direction').and('contain', '15/09/2026');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(from, 'a.dropdown-item[href*="postal-receive/"]');

    cy.url().should('match', /\/postal-receive\/\d+$/);
    cy.get('input[name="from_title"]').should('have.value', from);
    cy.get('input[name="reference_no"]').should('have.value', 'REF-001');
    cy.get('input[name="date"]').should('have.value', '15/09/2026');

    cy.get('input[name="from_title"]').clear().type(updatedFrom);
    cy.get('input[name="reference_no"]').clear().type('REF-002');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    cy.url().should('match', /\/postal-receive$/);
    findRow(updatedFrom).should('contain', 'REF-002');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedFrom, 'a.dropdown-item[data-target="#deletePostalReceiveModal"]');
    cy.get('#deletePostalReceiveModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    cy.url().should('match', /\/postal-receive$/);
    shouldBeGone(updatedFrom);
  });
});
