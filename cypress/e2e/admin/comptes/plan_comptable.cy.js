// cypress/e2e/admin/comptes/plan_comptable.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Comptes > Plan comptable : rubrique, type « Dépense » → la rubrique apparaît dans la liste
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il renomme et passe au type « Revenu » → la liste est à jour
//  3. « Sélectionner > Supprimer », confirme → la rubrique disparaît (contrôle en base)
//
// Remplace l'ancien bloc « Chart of Account » de accounts.cy.js (il soumettait le 1er formulaire de la page, celui de
// la recherche de l'en-tête). Modale de suppression dans le <tbody> → `.modal.show button[type=submit]`.
// Aucune notification. after() supprime la rubrique si elle reste.

import { php, findRow, rowAction, shouldBeGone } from '../administration/helpers';

describe('Admin – Comptes : Plan comptable (CRUD)', () => {
  const shot = 'admin/comptes/plan_comptable';
  const prefix = 'CyRubrique_';
  const name = `${prefix}${Date.now()}`;
  const updatedName = `${name}_M`;

  after(() => {
    php(`DB::table('aramisc_chart_of_accounts')->where('head', 'like', '${prefix}%')->delete();`);
  });

  const head = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_chart_of_accounts')->whereIn('head', ['${name}', '${updatedName}'])->first(['head', 'type']))).'>>CY';`);

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'une rubrique comptable", () => {
    cy.loginAsAdmin();
    cy.visit('/chart-of-account');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="head"]').clear().type(name);
    cy.get('select[name="type"]').select('E', { force: true });
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    head().should('deep.equal', { head: name, type: 'E' });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/chart-of-account$/);
    findRow(name).should('exist');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="chart-of-account/"]');

    cy.url().should('match', /\/chart-of-account\/\d+$/);
    cy.get('input[name="head"]').should('have.value', name).clear().type(updatedName);
    cy.get('select[name="type"]').should('have.value', 'E').select('I', { force: true });
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    head().should('deep.equal', { head: updatedName, type: 'I' });
    cy.url().should('match', /\/chart-of-account$/);
    findRow(updatedName).should('exist');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedName, 'a.dropdown-item[data-target^="#deleteChartOfAccountModal"]');
    cy.get('.modal.show[id^="deleteChartOfAccountModal"]').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    head().should('be.null');
    shouldBeGone(updatedName);
  });
});
