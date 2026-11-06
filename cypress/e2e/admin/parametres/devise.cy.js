// cypress/e2e/admin/parametres/devise.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Paramètres > Gérer la devise > Ajouter : nom, code, symbole, affichage « symbole », position « suffixe »,
//     espace « oui », 2 décimales, séparateurs → la devise apparaît dans la liste
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il renomme et passe la position en « préfixe » → la liste est à jour
//  3. « Sélectionner > Supprimer », confirme → la devise disparaît (contrôle en base)
//
// La devise créée n'est jamais activée (la devise courante de l'école ne peut pas être supprimée et n'est pas touchée).
// Aucune notification (AramiscManageCurrencyController). after() supprime la devise si elle reste.

import { php, findRow, rowAction, shouldBeGone, toastSuccess } from '../administration/helpers';

describe('Admin – Paramètres : Devise (CRUD)', () => {
  const shot = 'admin/parametres/devise';
  const stamp = String(Date.now()).slice(-6);
  const name = `CyDevise${stamp}`;
  const updatedName = `${name}M`;
  const code = `CY${stamp}`;

  after(() => {
    php(`DB::table('aramisc_currencies')->where('code', '${code}')->delete();`);
  });

  const currency = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_currencies')->where('code', '${code}')
      ->first(['name', 'symbol', 'currency_type', 'currency_position', 'space', 'decimal_digit']))).'>>CY';`);

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'une devise", () => {
    cy.loginAsAdmin();
    cy.visit('/create-currency');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="name"]').clear().type(name);
    cy.get('input[name="code"]').clear().type(code);
    cy.get('input[name="symbol"]').clear().type('Cy$');
    cy.get('#currency_symbol').check({ force: true });
    cy.get('#currency_suffix').check({ force: true });
    cy.get('#space_yes').check({ force: true });
    cy.get('input[name="decimal_digit"]').clear().type('2');
    cy.get('input[name="decimal_separator"]').clear().type(',');
    cy.get('input[name="thousand_separator"]').clear().type(' ');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    toastSuccess();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    currency().then((c) => {
      expect(c, 'devise enregistrée').to.not.be.null;
      expect(c).to.include({ name, symbol: 'Cy$', currency_type: 'S', currency_position: 'S' });
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/manage-currency$/);
    findRow(code).should('contain', name).and('contain', 'Cy$');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(code, 'a.dropdown-item[href*="manage-currency/edit"]');

    cy.url().should('include', '/manage-currency/edit/');
    cy.get('input[name="name"]').should('have.value', name).clear().type(updatedName);
    cy.get('input[name="code"]').should('have.value', code);
    cy.get('#currency_suffix').should('be.checked');
    cy.get('#currency_prefix').check({ force: true });
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    toastSuccess();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    currency().then((c) => {
      expect(c).to.include({ name: updatedName, currency_position: 'P' });
    });
    findRow(code).should('contain', updatedName);

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(code, 'a.dropdown-item[data-target^="#deleteCurrency"]');
    cy.get('.modal.show[id^="deleteCurrency"]').should('be.visible')
      .find('a[href*="manage-currency/delete"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    currency().should('be.null');
    shouldBeGone(code);
  });
});
