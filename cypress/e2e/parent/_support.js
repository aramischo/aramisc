// cypress/e2e/parent/_support.js
// Socle partagé des specs du rôle Parent.
//
// Le groupe de routes parent porte le middleware `fees_due_check` : avec due_fees_login = 1 et une
// facture impayée échue pour l'enfant, UserRolePermission renvoie 403 sur toutes les pages. On coupe
// le réglage pendant le run (et on vide le cache rememberForever `have_due_fees_<user_id>` + les sessions
// Cypress, generalSetting() étant recopié dans la session Laravel), puis on rétablit en after().
// Si Cypress plante avant after() : remettre due_fees_login à 1 dans aramisc_general_settings.

import { php } from '../admin/portefeuille/helpers';

const PARENT_EMAIL = 'parent1@aramisc.com';

export const disableDueFeesLogin = () =>
  php(`
    $old = DB::table('aramisc_general_settings')->pluck('due_fees_login', 'id');
    DB::table('aramisc_general_settings')->update(['due_fees_login' => 0]);
    Cache::forget('have_due_fees_'.DB::table('users')->where('email', '${PARENT_EMAIL}')->value('id'));
    echo 'CY<<'.base64_encode(json_encode($old)).'>>CY';
  `);

export const restoreDueFeesLogin = (backup) => {
  if (!backup) return;
  const cases = Object.entries(backup)
    .map(([id, v]) => `WHEN ${Number(id)} THEN ${Number(v)}`)
    .join(' ');
  return php(`
    DB::statement('UPDATE aramisc_general_settings SET due_fees_login = CASE id ${cases} ELSE due_fees_login END');
    Cache::forget('have_due_fees_'.DB::table('users')->where('email', '${PARENT_EMAIL}')->value('id'));
  `);
};

export const getFirstChildId = () =>
  php(`
    $uid = DB::table('users')->where('email','${PARENT_EMAIL}')->value('id');
    $pid = DB::table('aramisc_parents')->where('user_id',$uid)->value('id');
    $sid = DB::table('aramisc_students')->where('parent_id',$pid)->value('id');
    echo 'CY<<'.base64_encode(json_encode($sid)).'>>CY';
  `);

// À appeler en tête de describe : installe les hooks before/after/beforeEach communs
// et renvoie un contexte dont `childId` est renseigné avant l'exécution des `it`.
export function parentSetup() {
  const ctx = { childId: 1 };
  let backup;

  before(() => {
    disableDueFeesLogin().then((old) => {
      backup = old;
    });
    getFirstChildId().then((id) => {
      if (id) ctx.childId = id;
    });
    cy.then(() => Cypress.session.clearAllSavedSessions());
  });

  after(() => {
    restoreDueFeesLogin(backup);
    cy.then(() => Cypress.session.clearAllSavedSessions());
  });

  beforeEach(() => {
    cy.loginAsParent();
  });

  return ctx;
}
