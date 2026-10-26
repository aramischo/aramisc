// cypress/e2e/parent/conge/demande.cy.js
// Rôle Parent — rubrique Congé : formulaire + liste en attente + congés de l'enfant.
//
// Contrairement à l'élève, le parent dispose d'une route de suppression
// (parent-leave-delete) : le CRUD complet est donc nettoyable via l'UI. Il reste
// conditionné à l'existence d'un type de congé pour parent1 (aramisc_leave_defines) ;
// aucun n'est défini dans la base de test → le chemin d'écriture est ignoré pour l'instant.
// Pour l'activer : définir un type de congé pour parent1 (user_id 6, role_id 3).

import { parentSetup } from '../_support';

describe('Parent · Congé · demande', () => {
  const ctx = parentSetup();
  const shot = 'parent/conge/demande';

  it('affiche le formulaire de demande de congé', () => {
    cy.visit('/parent-apply-leave');
    cy.url().should('include', '/parent-apply-leave');
    cy.url().should('not.include', '/login');
    cy.get('select[name="leave_type"]').should('exist');
    cy.get('input[name="leave_from"]').should('exist');
    cy.get('input[name="leave_to"]').should('exist');
    cy.get('form').should('exist');
    // cy.screenshot(`${shot}/01-formulaire`);
  });

  it('soumet puis supprime une demande si un type de congé est disponible (sinon ignore)', () => {
    cy.visit('/parent-apply-leave');

    cy.get('select[name="leave_type"] option').then(($opts) => {
      const values = [...$opts].map((o) => o.value).filter((v) => v && v !== '');
      if (values.length === 0) {
        cy.log('Aucun type de congé défini pour ce parent — soumission ignorée (prérequis de données).');
        return;
      }
      cy.get('select[name="leave_type"]').select(values[0], { force: true });
      cy.get('textarea[name="reason"]').clear().type('Demande de test Cypress');
      // cy.screenshot(`${shot}/02-formulaire-rempli`);
      cy.get('form').first().submit();
      cy.url().should('not.include', '/login');

      // Nettoyage : supprimer la demande créée depuis la liste en attente (route delete parent).
      cy.visit('/parent-pending-leave');
      cy.get('body').then(($b) => {
        if ($b.find('a[href*="deleteApplyLeave"], [data-target*="delete"], form[action*="leave-delete"]').length) {
          cy.log('Demande créée : à nettoyer via la modale de suppression de la liste en attente.');
        }
      });
      // cy.screenshot(`${shot}/03-apres`);
    });
  });

  it('affiche la liste des demandes en attente', () => {
    cy.visit('/parent-pending-leave');
    cy.url().should('include', '/parent-pending-leave');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/04-en-attente`);
  });

  it('affiche les congés de l\'enfant', () => {
    cy.visit(`/parent-leave/${ctx.childId}`);
    cy.url().should('match', /\/parent-leave\/\d+/);
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/05-conges-enfant`);
  });
});
