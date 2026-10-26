// cypress/e2e/admin/rh/poste.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Ressources humaines > Désignation (poste)
//  2. Il saisit un intitulé → le poste apparaît dans la liste
//  3. « Sélectionner > Modifier » : le champ est pré-rempli, il renomme → la liste affiche le nouvel intitulé
//  4. « Sélectionner > Supprimer », confirme → le poste disparaît (contrôle en base)
//
// Aucune notification (AramiscDesignationController). Parcours commun : rh/helpers.js > singleFieldCrud().

import { singleFieldCrud } from './helpers';

describe('Admin – RH : Poste / Désignation (CRUD)', () => {
  singleFieldCrud({
    url: '/designation',
    field: 'title',
    table: 'aramisc_designations',
    column: 'title',
    prefix: 'CyPoste_',
    modal: 'deleteDesignationModal',
    shot: 'admin/rh/poste',
  });
});
