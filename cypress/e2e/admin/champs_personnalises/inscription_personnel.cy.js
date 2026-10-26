// cypress/e2e/admin/champs_personnalises/inscription_personnel.cy.js
//
// Scénario utilisateur (Champs personnalisés > Inscription au personnel) :
//  1. Libellé + Type « Saisie de texte » (longueur 2–50) + Obligatoire + Demi-largeur → « Enregistrer »
//  2. Le champ apparaît dans la liste (et en base dans aramisc_custom_fields, form_name = staff_registration)
//  3. Sélectionner > Modifier : nouveau libellé, type « Liste déroulante » avec 2 valeurs, pleine largeur, facultatif
//  4. Sélectionner > Supprimer → modale de confirmation → le champ disparaît
// Aucune notification n'est envoyée par ce contrôleur.

import { customFieldCrud, cleanupFields } from './helpers';

describe('Admin – Champs personnalisés : Inscription au personnel (CRUD)', () => {
  const label = `CF_Staff_${Date.now()}`;

  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // Filet de sécurité : supprime le champ s'il existe encore (test interrompu)
  after(() => {
    cleanupFields('staff_registration', [label, `${label}_Updated`]);
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un champ personnalisé du personnel", () => {
    customFieldCrud({
      listUrl: '/staff-reg-custom-field',
      formName: 'staff_registration',
      storePath: 'store-staff-registration-custom-field',
      editPath: 'edit-staff-custom-field',
      updatePath: 'update-staff-custom-field',
      deletePath: 'delete-staff-custom-field',
      label,
      shot: 'admin/champs_personnalises/inscription_personnel',
    });
  });
});
