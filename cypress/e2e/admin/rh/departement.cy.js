// cypress/e2e/admin/rh/departement.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Ressources humaines > Département
//  2. Il saisit un nom → le département apparaît dans la liste
//  3. « Sélectionner > Modifier » : le champ est pré-rempli, il renomme → la liste affiche le nouveau nom
//  4. « Sélectionner > Supprimer », confirme → le département disparaît (contrôle en base)
//
// Aucune notification (AramiscHumanDepartmentController). Parcours commun : rh/helpers.js > singleFieldCrud().

import { singleFieldCrud } from './helpers';

describe('Admin – RH : Département (CRUD)', () => {
  singleFieldCrud({
    url: '/department',
    field: 'name',
    table: 'aramisc_human_departments',
    column: 'name',
    prefix: 'CyDepartement_',
    modal: 'deleteHumanDepartModal',
    shot: 'admin/rh/departement',
  });
});
