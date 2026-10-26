// cypress/e2e/admin/champs_personnalises/helpers.js
//
// Parcours CRUD partagé par les deux pages « Champs personnalisés » (inscription élève / personnel) :
// elles utilisent le même formulaire (resources/views/backEnd/customField/_custom_form.blade.php)
// et le même contrôleur (AramiscCustomFieldController). Aucune notification n'est envoyée.
// (Ce fichier n'est pas un spec : il ne finit pas par .cy.js)

import { php } from '../portefeuille/helpers';

/** Lit une ligne de aramisc_custom_fields par libellé (null si absente). */
const fieldByLabel = (formName, label) =>
  php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_custom_fields')
    ->where('form_name','${formName}')->where('label','${label}')->first())).'>>CY';`);

/** Supprime les champs créés par le test s'ils existent encore (filet de sécurité). */
export const cleanupFields = (formName, labels) =>
  php(`DB::table('aramisc_custom_fields')->where('form_name','${formName}')
    ->whereIn('label', ${JSON.stringify(labels)})->delete();`);

/** Ouvre le menu « Sélectionner » de la ligne et clique sur l'action voulue. */
const rowAction = (text, selector) => {
  cy.get('#table_id', { timeout: 20000 }).contains('tr', text).within(() => {
    cy.get('.dropdown-toggle').click({ force: true });
    cy.get(selector).click({ force: true });
  });
};

/**
 * Création (texte 2–50, demi-largeur, obligatoire) → lecture → modification (liste déroulante
 * à 2 valeurs, pleine largeur, facultatif) → suppression.
 * cfg : { listUrl, formName, storePath, editPath, updatePath, deletePath, label, shot }
 */
export const customFieldCrud = (cfg) => {
  const { listUrl, formName, storePath, editPath, updatePath, deletePath, label, shot } = cfg;
  const updated = `${label}_Updated`;
  const values = ['Opt_A', 'Opt_B'];

  // ─────────────── 1. CREATE ───────────────
  cy.visit(listUrl);
  cy.get(`form[action$="/${storePath}"]`).within(() => {
    cy.get('input[name="label"]').type(label);
    cy.get('#inputType').select('textInput', { force: true });
    cy.get('input[name="min_max_length[]"]').eq(0).should('be.visible').type('2');
    cy.get('input[name="min_max_length[]"]').eq(1).type('50');
    cy.get('#labalRequired').check({ force: true });
    cy.get('select[name="width"]').select('col-6', { force: true });
  });
  // cy.screenshot(`${shot}/01-create-formulaire`);
  cy.get(`form[action$="/${storePath}"] button[type="submit"]`).click();
  // cy.screenshot(`${shot}/02-create-apres-submit`);

  // ─────────────── 2. READ ───────────────
  cy.url().should('include', listUrl);
  cy.get('#table_id', { timeout: 20000 }).contains('tr', label).should('contain', '50');
  fieldByLabel(formName, label).then((row) => {
    expect(row, 'champ créé en base').to.not.be.null;
    expect(row.type).to.eq('textInput');
    expect(JSON.parse(row.min_max_length)).to.deep.eq(['2', '50']);
    expect(row.width).to.eq('col-6');
    expect(Number(row.required)).to.eq(1);
  });

  // ─────────────── 3. UPDATE ───────────────
  rowAction(label, `a.dropdown-item[href*="/${editPath}/"]`);
  cy.url().should('match', new RegExp(`/${editPath}/\\d+$`));
  cy.get(`form[action$="/${updatePath}"]`).within(() => {
    cy.get('input[name="label"]').should('have.value', label).clear().type(updated);
    cy.get('#inputType').select('dropdownInput', { force: true });
    // Clic natif : avec cy.click(), après une saisie, la 2e ligne ajoutée reste en d-none (artefact Cypress,
    // vérifié : deux clics natifs affichent bien les deux lignes, comme pour un utilisateur).
    values.forEach((v, i) => {
      cy.get('#customFieldaddRowBtn').should('be.visible').then(($btn) => $btn[0].click());
      cy.get('#addCustRow .addRow').should('have.length', i + 1).last().should('be.visible')
        .find('input[name="name_value[]"]').type(v);
    });
    cy.get('#labalRequired').uncheck({ force: true });
    cy.get('select[name="width"]').select('col-12', { force: true });
  });
  // cy.screenshot(`${shot}/03-update-formulaire`);
  cy.get(`form[action$="/${updatePath}"] button[type="submit"]`).click();
  // cy.screenshot(`${shot}/04-update-apres-submit`);
  cy.url().should('include', listUrl);
  cy.get('#table_id', { timeout: 20000 }).contains('tr', updated).should('contain', values[0]).and('contain', values[1]);
  fieldByLabel(formName, updated).then((row) => {
    expect(row, 'champ modifié en base').to.not.be.null;
    expect(row.type).to.eq('dropdownInput');
    expect(JSON.parse(row.name_value)).to.deep.eq(values);
    expect(row.width).to.eq('col-12');
    expect(Number(row.required || 0)).to.eq(0);
  });

  // ─────────────── 4. DELETE ───────────────
  rowAction(updated, 'a.dropdown-item[data-target^="#deleteCustomField"]');
  // La modale est dans le <tbody> : le navigateur vide le <form> (fermé aussitôt), le bouton reste à côté
  // mais rattaché au formulaire par le parseur HTML → on cible le bouton et on vérifie qu'il pointe bien sur la route.
  cy.get('.modal.show', { timeout: 10000 }).find('button[type="submit"]')
    .should(($btn) => expect($btn[0].form && $btn[0].form.action).to.match(new RegExp(`/${deletePath}$`)))
    .click();
  // cy.screenshot(`${shot}/05-delete-apres-confirmation`);
  cy.url().should('include', listUrl);
  cy.get('#table_id', { timeout: 20000 }).should('not.contain', updated);
  fieldByLabel(formName, updated).should('be.null');
};
