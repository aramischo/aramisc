// cypress/e2e/admin/rh/demande_conge.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Congés > Demander un congé : type, du 15/10/2026 au 17/10/2026, motif → la demande apparaît « En attente »
//  2. « Sélectionner > Modifier » (possible tant qu'elle est en attente) : il prolonge au 18/10/2026 et change le motif
//  3. Congés > Demandes de congé en attente : « Sélectionner > Voir », il choisit « Approuvé » → la demande passe dans
//     « Approuver la demande de congé » avec le statut Approuvé
//  4. Approuver la demande de congé : « Sélectionner > Supprimer », confirme → la demande disparaît (contrôle en base)
//
// L'admin n'a aucun congé défini : before() crée un type de congé et le lui attribue (10 jours) ; after() les supprime
// avec toute demande restante.
// ⚠ La demande déclenche la notification Leave_Apply, un send_sms() direct et un push FCM (LeaveApprovedNotification) :
//   muteMessaging() coupe Email/SMS/App, réglages e-mail, passerelles SMS et le jeton push de l'admin ; restoreMessaging() rétablit.
// Dates volontairement > 12 : cas réel d'un utilisateur FR (format j/m/aaaa).

import { php, findRow, rowAction } from './helpers';
import { muteMessaging, restoreMessaging } from '../administration/helpers';

describe('Admin – RH : Demande de congé (demande, modification, approbation, suppression)', () => {
  const shot = 'admin/rh/demande_conge';
  const typeName = `CyTypeDemande_${Date.now()}`;
  const ADMIN = 'admin@aramisc.com';
  let ctx; // { defineId }
  let backup;

  before(() => {
    muteMessaging(['Leave_Apply', 'Leave_Approved', 'Leave_Declined'], [ADMIN]).then((b) => { backup = b; });
    php(`
      $admin = DB::table('users')->where('email', '${ADMIN}')->first(['id', 'role_id']);
      $type = DB::table('aramisc_leave_types')->insertGetId([
        'type' => '${typeName}', 'total_days' => 0, 'active_status' => 1, 'school_id' => 1,
        'academic_id' => getAcademicId(), 'created_at' => now(), 'updated_at' => now(),
      ]);
      $define = DB::table('aramisc_leave_defines')->insertGetId([
        'role_id' => $admin->role_id, 'user_id' => $admin->id, 'type_id' => $type, 'days' => 10, 'active_status' => 1,
        'school_id' => 1, 'academic_id' => getAcademicId(), 'created_at' => now(), 'updated_at' => now(),
      ]);
      echo 'CY<<'.base64_encode(json_encode(['defineId' => $define, 'adminName' => DB::table('users')->where('id', $admin->id)->value('full_name')])).'>>CY';
    `).then((r) => { ctx = r; });
  });

  after(() => {
    php(`
      $type = DB::table('aramisc_leave_types')->where('type', '${typeName}')->value('id');
      DB::table('aramisc_leave_requests')->where('type_id', $type)->delete();
      DB::table('aramisc_leave_defines')->where('type_id', $type)->delete();
      DB::table('aramisc_leave_types')->where('id', $type)->delete();
    `);
    restoreMessaging(backup);
  });

  const request = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_leave_requests')->where('leave_define_id', ${ctx.defineId})
      ->get(['id', 'leave_from', 'leave_to', 'reason', 'approve_status']))).'>>CY';`);

  // Pages « en attente » / « approuver » : la recherche rapide ne renvoie rien (même sur le nom, anomalie 30)
  // → ligne du type de congé du test cherchée directement dans le tableau (base de test : peu de demandes)
  const leaveRow = () => cy.get('#table_id tbody', { timeout: 20000 }).contains('tr', typeName);
  const leaveAction = (selector) =>
    leaveRow().within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get(selector).click({ force: true });
    });

  it('dépose une demande de congé, la modifie, l\'approuve puis la supprime', () => {
    cy.loginAsAdmin();
    cy.visit('/apply-leave');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('select[name="leave_define_id"]').select(String(ctx.defineId), { force: true });
    cy.get('input[name="leave_from"]').invoke('val', '15/10/2026');
    cy.get('input[name="leave_to"]').invoke('val', '17/10/2026');
    cy.get('textarea[name="reason"]').clear().type('Raison familiale (Cypress)');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    request().then((r) => {
      expect(r, 'une demande créée').to.have.length(1);
      expect(r[0]).to.include({ leave_from: '2026-10-15', leave_to: '2026-10-17', approve_status: 'P' });
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/apply-leave$/);
    findRow(typeName).should('contain', '15/10/2026').and('contain', '17/10/2026');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(typeName, 'a.dropdown-item[href*="apply-leave/"]');

    cy.url().should('match', /\/apply-leave\/\d+$/);
    cy.get('select[name="leave_define_id"]').should('have.value', String(ctx.defineId));
    cy.get('input[name="leave_from"]').should('have.value', '15/10/2026');
    cy.get('input[name="leave_to"]').invoke('val', '18/10/2026');
    cy.get('textarea[name="reason"]').clear().type('Raison familiale prolongée');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    request().then((r) => {
      expect(r[0]).to.include({ leave_to: '2026-10-18', reason: 'Raison familiale prolongée', approve_status: 'P' });
    });

    // ───────────────────────── 4. APPROBATION ─────────────────────────
    cy.visit('/pending-leave');
    leaveAction('a.dropdown-item.modalLink[href*="view-leave-details-approve"]');
    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible').within(() => {
      cy.contains('Raison familiale prolongée').should('exist');
      cy.get('input[name="approve_status"][value="A"]').check({ force: true });
      // cy.screenshot(`${shot}/05-approbation-formulaire`);
      cy.get('button.submit').click();
    });
    // cy.screenshot(`${shot}/06-approbation-apres-submit`);

    request().its('0.approve_status').should('eq', 'A');
    cy.url().should('include', '/approve-leave');
    leaveRow().should('contain', '18/10/2026');
    cy.visit('/pending-leave');
    cy.get('#table_id tbody tr', { timeout: 20000 }).should('exist');
    cy.get('#table_id tbody').should('not.contain', typeName);

    // ─────────────────────────── 5. DELETE ───────────────────────────
    cy.visit('/approve-leave');
    leaveAction('a.dropdown-item[onclick^="deleteApplyLeave"]');
    cy.get('#deleteApplyLeaveModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/07-delete-apres-confirmation`);

    request().should('have.length', 0);
  });
});
