// cypress/e2e/admin/administration/carte_identite.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Administration > Carte d'identité > Créer : titre, mise en page horizontale, utilisateur « Étudiant »,
//     logo + signature (PNG) → le modèle apparaît dans la liste
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il renomme et passe en mise en page verticale → la liste est à jour
//  3. « Sélectionner > Supprimer », confirme → le modèle disparaît (contrôle en base)
//
// Logo et signature propres à la carte : sans logo, la carte reprend celui de l'école (cf. anomalie 7, corrigée).
// Aucune notification (AramiscStudentIdCardController). after() supprime le modèle s'il reste.
// La génération / impression des cartes est testée dans imprime_en_vrac/carte_identite_vrac.cy.js.

import { php, toastSuccess } from './helpers';

describe("Admin – Administration : Modèle de carte d'identité (CRUD)", () => {
  const shot = 'admin/administration/carte_identite';
  const stamp = Date.now();
  const prefix = 'CyCarte_';
  const title = `${prefix}${stamp}`;
  const updatedTitle = `${title}_M`;
  const STUDENT_ROLE = '2';

  // PNG 1x1 valide (règle Laravel `image`)
  const png = (fileName) => ({
    contents: Cypress.Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==', 'base64'),
    fileName,
    mimeType: 'image/png',
  });

  after(() => {
    php(`DB::table('aramisc_student_id_cards')->where('title', 'like', '${prefix}%')->delete();`);
  });

  const card = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_student_id_cards')->whereIn('title', ['${title}', '${updatedTitle}'])
      ->first(['title', 'page_layout_style', 'role_id', 'logo', 'signature']))).'>>CY';`);

  const cardAction = (text, selector) =>
    cy.get('#table_id', { timeout: 20000 }).contains('tr', text).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get(selector).click({ force: true });
    });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un modèle de carte", () => {
    cy.loginAsAdmin();
    cy.visit('/create-id-card');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="title"]').clear().type(title);
    cy.get('select#pageLayoutStyle').select('horizontal', { force: true });
    cy.get('select#applicableUser').select(STUDENT_ROLE, { force: true });
    cy.get('input[name="logo"]').selectFile(png(`logo_${stamp}.png`), { force: true });
    cy.get('input[name="signature"]').selectFile(png(`signature_${stamp}.png`), { force: true });
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.savaIdCard').click();
    toastSuccess();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    card().then((c) => {
      expect(c, 'modèle enregistré').to.not.be.null;
      expect(c.page_layout_style).to.eq('horizontal');
      expect(JSON.parse(c.role_id)).to.include(STUDENT_ROLE);
      expect(c.logo, "logo propre à la carte (pas celui de l'école)").to.contain('uploads/studentIdCard/');
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/student-id-card\/?$/);
    cy.get('#table_id').contains('tr', title).should('exist');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    cardAction(title, 'a.dropdown-item[href*="student-id-card/"]');

    cy.url().should('match', /\/student-id-card\/\d+$/);
    cy.get('input[name="title"]').should('have.value', title).clear().type(updatedTitle);
    cy.get('select#pageLayoutStyle').should('have.value', 'horizontal').select('vertical', { force: true });
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.savaIdCard').click();
    toastSuccess();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    card().then((c) => {
      expect(c.title).to.eq(updatedTitle);
      expect(c.page_layout_style).to.eq('vertical');
    });
    cy.get('#table_id').contains('tr', updatedTitle).should('exist');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    cardAction(updatedTitle, 'a.dropdown-item[data-target^="#deleteIdCard"]');
    cy.get('.modal.show[id^="deleteIdCard"]').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    card().should('be.null');
    cy.get('#table_id').should('not.contain', updatedTitle);
  });
});
