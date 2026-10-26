// cypress/e2e/admin/communiquer/evenement.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Communiquer > Événement : titre, destinataire « Teacher » seul, lieu, du 15/10/2026 au 16/10/2026,
//     description → l'événement apparaît dans la liste
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il change le titre et le lieu → la liste est à jour
//  3. « Sélectionner > Supprimer » (fenêtre chargée en AJAX), confirme → l'événement disparaît (contrôle en base)
//
// ⚠ La création notifie les rôles choisis (Event) : muteMessaging() coupe Email/SMS/App (+ réglages e-mail / SMS),
//   restoreMessaging() rétablit et supprime les notifications Web créées. after() supprime l'événement s'il reste.
// Liste DataTables serveur `#eventDataTable` (pas `#table_id`). Dates volontairement > 12 (format FR j/m/aaaa).

import { php, muteMessaging, restoreMessaging, toastSuccess } from '../administration/helpers';

describe('Admin – Communiquer : Événement (CRUD)', () => {
  const shot = 'admin/communiquer/evenement';
  const prefix = 'CyEvenement_';
  const title = `${prefix}${Date.now()}`;
  const updatedTitle = `${title}_M`;
  const TEACHER_ROLE = '4';
  // admin@aramisc.com a le rôle Admin (5) : il voit aussi les événements destinés aux autres rôles (anomalie 51, corrigée)
  let backup;

  before(() => {
    muteMessaging(['Event']).then((b) => { backup = b; });
  });

  after(() => {
    php(`DB::table('aramisc_events')->where('event_title', 'like', '${prefix}%')->delete();`);
    restoreMessaging(backup);
  });

  const event = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_events')->whereIn('event_title', ['${title}', '${updatedTitle}'])
      ->first(['event_title', 'event_location', 'from_date', 'to_date', 'role_ids']))).'>>CY';`);

  // Ligne cherchée sans recherche rapide : la base de test n'a aucun autre événement
  const findEvent = (text) => cy.get('#eventDataTable tbody', { timeout: 20000 }).contains('tr', text);
  const eventAction = (text, selector) =>
    findEvent(text).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get(selector).click({ force: true });
    });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un événement", () => {
    cy.loginAsAdmin();
    cy.visit('/event');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="event_title"]').clear().type(title);
    cy.get('#selectMultiUsers').select([TEACHER_ROLE], { force: true });
    cy.get('input[name="event_location"]').clear().type('Salle polyvalente');
    cy.get('#event_from_date').invoke('val', '15/10/2026');
    cy.get('#event_to_date').invoke('val', '16/10/2026');
    cy.get('textarea[name="event_des"]').clear().type('Événement Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    toastSuccess();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    event().then((e) => {
      expect(e, 'événement enregistré').to.not.be.null;
      expect(e).to.include({ event_location: 'Salle polyvalente', from_date: '2026-10-15', to_date: '2026-10-16' });
      expect(JSON.parse(e.role_ids)).to.deep.equal([TEACHER_ROLE]);
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/event$/);
    findEvent(title).should('contain', 'Salle polyvalente').and('contain', '15/10/2026');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    eventAction(title, 'a.dropdown-item[href*="/event/"]');

    cy.url().should('match', /\/event\/\d+$/);
    cy.get('input[name="event_title"]').should('have.value', title).clear().type(updatedTitle);
    cy.get('input[name="event_location"]').clear().type('Gymnase');
    cy.get('#event_from_date').should('have.value', '15/10/2026');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    toastSuccess();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    event().then((e) => {
      expect(e).to.include({ event_title: updatedTitle, event_location: 'Gymnase', from_date: '2026-10-15' });
    });
    findEvent(updatedTitle).should('contain', 'Gymnase');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    eventAction(updatedTitle, 'a.dropdown-item.deleteUrl');
    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible')
      .find('a[href*="delete-event/"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    event().should('be.null');
  });
});
