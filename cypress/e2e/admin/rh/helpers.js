// cypress/e2e/admin/rh/helpers.js
//
// Fonctions partagées par les specs de la rubrique « Ressources humaines ».
// (Ce fichier n'est pas un spec : il ne finit pas par .cy.js)

import { php, dtSearch, findRow, rowAction, shouldBeGone } from '../administration/helpers';

export { php, dtSearch, findRow, rowAction, shouldBeGone };

/**
 * CRUD d'une page « un seul champ + liste » (département, poste, type de congé) :
 * formulaire à gauche, liste DataTables côté client, modification sur `<url>/<id>`, suppression par une modale
 * contenant un formulaire DELETE. La modale est dans le <tbody> : le navigateur vide le <form>, le bouton y reste
 * rattaché (button.form) → on clique `.modal.show button[type=submit]`.
 *
 * @param {object} o
 * @param {string} o.url      page de la liste (ex. '/department')
 * @param {string} o.field    nom du champ texte (ex. 'name')
 * @param {string} o.table    table en base, pour le after() de nettoyage
 * @param {string} o.column   colonne correspondant au champ
 * @param {string} o.prefix   préfixe des valeurs créées
 * @param {string} o.modal    début de l'id de la modale de suppression (ex. 'deleteHumanDepartModal')
 * @param {string} o.shot     chemin des captures (ex. 'admin/rh/departement')
 */
export const singleFieldCrud = (o) => {
  after(() => {
    php(`DB::table('${o.table}')->where('${o.column}', 'like', '${o.prefix}%')->delete();`);
  });

  it(`effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) sur ${o.url}`, () => {
    const name = `${o.prefix}${Date.now()}`;
    const updatedName = `${name}_M`;
    const input = () => cy.get(`input[name="${o.field}"]`);
    const submit = () => input().parents('form').find('button.primary-btn.fix-gr-bg').first().click();
    const listUrl = new RegExp(`${o.url}$`);

    cy.loginAsAdmin();
    cy.visit(o.url);

    // ─────────────────────────── 1. CREATE ───────────────────────────
    input().clear().type(name);
    // cy.screenshot(`${o.shot}/01-create-formulaire`);
    submit();
    // cy.screenshot(`${o.shot}/02-create-apres-submit`);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', listUrl);
    findRow(name).should('exist');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, `a.dropdown-item[href*="${o.url}/"]`);

    cy.url().should('match', new RegExp(`${o.url}/\\d+$`));
    input().should('have.value', name).clear().type(updatedName);
    // cy.screenshot(`${o.shot}/03-update-formulaire`);
    submit();
    // cy.screenshot(`${o.shot}/04-update-apres-submit`);

    cy.url().should('match', listUrl);
    findRow(updatedName).should('exist');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedName, `a.dropdown-item[data-target^="#${o.modal}"]`);
    cy.get(`.modal.show[id^="${o.modal}"]`).should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${o.shot}/05-delete-apres-confirmation`);

    cy.url().should('match', listUrl);
    shouldBeGone(updatedName);
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('${o.table}')->where('${o.column}', '${updatedName}')->count())).'>>CY';`)
      .should('eq', 0);
  });
};
