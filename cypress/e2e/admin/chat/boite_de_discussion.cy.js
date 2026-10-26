// cypress/e2e/admin/chat/boite_de_discussion.cy.js
//
// Scénario utilisateur :
//  1. Admin : Chat > Boîte de discussion → conversation avec « studen1 student » → message (éditeur Quill) → Envoyer
//     → le message apparaît dans la conversation
//  2. Élève (student1) : Chat → conversation avec l'admin → le message est visible
//  3. Admin : menu du message (flèche) → Supprimer → le message disparaît
//  4. Nettoyage (after) : messages et notifications (table `notifications`) créés par le test,
//     jetons d'appareil restaurés
//
// Aucun envoi externe : chatting_method = log (pas de Pusher) et device_token vidés pendant le test
// (MessageNotification passe par le canal `fcm` → FcmChannel appelle Google dès qu'un jeton existe).
// Captures d'écran : en commentaire pour l'instant.

import { php } from '../portefeuille/helpers';

describe('Admin – Chat : Boîte de discussion', () => {
  const shot = 'admin/chat/boite_de_discussion';
  const stamp = Date.now();
  const message = `Message_Cypress_${stamp}`;
  let ids;

  const emails = "['admin@aramisc.com','student1@aramisc.com']";

  // Les jetons d'appareil (device_token) sont vidés pendant le test : sinon chaque message déclenche
  // un push vers Google (ancienne API FCM, arrêtée → 404). Ils sont restaurés à l'identique dans after().
  before(() => {
    php(`
      $users = DB::table('users')->whereIn('email', ${emails})->get(['id', 'email', 'device_token']);
      DB::table('users')->whereIn('email', ${emails})->update(['device_token' => null]);
      echo 'CY<<'.base64_encode(json_encode([
        'admin' => $users->firstWhere('email', 'admin@aramisc.com')->id,
        'student' => $users->firstWhere('email', 'student1@aramisc.com')->id,
        'tokens' => $users->pluck('device_token', 'id'),
        'start' => DB::selectOne('select now() as n')->n,
      ])).'>>CY';
    `).then((v) => {
      ids = v;
    });
  });

  after(() => {
    if (!ids) return;
    const tokens = Cypress.Buffer.from(JSON.stringify(ids.tokens)).toString('base64');
    php(`
      foreach (json_decode(base64_decode('${tokens}'), true) as $id => $token) {
        DB::table('users')->where('id', $id)->update(['device_token' => $token]);
      }
      DB::table('chat_conversations')->where('message', 'like', '%${message}%')->delete();
      DB::table('notifications')->where('created_at', '>=', '${ids.start}')->delete();
    `);
  });

  it("envoie un message à un élève, l'élève le lit, puis l'admin le supprime", () => {
    // ─────────────── 1. Envoi (admin) ───────────────
    cy.loginAsAdmin();
    cy.visit(`/chat/open/${ids.student}`);
    cy.get('.ql-editor').should('be.visible').type(message);
    // cy.screenshot(`${shot}/01-message-formulaire`);
    cy.intercept('POST', '**/chat/send/user').as('send');
    cy.get('button.svg_send_button').click();
    cy.wait('@send').its('response.statusCode').should('eq', 200);
    // ⚠ Anomalie : l'expéditeur ne voit son message qu'après rechargement (messagePush commenté dans
    //   JqueryChatComponent, et newMessageCheck ne renvoie que les messages reçus).
    cy.reload();
    cy.contains('span.textmsg', message, { timeout: 20000 }).should('exist');
    // cy.screenshot(`${shot}/02-message-envoye`);

    // ─────────────── 2. Lecture (élève) ───────────────
    cy.loginAsStudent();
    cy.visit(`/chat/open/${ids.admin}`);
    cy.contains('span.textmsg', message, { timeout: 20000 }).should('exist');
    // cy.screenshot(`${shot}/03-message-lu-par-eleve`);

    // ─────────────── 3. Suppression (admin) ───────────────
    cy.loginAsAdmin();
    cy.visit(`/chat/open/${ids.student}`);
    cy.intercept('POST', '**/chat/delete').as('delete');
    cy.contains('span.textmsg', message, { timeout: 20000 })
      .closest('.chat_text_info_wraper')
      .find('.dropdown-menu a.dropdown-item')
      .last() // Citer, Transférer, Supprimer
      .click({ force: true });
    cy.wait('@delete').its('response.body.success').should('eq', true);
    cy.contains('span.textmsg', message).should('not.exist');
    // cy.screenshot(`${shot}/04-message-supprime`);
    cy.reload();
    cy.get('.ql-editor').should('be.visible');
    cy.contains('span.textmsg', message).should('not.exist');
  });
});
