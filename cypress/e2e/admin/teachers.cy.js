// cypress/e2e/admin/teachers.cy.js
//
// L'application n'a pas de page « enseignants » dédiée : les enseignants sont du personnel de rôle « Teacher ».
// Anciennes URL /teacher-list, /add-teacher, /teacher-evaluation(-report) inexistantes (404) → corrigées le 06/10/2026 :
//  - liste : Ressources humaines > Répertoire du personnel (/staff-directory), recherche de teacher1 ;
//  - ajout : Ressources humaines > Ajouter du personnel (/add-staff), le rôle « Teacher » est proposé.
// Le CRUD complet du personnel est dans rh/personnel.cy.js ; les pages d'évaluation des enseignants dans teacher_evaluation.cy.js.

import { findRow } from './administration/helpers';

describe('Admin – Teachers & Staff', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  describe('Teacher List', () => {
    it('affiche les enseignants dans le répertoire du personnel', () => {
      cy.visit('/staff-directory');
      cy.url().should('include', '/staff-directory');
      findRow('teacher1').should('contain', 'Teacher');
      cy.screenshot('admin_teachers_list');
    });

    it("charge la page d'ajout de personnel avec le rôle Teacher", () => {
      cy.visit('/add-staff');
      cy.url().should('include', '/add-staff');
      cy.get('form[action$="staff-store"]').should('exist');
      cy.get('select[name="role_id"] option[value="4"]').should('exist');
      cy.screenshot('admin_teachers_add_form');
    });
  });
});
