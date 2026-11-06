// cypress/e2e/admin/centre_telechargement/liste_de_contenu_partage.cy.js
//
// Scénario utilisateur (validé manuellement) :
//  0. Préparation : un type de contenu + un contenu (fichier .txt)
//  1. Liste de contenu : cocher le contenu → « Partager » → titre, date de partage (aujourd'hui),
//     valide jusqu'au (+15 jours), description, destinataires = groupe « Student » → Partager
//  2. Liste de contenu partagé : le partage apparaît → « Afficher » ouvre le détail
//  3. « Supprimer » + confirmation → le partage disparaît
//  4. Même cycle avec « Générer une URL » (partage public par lien) : création → « Lien » → suppression
//  5. Nettoyage : suppression du contenu et du type (base de test)
//
// Bug corrigé le 30/09/2026 : en locale FR (dates d/m/Y), `after:shareDate` lisait les dates en m/d/Y
//   → 422 dès que le jour > 12. ContentShareRequest::prepareForValidation() convertit maintenant en Y-m-d.
//   Ce test utilise des dates réalistes pour détecter une éventuelle régression.
//
// Captures d'écran : en commentaire pour l'instant.

describe('Admin – Centre de téléchargement : Liste de contenu partagé', () => {
  const shot = 'admin/centre_telechargement/liste_de_contenu_partage';
  const stamp = Date.now();
  const typeName = `CT_Share_${stamp}`;
  const fileName = `cy_share_${stamp}.txt`;
  const shareTitle = `Partage_${stamp}`;
  const urlTitle = `Lien_${stamp}`;

  // Même format que la date pré-remplie par l'application (d/m/Y en FR, Y-m-d sinon)
  const formatLike = (sample, date) => {
    const dd = String(date.getDate()).padStart(2, '0');
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    return sample.includes('/') ? `${dd}/${mm}/${date.getFullYear()}` : `${date.getFullYear()}-${mm}-${dd}`;
  };
  const inFifteenDays = () => {
    const d = new Date();
    d.setDate(d.getDate() + 15);
    return d;
  };

  const createTypeAndContent = () => {
    cy.visit('/download-center/content-type');
    cy.get('input#name').clear().type(typeName);
    cy.get('button.primary-btn.fix-gr-bg.submit').first().click();
    cy.get('#table_id').should('contain', typeName);

    cy.visit('/download-center/content-list');
    cy.get('select#content_type_id').find('option').contains(typeName).then(($opt) => {
      cy.get('select#content_type_id').select($opt.val(), { force: true });
    });
    cy.get('#upload_content_file').selectFile(
      { contents: Cypress.Buffer.from(`Partage Cypress ${stamp}`), fileName, mimeType: 'text/plain' },
      { force: true }
    );
    cy.get('select#content_type_id').closest('form').find('button.primary-btn.fix-gr-bg').click();
    cy.get('#table_id').should('contain', fileName);
  };

  const cleanup = () => {
    cy.visit('/download-center/content-list');
    cy.get('#table_id').contains('tr', fileName).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[data-target^="#deleteContentModal"]').click({ force: true });
    });
    cy.get('.modal.show[id^="deleteContentModal"]').find('a[href*="content-list-delete"] button').click();
    cy.get('#table_id').should('not.contain', fileName);

    cy.visit('/download-center/content-type');
    cy.get('#table_id').contains('tr', typeName).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[data-target^="#deleteTypeModal"]').click({ force: true });
    });
    cy.get('.modal.show[id^="deleteTypeModal"]').find('a[href*="content-type-delete"] button').click();
    cy.get('#table_id').should('not.contain', typeName);
  };

  const selectContent = () => {
    cy.visit('/download-center/content-list');
    cy.get('#table_id').contains('tr', fileName).find('input.contentDataCheckbox').check({ force: true });
    // cy.screenshot(`${shot}/00-contenu-coche`);
  };

  const deleteShare = (title) => {
    cy.visit('/download-center/content-share-list');
    cy.get('#table_id').contains('tr', title).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[data-target^="#deleteModal"]').click({ force: true });
    });
    cy.get('.modal.show[id^="deleteModal"]')
      .should('be.visible')
      .find('a[href*="content-share-list-delete"] button')
      .click();
    // cy.screenshot(`${shot}/${title}-delete-apres-confirmation`);
    cy.url().should('include', '/download-center/content-share-list');
    cy.get('#table_id').should('not.contain', title);
  };

  // Supprime la ligne `text` de la liste `url` uniquement si elle existe encore
  const deleteIfPresent = (url, text, modalPrefix, deleteHref) => {
    cy.visit(url);
    cy.get('#table_id').then(($table) => {
      if (!$table.text().includes(text)) return;
      cy.wrap($table).contains('tr', text).within(() => {
        cy.get('.dropdown-toggle').click({ force: true });
        cy.get(`a.dropdown-item[data-target^="#${modalPrefix}"]`).click({ force: true });
      });
      cy.get(`.modal.show[id^="${modalPrefix}"]`).find(`a[href*="${deleteHref}"] button`).click();
      cy.get('#table_id').should('not.contain', text);
    });
  };

  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // Filet de sécurité : si le test échoue en cours de route, on retire quand même les données créées
  after(() => {
    cy.loginAsAdmin();
    [shareTitle, urlTitle].forEach((title) =>
      deleteIfPresent('/download-center/content-share-list', title, 'deleteModal', 'content-share-list-delete')
    );
    deleteIfPresent('/download-center/content-list', fileName, 'deleteContentModal', 'content-list-delete');
    deleteIfPresent('/download-center/content-type', typeName, 'deleteTypeModal', 'content-type-delete');
  });

  it('partage un contenu à un groupe, le consulte puis le supprime, et fait de même avec un lien public', () => {
    createTypeAndContent();

    // ─────────────── 1. CREATE : partage à un groupe ───────────────
    selectContent();
    cy.get('#shareContentModal').click();
    cy.get('#shareContent').should('be.visible').within(() => {
      cy.get('.contentShowList').should('contain', fileName);
      cy.get('#title').type(shareTitle);
      cy.get('#shareDate').invoke('val').then((today) => {
        cy.get('#validUpto').invoke('val', formatLike(today, inFifteenDays()));
      });
      cy.get('#description').type('Partage créé par Cypress');
      cy.get('#selectTab').should('have.value', 'G');
      cy.get('#role_2').check({ force: true }); // Student
      // cy.screenshot(`${shot}/01-share-formulaire`);
    });
    cy.intercept('POST', '**/content-share-list-save').as('shareSave');
    cy.get('#save_button_qu').click();
    cy.wait('@shareSave').its('response.statusCode').should('eq', 200);
    // cy.screenshot(`${shot}/02-share-apres-submit`);

    // ─────────────── 2. READ ───────────────
    cy.visit('/download-center/content-share-list');
    cy.get('#table_id').contains('tr', shareTitle).should('exist');
    cy.get('#table_id').contains('tr', shareTitle).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[href*="content-view-link-modal"]').click({ force: true });
    });
    cy.get('#showDetaildModal', { timeout: 15000 })
      .should('be.visible')
      .and('contain', shareTitle)
      .and('contain', fileName);
    // cy.screenshot(`${shot}/03-share-detail`);

    // ─────────────── 3. DELETE ───────────────
    deleteShare(shareTitle);

    // ─────────────── 4. Générer une URL (partage public) ───────────────
    selectContent();
    cy.get('#generateUrlModal').click();
    cy.get('#generateUrl').should('be.visible').within(() => {
      cy.get('.contentShowList').should('contain', fileName);
      cy.get('#title').type(urlTitle);
      cy.get('#shareDateUrl').invoke('val').then((today) => {
        cy.get('#validUptoUrl').invoke('val', formatLike(today, inFifteenDays()));
      });
      // cy.screenshot(`${shot}/04-url-formulaire`);
    });
    cy.intercept('POST', '**/content-generate-url-save').as('urlSave');
    cy.get('#save_button_q').click();
    cy.wait('@urlSave').its('response.statusCode').should('eq', 200);
    // cy.screenshot(`${shot}/05-url-apres-submit`);

    cy.visit('/download-center/content-share-list');
    cy.get('#table_id').contains('tr', urlTitle).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[href*="content-share-link-modal"]').should('exist').click({ force: true });
    });
    cy.get('#showDetaildModal', { timeout: 15000 }).should('be.visible').and('contain', 'content-share-link');
    // cy.screenshot(`${shot}/06-url-lien`);

    // Le lien public affiche bien le contenu partagé
    cy.get('#textForClipboard').invoke('attr', 'href').then((href) => {
      cy.visit(href);
      cy.contains(fileName).should('exist');
      // cy.screenshot(`${shot}/07-url-page-publique`);
    });

    deleteShare(urlTitle);

    // ─────────────── 5. NETTOYAGE ───────────────
    cleanup();
  });
});
