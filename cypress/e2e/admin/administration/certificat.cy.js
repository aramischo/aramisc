// cypress/e2e/admin/administration/certificat.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Administration > Certificat : nom, en-tête, date 15/10/2026, corps, pieds de page, police 2em,
//     mise en page A4 portrait (hauteur / largeur remplies automatiquement), sans photo → le modèle apparaît dans la liste
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il renomme et change le corps → la liste est à jour
//  3. « Sélectionner > Supprimer », confirme → le modèle disparaît (contrôle en base)
//
// Aucune notification (AramiscStudentCertificateController). after() supprime le modèle s'il reste.
// Date volontairement > 12 : cas réel d'un utilisateur FR (format j/m/aaaa).
// La génération des certificats est testée dans imprime_en_vrac/certificat_vrac.cy.js.

import { php } from './helpers';

describe('Admin – Administration : Modèle de certificat (CRUD)', () => {
  const shot = 'admin/administration/certificat';
  const prefix = 'CyCertif_';
  const name = `${prefix}${String(Date.now()).slice(-8)}`; // nom limité à 50 caractères
  const updatedName = `${name}_M`;

  after(() => {
    php(`DB::table('aramisc_student_certificates')->where('name', 'like', '${prefix}%')->delete();`);
  });

  const certificate = () =>
    php(`echo 'CY<<'.base64_encode(json_encode(DB::table('aramisc_student_certificates')->whereIn('name', ['${name}', '${updatedName}'])
      ->first(['name', 'date', 'body', 'layout', 'body_font_size']))).'>>CY';`);

  const certificateAction = (text, selector) =>
    cy.get('#table_id', { timeout: 20000 }).contains('tr', text).within(() => {
      cy.get('.dropdown-toggle').click({ force: true });
      cy.get(selector).click({ force: true });
    });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'un modèle de certificat", () => {
    cy.loginAsAdmin();
    cy.visit('/student-certificate');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="name"]').clear().type(name);
    cy.get('input[name="header_left_text"]').clear().type('Ecole Aramisc');
    cy.get('input[name="date"]').invoke('val', '15/10/2026');
    cy.get('textarea[name="body"]').clear().type('Nous certifions que [name] est inscrit.');
    cy.get('input[name="footer_left_text"]').clear().type('Le directeur');
    cy.get('input[name="body_font_size"]').clear().type('2em');
    cy.get('select#layout').select('1', { force: true });
    // A4 portrait remplit et verrouille hauteur / largeur
    cy.get('input[name="height"]').should('have.value', '297');
    cy.get('input[name="width"]').should('have.value', '210');
    cy.get('input[name="student_photo"][value="0"]').check({ force: true });
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // Pas de toast affiché sur cette page : on attend la liste rechargée avant le contrôle en base
    cy.get('#table_id', { timeout: 30000 }).should('contain', name);
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    certificate().then((c) => {
      expect(c, 'modèle enregistré').to.not.be.null;
      expect(c).to.include({ date: '2026-10-15', body_font_size: '2em' });
      expect(String(c.layout)).to.eq('1');
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/student-certificate$/);
    cy.get('#table_id').contains('tr', name).should('exist');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    certificateAction(name, 'a.dropdown-item[href*="student-certificate/"]');

    cy.url().should('match', /\/student-certificate\/\d+$/);
    cy.get('input[name="name"]').should('have.value', name).clear().type(updatedName);
    cy.get('input[name="date"]').should('have.value', '15/10/2026');
    cy.get('textarea[name="body"]').clear().type('Certificat modifié pour [name].');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    cy.get('#table_id', { timeout: 30000 }).should('contain', updatedName);
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    certificate().then((c) => {
      expect(c).to.include({ name: updatedName, body: 'Certificat modifié pour [name].', date: '2026-10-15' });
    });
    cy.get('#table_id').contains('tr', updatedName).should('exist');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    certificateAction(updatedName, 'a.dropdown-item[data-target^="#deleteSectionModal"]');
    cy.get('.modal.show[id^="deleteSectionModal"]').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    certificate().should('be.null');
    cy.get('#table_id').should('not.contain', updatedName);
  });
});
