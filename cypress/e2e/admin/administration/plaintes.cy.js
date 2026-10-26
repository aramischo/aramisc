// cypress/e2e/admin/administration/plaintes.cy.js
//
// Scénario utilisateur :
//  1. L'admin ouvre Administration > Plainte
//  2. Il remplit le formulaire (plaignant, type, source, téléphone, date, action, assigné, description) → la plainte apparaît
//  3. Il ouvre « Sélectionner > Voir » : la fenêtre de détail affiche la plainte
//  4. Il clique « Sélectionner > Modifier », le formulaire est pré-rempli, il modifie → la liste affiche les nouvelles valeurs
//  5. Il clique « Sélectionner > Supprimer », confirme dans la modale → la plainte disparaît
//
// La base de test n'a aucun « type de plainte » (aramisc_setup_admins type 2) : le test en crée un en before() et le supprime en after().
// Date volontairement > 12 (15/09/2026) : c'est le cas réel d'un utilisateur FR (format j/m/aaaa).
// Aucune notification (AramiscComplaintController).

import { php, findRow, rowAction, shouldBeGone } from './helpers';

describe('Admin – Administration : Plainte (CRUD)', () => {
  const shot = 'admin/administration/plaintes';
  const prefix = 'CyPlainte_';
  const typeName = 'CyTypePlainte';
  let typeId;
  let sourceId;

  before(() => {
    php(`
      $id = DB::table('aramisc_setup_admins')->insertGetId([
        'type' => 2, 'name' => '${typeName}', 'description' => 'Cypress', 'school_id' => 1,
        'academic_id' => getAcademicId(), 'created_at' => now(), 'updated_at' => now(),
      ]);
      $src = DB::table('aramisc_setup_admins')->where('type', 3)->value('id');
      echo 'CY<<'.base64_encode(json_encode(['type' => $id, 'source' => $src])).'>>CY';
    `).then((r) => {
      expect(r.source, 'une source de plainte existe dans la base de test').to.be.a('number');
      typeId = r.type;
      sourceId = r.source;
    });
  });

  after(() => {
    php(`
      DB::table('aramisc_complaints')->where('complaint_by', 'like', '${prefix}%')->delete();
      DB::table('aramisc_setup_admins')->where('type', 2)->where('name', '${typeName}')->delete();
    `);
  });

  it("effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d'une plainte", () => {
    const by = `${prefix}${Date.now()}`;
    const updatedBy = `${by}_Modifie`;

    cy.loginAsAdmin();
    cy.visit('/complaint');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="complaint_by"]').clear().type(by);
    cy.get('select[name="complaint_type"]').select(String(typeId), { force: true });
    cy.get('select[name="complaint_source"]').select(String(sourceId), { force: true });
    cy.get('input[name="phone"]').clear().type('0611223344');
    cy.get('input[name="date"]').invoke('val', '15/09/2026');
    cy.get('input[name="action_taken"]').clear().type('Appel au parent');
    cy.get('input[name="assigned"]').clear().type('Surveillant général');
    cy.get('textarea[name="description"]').clear().type('Plainte créée par Cypress');
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/complaint$/);
    findRow(by).should('contain', typeName).and('contain', '0611223344').and('contain', '15/09/2026');

    rowAction(by, 'a.dropdown-item.modalLink[href*="complaint/"]');
    cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible')
      .and('contain', by).and('contain', 'Plainte créée par Cypress');
    cy.get('#showDetaildModal .close').first().click({ force: true });

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(by, 'a.dropdown-item[href$="/edit"]');

    cy.url().should('match', /\/complaint\/\d+\/edit$/);
    cy.get('input[name="complaint_by"]').should('have.value', by);
    cy.get('select[name="complaint_type"]').should('have.value', String(typeId));
    cy.get('input[name="date"]').should('have.value', '15/09/2026');

    cy.get('input[name="complaint_by"]').clear().type(updatedBy);
    cy.get('input[name="phone"]').clear().type('0699887766');
    cy.get('textarea[name="description"]').clear().type('Plainte traitée');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    cy.url().should('match', /\/complaint$/);
    findRow(updatedBy).should('contain', '0699887766');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    rowAction(updatedBy, 'a.dropdown-item[onclick^="deleteComplaint"]');
    cy.get('#deleteComplaintModal.show').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    cy.url().should('match', /\/complaint$/);
    shouldBeGone(updatedBy);
  });
});
