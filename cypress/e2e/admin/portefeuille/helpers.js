// cypress/e2e/admin/portefeuille/helpers.js
//
// Fonctions partagées par les specs de la rubrique « Portefeuille ».
// (Ce fichier n'est pas un spec : il ne finit pas par .cy.js)
//
// ⚠ Chaque action du portefeuille (ajout, approbation, rejet, remboursement) déclenche des notifications
//   Email + SMS + App (push Firebase) réelles : la base de test a le SMTP Gmail et TextLocal actifs.
//   setupWallet() coupe Email/SMS/App pour les 4 événements wallet (Web reste actif : simple ligne en base)
//   et teardownWallet() rétablit les réglages à l'identique, supprime les transactions / notifications créées
//   et remet le solde de l'élève à sa valeur de départ.
//   Si Cypress plante avant teardownWallet(), les notifications wallet restent COUPÉES (rien n'est envoyé) :
//   remettre Email/SMS/App à 1 dans aramisc_notification_settings.
//
// L'interface ne permet pas de supprimer une transaction : le nettoyage passe par la tâche `artisan` (tinker).

const WALLET_EVENTS = "['Wallet_Add','Approve_Deposit','Reject_Deposit','Refund_Deposit']";
const STUDENT_EMAIL = 'student1@aramisc.com';

const b64 = (value) => Cypress.Buffer.from(value).toString('base64');

/**
 * Exécute du PHP dans l'application (base de test) via `php artisan tinker`.
 * Le code est passé en base64 pour éviter tout problème d'échappement sous cmd.exe.
 * Pour renvoyer une valeur : echo 'CY<<'.base64_encode(json_encode($x)).'>>CY';
 */
export const php = (code) =>
  cy
    .task('artisan', `tinker --execute="eval(base64_decode('${b64(code)}'));"`, { timeout: 60000 })
    .then((out) => {
      const m = String(out).match(/CY<<(.*?)>>CY/);
      return m ? JSON.parse(Cypress.Buffer.from(m[1], 'base64').toString()) : null;
    });

/** Solde du portefeuille de l'élève de test (nombre). */
export const studentBalance = () =>
  php(`echo 'CY<<'.base64_encode(json_encode(DB::table('users')->where('email','${STUDENT_EMAIL}')->value('wallet_balance'))).'>>CY';`)
    .then((v) => Number(v));

/**
 * Coupe Email/SMS/App des notifications wallet et mémorise l'état de départ.
 * Renvoie (via cy) la sauvegarde à passer à teardownWallet().
 */
export const setupWallet = () =>
  php(`
    $dest = DB::table('aramisc_notification_settings')->whereIn('event', ${WALLET_EVENTS})->pluck('destination', 'id');
    $user = DB::table('users')->where('email', '${STUDENT_EMAIL}')->first(['id', 'wallet_balance']);
    $backup = [
      'dest' => $dest,
      'uid' => $user->id,
      'balance' => $user->wallet_balance,
      'maxWallet' => (int) DB::table('wallet_transactions')->max('id'),
      'maxNotif' => (int) DB::table('aramisc_notifications')->max('id'),
    ];
    foreach ($dest as $id => $d) {
      $x = json_decode($d, true);
      $x['Email'] = 0; $x['SMS'] = 0; $x['App'] = 0;
      DB::table('aramisc_notification_settings')->where('id', $id)->update(['destination' => json_encode($x)]);
    }
    echo 'CY<<'.base64_encode(json_encode($backup)).'>>CY';
  `).then((backup) => {
    expect(backup, 'sauvegarde avant test').to.have.property('uid');
    return backup;
  });

/** Rétablit les notifications et supprime tout ce que le test a créé. */
export const teardownWallet = (backup) => {
  if (!backup) return;
  php(`
    $b = json_decode(base64_decode('${b64(JSON.stringify(backup))}'), true);
    foreach ($b['dest'] as $id => $d) {
      DB::table('aramisc_notification_settings')->where('id', $id)->update(['destination' => $d]);
    }
    DB::table('wallet_transactions')->where('id', '>', $b['maxWallet'])->where('user_id', $b['uid'])->delete();
    DB::table('aramisc_notifications')->where('id', '>', $b['maxNotif'])->delete();
    DB::table('users')->where('id', $b['uid'])->update(['wallet_balance' => $b['balance']]);
    echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_notification_settings')->whereIn('event', ${WALLET_EVENTS})->pluck('destination', 'id'))).'>>CY';
  `).then((restored) => {
    expect(restored, 'réglages de notification rétablis').to.deep.equal(backup.dest);
  });
};

/** L'élève ajoute un montant à son portefeuille par virement bancaire (Mon portefeuille > Ajouter un solde). */
export const studentDeposit = (amount, note, shot) => {
  cy.loginAsStudent();
  cy.visit('/wallet/my-wallet');
  cy.get('[data-target="#addWalletPayment"]').click();
  cy.get('#addWalletPayment').should('be.visible').within(() => {
    cy.get('#walletAmount').type(String(amount));
    cy.get('select#addWalletPaymentMethod').select('Bank', { force: true });
    cy.get('select[name="bank"] option').eq(1).then(($o) => {
      cy.get('select[name="bank"]').select($o.val(), { force: true });
    });
    cy.get('#note').type(note);
    // cy.screenshot(`${shot}/00a-depot-formulaire`);
  });
  cy.intercept('POST', '**/wallet/add-wallet-amount').as('addWallet');
  cy.get('form#addWalletAmount button.generalPay').click();
  cy.wait('@addWallet').its('response.statusCode').should('eq', 200);
  // cy.screenshot(`${shot}/00b-depot-apres-submit`);
};

/** Ligne d'un tableau wallet (admin) repérée par la note horodatée de la transaction. */
export const rowByNote = (note) =>
  cy.get(`#table_id [data-note="${note}"]`, { timeout: 20000 }).first().closest('tr');

/** L'admin approuve ou rejette le dépôt en attente portant la note donnée. */
export const adminDecideDeposit = (note, decision, rejectNote, shot) => {
  cy.loginAsAdmin();
  cy.visit('/wallet/pending-diposit');
  rowByNote(note).within(() => {
    cy.get(`a.dropdown-item[class*="${decision === 'approve' ? 'approvePayment' : 'rejectwalletPayment'}"]`)
      .click({ force: true });
  });
  if (decision === 'approve') {
    cy.get('#approvePayment').should('be.visible').find('button[type="submit"]').click();
  } else {
    cy.get('#rejectwalletPayment').should('be.visible').within(() => {
      cy.get('textarea[name="reject_note"]').type(rejectNote);
      // cy.screenshot(`${shot}/00c-rejet-formulaire`);
      cy.get('button[type="submit"]').click();
    });
  }
  cy.url().should('include', '/wallet/pending-diposit');
  cy.get('#table_id tbody tr', { timeout: 20000 }).should('exist');
  cy.get(`#table_id [data-note="${note}"]`).should('not.exist');
};
