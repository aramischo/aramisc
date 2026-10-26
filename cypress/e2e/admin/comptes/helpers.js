// cypress/e2e/admin/comptes/helpers.js
//
// Fonctions partagées par les specs de la rubrique « Comptes ».
// (Ce fichier n'est pas un spec : il ne finit pas par .cy.js)
//
// Les revenus / dépenses / transferts payés par « Bank » modifient le solde (current_balance) des comptes bancaires
// et créent des relevés (aramisc_bank_statements). Pour ne pas toucher au compte réel de la base de test,
// chaque spec crée ses propres comptes (tinker) et les supprime en after() avec tout ce qui s'y rattache.

import { php, dtSearch, findRow, rowAction, shouldBeGone } from '../administration/helpers';

export { php, dtSearch, findRow, rowAction, shouldBeGone };

export const BANK_METHOD = '3'; // aramisc_payment_methhods : 3 = Bank

/** Crée un compte bancaire de test (solde = opening) et renvoie son id. */
export const createBank = (name, opening) =>
  php(`
    $id = DB::table('aramisc_bank_accounts')->insertGetId([
      'bank_name' => '${name}', 'account_name' => 'Cypress', 'account_number' => '${name}',
      'opening_balance' => ${opening}, 'current_balance' => ${opening}, 'active_status' => 1,
      'school_id' => 1, 'academic_id' => getAcademicId(), 'created_at' => now(), 'updated_at' => now(),
    ]);
    echo 'CY<<'.base64_encode(json_encode($id)).'>>CY';
  `);

/** Supprime les comptes dont le nom commence par `prefix`, leurs relevés, revenus / dépenses / transferts liés. */
export const deleteBanks = (prefix) =>
  php(`
    $ids = DB::table('aramisc_bank_accounts')->where('bank_name', 'like', '${prefix}%')->pluck('id');
    DB::table('aramisc_bank_statements')->whereIn('bank_id', $ids)->delete();
    DB::table('aramisc_add_incomes')->whereIn('account_id', $ids)->delete();
    DB::table('aramisc_add_expenses')->whereIn('account_id', $ids)->delete();
    DB::table('aramisc_amount_transfers')->whereIn('from_bank_name', $ids)->orWhereIn('to_bank_name', $ids)->delete();
    DB::table('aramisc_bank_accounts')->whereIn('id', $ids)->delete();
  `);

/** Solde courant d'un compte et nombre / total de ses relevés. */
export const bankState = (id) =>
  php(`echo 'CY<<'.base64_encode(json_encode([
    'balance' => (float) DB::table('aramisc_bank_accounts')->where('id', ${id})->value('current_balance'),
    'statements' => DB::table('aramisc_bank_statements')->where('bank_id', ${id})->count(),
  ])).'>>CY';`);
