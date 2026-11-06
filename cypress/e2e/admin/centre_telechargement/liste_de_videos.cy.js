// cypress/e2e/admin/centre_telechargement/liste_de_videos.cy.js
//
// Scénario utilisateur (validé manuellement) :
//  1. Centre de téléchargement > Liste de vidéos : « Ajouter » → modale : classe, section, titre, lien YouTube → Sauvegarder
//     → la vidéo apparaît dans la liste
//  2. Icône crayon → modale d'édition pré-remplie → nouveau titre → Sauvegarder → la liste affiche le nouveau titre
//  3. Recherche par titre → la vidéo est retrouvée
//  4. Icône corbeille → confirmation → la vidéo disparaît
//
// Pré-requis : au moins une classe avec une section dans la base de test.
// Captures d'écran : en commentaire pour l'instant.

describe('Admin – Centre de téléchargement : Liste de vidéos (CRUD)', () => {
  const shot = 'admin/centre_telechargement/liste_de_videos';
  const stamp = Date.now();
  const title = `Video_${stamp}`;
  const updated = `${title}_Updated`;
  const link = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

  beforeEach(() => {
    cy.loginAsAdmin();
    cy.visit('/download-center/video-list');
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Recherche, Suppression) d'une vidéo", () => {
    // ─────────────── 1. CREATE ───────────────
    cy.get('button[data-target="#addVideo"]').click();
    cy.get('#addVideo').should('be.visible').within(() => {
      // 1re classe disponible, puis 1re section chargée en AJAX
      cy.get('select#common_select_class option').eq(1).then(($o) => {
        cy.get('select#common_select_class').select($o.val(), { force: true });
      });
      cy.get('select#common_select_section option', { timeout: 15000 }).should('have.length.greaterThan', 1);
      cy.get('select#common_select_section option').eq(1).then(($o) => {
        cy.get('select#common_select_section').select($o.val(), { force: true });
      });
      cy.get('input#title').type(title);
      cy.get('input#video_link').type(link);
      cy.get('textarea#description').type('Vidéo créée par Cypress');
      // cy.screenshot(`${shot}/01-create-formulaire`);
      cy.get('#save_button_query').click();
    });
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────── 2. READ ───────────────
    cy.url().should('include', '/download-center/video-list');
    cy.contains('.single-video-item', title).should('exist');

    // ─────────────── 3. UPDATE ───────────────
    cy.contains('.single-video-item', title).find('a[href*="video-list-edit-modal"]').click({ force: true });
    cy.get('#showDetaildModal', { timeout: 15000 }).should('be.visible').within(() => {
      cy.get('input[name="title"]').should('have.value', title).clear().type(updated);
      // cy.screenshot(`${shot}/03-update-formulaire`);
      cy.get('input[name="video_link"]').should('have.value', link);
      cy.get('button.submit').click();
    });
    // cy.screenshot(`${shot}/04-update-apres-submit`);
    cy.url().should('include', '/download-center/video-list');
    cy.contains('.single-video-item', updated).should('exist');

    // ─────────────── 4. SEARCH ───────────────
    cy.get('form[action*="video-list-search"] input[name="title"]').type(updated);
    // cy.screenshot(`${shot}/05-search-formulaire`);
    cy.get('form[action*="video-list-search"] button[type="submit"]').click();
    // cy.screenshot(`${shot}/06-search-apres-submit`);
    cy.url().should('include', '/video-list-search');
    cy.contains('.single-video-item', updated).should('exist');

    // ─────────────── 5. DELETE ───────────────
    cy.contains('.single-video-item', updated).find('a[data-target^="#deleteModal"]').click({ force: true });
    cy.get('.modal.show[id^="deleteModal"]')
      .should('be.visible')
      .find('a[href*="video-list-delete"] button')
      .click();
    // cy.screenshot(`${shot}/07-delete-apres-confirmation`);
    cy.url().should('include', '/download-center/video-list');
    cy.contains('.single-video-item', updated).should('not.exist');
  });
});
