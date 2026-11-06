/**
 * Chantier permissions — specs d'autorisation data-driven.
 *
 * Pour chaque rôle, vérifie que TOUTES les routes GET (sans paramètre, gardées
 * par userRolePermission) que le rôle est AUTORISÉ à atteindre ne renvoient PAS
 * de 403. C'est la validation systématique de l'enforcement : complémentaire
 * des suites « consultation » (parcours triés à la main) et de la collecte audit
 * manuelle (qui, elle, révèle les écrans légitimes NON encore assignés).
 *
 * Source : cypress/fixtures/reachable_routes.json, générée par
 *   php artisan permissions:reachable-routes
 * (à relancer après toute modification des permissions/assignations).
 *
 * Les routes à effet de bord (logout, suppression, envoi, export/pdf…) sont
 * exclues en amont par la commande : on ne les « probe » pas en aveugle.
 *
 * Prend tout son sens sous enforce=true (vraie validation du refus/non-refus).
 * Sans enforce, chaque route renvoie de toute façon != 403 → sert alors de
 * simple smoke de « reachability ». Les routes à effet de bord étant exclues
 * par le générateur, ces GET sont en lecture seule et rejouables sans risque :
 *   node scripts/run-cypress.js run \
 *        --spec "cypress/e2e/autorisation/acces_routes.cy.js"
 * (mettre ACCESS_CONTROL_ENFORCE=true dans .env.cypress pour la validation).
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

describe('Autorisation — routes atteignables par rôle (enforce)', () => {
  Object.keys(roleLogins).forEach((slug) => {
    const uris = routes[slug] || [];
    if (!uris.length) return; // rôle sans route gardée atteignable (ex. driver)

    describe(`${slug} — ${uris.length} routes`, () => {
      beforeEach(() => {
        cy[roleLogins[slug]]();
      });

      uris.forEach((uri) => {
        it(`GET /${uri} ne doit pas renvoyer 403`, () => {
          cy.request({ url: '/' + uri, failOnStatusCode: false }).then((res) => {
            // Seul le 403 est un échec : un 200/302 = OK, un 500 = bug applicatif
            // (données/contrôleur), pas un refus de permission.
            expect(res.status, `/${uri}`).to.not.eq(403);
          });
        });
      });
    });
  });
});
