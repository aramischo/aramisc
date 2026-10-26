// cypress/e2e/admin/frais/frais_principaux.cy.js
//
// Mode « frais directs » actif dans la base de test (generalSetting()->direct_fees_assign = 1) : la page Fees master
// crée en une fois un groupe, un type et un frais principal pour une classe / section, avec ses échéances,
// puis affecte le frais à tous les élèves de la classe / section.
//
// Scénario utilisateur :
//  1. L'admin ouvre Frais > Frais principaux : nom, montant 500, classe Class2, section sec2, une échéance « Tranche 1 »
//     au 15/10/2026 pour 500 → le frais apparaît dans la liste, affecté aux 5 élèves de Class2 / sec2
//  2. « Sélectionner > Modifier » : formulaire pré-rempli, il renomme et passe à 600 (échéance 600) → la liste est à jour
//  3. « Sélectionner > Supprimer », confirme → le frais disparaît avec son groupe, son type et ses affectations
//
// Nettoyage : before() mémorise l'id maximal des tables concernées, after() supprime toute ligne créée au-delà
// (même si le test échoue). Aucune notification dans ce parcours.
// Date d'échéance volontairement > 12 : cas réel d'un utilisateur FR (format j/m/aaaa).

import { php, findRow, rowAction } from '../administration/helpers';

const TABLES = [
  'direct_fees_installment_assigns', 'aramisc_fees_assigns', 'direct_fees_installments',
  'aramisc_fees_masters', 'aramisc_fees_types', 'aramisc_fees_groups',
];

describe('Admin – Frais : Frais principaux en mode frais directs (CRUD)', () => {
  const shot = 'admin/frais/frais_principaux';
  const name = `CyFrais_${Date.now()}`;
  const updatedName = `${name}_M`;
  let maxIds;

  before(() => {
    php(`
      $m = [];
      foreach (${JSON.stringify(TABLES).replace(/"/g, "'")} as $t) { $m[$t] = (int) DB::table($t)->max('id'); }
      echo 'CY<<'.base64_encode(json_encode($m)).'>>CY';
    `).then((m) => { maxIds = m; });
  });

  after(() => {
    if (!maxIds) return;
    php(`
      $m = json_decode('${JSON.stringify(maxIds)}', true);
      foreach ($m as $t => $max) { DB::table($t)->where('id', '>', $max)->delete(); }
    `);
  });

  const state = () =>
    php(`
      $type = DB::table('aramisc_fees_types')->whereIn('name', ['${name}', '${updatedName}'])->first(['id', 'name', 'fees_group_id']);
      $master = $type ? DB::table('aramisc_fees_masters')->where('fees_type_id', $type->id)->first(['id', 'amount', 'class_id', 'section_id']) : null;
      echo 'CY<<'.base64_encode(json_encode([
        'type' => $type,
        'group' => $type ? DB::table('aramisc_fees_groups')->where('id', $type->fees_group_id)->value('name') : null,
        'master' => $master,
        'installments' => $master ? DB::table('direct_fees_installments')->where('fees_master_id', $master->id)->get(['title', 'due_date', 'amount']) : [],
        'assigns' => $master ? DB::table('aramisc_fees_assigns')->where('fees_master_id', $master->id)->count() : 0,
      ])).'>>CY';
    `);

  const fillInstallment = (amount) => {
    cy.get('#productTable input[name="title[]"]').first().clear().type('Tranche 1');
    cy.get('#productTable input[name="due_date[]"]').first().invoke('val', '15/10/2026');
    // Valeurs posées sans frappe : le keyup de la page compare des chaînes et peut ouvrir une alerte
    cy.get('#productTable input[name="unPercentage[]"]').first().invoke('val', String(amount));
    cy.get('input[name="totalInstallmentAmount"]').invoke('val', String(amount));
  };

  it('crée un frais principal pour Class2 / sec2, le modifie puis le supprime', () => {
    cy.loginAsAdmin();
    cy.intercept('GET', '**/ajaxStudentPromoteSection*').as('sections');
    cy.visit('/fees-master');

    // ─────────────────────────── 1. CREATE ───────────────────────────
    cy.get('input[name="name"]').clear().type(name);
    cy.get('#fees_amount').clear().type('500');
    cy.get('#select_class').select('2', { force: true });
    cy.wait('@sections');
    cy.get('#select_section_member option[value="2"]').should('exist');
    cy.get('#select_section_member').select('2', { force: true });
    fillInstallment(500);
    // cy.screenshot(`${shot}/01-create-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    state().then((s) => {
      expect(s.type, 'type de frais créé').to.not.be.null;
      expect(s.group, 'groupe de frais créé').to.eq(name);
      expect(s.master).to.include({ class_id: 2, section_id: 2 });
      expect(Number(s.master.amount)).to.eq(500);
      expect(s.installments, 'une échéance').to.have.length(1);
      expect(s.installments[0].due_date, "date d'échéance").to.eq('2026-10-15');
      expect(s.assigns, 'affecté aux élèves de Class2 / sec2').to.eq(5);
    });

    // ─────────────────────────── 2. READ ─────────────────────────────
    cy.url().should('match', /\/fees-master$/);
    findRow(name).should('contain', '500').and('contain', 'Tranche 1');

    // ─────────────────────────── 3. UPDATE ───────────────────────────
    rowAction(name, 'a.dropdown-item[href*="fees-master/"]');

    cy.url().should('match', /\/fees-master\/\d+$/);
    cy.get('input[name="name"]').should('have.value', name);
    // En modification, le champ montant n'a pas d'id (#fees_amount n'existe qu'à la création)
    cy.get('input[name="amount"]').invoke('val').then((v) => expect(Number(v)).to.eq(500));

    cy.get('input[name="name"]').clear().type(updatedName);
    cy.get('input[name="amount"]').clear().type('600');
    fillInstallment(600);
    // cy.screenshot(`${shot}/03-update-formulaire`);
    cy.get('button.primary-btn.submit').click();
    // cy.screenshot(`${shot}/04-update-apres-submit`);

    state().then((s) => {
      expect(s.type.name).to.eq(updatedName);
      expect(s.group).to.eq(updatedName);
      expect(Number(s.master.amount)).to.eq(600);
      expect(s.installments).to.have.length(1);
      expect(Number(s.installments[0].amount)).to.eq(600);
    });
    cy.url().should('match', /\/fees-master$/);
    findRow(updatedName).should('contain', '600');

    // ─────────────────────────── 4. DELETE ───────────────────────────
    // À 1280 px la colonne Action est repliée (DataTables responsive) et la modale reste dans la cellule masquée :
    // seul le fond grisé apparaît (anomalie 40). Suppression faite sur un écran large, où la colonne est visible.
    cy.viewport(1920, 1080);
    cy.visit('/fees-master');
    rowAction(updatedName, 'a.dropdown-item.deleteFeesMasterGroup');
    cy.get('.modal.show[id^="deleteFeesMasterGroup"]').should('be.visible').find('button[type="submit"]').click();
    // cy.screenshot(`${shot}/05-delete-apres-confirmation`);

    state().then((s) => {
      expect(s.type, 'type supprimé').to.be.null;
    });
    // La suppression doit emporter tout ce que la création a produit (groupe, type, frais, échéance, affectations)
    cy.then(() => {
      php(`
        $m = json_decode('${JSON.stringify(maxIds)}', true);
        $left = [];
        foreach ($m as $t => $max) { $left[$t] = DB::table($t)->where('id', '>', $max)->count(); }
        echo 'CY<<'.base64_encode(json_encode($left)).'>>CY';
      `).should('deep.equal', Object.fromEntries(TABLES.map((t) => [t, 0])));
    });
  });
});
