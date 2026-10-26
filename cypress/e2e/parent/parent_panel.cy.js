// cypress/e2e/parent/parent_panel.cy.js
//
// La base de test a `due_fees_login` = 1 et une facture impayée échue pour l'élève 1 (enfant de parent1) :
// UserRolePermission répond 403 sur toutes les pages de cet enfant. Le réglage est coupé pendant le spec
// et rétabli dans after(). Il faut aussi vider le cache `have_due_fees_<user_id>` (rememberForever) et les
// sessions Cypress (generalSetting() est recopié dans la session Laravel).
// Si Cypress plante avant after() : remettre due_fees_login à 1 dans aramisc_general_settings.

import { php } from '../admin/portefeuille/helpers';

const PARENT_EMAIL = 'parent1@aramisc.com';

const disableDueFeesLogin = () =>
  php(`
    $old = DB::table('aramisc_general_settings')->pluck('due_fees_login', 'id');
    DB::table('aramisc_general_settings')->update(['due_fees_login' => 0]);
    Cache::forget('have_due_fees_'.DB::table('users')->where('email', '${PARENT_EMAIL}')->value('id'));
    echo 'CY<<'.base64_encode(json_encode($old)).'>>CY';
  `);

describe('Parent Panel', () => {
  let backup;

  before(() => {
    disableDueFeesLogin().then((old) => {
      backup = old;
    });
    cy.then(() => Cypress.session.clearAllSavedSessions());
  });

  after(() => {
    if (backup) {
      const cases = Object.entries(backup)
        .map(([id, v]) => `WHEN ${Number(id)} THEN ${Number(v)}`)
        .join(' ');
      php(`
        DB::statement('UPDATE aramisc_general_settings SET due_fees_login = CASE id ${cases} ELSE due_fees_login END');
        Cache::forget('have_due_fees_'.DB::table('users')->where('email', '${PARENT_EMAIL}')->value('id'));
      `);
    }
    cy.then(() => Cypress.session.clearAllSavedSessions());
  });

  beforeEach(() => {
    cy.loginAsParent();
  });

  // ─────────────────── DASHBOARD ────────────────────────────────
  it('affiche le tableau de bord parent', () => {
    cy.visit('/parent-dashboard');
    cy.url().should('include', '/parent-dashboard');
    cy.get('body').should('be.visible');
    cy.get('[class*="card"], [class*="widget"], h2, h3').should('exist');
    cy.screenshot('parent_dashboard');
  });

  // ─────────────────── MY CHILDREN ──────────────────────────────
  it('affiche les informations de l\'enfant (id=1 par défaut)', () => {
    cy.visit('/my-children/1');
    cy.url().should('match', /\/my-children\/\d+/);
    cy.get('body').should('be.visible');
    cy.screenshot('parent_my_children');
  });

  // ─────────────────── FEES ─────────────────────────────────────
  it('affiche les frais scolaires de l\'enfant', () => {
    cy.visit('/parent-fees/1');
    cy.url().should('match', /\/parent-fees\/\d+/);
    cy.get('table, .card').should('exist');
    cy.screenshot('parent_fees');
  });

  // ─────────────────── CLASS ROUTINE ────────────────────────────
  it('affiche l\'emploi du temps de l\'enfant', () => {
    cy.visit('/parent-class-routine/1');
    cy.url().should('match', /\/parent-class-routine\/\d+/);
    cy.get('body').should('be.visible');
    cy.screenshot('parent_class_routine');
  });

  // ─────────────────── ATTENDANCE ───────────────────────────────
  it('affiche la présence de l\'enfant', () => {
    cy.visit('/parent-attendance/1');
    cy.url().should('match', /\/parent-attendance\/\d+/);
    cy.get('body').should('be.visible');
    cy.screenshot('parent_attendance');
  });

  // ─────────────────── HOMEWORK ─────────────────────────────────
  it('affiche les devoirs de l\'enfant', () => {
    cy.visit('/parent-homework/1');
    cy.url().should('match', /\/parent-homework\/\d+/);
    cy.get('body').should('be.visible');
    cy.screenshot('parent_homework');
  });

  // ─────────────────── NOTICEBOARD ──────────────────────────────
  it('affiche le tableau d\'affichage pour le parent', () => {
    cy.visit('/parent-noticeboard');
    cy.url().should('include', '/parent-noticeboard');
    cy.get('body').should('be.visible');
    cy.screenshot('parent_noticeboard');
  });

  // ─────────────────── ONLINE EXAMINATION ───────────────────────
  it('affiche la liste des examens en ligne de l\'enfant', () => {
    cy.visit('/parent-online-examination/1');
    cy.url().should('match', /\/parent-online-examination\/\d+/);
    cy.get('body').should('be.visible');
    cy.screenshot('parent_online_examination');
  });

  it('affiche les résultats des examens en ligne de l\'enfant', () => {
    cy.visit('/parent-online-examination-result/1');
    cy.url().should('match', /\/parent-online-examination-result\/\d+/);
    cy.get('body').should('be.visible');
    cy.screenshot('parent_online_examination_result');
  });

  // ─────────────────── EXAMINATION RESULTS ──────────────────────
  it('affiche les résultats d\'examen de l\'enfant', () => {
    cy.visit('/parent-examination/1');
    cy.url().should('match', /\/parent-examination\/\d+/);
    cy.get('body').should('be.visible');
    cy.screenshot('parent_examination_results');
  });

  // ─────────────────── EXAM SCHEDULE ────────────────────────────
  it('affiche le calendrier d\'examens de l\'enfant', () => {
    cy.visit('/parent-examination-schedule/1');
    cy.url().should('match', /\/parent-examination-schedule\/\d+/);
    cy.get('body').should('be.visible');
    cy.screenshot('parent_exam_schedule');
  });

  // ─────────────────── LIBRARY ──────────────────────────────────
  it('affiche la liste des livres de la bibliothèque (parent)', () => {
    cy.visit('/parent-library');
    cy.url().should('include', '/parent-library');
    cy.get('body').should('be.visible');
    cy.screenshot('parent_library');
  });

  it('affiche les livres empruntés par l\'enfant', () => {
    cy.visit('/parent-book-issue');
    cy.url().should('include', '/parent-book-issue');
    cy.get('body').should('be.visible');
    cy.screenshot('parent_book_issue');
  });

  // ─────────────────── SUBJECTS ─────────────────────────────────
  it('affiche les matières de l\'enfant', () => {
    cy.visit('/parent-subjects/1');
    cy.url().should('match', /\/parent-subjects\/\d+/);
    cy.get('body').should('be.visible');
    cy.screenshot('parent_subjects');
  });

  // ─────────────────── TEACHER LIST ─────────────────────────────
  it('affiche la liste des enseignants de l\'enfant', () => {
    cy.visit('/parent-teacher-list/1');
    cy.url().should('match', /\/parent-teacher-list\/\d+/);
    cy.get('body').should('be.visible');
    cy.screenshot('parent_teacher_list');
  });

  // ─────────────────── TRANSPORT ────────────────────────────────
  it('affiche les informations de transport de l\'enfant', () => {
    cy.visit('/parent-transport/1');
    cy.url().should('match', /\/parent-transport\/\d+/);
    cy.get('body').should('be.visible');
    cy.screenshot('parent_transport');
  });

  // ─────────────────── DORMITORY ────────────────────────────────
  it('affiche les informations de dortoir de l\'enfant', () => {
    cy.visit('/parent-dormitory/1');
    cy.url().should('match', /\/parent-dormitory\/\d+/);
    cy.get('body').should('be.visible');
    cy.screenshot('parent_dormitory');
  });

  // ─────────────────── APPLY LEAVE ──────────────────────────────
  it('affiche le formulaire de demande de congé parent', () => {
    cy.visit('/parent-apply-leave');
    cy.url().should('include', '/parent-apply-leave');
    cy.get('form, .card').should('exist');
    cy.screenshot('parent_apply_leave');
  });

  it('affiche la liste des congés en attente (parent)', () => {
    cy.visit('/parent-pending-leave');
    cy.url().should('include', '/parent-pending-leave');
    cy.get('table, .card').should('exist');
    cy.screenshot('parent_pending_leave');
  });

  it('affiche les congés de l\'enfant', () => {
    cy.visit('/parent-leave/1');
    cy.url().should('match', /\/parent-leave\/\d+/);
    cy.get('body').should('be.visible');
    cy.screenshot('parent_leave');
  });
});
