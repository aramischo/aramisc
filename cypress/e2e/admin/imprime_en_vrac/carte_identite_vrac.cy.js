// cypress/e2e/admin/imprime_en_vrac/carte_identite_vrac.cy.js
//
// Scénario utilisateur :
//  0. Préparation : la base ne contient aucun modèle de carte d'identité → on en crée un via /create-id-card
//     (titre, mise en page, utilisateur applicable = Étudiant, logo + signature) → il apparaît dans /student-id-card
//  1. Imprimé en vrac > Carte d'identité vrac : « Rechercher » sans critère → erreurs de validation
//  2. Rôle « Student » → la liste des cartes se charge en AJAX → choix de notre carte, espacement 10 px
//     → « Rechercher » → l'aperçu d'impression affiche une carte par étudiant
//  3. Nettoyage : suppression du modèle de carte (base de test)
//
// Remarque : sans logo, la carte reprend le logo général de l'école. Jusqu'au 30/09/2026,
//   AramiscStudentIdCardController::destroy() supprimait alors ce fichier (corrigé depuis).
//   On téléverse quand même un logo propre à la carte pour ne jamais dépendre du logo de l'école.
//
// Captures d'écran : en commentaire pour l'instant.

describe('Admin – Imprimé en vrac : Carte d\'identité vrac', () => {
  const shot = 'admin/imprime_en_vrac/carte_identite_vrac';
  const stamp = Date.now();
  const cardTitle = `Carte_Vrac_${stamp}`;
  const STUDENT_ROLE = '2';

  // PNG 1x1 valide (règle Laravel `image`)
  const png = (fileName) => ({
    contents: Cypress.Buffer.from(
      'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
      'base64'
    ),
    fileName,
    mimeType: 'image/png',
  });

  const deleteCardIfPresent = () => {
    cy.visit('/student-id-card');
    cy.get('#table_id').then(($table) => {
      if (!$table.text().includes(cardTitle)) return;
      cy.wrap($table).contains('tr', cardTitle).within(() => {
        cy.get('.dropdown-toggle').click({ force: true });
        cy.get('a.dropdown-item[data-target^="#deleteIdCard"]').click({ force: true });
      });
      cy.get('.modal.show[id^="deleteIdCard"]').find('button[type="submit"]').click();
      // cy.screenshot(`${shot}/05-delete-apres-confirmation`);
      cy.get('#table_id').should('not.contain', cardTitle);
    });
  };

  beforeEach(() => {
    cy.loginAsAdmin();
    // Les aperçus lancent window.print() au chargement : on le neutralise
    cy.on('window:before:load', (win) => {
      win.print = () => {};
    });
  });

  // Filet de sécurité : le modèle est supprimé même si le test échoue en cours de route
  after(() => {
    cy.loginAsAdmin();
    deleteCardIfPresent();
  });

  it('crée un modèle de carte, imprime les cartes des étudiants en vrac puis supprime le modèle', () => {
    // ─────────────── 0. Préparation : modèle de carte ───────────────
    cy.visit('/create-id-card');
    cy.get('input[name="title"]').type(cardTitle);
    cy.get('select#pageLayoutStyle').select('horizontal', { force: true });
    cy.get('select#applicableUser').select(STUDENT_ROLE, { force: true });
    cy.get('input[name="logo"]').selectFile(png(`logo_${stamp}.png`), { force: true });
    cy.get('input[name="signature"]').selectFile(png(`signature_${stamp}.png`), { force: true });
    // cy.screenshot(`${shot}/01-carte-formulaire`);
    cy.get('button.savaIdCard').click();
    cy.url().should('match', /\/student-id-card\/?$/);
    cy.get('#table_id').should('contain', cardTitle);
    // cy.screenshot(`${shot}/02-carte-creee`);

    // ─────────────── 1. Validation ───────────────
    cy.visit('/bulkprint/student-id-card-bulk-print');
    cy.get('form[action$="/bulkprint/student-id-card-bulk-print"]').contains('button[type="submit"]', /recherch|search/i).click();
    cy.url().should('include', '/bulkprint/student-id-card-bulk-print');
    cy.get('select#role_id').should('have.class', 'is-invalid');
    cy.get('select#id_card_list').should('have.class', 'is-invalid');

    // ─────────────── 2. Recherche → aperçu d'impression ───────────────
    cy.intercept('GET', '**/bulkprint/ajaxIdCard*').as('ajaxIdCard');
    cy.get('select#role_id').select(STUDENT_ROLE, { force: true });
    cy.wait('@ajaxIdCard');
    cy.get('select#id_card_list option').contains(cardTitle).then(($o) => {
      cy.get('select#id_card_list').select($o.val(), { force: true });
    });
    cy.get('input[name="grid_gap"]').clear().type('10');
    // cy.screenshot(`${shot}/03-criteres-formulaire`);
    cy.get('form[action$="/bulkprint/student-id-card-bulk-print"]').contains('button[type="submit"]', /recherch|search/i).click();
    cy.get('.id_card', { timeout: 60000 }).should('have.length.at.least', 1);
    // cy.screenshot(`${shot}/04-apercu-impression`);

    // ─────────────── 3. Nettoyage ───────────────
    deleteCardIfPresent();
  });
});
