// cypress/e2e/admin/inventaire/fournisseur.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Inventaire > Fournisseur : société, adresse, contact, mobile, e-mail, description
//     → le fournisseur apparaît dans la liste
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il change la société et le contact → la liste est à jour
//  3. « Sélectionner > Supprimer » (fenêtre chargée en AJAX), confirme → le fournisseur disparaît (contrôle en base)
//
// Aucune notification (AramiscSupplierController). after() supprime le fournisseur s'il reste.

import { php, findRow, rowAction, shouldBeGone } from '../administration/helpers';

describe('Admin – Inventaire : Fournisseur (CRUD)', () => {
  const shot = 'admin/inventaire/fournisseur';
  const prefix = 'CyFournisseur_';
  const stamp = Date.now();
  const company = `${prefix}${stamp}`;
  const updatedCompany = `${company}_M`;
  const email = `cyfournisseur_${stamp}@exemple.ma`;
  const mobile = `0522${String(stamp).slice(-6)}`;

  after(() => {
    php(`DB::table('aramisc_suppliers')->where('company_name', 'like', '${prefix}%')->delete();`);
  });

  const supplier = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_suppliers')->where('contact_person_email', '${email}')->first(['company_name', 'contact_person_name', 'contact_person_mobile']))).'>>CY';`);

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un fournisseur", () => {
    cy.loginAsAdmin();
    cy.visit('/suppliers');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="company_name"]').clear().type(company);
    cy.get('textarea[name="company_address"]').clear().type('Zone industrielle, Casablanca');
    cy.get('input[name="contact_person_name"]').clear().type('Contact Cypress');
    cy.get('input[name="contact_person_mobile"]').clear().type(mobile);
    cy.get('input[name="contact_person_email"]').clear().type(email);
    cy.get('textarea[name="description"]').clear().type('Fournisseur Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    supplier().should('deep.equal', { company_name: company, contact_person_name: 'Contact Cypress', contact_person_mobile: mobile });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/suppliers$/);
    findRow(company).should('contain', 'Contact Cypress').and('contain', email);

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(company, 'a.dropdown-item[href*="suppliers/"]');

    cy.url().should('match', /\/suppliers\/\d+$/);
    cy.get('input[name="company_name"]').should('have.value', company).clear().type(updatedCompany);
    cy.get('input[name="contact_person_email"]').should('have.value', email);
    cy.get('input[name="contact_person_name"]').clear().type('Contact Modifie');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    supplier().should('deep.equal', { company_name: updatedCompany, contact_person_name: 'Contact Modifie', contact_person_mobile: mobile });
    cy.url().should('match', /\/suppliers$/);
    findRow(updatedCompany).should('contain', 'Contact Modifie');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedCompany, 'a.dropdown-item.deleteUrl');
    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible')
      .find('a[href*="delete-supplier/"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    supplier().should('be.null');
    shouldBeGone(updatedCompany);
  });
});
