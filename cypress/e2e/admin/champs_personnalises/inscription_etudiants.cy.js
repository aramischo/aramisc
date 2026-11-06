// cypress/e2e/admin/champs_personnalises/inscription_etudiants.cy.js
//
// Scénario utilisateur (Champs personnalisés > Inscription des étudiants) :
//  1. Libellé + Type « Saisie de texte » (longueur 2–50) + Obligatoire + Demi-largeur → « Enregistrer »
//  2. Le champ apparaît dans la liste (et en base dans aramisc_custom_fields, form_name = student_registration)
//  3. Sélectionner > Modifier : nouveau libellé, type « Liste déroulante » avec 2 valeurs, pleine largeur, facultatif
//  4. Sélectionner > Supprimer → modale de confirmation → le champ disparaît
// Aucune notification n'est envoyée par ce contrôleur.

import { customFieldCrud, cleanupFields } from './helpers';

describe('Admin – Champs personnalisés : Inscription des étudiants (CRUD)', () => {
  const label = `CF_Eleve_${Date.now()}`;

  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // Filet de sécurité : supprime le champ s'il existe encore (test interrompu)
  after(() => {
    cleanupFields('student_registration', [label, `${label}_Updated`]);
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un champ personnalisé élève", () => {
    customFieldCrud({
      listUrl: '/student-registration-custom-field',
      formName: 'student_registration',
      storePath: 'store-student-registration-custom-field',
      editPath: 'edit-custom-field',
      updatePath: 'update-student-registration-custom-field',
      deletePath: 'delete-custom-field',
      label,
      shot: 'admin/champs_personnalises/inscription_etudiants',
    });
  });
});
