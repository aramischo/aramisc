// cypress/e2e/admin/devoirs/devoir.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Devoirs > Ajouter un devoir : Class1, matière Math (chargée en AJAX), section sec1 (chargée en AJAX),
//     date du devoir 15/10/2026, remise le 20/10/2026, note 73, description → le devoir est créé
//  2. Devoirs > Liste des devoirs : le devoir apparaît (Class1, sec1, Math, 73)
//  3. « Sélectionner > Modifier » : formulaire pré-rempli, il change la note (74) et la description → enregistré
//  4. « Sélectionner > Supprimer », confirme → le devoir disparaît (contrôle en base)
//
// ⚠ La création notifie élèves et parents (Assign_homework) : muteMessaging() coupe Email/SMS/App (+ réglages e-mail / SMS),
//   restoreMessaging() rétablit et supprime les notifications Web créées. after() supprime le devoir s'il reste.
// Dates volontairement > 12 : cas réel d'un utilisateur FR (format j/m/aaaa).

import { php, dtSearch, muteMessaging, restoreMessaging, toastSuccess } from '../administration/helpers';

describe('Admin – Devoirs : Devoir (CRUD)', () => {
  const shot = 'admin/devoirs/devoir';
  const description = `CyDevoir_${Date.now()}`;
  const updatedDescription = `${description}_M`;
  let backup;
  let homeworkId;

  before(() => {
    muteMessaging(['Assign_homework']).then((b) => { backup = b; });
  });

  after(() => {
    php(`DB::table('aramisc_homeworks')->whereIn('description', ['${description}', '${updatedDescription}'])->delete();`);
    restoreMessaging(backup);
  });

  const homework = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_homeworks')->whereIn('description', ['${description}', '${updatedDescription}'])
      ->get(['id', 'class_id', 'section_id', 'subject_id', 'marks', 'homework_date', 'submission_date', 'description']))).'>>CY';`);

  // Liste DataTables serveur : filtre sur la note, puis ligne du devoir (lien de modification portant son id)
  // (cy.then : l'id n'est connu qu'après le contrôle en base de l'étape 1)
  const homeworkRow = (marks) => {
    dtSearch(String(marks));
    return cy.then(() => cy.get(`#table_id a[href$="/homework-edit/${homeworkId}"]`, { timeout: 20000 }).parents('tr'));
  };

  it('ajoute un devoir pour Class1 / sec1 / Math, le modifie puis le supprime', () => {
    cy.loginAsAdmin();
    cy.intercept('GET', '**/ajaxSubjectFromClass*').as('subjects');
    cy.intercept('GET', '**/ajaxSubjectSection*').as('sections');
    cy.visit('/add-homeworks');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('#classSelectStudentHomeWork').select('1', { force: true });
    cy.wait('@subjects');
    cy.get('#subjectSelect option[value="1"]').should('exist');
    cy.get('#subjectSelect').select('1', { force: true });
    cy.wait('@sections');
    cy.get('#selectSectionss option[value="1"]').should('exist');
    cy.get('#selectSectionss').select(['1'], { force: true });
    cy.get('#homework_date').invoke('val', '15/10/2026');
    cy.get('#submission_date').invoke('val', '20/10/2026');
    cy.get('input[name="marks"]').clear().type('73');
    cy.get('textarea[name="description"]').clear().type(description);
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('form[action$="save-homework-data"] button.primary-btn.submit').click();
    toastSuccess();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    homework().then((h) => {
      expect(h, 'un devoir').to.have.length(1);
      expect(h[0]).to.include({ class_id: 1, section_id: 1, subject_id: 1, homework_date: '2026-10-15', submission_date: '2026-10-20' });
      expect(Number(h[0].marks)).to.eq(73);
      homeworkId = h[0].id;
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.visit('/homework-list');
    homeworkRow(73).should('contain', 'Class1').and('contain', 'Math').and('contain', '73');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    homeworkRow(73).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get(`a.dropdown-item[href$="/homework-edit/${homeworkId}"]`).click({ force: true });
    });

    cy.url().should('include', '/homework-edit/');
    cy.get('#homework_date').should('have.value', '15/10/2026');
    cy.get('textarea[name="description"]').should('contain.value', description);
    cy.get('input[name="marks"]').clear().type('74');
    cy.get('textarea[name="description"]').clear().type(updatedDescription);
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('form[action$="homework-update"] button.primary-btn.fix-gr-bg').last().click();
    toastSuccess();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    homework().then((h) => {
      expect(h).to.have.length(1);
      expect(Number(h[0].marks)).to.eq(74);
      expect(h[0].description).to.eq(updatedDescription);
      expect(h[0].submission_date).to.eq('2026-10-20');
    });

    // ─────────────────────────── 4. DELETE ───────────────────────────
    cy.visit('/homework-list');
    homeworkRow(74).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get('a.dropdown-item[onclick^="deleteHomeWork"]').click({ force: true });
    });
    cy.get('#deleteHomeWorkModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    homework().should('have.length', 0);
  });
});
