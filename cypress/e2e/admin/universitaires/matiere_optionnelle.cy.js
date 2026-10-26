// cypress/e2e/admin/universitaires/matiere_optionnelle.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Universitaires > Configuration des matières optionnelles : coche Class2, GPA au-dessus de 2.50
//     → la ligne Class2 / 2.50 apparaît dans la liste
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il passe le GPA à 3.00 → la liste affiche 3.00
//  3. « Sélectionner > Supprimer », confirme → la ligne disparaît (contrôle en base)
//
// Une seule configuration par classe : l'enregistrement remplace celle de la classe si elle existe. La base de test n'en a
// aucune (contrôlé en before()). Aucune notification. after() supprime la configuration de Class2 si elle reste.
// L'affectation des matières optionnelles aux élèves (/optional-subject) n'est pas un CRUD (visite dans academics.cy.js).

import { php, toastSuccess } from '../administration/helpers';

describe('Admin – Universitaires : Configuration des matières optionnelles (CRUD)', () => {
  const shot = 'admin/universitaires/matiere_optionnelle';
  const CLASS_ID = 2;

  before(() => {
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_class_optional_subject')->where('class_id', ${CLASS_ID})->count())).'>>CY';`)
      .should('eq', 0);
  });

  after(() => {
    php(`DB::table('aramisc_class_optional_subject')->where('class_id', ${CLASS_ID})->delete();`);
  });

  const setup = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_class_optional_subject')->where('class_id', ${CLASS_ID})->pluck('gpa_above'))).'>>CY';`);

  const rowAction = (selector) =>
    cy.get('#table_id', { timeout: 20000 }).contains('tr', 'Class2').within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get(selector).click({ force: true });
    });

  it('configure le GPA des matières optionnelles de Class2, le modifie puis le supprime', () => {
    cy.loginAsAdmin();
    cy.visit('/optional-subject-setup');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get(`#class${CLASS_ID}`).check({ force: true });
    cy.get('input[name="gpa_above"]').clear().type('2.50');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    toastSuccess();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    setup().then((g) => {
      expect(g, 'une configuration pour Class2').to.have.length(1);
      expect(Number(g[0])).to.eq(2.5);
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.get('#table_id').contains('tr', 'Class2').should('contain', '2.50');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction('a.dropdown-item[href*="optional-subject-setup/edit"]');

    cy.url().should('include', '/optional-subject-setup/edit/');
    cy.get(`#class${CLASS_ID}`).should('be.checked');
    cy.get('input[name="gpa_above"]').should('have.value', '2.50').clear().type('3');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    toastSuccess();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    setup().then((g) => {
      expect(g, 'toujours une seule configuration').to.have.length(1);
      expect(Number(g[0])).to.eq(3);
    });
    cy.get('#table_id').contains('tr', 'Class2').should('contain', '3.00');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction('a.dropdown-item[data-target^="#deleteSubjectModal"]');
    cy.get('.modal.show[id^="deleteSubjectModal"]').should('be.visible')
      .find('a[href*="optional-subject-setup/delete"] button').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    setup().should('have.length', 0);
  });
});
