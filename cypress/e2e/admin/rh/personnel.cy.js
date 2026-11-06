// cypress/e2e/admin/rh/personnel.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Ressources humaines > Ajouter du personnel : n° (proposé), rôle Comptable, département, poste,
//     prénom, nom, e-mail, genre, date de naissance, date d'embauche, mobile → « Enregistrer »
//  2. Ressources humaines > Répertoire du personnel : le membre apparaît (recherche rapide)
//  3. « Sélectionner > Modifier » : formulaire pré-rempli, il change nom de famille et mobile → la liste est à jour
//  4. « Sélectionner > Supprimer », confirme → le membre et son compte disparaissent (contrôle en base)
//
// ⚠ L'ajout appelle send_mail() (identifiants) et send_sms() en direct : muteMessaging() coupe les réglages e-mail et les
//   passerelles SMS ; restoreMessaging() les rétablit. Le after() supprime aussi, si le test a échoué en route, le membre,
//   son compte et les congés créés pour lui (aramisc_leave_defines).
// Dates volontairement > 12 : cas réel d'un utilisateur FR (format j/m/aaaa).

import { php, findRow, rowAction, shouldBeGone } from './helpers';
import { muteMessaging, restoreMessaging } from '../administration/helpers';

describe('Admin – RH : Personnel (ajout, répertoire, modification, suppression)', () => {
  const shot = 'admin/rh/personnel';
  const stamp = Date.now();
  const firstName = 'CyPersonnel';
  const lastName = `Test${stamp}`;
  const updatedLastName = `${lastName}M`;
  const email = `cypersonnel_${stamp}@exemple.ma`;
  const ACCOUNTANT_ROLE = '6';
  let backup;
  let userId = 0; // mémorisé à la création : delete_staff() supprime le compte mais pas ses congés (anomalie 32)

  before(() => {
    muteMessaging([]).then((b) => { backup = b; });
  });

  after(() => {
    php(`
      $users = DB::table('users')->where('email', '${email}')->pluck('id')->push(${userId});
      $staffs = DB::table('aramisc_staffs')->where('email', '${email}')->pluck('id');
      DB::table('aramisc_expert_teachers')->whereIn('staff_id', $staffs)->delete();
      DB::table('aramisc_staffs')->whereIn('id', $staffs)->delete();
      DB::table('aramisc_leave_defines')->whereIn('user_id', $users)->delete();
      DB::table('users')->whereIn('id', $users)->delete();
    `);
    restoreMessaging(backup);
  });

  const dbState = () =>
    php(`echo 'CY<<'.base64_encode(json_encode([
      'staff' => DB::table('aramisc_staffs')->where('email', '${email}')->first(['id', 'last_name', 'mobile', 'date_of_birth', 'date_of_joining', 'role_id']),
      'user' => DB::table('users')->where('email', '${email}')->count(),
      'userId' => (int) DB::table('users')->where('email', '${email}')->value('id'),    ])).'>>CY';`);

  it('ajoute un membre du personnel, le modifie puis le supprime', () => {
    const mobile = `0612${String(stamp).slice(-7)}`;
    const updatedMobile = `0655${String(stamp).slice(-7)}`;

    cy.loginAsAdmin();
    cy.visit('/add-staff');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="staff_no"]').invoke('val').should('not.be.empty');
    cy.get('select[name="role_id"]').select(ACCOUNTANT_ROLE, { force: true });
    cy.get('select[name="department_id"]').select('1', { force: true });
    cy.get('select[name="designation_id"]').select('1', { force: true });
    cy.get('input[name="first_name"]').clear().type(firstName);
    cy.get('input[name="last_name"]').clear().type(lastName);
    cy.get('input[name="email"]').clear().type(email);
    cy.get('select[name="gender_id"]').select('1', { force: true });
    cy.get('input[name="date_of_birth"]').invoke('val', '15/03/1990');
    cy.get('input[name="date_of_joining"]').invoke('val', '15/09/2026');
    cy.get('input[name="mobile"]').clear().type(mobile);
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.fix-gr-bg.submit').first().click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    dbState().then((s) => {
      expect(s.staff, 'membre enregistré').to.not.be.null;
      expect(s.staff).to.include({ date_of_birth: '1990-03-15', date_of_joining: '2026-09-15', role_id: 6 });
      expect(s.user, 'compte créé').to.eq(1);
      userId = s.userId;
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.visit('/staff-directory');
    findRow(lastName).should('contain', firstName).and('contain', mobile);

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(lastName, 'a.dropdown-item[href*="edit-staff/"]');

    cy.url().should('include', '/edit-staff/');
    // editStaff.blade.php entoure les valeurs d'espaces (value=" {{ ... }} ") → comparaison sans les espaces
    const shouldHave = (field, value) =>
      cy.get(`input[name="${field}"]`).invoke('val').then((v) => expect(String(v).trim(), field).to.eq(value));
    shouldHave('first_name', firstName);
    shouldHave('last_name', lastName);
    shouldHave('email', email);
    shouldHave('date_of_joining', '15/09/2026');

    cy.get('input[name="last_name"]').clear().type(updatedLastName);
    cy.get('input[name="mobile"]').clear().type(updatedMobile);
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('form[action$="update-staff"] button.primary-btn.fix-gr-bg.submit').first().click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    dbState().then((s) => {
      expect(s.staff).to.include({ last_name: updatedLastName, mobile: updatedMobile, date_of_joining: '2026-09-15' });
    });
    cy.visit('/staff-directory');
    findRow(updatedLastName).should('contain', updatedMobile);

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedLastName, 'a.dropdown-item[onclick^="deleteStaff"]');
    cy.get('#deleteStaffModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    dbState().then((s) => {
      expect(s.staff, 'membre supprimé').to.be.null;
      expect(s.user, 'compte supprimé').to.eq(0);
    });
    cy.visit('/staff-directory');
    shouldBeGone(updatedLastName);
  });
});
