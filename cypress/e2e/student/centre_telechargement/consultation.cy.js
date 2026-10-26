// cypress/e2e/student/centre_telechargement/consultation.cy.js
// Rôle Student — rubrique Centre de téléchargement : devoirs, matériel pédagogique,
// programme (syllabus) et autres téléchargements (lecture seule).

describe('Student · Centre de téléchargement · consultation', () => {
  const shot = 'student/centre_telechargement/consultation';

  beforeEach(() => {
    cy.loginAsStudent();
  });

  const pages = [
    { url: '/student-assignment', nom: 'devoirs' },
    { url: '/student-study-material', nom: 'materiel-pedagogique' },
    { url: '/student-syllabus', nom: 'syllabus' },
    { url: '/student-others-download', nom: 'autres-telechargements' },
  ];

  pages.forEach(({ url, nom }, i) => {
    it(`affiche la page « ${nom} »`, () => {
      cy.visit(url);
      cy.url().should('include', url);
      cy.url().should('not.include', '/login');
      cy.get('table, .card, .white-box').should('exist');
      // cy.screenshot(`${shot}/0${i + 1}-${nom}`);
    });
  });
});
