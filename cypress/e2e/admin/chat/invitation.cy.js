// cypress/e2e/admin/chat/invitation.cy.js
//
// Scénario utilisateur :
//  0. Précondition : l'admin n'est pas encore connecté à « teacher1 teacher1 » (aucune invitation entre eux)
//  1. Admin : Chat > Boîte de discussion → recherche « teacher1 » → « Démarrer »
//     → la conversation avec teacher1 s'ouvre
//  2. Chat > Invitation : teacher1 apparaît dans « Connexion connectée à vous »
//  3. Nettoyage (after) : suppression de la connexion créée (l'interface ne permet pas de la défaire)
//
// Réglages du chat (storage/app/chat/school_settings.json, partagé avec le dev — on n'y touche pas) :
//   chat_invitation_requirement = none → « Démarrer » connecte directement (invitation créée au statut 1).
//   Le parcours « demande en attente → Accepter / Rejeter » n'est donc pas accessible par l'interface.
//
// Captures d'écran : en commentaire pour l'instant.

import { php } from '../portefeuille/helpers';

describe('Admin – Chat : Invitation', () => {
  const shot = 'admin/chat/invitation';
  let ctx;

  before(() => {
    php(`
      $admin = DB::table('users')->where('email', 'admin@aramisc.com')->value('id');
      $teacher = DB::table('users')->where('email', 'teacher1@aramisc.com')->first(['id', 'full_name']);
      echo 'CY<<'.base64_encode(json_encode([
        'admin' => $admin,
        'teacher' => $teacher->id,
        'teacherName' => $teacher->full_name,
        'maxInvitation' => (int) DB::table('chat_invitations')->max('id'),
        'existing' => DB::table('chat_invitations')
          ->where(fn ($q) => $q->where('from', $admin)->where('to', $teacher->id))
          ->orWhere(fn ($q) => $q->where('from', $teacher->id)->where('to', $admin))
          ->count(),
      ])).'>>CY';
    `).then((v) => {
      ctx = v;
      expect(ctx.existing, "précondition : pas encore d'invitation admin ↔ teacher1").to.eq(0);
    });
  });

  after(() => {
    if (!ctx) return;
    php(`
      DB::table('chat_invitations')->where('id', '>', ${ctx.maxInvitation})
        ->whereIn('from', [${ctx.admin}, ${ctx.teacher}])->whereIn('to', [${ctx.admin}, ${ctx.teacher}])->delete();
    `);
  });

  it('connecte l\'admin à un enseignant depuis la recherche et le retrouve dans les invitations', () => {
    cy.loginAsAdmin();

    // ─────────────── Avant : teacher1 n'est pas dans les connexions ───────────────
    cy.visit('/chat/invitation/index');
    cy.contains('h3', /connexion connectée|connected/i).should('exist');
    cy.get('body').should('not.contain', ctx.teacherName);

    // ─────────────── 1. Recherche → Démarrer ───────────────
    cy.visit('/chat/open');
    cy.get('#users_list_sidebar').type('teacher1');
    // cy.screenshot(`${shot}/01-recherche`);
    cy.get('#users_list_sidebar').closest('form').submit();
    cy.url().should('include', '/chat/user/search');
    cy.get(`form[action$="/chat/invitation/user/open"] input[name="to"][value="${ctx.teacher}"]`)
      .closest('form')
      .find('a.primary-btn')
      .click();
    // cy.screenshot(`${shot}/02-demarrer`);
    cy.url().should('match', new RegExp(`/chat/open/${ctx.teacher}$`));

    // ─────────────── 2. Invitation : teacher1 connecté ───────────────
    cy.visit('/chat/invitation/index');
    cy.contains(ctx.teacherName).should('exist');
    // cy.screenshot(`${shot}/03-connecte`);
  });
});
