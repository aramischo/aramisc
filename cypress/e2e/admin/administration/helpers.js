// cypress/e2e/admin/administration/helpers.js
//
// Fonctions partagées par les specs de la rubrique « Administration ».
// (Ce fichier n'est pas un spec : il ne finit pas par .cy.js)

import { php } from '../portefeuille/helpers';

export { php };

const b64 = (value) => Cypress.Buffer.from(value).toString('base64');

/**
 * Coupe tout envoi réel pendant un test : Email/SMS/App des événements de notification donnés,
 * réglages e-mail (aramisc_email_settings, utilisés par send_mail()), passerelles SMS (send_sms()),
 * jetons push (device_token) des utilisateurs donnés par e-mail. Web reste actif (simple ligne en base).
 * Renvoie (via cy) la sauvegarde à passer à restoreMessaging().
 * Si Cypress plante avant restoreMessaging(), ces réglages restent coupés : les rétablir à la main.
 */
export const muteMessaging = (events, userEmails = []) =>
  php(`
    $events = json_decode(base64_decode('${b64(JSON.stringify(events))}'), true);
    $emails = json_decode(base64_decode('${b64(JSON.stringify(userEmails))}'), true);
    $b = [
      'notif' => DB::table('aramisc_notification_settings')->whereIn('event', $events)->pluck('destination', 'id'),
      'email' => DB::table('aramisc_email_settings')->pluck('active_status', 'id'),
      'sms' => DB::table('aramisc_sms_gateways')->pluck('active_status', 'id'),
      'tokens' => DB::table('users')->whereIn('email', $emails)->pluck('device_token', 'id'),
      'maxNotif' => (int) DB::table('aramisc_notifications')->max('id'),
    ];
    foreach ($b['notif'] as $id => $d) {
      $x = json_decode($d, true); $x['Email'] = 0; $x['SMS'] = 0; $x['App'] = 0;
      DB::table('aramisc_notification_settings')->where('id', $id)->update(['destination' => json_encode($x)]);
    }
    DB::table('aramisc_email_settings')->update(['active_status' => 0]);
    DB::table('aramisc_sms_gateways')->update(['active_status' => 0]);
    DB::table('users')->whereIn('email', $emails)->update(['device_token' => null]);
    echo 'CY<<'.base64_encode(json_encode($b)).'>>CY';
  `).then((b) => {
    expect(b, 'sauvegarde des réglages d\'envoi').to.have.property('maxNotif');
    return b;
  });

/** Rétablit les réglages sauvegardés par muteMessaging() et supprime les notifications créées pendant le test. */
export const restoreMessaging = (b) => {
  if (!b) return;
  php(`
    $b = json_decode(base64_decode('${b64(JSON.stringify(b))}'), true);
    DB::table('aramisc_notifications')->where('id', '>', $b['maxNotif'])->delete();
    foreach ($b['notif'] as $id => $d) { DB::table('aramisc_notification_settings')->where('id', $id)->update(['destination' => $d]); }
    foreach ($b['email'] as $id => $s) { DB::table('aramisc_email_settings')->where('id', $id)->update(['active_status' => $s]); }
    foreach ($b['sms'] as $id => $s) { DB::table('aramisc_sms_gateways')->where('id', $id)->update(['active_status' => $s]); }
    foreach ($b['tokens'] as $id => $t) { DB::table('users')->where('id', $id)->update(['device_token' => $t]); }
    echo 'CY<<'.base64_encode(json_encode([
      'notif' => DB::table('aramisc_notification_settings')->whereIn('id', array_keys($b['notif']))->pluck('destination', 'id'),
      'email' => DB::table('aramisc_email_settings')->pluck('active_status', 'id'),
      'sms' => DB::table('aramisc_sms_gateways')->pluck('active_status', 'id'),
    ])).'>>CY';
  `).then((r) => {
    expect(r.notif, 'notifications rétablies').to.deep.equal(b.notif);
    expect(r.email, 'réglages e-mail rétablis').to.deep.equal(b.email);
    expect(r.sms, 'passerelles SMS rétablies').to.deep.equal(b.sms);
  });
};

/**
 * Filtre une liste DataTables (côté serveur) avec la recherche rapide.
 * Le champ est relu entre clear et type : certaines listes (ex. élèves) se redessinent et remplacent le champ.
 */
export const dtSearch = (text) => {
  const input = () => cy.get('.dataTables_filter input', { timeout: 20000 }).first();
  input().clear();
  input().type(text);
  return input().should('have.value', text);
};

/** Filtre la liste puis renvoie la ligne contenant `text`. */
export const findRow = (text) => {
  dtSearch(text);
  return cy.get('#table_id tbody', { timeout: 20000 }).contains('tr', text);
};

/** Ouvre le menu « Sélectionner » de la ligne et clique l'entrée ciblée par `selector`. */
export const rowAction = (text, selector) =>
  findRow(text).within(() => {
    cy.get('.dropdown-toggle').click({ force: true });
    cy.get(selector).click({ force: true });
  });

/**
 * Attend le message de succès (Toastr) affiché après un enregistrement : à appeler avant tout contrôle en base,
 * sinon la requête tinker peut passer pendant que le serveur enregistre encore (ex. lignes d'une réception).
 */
export const toastSuccess = () => cy.get('#toast-container .toast-success', { timeout: 30000 }).should('exist');

/** Vérifie que la valeur numérique d'un champ calculé par la page vaut `expected` (réessaie : calculs asynchrones). */
export const numberShouldBe = (selector, expected, label) =>
  cy.get(selector).should(($el) => expect(Number($el.val()), label).to.eq(expected));

/** Vérifie que la ligne a disparu de la liste filtrée. */
export const shouldBeGone = (text) => {
  dtSearch(text);
  cy.get('#table_id tbody', { timeout: 20000 }).should('not.contain', text);
};
