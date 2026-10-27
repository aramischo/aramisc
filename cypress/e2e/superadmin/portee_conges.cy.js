// cypress/e2e/superadmin/portee_conges.cy.js
//
// Super admin (role_id 1) — PORTÉE DES DONNÉES (la vraie différence de comportement).
//
// L'endpoint DataTables des congés à approuver (DatatableQueryController@ajaxApproveLeave)
// applique une portée différente selon le rôle :
//   - role_id == 1 (Super admin) : TOUTES les demandes de l'école (vue globale)
//   - autre rôle                 : seulement ses propres demandes (staff_id == user id)
//
// Ce spec sème deux demandes (une « au Driver », une « à un autre staff »), interroge
// l'endpoint en Super admin puis en Driver, et vérifie que seul le Super admin voit
// la demande d'autrui. Les données semées sont supprimées en fin de spec (after).

import { php } from '../admin/portefeuille/helpers.js';

const AJAX_LEAVE = '/approve-leave-request-ajax';
// NB : aramisc_leave_requests.staff_id est une FK vers users(id) — c'est donc un USER id.
const DRIVER_UID = 41;    // user id de driver@aramisc.com — l'endpoint filtre staff_id == user id
const OTHER_STAFFID = 4;  // user id d'un autre staff (teacher) : visible uniquement par la vue globale (role 1)
const TAG_DRIVER = 'CYSCOPE_DRIVER';
const TAG_OTHER = 'CYSCOPE_OTHER';

const seedLeaves = () =>
  php(`
    $acad = optional(generalSetting())->session_id ?: DB::table('aramisc_academic_years')->where('active_status',1)->value('id');
    $base = [
      'approve_status'=>'A','active_status'=>1,'school_id'=>1,'academic_id'=>$acad,
      'leave_from'=>'2024-01-01','leave_to'=>'2024-01-02','apply_date'=>'2024-01-01',
      'created_at'=>now(),'updated_at'=>now(),
    ];
    DB::table('aramisc_leave_requests')->where('reason', '${TAG_DRIVER}')->orWhere('reason', '${TAG_OTHER}')->delete();
    DB::table('aramisc_leave_requests')->insert(array_merge($base, ['staff_id'=>${DRIVER_UID}, 'role_id'=>9, 'reason'=>'${TAG_DRIVER}']));
    DB::table('aramisc_leave_requests')->insert(array_merge($base, ['staff_id'=>${OTHER_STAFFID}, 'role_id'=>4, 'reason'=>'${TAG_OTHER}']));
    echo 'CY<<'.base64_encode(json_encode((int) DB::table('aramisc_leave_requests')->whereIn('reason', ['${TAG_DRIVER}','${TAG_OTHER}'])->count())).'>>CY';
  `);

const cleanupLeaves = () =>
  php(`
    DB::table('aramisc_leave_requests')->whereIn('reason', ['${TAG_DRIVER}','${TAG_OTHER}'])->delete();
    echo 'CY<<'.base64_encode(json_encode((int) DB::table('aramisc_leave_requests')->whereIn('reason', ['${TAG_DRIVER}','${TAG_OTHER}'])->count())).'>>CY';
  `);

const fetchLeaveJson = () =>
  cy
    .request({
      url: AJAX_LEAVE,
      headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
      failOnStatusCode: false,
    })
    .then((res) => {
      expect(res.status, 'statut endpoint congés').to.eq(200);
      return JSON.stringify(res.body.data || res.body);
    });

describe('Super admin – Portée globale des congés (vs rôle restreint)', () => {
  before(() => {
    seedLeaves().then((n) => {
      expect(n, 'demandes de congé semées').to.eq(2);
    });
  });

  after(() => {
    cleanupLeaves().then((n) => {
      expect(n, 'données de test supprimées').to.eq(0);
    });
  });

  it('le Super admin voit la demande d\'un AUTRE staff (vue globale)', () => {
    cy.loginAsSuperAdmin();
    fetchLeaveJson().then((data) => {
      expect(data, 'voit la demande du Driver').to.contain(TAG_DRIVER);
      expect(data, 'voit AUSSI la demande d\'un autre staff').to.contain(TAG_OTHER);
    });
  });

  it('le Driver ne voit QUE ses propres demandes (portée restreinte)', () => {
    cy.loginAsDriver();
    fetchLeaveJson().then((data) => {
      expect(data, 'voit sa propre demande').to.contain(TAG_DRIVER);
      expect(data, 'ne voit PAS celle d\'un autre staff').to.not.contain(TAG_OTHER);
    });
  });
});
