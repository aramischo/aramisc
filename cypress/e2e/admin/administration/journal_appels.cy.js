// cypress/e2e/admin/administration/journal_appels.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Administration > Journal des appels
//  2. Il remplit le formulaire (nom, téléphone, date, date de suivi, durée, description, type « entrant ») → l'appel apparaît
//  3. Il clique « Sélectionner > Modifier », le formulaire est pré-rempli, il modifie (type « sortant ») → la liste est à jour
//  4. Il clique « Sélectionner > Supprimer », confirme dans la modale → l'appel disparaît
//
// Dates volontairement > 12 (15/09/2026, suivi le 20/09/2026) : cas réel d'un utilisateur FR (format j/m/aaaa).
// Aucune notification (AramiscPhoneCallLogController). Le after() supprime l'appel s'il reste.

import { php, findRow, rowAction, shouldBeGone } from './helpers';

describe('Admin – Administration : Journal des appels (CRUD)', () => {
  const shot = 'admin/administration/journal_appels';
  const prefix = 'CyAppel_';

  after(() => {
    php(`DB::table('aramisc_phone_call_logs')->where('name', 'like', '${prefix}%')->delete();`);
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un appel", () => {
    const name = `${prefix}${Date.now()}`;
    const updatedName = `${name}_Modifie`;

    cy.loginAsAdmin();
    cy.visit('/phone-call');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="name"]').clear().type(name);
    cy.get('input[name="phone"]').clear().type('0655443322');
    cy.get('input[name="date"]').invoke('val', '15/09/2026');
    cy.get('input[name="follow_up_date"]').invoke('val', '20/09/2026');
    cy.get('input[name="call_duration"]').clear().type('5 min');
    cy.get('textarea[name="description"]').clear().type('Demande de renseignements');
    cy.get('input[name="call_type"][value="I"]').check({ force: true });
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/phone-call$/);
    findRow(name).should('contain', '0655443322').and('contain', '15/09/2026').and('contain', '20/09/2026').and('contain', '5 min');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="phone-call/"]');

    cy.url().should('match', /\/phone-call\/\d+$/);
    cy.get('input[name="name"]').should('have.value', name);
    cy.get('input[name="phone"]').should('have.value', '0655443322');
    cy.get('input[name="call_type"][value="I"]').should('be.checked');

    cy.get('input[name="name"]').clear().type(updatedName);
    cy.get('input[name="call_duration"]').clear().type('8 min');
    cy.get('input[name="call_type"][value="O"]').check({ force: true });
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    cy.url().should('match', /\/phone-call$/);
    findRow(updatedName).should('contain', '8 min');
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_phone_call_logs')->where('name', '${updatedName}')->value('call_type'))).'>>CY';`)
      .should('eq', 'O');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedName, 'a.dropdown-item[data-target="#deleteCallLogModal"]');
    cy.get('#deleteCallLogModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    cy.url().should('match', /\/phone-call$/);
    shouldBeGone(updatedName);
  });
});
