// cypress/e2e/admin/examen_en_ligne/banque_de_questions.cy.js
//
// Scénario utilisateur :
//  0. Préparation : création d'un groupe de questions (la base de test n'en a aucun, il est obligatoire)
//  1. Examen en ligne > Banque de questions : groupe → classe → section(s) (AJAX) → type → question → note
//     → champs propres au type → « Enregistrer la question » → la question apparaît dans la liste
//  2. Sélectionner > Modifier : formulaire pré-rempli → modification → « Mettre à jour la question »
//  3. Sélectionner > Supprimer → modale de confirmation → la question disparaît
//  Trois types testés : Vrai / Faux, Texte à trou, Choix multiple (bonne réponse vérifiée en base).
//  4. Nettoyage : suppression du groupe (base de test)
// Aucune notification n'est envoyée par ce contrôleur.
// Captures d'écran : en commentaire pour l'instant.

import { php } from '../portefeuille/helpers';

const CLASS_ID = '1'; // Class1
const SECTION_ID = '1'; // sec1

/** Pilote un <select> habillé par nice-select. */
const selectValue = (selector, value) => cy.get(selector).select(value, { force: true });

/** Ouvre le menu « Sélectionner » de la ligne et clique sur l'action voulue. */
const rowAction = (text, selector) => {
  cy.get('#table_id', { timeout: 20000 }).contains('tr', text).within(() => {
    cy.get('.dropdown-toggle').click({ force: true });
    cy.get(selector).click({ force: true });
  });
};

/** Remplit la partie commune du formulaire de création. */
const fillCommon = (groupId, type, question, marks) => {
  cy.visit('/question-bank');
  selectValue('#question_bank select[name="group"]', String(groupId));
  selectValue('#class_id_email_sms', CLASS_ID);
  cy.get(`#selectMultiSections option[value="${SECTION_ID}"]`, { timeout: 20000 }).should('exist');
  cy.get('#selectMultiSections').select([SECTION_ID], { force: true });
  selectValue('#question-type', type);
  cy.get('#question_bank textarea[name="question"]').type(question);
  cy.get('#question_bank input[name="marks"]').type(marks);
};

const submit = () => cy.get('#question_bank_submit').click();

/** Ligne de la question en base (null si absente). */
const dbQuestion = (question) =>
  php(`
    $q = DB::table('aramisc_question_banks')->where('question', '${question}')->first();
    if ($q) { $q->options = DB::table('aramisc_question_bank_mu_options')->where('question_bank_id', $q->id)->orderBy('id')->get(['title', 'status']); }
    echo 'CY<<'.base64_encode(json_encode($q)).'>>CY';
  `);

/** Suppression via l'interface, puis vérification de la liste. */
const deleteQuestion = (question, shot) => {
  rowAction(question, 'a.dropdown-item[data-target^="#deleteQuestionBankModal"]');
  cy.get('.modal.show', { timeout: 10000 }).find('button[type="submit"]').click();
  // cy.screenshot(`${shot}/05-delete-apres-confirmation`);
  cy.url().should('match', /\/question-bank$/);
  cy.get('#table_id', { timeout: 20000 }).should('not.contain', question);
};

describe('Admin – Examen en ligne : Banque de questions (CRUD)', () => {
  const stamp = Date.now();
  const groupTitle = `Groupe_QB_${stamp}`;
  const prefix = `Question_${stamp}`;
  let groupId;

  before(() => {
    php(`
      $id = DB::table('aramisc_question_groups')->insertGetId([
        'title' => '${groupTitle}', 'active_status' => 1, 'school_id' => 1, 'academic_id' => 1,
        'created_at' => now(), 'updated_at' => now(),
      ]);
      echo 'CY<<'.base64_encode(json_encode($id)).'>>CY';
    `).then((id) => {
      expect(id, 'groupe de questions créé').to.be.a('number');
      groupId = id;
    });
  });

  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // Nettoyage : questions restantes (test interrompu), leurs options, puis le groupe
  after(() => {
    php(`
      $ids = DB::table('aramisc_question_banks')->where('question', 'like', '${prefix}%')->pluck('id');
      DB::table('aramisc_question_bank_mu_options')->whereIn('question_bank_id', $ids)->delete();
      DB::table('aramisc_question_banks')->whereIn('id', $ids)->delete();
      DB::table('aramisc_question_groups')->where('title', '${groupTitle}')->delete();
    `);
  });

  it('Vrai / Faux : création, lecture, modification, suppression', () => {
    const shot = 'admin/examen_en_ligne/banque_de_questions/vrai_faux';
    const question = `${prefix}_VF`;
    const updated = `${question}_Updated`;

    // ─────────────── 1. CREATE ───────────────
    fillCommon(groupId, 'T', question, '2');
    cy.get('#relationFather').check({ force: true }); // Vrai
    // cy.screenshot(`${shot}/01-create-formulaire`);
    submit();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────── 2. READ ───────────────
    cy.get('#table_id', { timeout: 20000 }).contains('tr', question)
      .should('contain', groupTitle).and('contain', 'Class1').and('contain', 'True False');
    dbQuestion(question).then((q) => expect(q.trueFalse, 'réponse enregistrée').to.eq('T'));

    // ─────────────── 3. UPDATE ───────────────
    rowAction(question, 'a.dropdown-item[href*="/question-bank/"]');
    cy.url().should('match', /\/question-bank\/\d+$/);
    cy.get('#question_bank textarea[name="question"]').should('have.value', question).clear().type(updated);
    cy.get('#relationMother').check({ force: true }); // Faux
    // cy.screenshot(`${shot}/03-update-formulaire`);
    submit();
    // cy.screenshot(`${shot}/04-update-apres-submit`);
    cy.url().should('match', /\/question-bank$/);
    cy.get('#table_id', { timeout: 20000 }).contains('tr', updated).should('exist');
    dbQuestion(updated).then((q) => expect(q.trueFalse, 'réponse modifiée').to.eq('F'));

    // ─────────────── 4. DELETE ───────────────
    deleteQuestion(updated, shot);
  });

  it('Texte à trou : création, lecture, modification, suppression', () => {
    const shot = 'admin/examen_en_ligne/banque_de_questions/texte_a_trou';
    const question = `${prefix}_TT`;
    const words = 'Rabat';
    const updatedWords = 'Rabat, Salé';

    // ─────────────── 1. CREATE ───────────────
    fillCommon(groupId, 'F', question, '3');
    cy.get('#question_bank textarea[name="suitable_words"]').should('be.visible').type(words);
    // cy.screenshot(`${shot}/01-create-formulaire`);
    submit();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────── 2. READ ───────────────
    cy.get('#table_id', { timeout: 20000 }).contains('tr', question).should('contain', 'Fill in the blank');
    dbQuestion(question).then((q) => {
      expect(q.suitable_words).to.eq(words);
      expect(q.marks).to.eq(3);
    });

    // ─────────────── 3. UPDATE ───────────────
    rowAction(question, 'a.dropdown-item[href*="/question-bank/"]');
    cy.get('#question_bank textarea[name="suitable_words"]').should('have.value', words).clear().type(updatedWords);
    cy.get('#question_bank input[name="marks"]').clear().type('4');
    // cy.screenshot(`${shot}/03-update-formulaire`);
    submit();
    // cy.screenshot(`${shot}/04-update-apres-submit`);
    cy.url().should('match', /\/question-bank$/);
    dbQuestion(question).then((q) => {
      expect(q.suitable_words).to.eq(updatedWords);
      expect(q.marks).to.eq(4);
    });

    // ─────────────── 4. DELETE ───────────────
    deleteQuestion(question, shot);
  });

  it('Choix multiple : création, lecture, modification, suppression (bonne réponse vérifiée en base)', () => {
    const shot = 'admin/examen_en_ligne/banque_de_questions/choix_multiple';
    const question = `${prefix}_QCM`;
    const options = ['Paris', 'Rabat', 'Madrid'];

    // ─────────────── 1. CREATE ───────────────
    fillCommon(groupId, 'M', question, '5');
    cy.get('#number_of_option').should('be.visible').type(String(options.length));
    cy.get('#create-option').click();
    cy.get('#question_bank input[name="option[]"]').should('have.length', options.length).each(($el, i) => {
      cy.wrap($el).type(options[i]);
    });
    cy.get('#option_check_2').check({ force: true }); // bonne réponse : Rabat
    // cy.screenshot(`${shot}/01-create-formulaire`);
    submit();
    // cy.screenshot(`${shot}/02-create-apres-submit`);

    // ─────────────── 2. READ ───────────────
    cy.get('#table_id', { timeout: 20000 }).contains('tr', question).should('contain', 'Multiple Choice');
    dbQuestion(question).then((q) => {
      expect(q.options.map((o) => o.title), 'options enregistrées').to.deep.eq(options);
      expect(q.options.map((o) => o.status), 'bonne réponse = option 2').to.deep.eq([0, 1, 0]);
    });

    // ─────────────── 3. UPDATE ───────────────
    rowAction(question, 'a.dropdown-item[href*="/question-bank/"]');
    cy.get('#question_bank input[name="option[]"]').should('have.length', options.length);
    cy.get('#question_bank input[name="option[]"]').eq(2).clear().type('Casablanca');
    cy.get('#option_check_2').uncheck({ force: true });
    cy.get('#option_check_3').check({ force: true }); // nouvelle bonne réponse : Casablanca
    // cy.screenshot(`${shot}/03-update-formulaire`);
    submit();
    // cy.screenshot(`${shot}/04-update-apres-submit`);
    cy.url().should('match', /\/question-bank$/);
    dbQuestion(question).then((q) => {
      expect(q.options.map((o) => o.title)).to.deep.eq(['Paris', 'Rabat', 'Casablanca']);
      expect(q.options.map((o) => o.status), 'bonne réponse = option 3').to.deep.eq([0, 0, 1]);
    });

    // ─────────────── 4. DELETE ───────────────
    deleteQuestion(question, shot);
    dbQuestion(question).then((q) => expect(q, 'question supprimée en base').to.be.null);
  });
});
