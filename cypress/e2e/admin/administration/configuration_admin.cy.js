// cypress/e2e/admin/administration/configuration_admin.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Administration > Configuration d'admin
//  2. Il crée une entrée de type « Type de plainte » (nom + description) → elle apparaît dans le groupe « Type de plainte »
//  3. Il clique sur l'entrée > Modifier, le formulaire est pré-rempli, il renomme → la liste affiche le nouveau nom
//  4. Il clique sur l'entrée > Supprimer, confirme dans la modale → l'entrée disparaît
//
// Liste en accordéon (un groupe par type), pas de DataTables. Aucune notification (AramiscSetupAdminController).
// Base de test : l'entrée créée est supprimée par l'étape DELETE, et par le after() si elle reste.

import { php } from './helpers';

describe("Admin – Administration : Configuration d'admin (CRUD)", () => {
  const shot = 'admin/administration/configuration_admin';
  const prefix = 'CyConfig_';

  // Ouvre l'accordéon du groupe puis le menu de l'entrée
  const openEntry = (name) => {
    cy.get('[data-target="#Role2"]').click({ force: true });
    cy.contains('#Role2 button.dropdown-toggle', name).click({ force: true });
    return cy.contains('#Role2 button.dropdown-toggle', name).parent();
  };

  after(() => {
    php(`DB::table('aramisc_setup_admins')->where('name', 'like', '${prefix}%')->delete();`);
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'une entrée de configuration", () => {
    const name = `${prefix}${Date.now()}`.slice(0, 30);
    const updatedName = `${name}_M`;

    cy.loginAsAdmin();
    cy.visit('/setup-admin');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('select[name="type"]').select('2', { force: true });
    cy.get('input[name="name"]').clear().type(name);
    cy.get('textarea[name="description"]').clear().type('Créé par Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/setup-admin$/);
    cy.contains('#Role2 button.dropdown-toggle', name).should('exist');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    openEntry(name).find('a.dropdown-item[href*="setup-admin/"]').click({ force: true });

    cy.url().should('match', /\/setup-admin\/\d+$/);
    cy.get('select[name="type"]').should('have.value', '2');
    cy.get('input[name="name"]').should('have.value', name);
    cy.get('textarea[name="description"]').should('have.value', 'Créé par Cypress');

    cy.get('input[name="name"]').clear().type(updatedName);
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    cy.url().should('match', /\/setup-admin$/);
    cy.contains('#Role2 button.dropdown-toggle', updatedName).should('exist');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    openEntry(updatedName).find('a.deleteSetupAdminModal').click({ force: true });
    cy.get('#deleteSetupAdminModal.show').should('be.visible').find('a.primary-btn[href*="setup-admin-delete"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    cy.url().should('include', '/setup-admin');
    cy.contains('#Role2 button.dropdown-toggle', updatedName).should('not.exist');
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_setup_admins')->where('name', '${updatedName}')->count())).'>>CY';`)
      .should('eq', 0);
  });
});
