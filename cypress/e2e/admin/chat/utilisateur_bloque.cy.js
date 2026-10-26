// cypress/e2e/admin/chat/utilisateur_bloque.cy.js
//
// Scénario utilisateur :
//  1. Admin : Chat > Boîte de discussion → menu de « studen1 student » → « Bloquer cet utilisateur »
//  2. Chat > Utilisateur bloqué : l'élève apparaît dans la liste
//  3. « Débloquer » → l'élève disparaît de la liste des utilisateurs bloqués
//  4. Nettoyage (after) : si le test échoue en cours de route, le blocage créé est supprimé
//
// Captures d'écran : en commentaire pour l'instant.

import { php } from '../portefeuille/helpers';

describe('Admin – Chat : Utilisateur bloqué', () => {
  const shot = 'admin/chat/utilisateur_bloque';
  let ctx;

  before(() => {
    php(`
      echo 'CY<<'.base64_encode(json_encode([
        'student' => DB::table('users')->where('email', 'student1@aramisc.com')->first(['id', 'full_name']),
        'maxBlock' => (int) DB::table('chat_block_users')->max('id'),
      ])).'>>CY';
    `).then((v) => {
      ctx = v;
    });
  });

  after(() => {
    if (!ctx) return;
    php(`DB::table('chat_block_users')->where('id', '>', ${ctx.maxBlock})->delete();`);
  });

  it('bloque un élève, le retrouve dans les utilisateurs bloqués puis le débloque', () => {
    cy.loginAsAdmin();

    // ─────────────── 1. Blocage ───────────────
    cy.visit('/chat/open');
    cy.get(`a[href$="/chat/user/action/block/${ctx.student.id}"]`).first().click({ force: true });
    // cy.screenshot(`${shot}/01-apres-blocage`);
    cy.url().should('include', '/chat/open');

    // ─────────────── 2. Liste des utilisateurs bloqués ───────────────
    cy.visit('/chat/users/blocked');
    cy.get(`a[href$="/chat/user/action/unblock/${ctx.student.id}"]`).should('exist');
    cy.contains(ctx.student.full_name).should('exist');
    // cy.screenshot(`${shot}/02-liste-bloques`);

    // ─────────────── 3. Déblocage ───────────────
    cy.get(`a[href$="/chat/user/action/unblock/${ctx.student.id}"]`).first().click({ force: true });
    // cy.screenshot(`${shot}/03-apres-deblocage`);
    cy.url().should('include', '/chat/open');
    cy.visit('/chat/users/blocked');
    cy.get(`a[href$="/chat/user/action/unblock/${ctx.student.id}"]`).should('not.exist');
    // cy.screenshot(`${shot}/04-liste-vide`);
  });
});
