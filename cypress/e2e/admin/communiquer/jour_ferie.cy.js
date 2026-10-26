// cypress/e2e/admin/communiquer/jour_ferie.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Communiquer > Jour férié : titre, du 15/10/2026 au 16/10/2026, détails → le jour férié apparaît (2 jours)
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il renomme et prolonge au 17/10/2026 → la liste affiche 3 jours
//  3. « Sélectionner > Supprimer » (fenêtre chargée en AJAX), confirme → le jour férié disparaît (contrôle en base)
//
// Aucune notification (AramiscHolidayController). after() supprime le jour férié s'il reste.
// Dates volontairement > 12 : cas réel d'un utilisateur FR (format j/m/aaaa).

import { php, findRow, rowAction, shouldBeGone, toastSuccess } from '../administration/helpers';

describe('Admin – Communiquer : Jour férié (CRUD)', () => {
  const shot = 'admin/communiquer/jour_ferie';
  const prefix = 'CyFerie_';
  const title = `${prefix}${Date.now()}`;
  const updatedTitle = `${title}_M`;

  after(() => {
    php(`DB::table('aramisc_holidays')->where('holiday_title', 'like', '${prefix}%')->delete();`);
  });

  const holiday = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_holidays')->whereIn('holiday_title', ['${title}', '${updatedTitle}'])
      ->first(['holiday_title', 'from_date', 'to_date', 'details']))).'>>CY';`);

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un jour férié", () => {
    cy.loginAsAdmin();
    cy.visit('/holiday');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="holiday_title"]').clear().type(title);
    cy.get('input[name="from_date"]').invoke('val', '15/10/2026');
    cy.get('input[name="to_date"]').invoke('val', '16/10/2026');
    cy.get('textarea[name="details"]').clear().type('Jour férié Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    toastSuccess();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    holiday().then((h) => {
      expect(h, 'jour férié enregistré').to.not.be.null;
      expect(h).to.include({ from_date: '2026-10-15', to_date: '2026-10-16' });
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/holiday$/);
    findRow(title).should('contain', '2 days');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(title, 'a.dropdown-item[href*="/edit"]');

    cy.url().should('match', /\/holiday\/\d+\/edit$/);
    cy.get('input[name="holiday_title"]').should('have.value', title).clear().type(updatedTitle);
    cy.get('input[name="from_date"]').should('have.value', '15/10/2026');
    cy.get('input[name="to_date"]').invoke('val', '17/10/2026');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    toastSuccess();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    holiday().then((h) => {
      expect(h).to.include({ holiday_title: updatedTitle, from_date: '2026-10-15', to_date: '2026-10-17' });
    });
    findRow(updatedTitle).should('contain', '3 days');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedTitle, 'a.dropdown-item[href*="delete-holiday-data-view"]');
    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible')
      .find('a[href*="delete-holiday-data/"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    holiday().should('be.null');
    shouldBeGone(updatedTitle);
  });
});
