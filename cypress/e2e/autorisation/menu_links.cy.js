/**
 * Chantier permissions — filet complémentaire : liens réellement rendus par rôle.
 *
 * La spec acces_routes.cy.js couvre les routes GET SANS paramètre (fixture statique).
 * Celle-ci complète l'angle mort : après login, elle récupère TOUS les liens
 * `a[href]` effectivement rendus dans la page du rôle (menu, sous-menus repliés
 * inclus — ils sont dans le DOM — et liens d'action), donc aussi les liens
 * PARAMÉTRÉS avec de vrais IDs (edit/view), puis vérifie que chacun ne renvoie
 * pas 403 sous enforce=true.
 *
 * Liens filtrés (non probés) : externes, ancres/#, javascript:, et liens à effet
 * de bord (logout, suppression, envoi, export/pdf…).
 *
 * N'a de sens que sous enforce=true (sinon le menu n'est pas filtré et tout
 * renvoie 200). Lancer avec ACCESS_CONTROL_ENFORCE=true dans .env.cypress :
 *   node scripts/run-cypress.js run --spec "cypress/e2e/autorisation/menu_links.cy.js"
 */
const routes = require('../../fixtures/reachable_routes.json');

const roleLogins = {
  admin: 'loginAsAdmin',
  teacher: 'loginAsTeacher',
  accountant: 'loginAsAccountant',
  student: 'loginAsStudent',
  parent: 'loginAsParent',
  receptionist: 'loginAsReceptionist',
  librarian: 'loginAsLibrarian',
  driver: 'loginAsDriver',
};

const DANGER = [
  'logout', 'signout', 'sign-out', 'delete', 'destroy', 'remove', 'trash',
  'cancel', 'approve', 'reject', 'send', 'resend', 'reset', 'clear', 'flush',
  'backup', 'restore', 'sync', 'import', 'truncate', 'activate', 'deactivate',
  'enable', 'disable', 'impersonate', 'switch', 'login-as', 'mark-', 'read-all',
  'generate', 'regenerate', 'migrate', 'seed', 'install', 'download', 'export',
  'print', 'pdf', 'excel', 'csv',
];
const isDanger = (h) => DANGER.some((f) => h.toLowerCase().includes(f));

const BASE = 'http://dev.aramisc.com';

// Liens dont un 403 N'EST PAS un défaut de menu/permission mais un blocage
// CONTEXTUEL pré-existant de l'app : la règle « frais impayés » (middleware
// UserRolePermission, avant notre contrôle d'accès) bloque les pages d'un
// enfant qui a des frais dus. Prouvé : 0 deny loggé, routes assignées au rôle.
// On les ignore donc dans l'assertion (ils dépendent de la donnée, pas du menu).
const CONTEXTUAL = [
  /^\/parent-subjects\/\d+/,
  /^\/parent-examination-schedule\/\d+/,
  /^\/parent-online-examination\/\d+/,
  /^\/parent-teacher-list\/\d+/,
  /^\/parent-homework\/\d+/,
  /^\/parent-attendance\/\d+/,
  /^\/my-children\/\d+/,
];
const isContextual = (u) => CONTEXTUAL.some((re) => re.test(u));

describe('Autorisation — liens rendus par rôle (filet enforce)', () => {
  Object.keys(roleLogins).forEach((slug) => {
    const landing = (routes[slug] || [])[0];
    if (!landing) return; // rôle sans page gardée atteignable (driver)

    it(`${slug} : aucun lien de page ne renvoie 403`, () => {
      cy[roleLogins[slug]]();
      cy.visit('/' + landing);

      cy.get('a[href]').then(($a) => {
        const urls = [
          ...new Set(
            $a
              .toArray()
              .map((a) => (a.getAttribute('href') || '').trim())
              .map((h) => h.replace(BASE, ''))
              .filter((h) => h.startsWith('/'))
              .filter((h) => h !== '/' && !h.startsWith('/#'))
              .filter((h) => !isDanger(h))
          ),
        ];
        cy.log(`${slug} : ${urls.length} liens probés`);

        const denied = [];
        cy.wrap(urls, { log: false })
          .each((u) => {
            cy.request({ url: u, failOnStatusCode: false }).then((res) => {
              if (res.status === 403 && !isContextual(u)) denied.push(u);
            });
          })
          .then(() => {
            cy.log(`${slug} — ${denied.length} liens en 403`);
            expect(denied, `${slug} — liens en 403 : ${denied.join(' , ')}`).to.have.length(0);
          });
      });
    });
  });
});
