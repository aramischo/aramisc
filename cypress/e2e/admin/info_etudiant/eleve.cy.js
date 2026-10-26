// cypress/e2e/admin/info_etudiant/eleve.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Info-étudiant > Ajouter un étudiant : année, classe (Class2), section (sec2), identité, date de naissance,
//     contact élève, tuteur (nom, e-mail, téléphone) → « Enregistrer l'étudiant »
//  2. Info-étudiant > Liste des étudiants : l'élève apparaît (recherche rapide)
//  3. « Sélectionner > Modifier » : le formulaire est pré-rempli, il change nom de famille et téléphone → la liste est à jour
//  4. « Sélectionner > Désactiver », confirme → l'élève quitte la liste et apparaît dans « Étudiants désactivés »
//  5. Étudiants désactivés > « Sélectionner > Supprimer », confirme → l'élève, son tuteur et leurs comptes sont supprimés (contrôle en base)
//
// ⚠ L'ajout et la désactivation envoient de vrais messages : notifications Student_Admission / Enable/Disable_Student
//   (Email + SMS + App), plus send_mail() (identifiants élève et parent) et send_sms() en direct.
//   before() coupe Email/SMS/App de ces 2 événements, désactive les réglages e-mail (aramisc_email_settings) et les passerelles SMS ;
//   after() rétablit tout à l'identique, supprime les notifications créées et, si le test a échoué en route,
//   l'élève, son tuteur, leurs comptes, congés et lignes liées (tables « student_id »).
//   Si Cypress plante avant after() : remettre ces réglages à la main (valeurs affichées dans le journal de commandes du before()).
// Date de naissance volontairement > 12 (15/03/2012) : cas réel d'un utilisateur FR (format j/m/aaaa).

import { php, findRow, rowAction, shouldBeGone } from '../administration/helpers';

const b64 = (value) => Cypress.Buffer.from(value).toString('base64');

describe('Admin – Info-étudiant : Élève (ajout, liste, modification, désactivation, suppression)', () => {
  const shot = 'admin/info_etudiant/eleve';
  const EVENTS = "['Student_Admission','Enable/Disable_Student']";
  const stamp = Date.now();
  const firstName = 'CyEleve';
  const lastName = `Test${stamp}`;
  const updatedLastName = `${lastName}M`;
  const studentEmail = `cyeleve_${stamp}@exemple.ma`;
  const guardianEmail = `cyeleve_tuteur_${stamp}@exemple.ma`;
  let backup;

  before(() => {
    php(`
      $b = [
        'notif' => DB::table('aramisc_notification_settings')->whereIn('event', ${EVENTS})->pluck('destination', 'id'),
        'email' => DB::table('aramisc_email_settings')->pluck('active_status', 'id'),
        'sms' => DB::table('aramisc_sms_gateways')->pluck('active_status', 'id'),
        'maxNotif' => (int) DB::table('aramisc_notifications')->max('id'),
      ];
      foreach ($b['notif'] as $id => $d) {
        $x = json_decode($d, true); $x['Email'] = 0; $x['SMS'] = 0; $x['App'] = 0;
        DB::table('aramisc_notification_settings')->where('id', $id)->update(['destination' => json_encode($x)]);
      }
      DB::table('aramisc_email_settings')->update(['active_status' => 0]);
      DB::table('aramisc_sms_gateways')->update(['active_status' => 0]);
      echo 'CY<<'.base64_encode(json_encode($b)).'>>CY';
    `).then((b) => {
      expect(b, 'sauvegarde des réglages').to.have.property('maxNotif');
      backup = b;
    });
  });

  after(() => {
    if (!backup) return;
    php(`
      $b = json_decode(base64_decode('${b64(JSON.stringify(backup))}'), true);
      $users = DB::table('users')->whereIn('email', ['${studentEmail}', '${guardianEmail}'])->pluck('id');
      $students = DB::table('aramisc_students')->where('email', '${studentEmail}')->pluck('id');
      foreach (App\\tableList::ONLY_TABLE_LIST('student_id') as $t) { DB::table($t)->whereIn('student_id', $students)->delete(); }
      DB::table('aramisc_students')->whereIn('id', $students)->delete();
      DB::table('aramisc_parents')->where('guardians_email', '${guardianEmail}')->delete();
      DB::table('aramisc_leave_defines')->whereIn('user_id', $users)->delete();
      DB::table('users')->whereIn('id', $users)->delete();
      DB::table('aramisc_notifications')->where('id', '>', $b['maxNotif'])->delete();
      foreach ($b['notif'] as $id => $d) { DB::table('aramisc_notification_settings')->where('id', $id)->update(['destination' => $d]); }
      foreach ($b['email'] as $id => $s) { DB::table('aramisc_email_settings')->where('id', $id)->update(['active_status' => $s]); }
      foreach ($b['sms'] as $id => $s) { DB::table('aramisc_sms_gateways')->where('id', $id)->update(['active_status' => $s]); }
      echo 'CY<<'.base64_encode(json_encode([
        'notif' => DB::table('aramisc_notification_settings')->whereIn('event', ${EVENTS})->pluck('destination', 'id'),
        'email' => DB::table('aramisc_email_settings')->pluck('active_status', 'id'),
        'sms' => DB::table('aramisc_sms_gateways')->pluck('active_status', 'id'),
      ])).'>>CY';
    `).then((r) => {
      expect(r.notif, 'notifications rétablies').to.deep.equal(backup.notif);
      expect(r.email, 'réglages e-mail rétablis').to.deep.equal(backup.email);
      expect(r.sms, 'passerelles SMS rétablies').to.deep.equal(backup.sms);
    });
  });

  // État en base de l'élève et de son tuteur
  const dbState = () =>
    php(`echo 'CY<<'.base64_encode(json_encode([
      'student' => DB::table('aramisc_students')->where('email', '${studentEmail}')->first(['id', 'last_name', 'mobile', 'date_of_birth', 'active_status']),
      'parent' => DB::table('aramisc_parents')->where('guardians_email', '${guardianEmail}')->count(),
      'users' => DB::table('users')->whereIn('email', ['${studentEmail}', '${guardianEmail}'])->count(),
    ])).'>>CY';`);

  it("ajoute un élève, le modifie, le désactive puis le supprime définitivement", () => {
    cy.loginAsAdmin();

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.intercept('GET', '**/ajaxSectionStudent*').as('sections');
    cy.visit('/student-admission');

    // L'année en cours est présélectionnée et ses classes sont rendues par le serveur
    cy.get('#academic_year').should('have.value', '1');
    cy.get('#classSelectStudent option[value="2"]').should('exist');
    cy.get('#classSelectStudent').select('2', { force: true });
    cy.wait('@sections');
    cy.get('#sectionSelectStudent option[value="2"]').should('exist');
    cy.get('#sectionSelectStudent').select('2', { force: true });

    cy.get('input[name="first_name"]').clear().type(firstName);
    cy.get('input[name="last_name"]').clear().type(lastName);
    cy.get('select[name="gender"]').select('1', { force: true });
    cy.get('input[name="date_of_birth"]').invoke('val', '15/03/2012');
    cy.get('input[name="email_address"]').clear().type(studentEmail);
    cy.get('input[name="phone_number"]').clear().type(`06${String(stamp).slice(-8)}`);
    cy.get('a.nav-link[href="#parents_and_guardian_info"]').click();
    cy.get('#relationFather').check({ force: true });
    cy.get('input[name="guardians_name"]').clear().type('Tuteur Cypress');
    cy.get('input[name="guardians_email"]').clear().type(guardianEmail);
    cy.get('input[name="guardians_phone"]').clear().type(`07${String(stamp).slice(-8)}`);
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('#_submit_btn_admission').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    dbState().then((s) => {
      expect(s.student, 'élève enregistré').to.not.be.null;
      expect(s.student.date_of_birth, 'date de naissance').to.eq('2012-03-15');
      expect(s.parent, 'tuteur enregistré').to.eq(1);
      expect(s.users, 'comptes élève + tuteur').to.eq(2);
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.visit('/student-list');
    findRow(lastName).should('contain', firstName).and('contain', 'Class2');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(lastName, 'a.dropdown-item[href*="student-edit"]');

    cy.url().should('include', '/student-edit/');
    cy.get('input[name="first_name"]').should('have.value', firstName);
    cy.get('input[name="last_name"]').should('have.value', lastName);
    cy.get('input[name="date_of_birth"]').should('have.value', '15/03/2012');
    cy.get('input[name="email_address"]').should('have.value', studentEmail);

    cy.get('input[name="last_name"]').clear().type(updatedLastName);
    cy.get('input[name="phone_number"]').clear().type(`05${String(stamp).slice(-8)}`);
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('#student_form button.submit').first().click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    dbState().then((s) => {
      expect(s.student.last_name).to.eq(updatedLastName);
      expect(s.student.mobile).to.eq(`05${String(stamp).slice(-8)}`);
    });
    cy.visit('/student-list');
    findRow(updatedLastName).should('contain', firstName);

    // ───────────────────────── 4. DÉSACTIVATION ─────────────────────────
    rowAction(updatedLastName, 'a.dropdown-item[data-target="#deleteStudentModal"]');
    cy.get('#deleteStudentModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-desactivation-apres-confirmation`);

    dbState().its('student.active_status').should('eq', 0);
    cy.visit('/student-list');
    shouldBeGone(updatedLastName);

    // ──────────────────── 5. SUPPRESSION DÉFINITIVE ────────────────────
    cy.visit('/disabled-student');
    rowAction(updatedLastName, 'a.dropdown-item[data-target="#deleteStudentModal"]');
    cy.get('#deleteStudentModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/06-delete-apres-confirmation`);

    dbState().then((s) => {
      expect(s.student, 'élève supprimé').to.be.null;
      expect(s.parent, 'tuteur supprimé').to.eq(0);
      expect(s.users, 'comptes supprimés').to.eq(0);
    });
    cy.visit('/disabled-student');
    shouldBeGone(updatedLastName);
  });
});
