// cypress/e2e/admin/administration/courrier_envoye.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Administration > Envoi postal
//  2. Il remplit le formulaire (destinataire, référence, adresse, note, expéditeur, date) → le courrier apparaît dans la liste
//  3. Il clique « Sélectionner > Modifier », le formulaire est pré-rempli, il modifie → la liste affiche les nouvelles valeurs
//  4. Il clique « Sélectionner > Supprimer », confirme dans la modale → le courrier disparaît
//
// Le champ date est en lecture seule (datepicker) : valeur posée directement.
// Date volontairement > 12 (15/09/2026) : cas réel d'un utilisateur FR (format j/m/aaaa).
// Aucune notification (AramiscPostalDispatchController). Le after() supprime le courrier s'il reste.

import { php, findRow, rowAction, shouldBeGone } from './helpers';

describe('Admin – Administration : Envoi postal (CRUD)', () => {
  const shot = 'admin/administration/courrier_envoye';
  const prefix = 'CyEnvoye_';

  after(() => {
    php(`DB::table('aramisc_postal_dispatches')->where('to_title', 'like', '${prefix}%')->delete();`);
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un courrier envoyé", () => {
    const to = `${prefix}${Date.now()}`;
    const updatedTo = `${to}_Modifie`;

    cy.loginAsAdmin();
    cy.visit('/postal-dispatch');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="to_title"]').clear().type(to);
    cy.get('input[name="reference_no"]').clear().type('ENV-001');
    cy.get('input[name="address"]').clear().type('Académie régionale, Casablanca');
    cy.get('textarea[name="note"]').clear().type('Courrier envoyé par Cypress');
    cy.get('input[name="from_title"]').clear().type('Secrétariat');
    cy.get('input[name="date"]').invoke('val', '15/09/2026');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/postal-dispatch$/);
    findRow(to).should('contain', 'ENV-001').and('contain', 'Secrétariat').and('contain', '15/09/2026');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(to, 'a.dropdown-item[href*="postal-dispatch/"]');

    cy.url().should('match', /\/postal-dispatch\/\d+$/);
    cy.get('input[name="to_title"]').should('have.value', to);
    cy.get('input[name="reference_no"]').should('have.value', 'ENV-001');
    cy.get('input[name="date"]').should('have.value', '15/09/2026');

    cy.get('input[name="to_title"]').clear().type(updatedTo);
    cy.get('input[name="reference_no"]').clear().type('ENV-002');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    cy.url().should('match', /\/postal-dispatch$/);
    findRow(updatedTo).should('contain', 'ENV-002');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedTo, 'a.dropdown-item[data-target="#deleteDispatchReceiveModal"]');
    cy.get('#deleteDispatchReceiveModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    cy.url().should('match', /\/postal-dispatch$/);
    shouldBeGone(updatedTo);
  });
});
