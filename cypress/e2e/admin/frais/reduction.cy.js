// cypress/e2e/admin/frais/reduction.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Frais > Réduction des frais : nom, code, montant 50, description
//     → la réduction apparaît dans la liste
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il passe au montant 75 → la liste est à jour
//  3. « Sélectionner > Supprimer », confirme → la réduction disparaît (contrôle en base)
//
// Mode « frais directs » actif : le choix du type (Une fois / Annuel) n'est pas affiché par la page.
// La réduction n'est affectée à aucun élève (l'affectation n'est pas testée ici). Aucune notification.
// after() supprime la réduction si elle reste.

import { php, findRow, rowAction, shouldBeGone } from '../administration/helpers';

describe('Admin – Frais : Réduction des frais (CRUD)', () => {
  const shot = 'admin/frais/reduction';
  const prefix = 'CyReduction_';
  const name = `${prefix}${Date.now()}`;
  const code = `CY${String(Date.now()).slice(-6)}`;

  after(() => {
    php(`DB::table('aramisc_fees_discounts')->where('name', 'like', '${prefix}%')->delete();`);
  });

  const discount = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_fees_discounts')->where('name', '${name}')->first(['type', 'amount', 'code']))).'>>CY';`);

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'une réduction", () => {
    cy.loginAsAdmin();
    cy.visit('/fees-discount');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="name"]').clear().type(name);
    cy.get('input[name="code"]').clear().type(code);
    cy.get('input[name="amount"]').clear().type('50');
    cy.get('textarea[name="description"]').clear().type('Réduction Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    discount().then((d) => {
      expect(d, 'réduction enregistrée').to.not.be.null;
      expect(d).to.include({ code });
      expect(Number(d.amount)).to.eq(50);
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/fees-discount$/);
    findRow(name).should('contain', code).and('contain', '50');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="fees-discount-edit"]');

    cy.url().should('include', '/fees-discount-edit/');
    cy.get('input[name="name"]').should('have.value', name);
    cy.get('input[name="code"]').should('have.value', code);

    cy.get('input[name="amount"]').clear().type('75');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    discount().then((d) => {
      expect(Number(d.amount)).to.eq(75);
    });
    findRow(name).should('contain', '75');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(name, 'a.dropdown-item[data-target^="#deleteFeesDiscountModal"]');
    cy.get('.modal.show[id^="deleteFeesDiscountModal"]').should('be.visible')
      .find('a[href*="fees-discount-delete"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    discount().should('be.null');
    shouldBeGone(name);
  });
});
