// cypress/e2e/admin/settings.cy.js

import { php } from './portefeuille/helpers';

describe('Admin – Settings', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // ─────────────────── GENERAL SETTINGS ─────────────────────────
  describe('General Settings', () => {
    it('affiche la page de paramètres généraux', () => {
      cy.visit('/general-settings');
      cy.url().should('include', '/general-settings');
      cy.get('form, .card').should('exist');
      cy.screenshot('admin_settings_general_setting');
    });
  });

  // ─────────────────── ACADEMIC YEAR (FULL CRUD) ────────────────────
  // ⚠ AramiscAcademicYearController::store() bascule TOUTE l'école sur la nouvelle année
  //   (aramisc_general_settings.session_id / academic_id / session_year + session de l'admin)
  //   et recopie les barèmes de notes (aramisc_marks_grades) de la dernière année vers la nouvelle.
  //   destroy() refuse de supprimer l'année courante.
  // Sécurisation :
  //   - before() mémorise l'année courante et choisit une année libre (2090–2099) ;
  //   - après la création, retour à l'année d'origine par le sélecteur de l'en-tête (#aramisc_session),
  //     comme un utilisateur, puis modification et suppression par l'interface ;
  //   - after() (filet de sécurité) rétablit les réglages, supprime l'année créée + barèmes / sections recopiés,
  //     et vide les sessions Cypress en cache (elles pourraient pointer sur l'année supprimée).
  describe('Academic Year - Full CRUD Lifecycle', () => {
    let backup;
    let year;
    let updatedYear;
    const title = `AY_${Date.now()}`;
    const updatedTitle = `${title}_Updated`;

    before(() => {
      php(`
        $gs = DB::table('aramisc_general_settings')->where('school_id', 1)->first(['session_id','academic_id','session_year']);
        $years = DB::table('aramisc_academic_years')->pluck('year')->all();
        echo 'CY<<'.base64_encode(json_encode(['gs' => $gs, 'years' => $years])).'>>CY';
      `).then((res) => {
        backup = res.gs;
        const free = [2090, 2091, 2092, 2093, 2094, 2095, 2096, 2097, 2098, 2099].filter((y) => !res.years.map(String).includes(String(y)));
        expect(free.length, 'années libres 2090–2099').to.be.at.least(2);
        [year, updatedYear] = free.map(String);
      });
    });

    after(() => {
      php(`
        $ids = DB::table('aramisc_academic_years')->whereIn('title', ['${title}', '${updatedTitle}'])->pluck('id');
        DB::table('aramisc_marks_grades')->whereIn('academic_id', $ids)->delete();
        DB::table('aramisc_class_sections')->whereIn('academic_id', $ids)->delete();
        DB::table('aramisc_academic_years')->whereIn('id', $ids)->delete();
        DB::table('aramisc_general_settings')->where('school_id', 1)->update([
          'session_id' => ${backup.session_id}, 'academic_id' => ${backup.academic_id}, 'session_year' => '${backup.session_year}',
        ]);
      `);
      Cypress.session.clearAllSavedSessions();
    });

    const rowAction = (text, selector) => {
      cy.get('#table_id', { timeout: 20000 }).contains('tr', text).within(() => {
        cy.get('.dropdown-toggle').click({ force: true });
        cy.get(selector).click({ force: true });
      });
    };

    it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'une année académique", () => {
      // 1. CREATE (dates par défaut du formulaire, aucune donnée recopiée)
      cy.visit('/academic-year');
      cy.get('form[action$="/academic-year"][method="POST"]').within(() => {
        cy.get('input[name="year"]').clear().type(year);
        cy.get('input[name="title"]').clear().type(title);
      });
      cy.get('form[action$="/academic-year"][method="POST"] button.submit').click();
      cy.get('#table_id', { timeout: 20000 }).contains('tr', title).should('contain', year);

      // L'école a basculé sur la nouvelle année : retour à l'année d'origine via le sélecteur de l'en-tête
      php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_general_settings')->where('school_id', 1)->value('academic_id'))).'>>CY';`)
        .then((current) => expect(String(current), 'bascule sur la nouvelle année').to.not.eq(String(backup.academic_id)));
      cy.intercept('GET', '**/change-academic-year*').as('changeYear');
      cy.get('#aramisc_session').select(String(backup.academic_id), { force: true });
      cy.wait('@changeYear');
      php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_general_settings')->where('school_id', 1)->value('academic_id'))).'>>CY';`)
        .then((current) => expect(String(current), "retour à l'année d'origine").to.eq(String(backup.academic_id)));

      // 2. UPDATE
      cy.visit('/academic-year');
      rowAction(title, 'a.dropdown-item[href*="/academic-year/"]');
      cy.url().should('match', /\/academic-year\/\d+$/);
      cy.get('form[action*="/academic-year/"] input[name="year"]').should('have.value', year).clear().type(updatedYear);
      cy.get('form[action*="/academic-year/"] input[name="title"]').clear().type(updatedTitle);
      cy.get('form[action*="/academic-year/"] button.submit').click();
      cy.url().should('match', /\/academic-year$/);
      cy.get('#table_id', { timeout: 20000 }).contains('tr', updatedTitle).should('contain', updatedYear);

      // 3. DELETE (possible car ce n'est plus l'année courante)
      rowAction(updatedTitle, 'a.dropdown-item[data-target^="#deleteAcademicYearModal"]');
      cy.get('.modal.show', { timeout: 10000 }).find('button[type="submit"]').click();
      cy.get('#table_id', { timeout: 20000 }).should('not.contain', updatedTitle);
      php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_academic_years')->where('title', '${updatedTitle}')->count())).'>>CY';`)
        .should('eq', 0);
    });
  });

  // ─────────────────── ROLE MANAGEMENT (FULL CRUD) ──────────────────
  // Page du menu : /rolepermission/role (module RolePermission). L'ancienne route /role (vue sans route
  // « assign_permission ») renvoie une 500 et n'est plus liée au menu.
  describe('Role Management - Full CRUD Lifecycle', () => {
    const name = `Role_${Date.now()}`;
    const updatedName = `${name}_Updated`;
    const roleCount = (n) =>
      php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_roles')->where('name', '${n}')->count())).'>>CY';`);

    // Filet de sécurité : supprime le rôle s'il existe encore (test interrompu)
    after(() => {
      php(`DB::table('aramisc_roles')->whereIn('name', ['${name}', '${updatedName}'])->where('type', 'User Defined')->delete();`);
    });

    const rowAction = (text, selector) => {
      cy.get('#table_id', { timeout: 20000 }).contains('tr', text).within(() => {
        cy.get('.dropdown-toggle').click({ force: true });
        cy.get(selector).click({ force: true });
      });
    };

    it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un rôle", () => {
      // 1. CREATE
      cy.visit('/rolepermission/role');
      cy.get('form[action$="/rolepermission/role-store"] input[name="name"]').type(name);
      // cy.screenshot('admin/settings/role/01-create-formulaire');
      cy.get('form[action$="/rolepermission/role-store"] button.primary-btn').click();
      // cy.screenshot('admin/settings/role/02-create-apres-submit');
      cy.get('#table_id', { timeout: 20000 }).contains('td', name).should('exist');
      roleCount(name).should('eq', 1);

      // 2. UPDATE
      rowAction(name, 'a.dropdown-item[href*="/rolepermission/role-edit/"]');
      cy.get('form[action$="/rolepermission/role-update"] input[name="name"]').should('have.value', name).clear().type(updatedName);
      // cy.screenshot('admin/settings/role/03-update-formulaire');
      cy.get('form[action$="/rolepermission/role-update"] button.primary-btn').click();
      // cy.screenshot('admin/settings/role/04-update-apres-submit');
      cy.url().should('match', /\/rolepermission\/role$/);
      cy.get('#table_id', { timeout: 20000 }).contains('td', updatedName).should('exist');
      roleCount(updatedName).should('eq', 1);

      // 3. DELETE
      rowAction(updatedName, 'a.dropdown-item[data-target^="#deleteRole"]');
      cy.get('.modal.show', { timeout: 10000 }).find('button[type="submit"]').click();
      // cy.screenshot('admin/settings/role/05-delete-apres-confirmation');
      cy.get('#table_id', { timeout: 20000 }).should('not.contain', updatedName);
      roleCount(updatedName).should('eq', 0);
    });
  });

  // ─────────────────── MODULE PERMISSION ────────────────────────
  describe('Module Permission', () => {
    it('affiche la page des permissions de modules', () => {
      cy.visit('/module-permission');
      cy.url().should('include', '/module-permission');
      cy.get('table, .card').should('exist');
      cy.screenshot('admin_settings_module_permission');
    });
  });

  // ─────────────────── STUDENT ID CARD ──────────────────────────
  describe('Student ID Card', () => {
    it('affiche la liste des modèles de cartes', () => {
      cy.visit('/student-id-card');
      cy.url().should('include', '/student-id-card');
      cy.get('table, .card').should('exist');
      cy.screenshot('admin_settings_student_id_card');
    });

    it('affiche la page de génération de cartes', () => {
      cy.visit('/generate-id-card');
      cy.url().should('include', '/generate-id-card');
      cy.get('form, .card').should('exist');
      cy.screenshot('admin_settings_generate_id_card');
    });
  });

  // ─────────────────── STUDENT CERTIFICATE ──────────────────────
  describe('Student Certificate', () => {
    it('affiche la liste des modèles de certificats', () => {
      cy.visit('/student-certificate');
      cy.url().should('include', '/student-certificate');
      cy.get('table, .card').should('exist');
      cy.screenshot('admin_settings_student_certificate');
    });

    it('affiche la page de génération de certificats', () => {
      cy.visit('/generate-certificate');
      cy.url().should('include', '/generate-certificate');
      cy.get('form, .card').should('exist');
      cy.screenshot('admin_settings_generate_certificate');
    });
  });

  // ─────────────────── USER LOG ─────────────────────────────────
  describe('User Log', () => {
    it('affiche le journal d\'activité des utilisateurs', () => {
      cy.visit('/user-log');
      cy.url().should('include', '/user-log');
      cy.get('table, .card').should('exist');
      cy.screenshot('admin_settings_user_log');
    });
  });

  // ─────────────────── PRELOADER SETTING ────────────────────────
  describe('Preloader Setting', () => {
    it('affiche la page de paramètres du preloader', () => {
      cy.visit('/preloader-setting');
      cy.url().should('include', '/preloader-setting');
      cy.get('form, .card').should('exist');
      cy.screenshot('admin_settings_preloader_setting');
    });
  });

  // ─────────────────── CHANGE PASSWORD ──────────────────────────
  describe('Change Password', () => {
    it('affiche la page de changement de mot de passe', () => {
      cy.visit('/change-password');
      cy.url().should('include', '/change-password');
      cy.get('form').should('exist');
      cy.get('input[name="old_password"], input[name="current_password"]').should('exist');
      cy.screenshot('admin_settings_change_password');
    });
  });

  // ─────────────────── EXAM SIGNATURE SETTINGS ──────────────────
  describe('Exam Signature Settings', () => {
    it('affiche les paramètres de signature d\'examen', () => {
      cy.visit('/exam-signature-settings');
      cy.url().should('include', '/exam-signature-settings');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_settings_exam_signature_settings');
    });
  });

  // ─────────────────── DUE FEES LOGIN PERMISSION ────────────────
  describe('Due Fees Login Permission', () => {
    it('affiche les permissions de connexion pour frais impayés', () => {
      cy.visit('/due_fees_login_permission');
      cy.url().should('include', '/due_fees_login_permission');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_settings_due_fees_login_permission');
    });
  });

  // ─────────────────── FRONTEND PAGE BUILDER ────────────────────
  // Partie front non utilisée pour le moment (demande de l'utilisateur, 01/10/2026) : test désactivé.
  describe('Frontend Page Builder', () => {
    it.skip('affiche le constructeur de page front-end', () => {
      cy.visit('/frontend-page-builder');
      cy.url().should('include', '/frontend-page-builder');
      cy.get('body').should('be.visible');
      cy.screenshot('admin_settings_frontend_page_builder');
    });
  });
});
