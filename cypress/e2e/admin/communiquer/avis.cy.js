// cypress/e2e/admin/communiquer/avis.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Communiquer > Panneau d'avis > Ajouter : titre, date de l'avis 15/10/2026, publication le 16/10/2026,
//     destinataires « Teacher » → l'avis apparaît dans la liste (accordéon)
//  2. « Modifier » : formulaire pré-rempli, il change le titre et la date de publication (17/10/2026) → la liste est à jour
//  3. « Supprimer » (fenêtre chargée en AJAX), confirme → l'avis disparaît (contrôle en base)
//
// Le message (éditeur CKEditor) est facultatif et n'est pas saisi.
// ⚠ La création notifie les rôles choisis (Notice) : muteMessaging() coupe Email/SMS/App (+ réglages e-mail / SMS),
//   restoreMessaging() rétablit et supprime les notifications Web créées. after() supprime l'avis s'il reste.
// Dates volontairement > 12 : cas réel d'un utilisateur FR (format j/m/aaaa).

import { php, muteMessaging, restoreMessaging, toastSuccess } from '../administration/helpers';

describe("Admin – Communiquer : Panneau d'avis (CRUD)", () => {
  const shot = 'admin/communiquer/avis';
  const prefix = 'CyAvis_';
  const title = `${prefix}${String(Date.now()).slice(-8)}`; // titre limité à 50 caractères
  const updatedTitle = `${title}_M`;
  const TEACHER_ROLE = '4';
  let backup;

  before(() => {
    muteMessaging(['Notice']).then((b) => { backup = b; });
  });

  after(() => {
    php(`DB::table('aramisc_notice_boards')->where('notice_title', 'like', '${prefix}%')->delete();`);
    restoreMessaging(backup);
  });

  const notice = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_notice_boards')->whereIn('notice_title', ['${title}', '${updatedTitle}'])
      ->first(['id', 'notice_title', 'notice_date', 'publish_on', 'inform_to']))).'>>CY';`);

  const card = (text) => cy.contains('#accordion .card', text, { timeout: 20000 });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un avis", () => {
    cy.loginAsAdmin();
    cy.visit('/add-notice');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="notice_title"]').clear().type(title);
    cy.get('#notice_date').invoke('val', '15/10/2026');
    cy.get('#publish_on').invoke('val', '16/10/2026');
    cy.get(`#role_${TEACHER_ROLE}`).check({ force: true });
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('form[action$="save-notice-data"] button.primary-btn.submit').click();
    toastSuccess();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    notice().then((n) => {
      expect(n, 'avis enregistré').to.not.be.null;
      expect(n).to.include({ notice_date: '2026-10-15', publish_on: '2026-10-16' });
      expect(JSON.parse(n.inform_to)).to.deep.equal([TEACHER_ROLE]);
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.visit('/notice-list');
    card(title).should('exist');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    card(title).find('a[href*="edit-notice/"]').click({ force: true });

    cy.url().should('include', '/edit-notice/');
    cy.get('input[name="notice_title"]').should('have.value', title).clear().type(updatedTitle);
    cy.get('#notice_date').should('have.value', '15/10/2026');
    cy.get('#publish_on').invoke('val', '17/10/2026');
    cy.get(`#role${TEACHER_ROLE}`).should('be.checked'); // page de modification : id « role4 » (« role_4 » à la création)
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('form[action$="update-notice-data"] button.primary-btn.fix-gr-bg').last().click();
    toastSuccess();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    notice().then((n) => {
      expect(n).to.include({ notice_title: updatedTitle, notice_date: '2026-10-15', publish_on: '2026-10-17' });
    });

    // ─────────────────────────── 4. DELETE ───────────────────────────
    cy.visit('/notice-list');
    card(updatedTitle).find('a.deleteUrl[href*="delete-notice-view"]').click({ force: true });
    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible')
      .find('a[href*="delete-notice/"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    notice().should('be.null');
    cy.get('#accordion').should('not.contain', updatedTitle);
  });
});
