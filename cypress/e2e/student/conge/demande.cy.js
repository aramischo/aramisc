// cypress/e2e/student/conge/demande.cy.js
// Rôle Student — rubrique Congé : formulaire de demande + liste des demandes en attente.
//
// Limites (voir NOTES_PASSATION) :
//  - student1 n'a aucun type de congé défini (aramisc_leave_defines) → le <select> leave_type
//    est vide : impossible de soumettre une demande valide. Le chemin d'écriture ci-dessous ne
//    s'exécute donc que si au moins un type de congé est proposé.
//  - La suppression d'une demande côté panneau élève pointe vers la route ADMIN apply-leave-delete
//    (permission admin) : un élève ne peut pas nettoyer sa propre demande via l'UI → on ne crée
//    pas de donnée non nettoyable.
//  - Bug confirmé : leaveStore()/update() appellent filesize($request->file('attach_file')) avant
//    de vérifier la présence du fichier (crash/avertissement selon le cas).

describe('Student · Congé · demande', () => {
  const shot = 'student/conge/demande';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche le formulaire de demande de congé avec ses champs', () => {
    cy.visit('/student-apply-leave');
    cy.url().should('include', '/student-apply-leave');
    cy.url().should('not.include', '/login');

    cy.get('input[name="apply_date"]').should('exist');
    cy.get('select[name="leave_type"]').should('exist');
    cy.get('input[name="leave_from"]').should('exist');
    cy.get('input[name="leave_to"]').should('exist');
    cy.get('textarea[name="reason"]').should('exist');
    cy.get('form').find('button').contains(/soumettre|submit|save|enregistrer/i).should('exist');
    // cy.screenshot(`${shot}/01-formulaire`);
  });

  it('soumet une demande si un type de congé est disponible (sinon ignore)', () => {
    cy.visit('/student-apply-leave');

    cy.get('select[name="leave_type"] option').then(($opts) => {
      const values = [...$opts].map((o) => o.value).filter((v) => v && v !== '');
      if (values.length === 0) {
        cy.log('Aucun type de congé défini pour cet élève — soumission ignorée (prérequis de données).');
        return;
      }
      cy.get('select[name="leave_type"]').select(values[0], { force: true });
      cy.get('textarea[name="reason"]').clear().type('Demande de test Cypress');
      // cy.screenshot(`${shot}/02-formulaire-rempli`);
      cy.get('form').first().submit();
      cy.url().should('not.include', '/login');
      // cy.screenshot(`${shot}/03-apres-soumission`);
    });
  });

  it('affiche la liste des demandes de congé en attente', () => {
    cy.visit('/student-pending-leave');
    cy.url().should('include', '/student-pending-leave');
    cy.url().should('not.include', '/login');
    cy.get('table, .card, .white-box').should('exist');
    // cy.screenshot(`${shot}/04-demandes-en-attente`);
  });
});
