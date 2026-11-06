// cypress/e2e/admin/centre_telechargement/liste_de_contenu.cy.js
//
// Scénario utilisateur (validé manuellement) :
//  0. Préparation : l'admin crée 2 types de contenu (A et B)
//  1. Liste de contenu : choisit le type A, joint un fichier .txt, sauvegarde → le contenu apparaît avec le type A
//  2. Sélectionner > Modifier : formulaire pré-rempli, renomme le fichier et passe au type B → liste mise à jour
//  3. Recherche par nom → le contenu est retrouvé
//  4. Sélectionner > Supprimer + confirmation → le contenu disparaît
//  5. Nettoyage : suppression des 2 types créés (base de test)
//
// Captures d'écran : en commentaire pour l'instant.

describe('Admin – Download Center: Content List (CRUD)', () => {
  const shot = 'admin/centre_telechargement/liste_de_contenu';
  const stamp = Date.now();
  const typeA = `CT_A_${stamp}`;
  const typeB = `CT_B_${stamp}`;
  const fileName = `cy_content_${stamp}.txt`;
  const renamed = `cy_content_${stamp}_renamed`;

  const createType = (name) => {
    cy.visit('/download-center/content-type');
    cy.get('input#name').clear().type(name);
    cy.get('button.primary-btn.fix-gr-bg.submit').first().click();
    cy.get('#table_id').should('contain', name);
  };

  const deleteType = (name) => {
    cy.visit('/download-center/content-type');
    cy.get('#table_id').contains('tr', name).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[data-target^="#deleteTypeModal"]').click({ force: true });
    });
    cy.get('.modal.show[id^="deleteTypeModal"]').find('a[href*="content-type-delete"] button').click();
    cy.get('#table_id').should('not.contain', name);
  };

  // Le select « Type de contenu » est habillé par nice-select : on pilote le <select> natif
  const selectType = (name) => {
    cy.get('select#content_type_id')
      .find('option')
      .contains(name)
      .then(($opt) => {
        cy.get('select#content_type_id').select($opt.val(), { force: true });
      });
  };

  const contentForm = () => cy.get('select#content_type_id').closest('form');

  beforeEach(() => {
    cy.loginAsAdmin();
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Recherche, Suppression) d'un contenu", () => {
    // ─────────────── 0. PRÉPARATION ───────────────
    createType(typeA);
    createType(typeB);

    // ─────────────── 1. CREATE ───────────────
    cy.visit('/download-center/content-list');
    selectType(typeA);
    cy.get('#upload_content_file').selectFile(
      { contents: Cypress.Buffer.from(`Contenu de test Cypress ${stamp}`), fileName, mimeType: 'text/plain' },
      { force: true }
    );
    // cy.screenshot(`${shot}/01-create-formulaire`);
    contentForm().find('button.primary-btn.fix-gr-bg').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────── 2. READ ───────────────
    cy.url().should('include', '/download-center/content-list');
    cy.get('#table_id').contains('tr', fileName).should('contain', typeA);

    // ─────────────── 3. UPDATE ───────────────
    cy.get('#table_id').contains('tr', fileName).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[href*="content-list-edit"]').click({ force: true });
    });
    cy.url().should('include', '/content-list-edit/');
    cy.get('input[name="file_name"]').should('have.value', fileName).clear().type(renamed);
    selectType(typeB);
    // cy.screenshot(`${shot}/03-update-formulaire`);
    contentForm().find('button.primary-btn.fix-gr-bg').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    cy.url().should('not.include', '/content-list-edit/');
    cy.get('#table_id').contains('tr', renamed).should('contain', typeB);
    cy.get('#table_id').should('not.contain', fileName);

    // ─────────────── 4. SEARCH ───────────────
    cy.get('input[name="name"]').clear().type(renamed);
    // cy.screenshot(`${shot}/05-search-formulaire`);
    cy.get('#btnsubmit').click();
    // cy.screenshot(`${shot}/06-search-apres-submit`);
    cy.url().should('include', '/content-list-search');
    cy.get('#table_id').should('contain', renamed);

    // ─────────────── 5. DELETE ───────────────
    cy.get('#table_id').contains('tr', renamed).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[data-target^="#deleteContentModal"]').click({ force: true });
    });
    cy.get('.modal.show[id^="deleteContentModal"]')
      .should('be.visible')
      .find('a[href*="content-list-delete"] button')
      .click();
    // cy.screenshot(`${shot}/07-delete-apres-confirmation`);
    cy.url().should('include', '/download-center/content-list');
    cy.get('#table_id').should('not.contain', renamed);

    // ─────────────── 6. NETTOYAGE (base de test) ───────────────
    deleteType(typeA);
    deleteType(typeB);
  });
});
