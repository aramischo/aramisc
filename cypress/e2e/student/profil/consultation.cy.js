// cypress/e2e/student/profil/consultation.cy.js
// Rôle Student — rubrique Profil : consultation de la fiche personnelle.
// La mise à jour réutilise le formulaire d'admission complet (logique parent/fratrie,
// AramiscStudentAdmissionRequest) — déjà couvert côté admin et destructeur à tester ici,
// donc on se limite à la consultation.

describe('Student · Profil · consultation', () => {
  const shot = 'student/profil/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  it('affiche la fiche profil avec ses onglets', () => {
    cy.visit('/student-profile');
    cy.url().should('include', '/student-profile');

    // Les onglets structurants de la fiche élève.
    cy.get('a[href="#studentProfile"]').should('exist');
    cy.get('#studentProfile').should('exist');
    cy.get('a.nav-link').its('length').should('be.gte', 3);

    // La fiche affiche bien des données (tableau / détails) et pas une page vide.
    cy.get('#studentProfile').within(() => {
      cy.get('table, .single-info-details, .row').should('exist');
    });

    // cy.screenshot(`${shot}/01-fiche-profil`);
  });

  it('ouvre le formulaire d\'édition du profil (sans enregistrer)', () => {
    cy.visit('/student-profile');
    // Lien « Modifier » vers update-my-profile/{id} s'il est présent dans le thème.
    cy.get('body').then(($b) => {
      if ($b.find('a[href*="update-my-profile"]').length) {
        cy.get('a[href*="update-my-profile"]').first().click({ force: true });
        cy.url().should('include', '/update-my-profile');
        cy.get('form').should('exist');
        cy.get('input, select').its('length').should('be.gt', 5);
        // cy.screenshot(`${shot}/02-formulaire-edition`);
      } else {
        cy.log('Aucun lien « Modifier » exposé pour ce profil élève — étape ignorée.');
      }
    });
  });
});
