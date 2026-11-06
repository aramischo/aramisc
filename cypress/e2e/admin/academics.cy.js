// cypress/e2e/admin/academics.cy.js

import { php } from './portefeuille/helpers';

const ts = () => Date.now();

describe('Admin – Academics', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // ─────────────────────────── SECTION (FULL CRUD) ──────────────────────────
  describe('Section - Full CRUD Lifecycle', () => {
    it('effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d\'une section', () => {
      const uniqueName = `TestSection_${ts()}`;
      const updatedName = `${uniqueName}_Updated`;

      // 1. Visit & Create
      cy.visit('/section');
      cy.get('input[name="name"], input[name="section_name"]').first().clear().type(uniqueName);
      cy.get('button.submit').first().click();
      cy.get('body').should('be.visible');

      // Filter via DataTables search if present
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(uniqueName);
        }
      });

      cy.contains(uniqueName, { timeout: 10000 }).should('exist');
      // cy.screenshot('admin_academics_section_created');

      // 2. Edit / Update
      cy.contains('tr', uniqueName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[href*="section-edit"]').click({ force: true });
      });

      cy.get('input[name="name"], input[name="section_name"]').first().clear().type(updatedName);
      cy.get('button.submit').first().click();

      // Filter via DataTables search
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 10000 }).should('exist');
      // cy.screenshot('admin_academics_section_updated');

      // 3. Delete
      cy.contains('tr', updatedName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[data-target*="deleteSectionModal"], a[href*="section-delete"]').click({ force: true });
      });

      cy.contains('tr', updatedName)
          .find('a[data-target*="deleteSectionModal"], a[href*="section-delete"]')
          .invoke('attr', 'data-target')
          .then((targetValue) => {
            targetValue = targetValue.replace('#', '');
            cy.log(targetValue);

            // Recherche globale sur la page (body), plus scopée au <tr>
            cy.get('#' + targetValue).should('be.visible').within(() => {
              cy.get('a[href*="section-delete"]').click({ force: true });
            });
          });

      /*cy.get('.modal.show, [id^="deleteSectionModal"]').within(() => {
        cy.get('button[type="submit"], a[href*="section-delete"]').click({ force: true });
      });*/

      // cy.screenshot('admin_academics_section_deleted');
    });
  });

  // ─────────────────────────── CLASS (FULL CRUD) ────────────────────────────
  describe('Class - Full CRUD Lifecycle', () => {
    it('effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d\'une classe', () => {
      const uniqueName = `TestClass_${ts()}`;
      const updatedName = `${uniqueName}_Updated`;

      // 1. Visit & Create
      cy.visit('/class');
      cy.get('input[name="name"]').first().clear().type(uniqueName);

      // Fill pass_mark if present
      cy.get('body').then(($body) => {
        if ($body.find('input[name="pass_mark"]').length > 0) {
          cy.get('input[name="pass_mark"]').clear().type('50');
        }
      });

      // Select section checkboxes
      cy.get('input[name="section[]"]').then(($checkboxes) => {
        if ($checkboxes.length > 0) {
          cy.wrap($checkboxes.first()).check({ force: true });
        }
      });

      cy.get('button.submit').first().click();
      cy.get('body').should('be.visible');

      // Filter via DataTables search if present
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(uniqueName);
        }
      });

      // 2. Read - Verify creation in list
      cy.contains(uniqueName, { timeout: 10000 }).should('exist');
      // cy.screenshot('admin_academics_class_created');

      // 3. Edit / Update
      cy.contains('tr', uniqueName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[href*="class-edit"]').click({ force: true });
      });

      cy.url().should('include', 'class-edit');
      cy.get('input[name="name"]').clear().type(updatedName);
      cy.get('button.submit').first().click();

      // Filter via DataTables search
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 10000 }).should('exist');
      // cy.screenshot('admin_academics_class_updated');

      // 4. Delete
      cy.contains('tr', updatedName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[data-target*="deleteClassModal"], a[href*="class-delete"]').click({ force: true });
      });

      cy.contains('tr', updatedName)
          .find('a[data-target*="deleteClassModal"], a[href*="class-delete"]')
          .invoke('attr', 'data-target')
          .then((targetValue) => {
            targetValue = targetValue.replace('#', '');
           // cy.log(targetValue);

            cy.get('#' + targetValue).should('be.visible').within(() => {
              cy.get('a[href*="class-delete"]').click({ force: true });
            });
          });

      // cy.screenshot('admin_academics_class_deleted');
    });
  });

  // ─────────────────────────── SUBJECT ──────────────────────────
  describe('Subject', () => {
    it('affiche la liste des matières', () => {
      const uniqueName = `Subject_${ts()}`;
      const shortName = `Sb${ts()}`;
      const updatedName = `${uniqueName}_Updated`;
      cy.visit('/subject');
      cy.url().should('include', '/subject');
      cy.get('table, .card').should('exist');
      cy.get('input[name="subject_name"], input[name="subject_name"]').first().clear().type(uniqueName);
      cy.get('input[name="subject_type"], input[name="subject_type"]').first().check({ force: true });
      cy.get('input[name="subject_code"], input[name="subject_code"]').first().clear().type(shortName);
      cy.get('button.submit').first().click();
      cy.get('body').should('be.visible');
      // cy.screenshot('admin_academics_subject_list');

      // Filter via DataTables search if present
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(uniqueName);
        }
      });

      cy.contains(uniqueName, { timeout: 10000 }).should('exist');
      // cy.screenshot('admin_academics_subject_created');

      // 2. Edit / Update
      cy.contains('tr', uniqueName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[href*="subject-edit"]').click({ force: true });
      });

      cy.get('input[name="subject_name"], input[name="subject_name"]').first().clear().type(updatedName);
      cy.get('input[name="subject_type"], input[name="subject_type"]').last().check({ force: true });
      cy.get('button.submit').first().click();

      // Filter via DataTables search
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 10000 }).should('exist');
      // cy.screenshot('admin_academics_subject_updated');

      // 3. Delete
      cy.contains('tr', updatedName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[data-target*="deleteSubjectModal"], a[href*="subject-delete"]').click({ force: true });
      });

      cy.contains('tr', updatedName)
          .find('a[data-target*="deleteSubjectModal"], a[href*="subject-delete"]')
          .invoke('attr', 'data-target')
          .then((targetValue) => {
            targetValue = targetValue.replace('#', '');
            cy.log(targetValue);

            // Recherche globale sur la page (body), plus scopée au <tr>
            cy.get('#' + targetValue).should('be.visible').within(() => {
              cy.get('a[href*="subject-delete"]').click({ force: true });
            });
          });

      // cy.screenshot('admin_academics_subject_deleted');



    });
  });

  // ─────────────── AFFECTATIONS : MATIÈRES & PROFESSEUR PRINCIPAL (sécurisé) ───────────────
  // ⚠ Effets de bord de l'appli :
  //   - Assign_Subject / Assign_Class_Teacher : notifications Email + SMS + App réelles → coupées pendant le test ;
  //   - assignSubjectStore() supprime puis recrée toutes les affectations de la classe/section, et l'événement
  //     CreateClassGroupChat crée groupes / membres / invitations de chat ;
  //   - la modification d'un professeur principal (ListenClassTeacherGetAllStudent, type 'update') exécute
  //     Invitation::with(...)->delete() : with() ne filtre pas → TOUTES les invitations de chat sont supprimées.
  // Sécurisation : before() sauvegarde intégralement les tables touchées (petites) dans un fichier du dossier
  // temporaire système (hors projet) ; after() les restaure à l'identique, supprime les notifications créées,
  // rétablit les réglages de notification et efface le fichier.
  // Données utilisées : Class2 / section 2 (aucune matière affectée, aucun professeur principal).
  describe('Affectations (matières, professeur principal) - sécurisé', () => {
    const TABLES = "['aramisc_assign_subjects','aramisc_assign_class_teachers','aramisc_class_teachers','chat_groups','chat_group_users','chat_invitations','chat_invitation_types']";
    const EVENTS = "['Assign_Subject','Assign_Class_Teacher']";
    const SNAP = "sys_get_temp_dir().'/cy_academics_snapshot.json'";
    const CLASS_ID = '2';
    const SECTION_ID = '2';
    let ctx; // { subjects, teachers }

    before(() => {
      php(`
        $snap = ['tables' => [], 'settings' => DB::table('aramisc_notification_settings')->whereIn('event', ${EVENTS})->pluck('destination', 'id'),
                 'maxNotif' => (int) DB::table('aramisc_notifications')->max('id')];
        foreach (${TABLES} as $t) { $snap['tables'][$t] = DB::table($t)->get()->map(fn($r) => (array) $r)->all(); }
        if (!file_exists(${SNAP})) { file_put_contents(${SNAP}, json_encode($snap)); }
        foreach ($snap['settings'] as $id => $dest) {
          $x = json_decode($dest, true); $x['Email'] = 0; $x['SMS'] = 0; $x['App'] = 0;
          DB::table('aramisc_notification_settings')->where('id', $id)->update(['destination' => json_encode($x)]);
        }
        echo 'CY<<'.base64_encode(json_encode([
          'assigned' => DB::table('aramisc_assign_subjects')->where('class_id', ${CLASS_ID})->where('section_id', ${SECTION_ID})->count(),
          'classTeacher' => DB::table('aramisc_assign_class_teachers')->where('class_id', ${CLASS_ID})->where('section_id', ${SECTION_ID})->count(),
          'subjects' => DB::table('aramisc_subjects')->where('academic_id', getAcademicId())->where('active_status', 1)->orderBy('id')->limit(2)->pluck('id'),
          'teachers' => DB::table('aramisc_staffs')->where('active_status', 1)->where(fn($q) => $q->where('role_id', 4)->orWhere('previous_role_id', 4))->orderBy('id')->limit(2)->pluck('id'),
        ])).'>>CY';
      `).then((r) => {
        expect(r.assigned, 'Class2/sec2 sans matière affectée').to.eq(0);
        expect(r.classTeacher, 'Class2/sec2 sans professeur principal').to.eq(0);
        expect(r.subjects).to.have.length(2);
        expect(r.teachers).to.have.length(2);
        ctx = { subjects: r.subjects.map(String), teachers: r.teachers.map(String) };
      });
    });

    after(() => {
      php(`
        if (file_exists(${SNAP})) {
          $snap = json_decode(file_get_contents(${SNAP}), true);
          Schema::disableForeignKeyConstraints();
          foreach ($snap['tables'] as $t => $rows) {
            DB::table($t)->delete();
            foreach (array_chunk($rows, 200) as $chunk) { DB::table($t)->insert($chunk); }
          }
          Schema::enableForeignKeyConstraints();
          DB::table('aramisc_notifications')->where('id', '>', $snap['maxNotif'])->delete();
          foreach ($snap['settings'] as $id => $dest) { DB::table('aramisc_notification_settings')->where('id', $id)->update(['destination' => $dest]); }
          $ok = true;
          foreach ($snap['tables'] as $t => $rows) { $ok = $ok && DB::table($t)->count() === count($rows); }
          unlink(${SNAP});
          echo 'CY<<'.base64_encode(json_encode($ok)).'>>CY';
        }
      `).then((ok) => expect(ok, 'tables restaurées').to.eq(true));
    });

    /** Choisit la classe puis la section (chargée en AJAX). */
    const pickClassSection = () => {
      cy.get('#select_class').select(CLASS_ID, { force: true });
      cy.get(`#select_section option[value="${SECTION_ID}"]`, { timeout: 20000 }).should('exist');
      cy.get('#select_section').select(SECTION_ID, { force: true });
    };

    const assignedSubjects = () =>
      php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_assign_subjects')->where('class_id', ${CLASS_ID})
        ->where('section_id', ${SECTION_ID})->get(['subject_id','teacher_id']))).'>>CY';`);

    const searchAssignSubject = () => {
      cy.visit('/assign-subject-create');
      pickClassSection();
      cy.get('form[action*="assign-subject-search"] button[type="submit"]').click();
      cy.url().should('include', '/assign-subject-search');
    };

    const classTeacher = () =>
      php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_assign_class_teachers as a')
        ->join('aramisc_class_teachers as t', 't.assign_class_teacher_id', '=', 'a.id')
        ->where('a.class_id', ${CLASS_ID})->where('a.section_id', ${SECTION_ID})->get(['a.id', 't.teacher_id']))).'>>CY';`);

    it('affecte, modifie puis retire une matière (Class2 / section 2)', () => {
      // 1. CREATE : la section n'a aucune affectation → une ligne vide est proposée
      searchAssignSubject();
      cy.get('#assign-subject [id^="assign-subject-"]').should('have.length', 1).first().within(() => {
        cy.get('select[name="subjects[]"]').select(ctx.subjects[0], { force: true });
        cy.get('select[name="teachers[]"]').select(ctx.teachers[0], { force: true });
      });
      // cy.screenshot('admin/academics/assign_subject/01-create-formulaire');
      cy.get('form#assign_subject button.submit').click();
      // cy.screenshot('admin/academics/assign_subject/02-create-apres-submit');
      assignedSubjects().then((rows) => {
        expect(rows).to.have.length(1);
        expect(String(rows[0].subject_id)).to.eq(ctx.subjects[0]);
        expect(String(rows[0].teacher_id)).to.eq(ctx.teachers[0]);
      });

      // 2. UPDATE : changement d'enseignant
      searchAssignSubject();
      cy.get('#assign-subject [id^="assign-subject-"]').should('have.length', 1).first()
        .find('select[name="teachers[]"]').should('have.value', ctx.teachers[0]).select(ctx.teachers[1], { force: true });
      // cy.screenshot('admin/academics/assign_subject/03-update-formulaire');
      cy.get('form#assign_subject button.submit').click();
      // cy.screenshot('admin/academics/assign_subject/04-update-apres-submit');
      assignedSubjects().then((rows) => {
        expect(rows).to.have.length(1);
        expect(String(rows[0].teacher_id)).to.eq(ctx.teachers[1]);
      });

      // 3. DELETE : retrait de la ligne puis enregistrement
      searchAssignSubject();
      cy.get('#assign-subject [id^="assign-subject-"]').should('have.length', 1).first().find('#removeSubject').click();
      cy.get('#assign-subject [id^="assign-subject-"]').should('have.length', 0);
      cy.get('form#assign_subject button.submit').click();
      // cy.screenshot('admin/academics/assign_subject/05-delete-apres-submit');
      assignedSubjects().should('have.length', 0);
    });

    it('affecte, modifie puis supprime un professeur principal (Class2 / section 2)', () => {
      // 1. CREATE
      cy.visit('/assign-class-teacher');
      cy.get('form[action$="/assign-class-teacher"]').within(() => {
        pickClassSection();
        cy.get(`input[name="teacher"][value="${ctx.teachers[0]}"]`).check({ force: true });
      });
      // cy.screenshot('admin/academics/assign_class_teacher/01-create-formulaire');
      cy.get('form[action$="/assign-class-teacher"] button.submit').click();
      // cy.screenshot('admin/academics/assign_class_teacher/02-create-apres-submit');
      classTeacher().then((rows) => {
        expect(rows).to.have.length(1);
        expect(String(rows[0].teacher_id)).to.eq(ctx.teachers[0]);
        const id = rows[0].id;

        // 2. UPDATE (Sélectionner > Modifier)
        // Non-régression anomalie 22 : les invitations de chat hors « professeur principal de cette section » doivent rester
        php(`echo 'CY<<'.base64_encode(json_encode(DB::table('chat_invitations')->whereNotIn('id', DB::table('chat_invitation_types')->where('type', 'class-teacher')->where('section_id', ${SECTION_ID})->pluck('invitation_id'))->count())).'>>CY';`).as('otherInvitations');
        cy.visit('/assign-class-teacher');
        cy.get(`#table_id a.dropdown-item[data-target="#deleteClassModal${id}"]`, { timeout: 20000 })
          .closest('tr').within(() => {
            cy.get('.dropdown-toggle').click({ force: true });
            cy.get('a.dropdown-item').not('[data-toggle="modal"]').click({ force: true });
          });
        cy.get(`input[name="teacher"][value="${ctx.teachers[0]}"]`).should('be.checked');
        cy.get(`input[name="teacher"][value="${ctx.teachers[1]}"]`).check({ force: true });
        // cy.screenshot('admin/academics/assign_class_teacher/03-update-formulaire');
        cy.get(`form[action$="/${id}"] button.submit`).click();
        // cy.screenshot('admin/academics/assign_class_teacher/04-update-apres-submit');
        cy.url().should('match', /\/assign-class-teacher$/);
        classTeacher().then((after) => {
          expect(after).to.have.length(1);
          expect(String(after[0].teacher_id)).to.eq(ctx.teachers[1]);
        });
        cy.get('@otherInvitations').then((before) => {
          php(`echo 'CY<<'.base64_encode(json_encode(DB::table('chat_invitations')->whereNotIn('id', DB::table('chat_invitation_types')->where('type', 'class-teacher')->where('section_id', ${SECTION_ID})->pluck('invitation_id'))->count())).'>>CY';`).should('eq', before);
        });

        // 3. DELETE
        cy.get(`#table_id a.dropdown-item[data-target="#deleteClassModal${id}"]`, { timeout: 20000 })
          .closest('tr').within(() => {
            cy.get('.dropdown-toggle').click({ force: true });
            cy.get(`a.dropdown-item[data-target="#deleteClassModal${id}"]`).click({ force: true });
          });
        cy.get('.modal.show', { timeout: 10000 }).find('button[type="submit"]').click();
        // cy.screenshot('admin/academics/assign_class_teacher/05-delete-apres-confirmation');
        classTeacher().should('have.length', 0);
      });
    });
  });

  // ─────────────────────── CLASS ROOM ───────────────────────────
  describe('Class Room', () => {
    it('affiche la liste des salles de classe', () => {
      const uniqueName = `Classroom_${ts()}`;
      const updatedName = `${uniqueName}_Updated`;
      const uniquCapacity = 20;
      const updatedCapacity = 30;

      cy.visit('/class-room');
      cy.url().should('include', '/class-room');
      cy.get('table, .card').should('exist');

      cy.get('input[name="room_no"], input[name="room_no"]').first().clear().type(uniqueName);
      cy.get('input[name="capacity"], input[name="capacity"]').first().clear().type(uniquCapacity);
      cy.get('button.submit').first().click();
      cy.get('body').should('be.visible');

      // Filter via DataTables search if present
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(uniqueName);
        }
      });

      cy.contains(uniqueName).should('exist');

      // cy.screenshot('admin_academics_section_created');

      // 2. Edit / Update
      cy.contains('tr', uniqueName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[href*="class-room"]').click({ force: true });
      });

      cy.get('input[name="room_no"], input[name="room_no"]').first().clear().type(updatedName);
      cy.get('input[name="capacity"], input[name="capacity"]').first().clear().type(updatedCapacity);
      cy.get('button.submit').first().click();

      // Filter via DataTables search
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName).should('exist');
      // cy.screenshot('admin_academics_section_updated');

      // 3. Delete
      cy.contains('tr', updatedName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[data-target*="deleteClassRoomModal"]').click({ force: true });
      });

      cy.contains('tr', updatedName)
          .find('a[data-target*="deleteClassRoomModal"], a[href*="section-delete"]')
          .invoke('attr', 'data-target')
          .then((targetValue) => {
            targetValue = targetValue.replace('#', '');
            cy.log(targetValue);

            // Recherche globale sur la page (body), plus scopée au <tr>
            cy.get('#' + targetValue).should('be.visible').within(() => {
              cy.get('button[type="submit"]').click({ force: true });
            });
          });

      cy.url().should('include', '/class-room');
      cy.get('body').should('be.visible');

      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
        cy.contains('tr',updatedName).should('not.exist');
      });





      // cy.screenshot('admin_academics_class_room_list');
    });
  });

  // ─────────────────────── CLASS ROUTINE (sécurisé) ───────────────────────
  // addNewClassRoutineStore() supprime puis recrée toutes les lignes du jour (Class1 / section 1) : la table
  // aramisc_class_routine_updates est sauvegardée intégralement avant le test (dossier temporaire système, hors projet)
  // et restaurée à l'identique dans after(). Aucune notification n'est envoyée.
  // Jour utilisé : le premier jour actif sans cours pour Class1 / section 1.
  // L'exception JS « Cannot read properties of null (reading 'value') » vient de isBusy() (appelé avec un
  // data-teacher_row_id absent sur les champs horaires) : non bloquante, ignorée par cypress/support/e2e.js.
  describe('Class Routine', () => {
    const SNAP = "sys_get_temp_dir().'/cy_class_routine_snapshot.json'";
    let ctx; // { day, subject }

    before(() => {
      php(`
        if (!file_exists(${SNAP})) {
          file_put_contents(${SNAP}, json_encode(DB::table('aramisc_class_routine_updates')->get()->map(fn($r) => (array) $r)->all()));
        }
        $busy = DB::table('aramisc_class_routine_updates')->where('class_id', 1)->where('section_id', 1)->pluck('day');
        echo 'CY<<'.base64_encode(json_encode([
          'day' => DB::table('aramisc_weekends')->where('school_id', 1)->where('active_status', 1)->whereNotIn('id', $busy)->orderBy('order')->value('id'),
          'subject' => DB::table('aramisc_assign_subjects')->where('class_id', 1)->where('section_id', 1)->orderBy('id')->value('subject_id'),
        ])).'>>CY';
      `).then((r) => {
        expect(r.day, 'jour libre pour Class1/sec1').to.not.be.null;
        expect(r.subject, 'matière affectée à Class1/sec1').to.not.be.null;
        ctx = { day: String(r.day), subject: String(r.subject) };
      });
    });

    after(() => {
      php(`
        if (file_exists(${SNAP})) {
          $rows = json_decode(file_get_contents(${SNAP}), true);
          DB::table('aramisc_class_routine_updates')->delete();
          foreach (array_chunk($rows, 200) as $chunk) { DB::table('aramisc_class_routine_updates')->insert($chunk); }
          unlink(${SNAP});
          echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_class_routine_updates')->count() === count($rows))).'>>CY';
        }
      `).then((ok) => expect(ok, 'emploi du temps restauré').to.eq(true));
    });

    const dayRoutines = () =>
      php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_class_routine_updates')->where('class_id', 1)
        ->where('section_id', 1)->where('day', ${ctx.day})->get(['id', 'subject_id', 'start_time', 'end_time']))).'>>CY';`);

    let openCount = 0;
    const openDay = () => {
      // Alias propre à chaque ouverture : la page charge d'abord l'onglet actif (document.ready), il faut attendre
      // sa réponse avant de changer de jour, sinon elle arrive après et écrase le contenu du jour voulu.
      const alias = `dayWise${(openCount += 1)}`;
      cy.intercept('POST', '**/day-wise-class-routine').as(alias);
      cy.visit('/class-routine');
      cy.get('#search_student #select_class').select('1', { force: true });
      cy.get('#select_section option[value="1"]', { timeout: 20000 }).should('exist');
      cy.get('#select_section').select('1', { force: true });
      cy.get('#search_student button[type="submit"]').click();
      cy.url().should('include', '/class-routine-new');
      cy.get('.tab_link.active').invoke('attr', 'data-aramisc_weekend_id').then((active) => {
        cy.wait(`@${alias}`).its('request.body').should('include', `day_id=${active}`);
        if (String(active) !== ctx.day) {
          cy.get(`a.tab_link[data-aramisc_weekend_id="${ctx.day}"]`).click();
          cy.wait(`@${alias}`).its('request.body').should('include', `day_id=${ctx.day}`);
        }
      });
      cy.get('#show_routine input[name="day"]', { timeout: 20000 }).should('have.value', ctx.day);
    };

    it("ajoute, vérifie puis supprime un cours dans l'emploi du temps (Class1 / section 1)", () => {
      // 1. CREATE : jour sans cours → « Ajouter » une ligne
      openDay();
      cy.get('#classRoutineTable tbody tr').should('have.length', 0);
      cy.get('#addRowBtn').click();
      cy.get('#classRoutineTable tbody tr', { timeout: 20000 }).should('have.length', 1);
      // La ligne est re-rendue (nice-select détruit / recréé) : on la re-sélectionne par son id à chaque action
      cy.get('#classRoutineTable tbody tr').invoke('attr', 'id').then((rowId) => {
        const row = `#${rowId}`;
        cy.get(`${row} select.selectSubject`).select(ctx.subject, { force: true });
        cy.get(`${row} select.selectTeacher option`).not('[value=""]').first().invoke('val')
          .then((v) => cy.get(`${row} select.selectTeacher`).select(v, { force: true }));
        cy.get(`${row} input.selectStartTime`).invoke('val', '10:00 AM');
        cy.get(`${row} input.selectEndTime`).invoke('val', '11:00 AM');
        cy.get(`${row} select.selectRoom option`).not('[value=""]').first().invoke('val')
          .then((v) => cy.get(`${row} select.selectRoom`).select(v, { force: true }));
      });
      // cy.screenshot('admin/academics/class_routine/01-create-formulaire');
      cy.get('#classRoutineSubmitButton').click();
      // cy.screenshot('admin/academics/class_routine/02-create-apres-submit');
      dayRoutines().then((rows) => {
        expect(rows).to.have.length(1);
        expect(String(rows[0].subject_id)).to.eq(ctx.subject);
        expect(rows[0].start_time).to.eq('10:00:00');
        expect(rows[0].end_time).to.eq('11:00:00');
      });

      // 2. READ : le cours apparaît dans l'onglet du jour, et l'impression répond
      openDay();
      cy.get('#classRoutineTable tbody tr').should('have.length', 1);
      cy.get('a[href*="/class-routine/print/"], a[href*="class-routine-print"]').first().invoke('attr', 'href')
        .then((href) => cy.request(href).its('status').should('eq', 200));

      // 3. DELETE : bouton corbeille → modale → « Supprimer » (AJAX)
      cy.intercept('POST', '**/delete-class-routine').as('deleteRoutine');
      dayRoutines().then((rows) => cy.get(`#classRoutineTable .removeRoutineRowBtn[data-class_routine_id="${rows[0].id}"]`).click());
      cy.get('#classRoutineDeleteModal').should('be.visible').find('#classRoutineDeleteSubmitButton').click();
      cy.wait('@deleteRoutine');
      // cy.screenshot('admin/academics/class_routine/03-delete-apres-confirmation');
      dayRoutines().should('have.length', 0);
    });
  });

  // ──────────────────── OPTIONAL SUBJECT ────────────────────────
  describe('Optional Subject', () => {
    it('affiche la page des matières optionnelles', () => {
      cy.visit('/optional-subject');
      cy.url().should('include', '/optional-subject');
      cy.get('body').should('be.visible');


      // cy.screenshot('admin_academics_optional_subject');
      cy.get('#common_select_class_div span.current').click();
      cy.get('#common_select_class_div li[data-value="1"]').click();
      cy.get('#common_select_section_div span.current').click();
      cy.get('#common_select_section_div li[data-value="1"]').click();
      cy.get('#main-content').click();
      cy.get('#common_select_subject_div span.current').click();
      cy.get('#common_select_subject_div li[data-value="3"]').click();
      cy.get('#search_student button.small').click();

      cy.url().should('include', '/assign-optional-subject');
      cy.get('body').should('be.visible');

      cy.get('form[action*="/assign-optional-subject-store"]').should('exist').and('be.visible');

      cy.get('form[action*="/assign-optional-subject-store"]').within(()=>{
        cy.get('table').within(()=>{
          cy.get('tbody tr td input.optional_subject')
              .its('length')
              .should('gt', 0);
          cy.get('thead tr th span#all_check').first().click();
        });

        cy.get('button[type="submit"]').click();
      })

    });
  });
});
