// cypress/e2e/admin/centre_telechargement/type_de_contenu.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Centre de téléchargement > Type de contenu
//  2. Il crée un type (nom + description) → toast succès, le type apparaît dans la liste
//  3. Il clique « Sélectionner > Modifier », le formulaire est pré-rempli, il renomme → la liste affiche le nouveau nom
//  4. Il clique « Sélectionner > Supprimer », confirme dans la modale → le type disparaît de la liste
//
// Base de test : toute donnée créée par le test est supprimée à la fin (étape DELETE).
// Captures d'écran : laissées en commentaire pour l'instant (décommenter pour capturer les messages).

describe('Admin – Download Center: Content Type (CRUD)', () => {
  const shot = 'admin/centre_telechargement/type_de_contenu';

  beforeEach(() => {
    cy.loginAsAdmin();
    cy.visit('/download-center/content-type');
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un type de contenu", () => {
    const typeName = `Type_${Date.now()}`;
    const updatedName = `${typeName}_Updated`;
    const description = 'Description automatique générée par Cypress';

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input#name').clear().type(typeName);
    cy.get('textarea#description').clear().type(description);
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.fix-gr-bg.submit').first().click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('include', '/download-center/content-type');
    cy.get('#table_id').should('contain', typeName).and('contain', description);

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    cy.get('#table_id').contains('tr', typeName).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[href*="content-type-edit"]').click({ force: true });
    });

    cy.url().should('include', '/content-type-edit/');
    cy.get('input#name').should('have.value', typeName);
    cy.get('textarea#description').should('have.value', description);

    cy.get('input#name').clear().type(updatedName);
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.fix-gr-bg.submit').first().click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    cy.url().should('not.include', '/content-type-edit/');
    cy.get('#table_id').should('contain', updatedName);

    // ─────────────────────────── 4. DELETE ───────────────────────────
    cy.get('#table_id').contains('tr', updatedName).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[data-target^="#deleteTypeModal"]').click({ force: true });
    });

    // Confirmation dans la modale : on cible UNIQUEMENT le bouton à l'intérieur du lien
    cy.get('.modal.show[id^="deleteTypeModal"]')
      .should('be.visible')
      .find('a[href*="content-type-delete"] button')
      .click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    cy.url().should('include', '/download-center/content-type');
    cy.get('#table_id').should('not.contain', updatedName);
  });
});
