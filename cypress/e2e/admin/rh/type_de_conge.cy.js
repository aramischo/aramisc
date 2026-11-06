// cypress/e2e/admin/rh/type_de_conge.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Congés > Type de congé
//  2. Il saisit un type → le type apparaît dans la liste
//  3. « Sélectionner > Modifier » : le champ est pré-rempli, il renomme → la liste affiche le nouveau nom
//  4. « Sélectionner > Supprimer », confirme → le type disparaît (contrôle en base)
//
// Aucune notification (AramiscLeaveTypeController). Parcours commun : rh/helpers.js > singleFieldCrud().

import { singleFieldCrud } from './helpers';

describe('Admin – RH : Type de congé (CRUD)', () => {
  singleFieldCrud({
    url: '/leave-type',
    field: 'type',
    table: 'aramisc_leave_types',
    column: 'type',
    prefix: 'CyConge_',
    modal: 'deleteLeaveTypeModal',
    shot: 'admin/rh/type_de_conge',
  });
});
