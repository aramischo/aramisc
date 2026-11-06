// cypress/e2e/admin/transport.cy.js

describe('Admin – Transport', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // ─────────────────── VEHICLE ──────────────────────────────────
  describe('Vehicle', () => {
    const num =
        it('affiche la liste des véhicules', () => {
          cy.visit('/vehicle');
          cy.url().should('include', '/vehicle');
          cy.get('body').should('be.visible');
          cy.get('table, .card').should('exist');
          // cy.screenshot('admin_transport_vehicle_list');
        });

    it('crée un nouveau véhicule', () => {

      const uniqueName = `VH${Math.random() * 20}`;
      const updatedName = `${uniqueName}_Updated`;

      cy.intercept('GET', '/vehicle').as('reloadVehiclePage');

      cy.visit('/vehicle');
      cy.wait('@reloadVehiclePage');
      cy.get('input[name="vehicle_number"]').clear().type(uniqueName);
      cy.get('input[name="vehicle_model"]').clear().type(uniqueName);
      cy.get('input[name="year_made"]').clear().type('2026');
      cy.get('select[name="driver_id"]').parent().within(()=>{
        cy.get('div.primary_select').click();
        cy.get('div.primary_select.open > ul > li').eq(1).click();
      });

      cy.intercept('POST', '/vehicle').as('createVehicle');
      cy.get('button.submit').click();
      cy.wait('@createVehicle');cy.wait('@reloadVehiclePage');

      cy.get('body').should('be.visible');

      // Filter via DataTables search if present
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(uniqueName);
        }
      });

      cy.contains(uniqueName).should('exist');

      cy.get('button.dt-button.buttons-collection.buttons-colvis').click();
      cy.get('div.dt-button-collection button.buttons-columnVisibility').eq(1).click();
      cy.get('div.dt-button-collection button.buttons-columnVisibility').eq(2).click();
      cy.get('div.dt-button-collection button.buttons-columnVisibility').eq(3).click();
      cy.get('body').click();

      cy.wait(1000);

      // 3. Edit / Update
      cy.contains('tr', uniqueName).within(() => {
        //cy.get('td.sorting_1').click();
        //cy.wait(10000);
        cy.get('button[id="dropdownMenu2"]').should('be.visible').click();
        // cy.wait(10000);
        cy.get('a[href*="/vehicle/"]').click();
      });

      cy.url().should('include', '/vehicle/');
      cy.get('body').should('be.visible');
      cy.get('input[name="vehicle_number"]').clear().type(updatedName);
      cy.get('input[name="vehicle_model"]').clear().type(updatedName);

      //cy.intercept('POST', '/vehicle').as('updateVehicle');
      cy.get('button.submit').click();
      //cy.wait('@updateVehicle');

      cy.wait(10000);
      cy.url().should('include', '/vehicle');
      cy.get('body').should('be.visible');
      cy.wait(10000);

      // Filter via DataTables search
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });
      cy.wait(1000);
      cy.contains(updatedName).should('exist');

      cy.get('button.dt-button.buttons-collection.buttons-colvis').click();
      cy.get('div.dt-button-collection button.buttons-columnVisibility').eq(1).click();
      cy.get('div.dt-button-collection button.buttons-columnVisibility').eq(2).click();
      cy.get('div.dt-button-collection button.buttons-columnVisibility').eq(3).click();
      cy.get('body').click();

      cy.wait(1000);

      cy.contains('tr', uniqueName).within(() => {
        //cy.get('td.sorting_1').click();
        //cy.wait(10000);
        cy.get('button[id="dropdownMenu2"]').should('be.visible').click();
        // cy.wait(10000);
        cy.get('a[data-target*="deleteRoomTypeModal"]').click();
      });

      cy.contains('tr', updatedName)
          .find('a[data-target*="deleteRoomTypeModal"]')
          .invoke('attr', 'data-target')
          .then((targetValue) => {
            targetValue = targetValue.replace('#', '');
            // cy.log(targetValue);

            cy.get('#' + targetValue).should('be.visible').within(() => {
              cy.get('button[type="submit"]').click({ force: true });
            });
          });

      cy.wait(10000);
      cy.get('body').should('be.visible');
      cy.wait(1000);

      // Filter via DataTables search
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });
      cy.wait(1000);
      cy.contains(updatedName).should('not.exist');



      //cy.screenshot('admin_transport_vehicle_created');

    });
  });

  // ─────────────────── TRANSPORT ROUTE (FULL CRUD) ──────────────────────────
  describe('Transport Route - Full CRUD Lifecycle', () => {
    it('effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d\'une route de transport', () => {
      const name = `Route_${Date.now()}`;
      const updatedName = `${name}_Updated`;
      const fare = 10;
      const updatedfare = 15;

      // 1. Visit & Create
      cy.visit('/transport-route');
      cy.get('input[name="title"]').clear().type(name);
      cy.get('input[name="far"]').clear().type(fare);
      cy.get('form[action*="/transport-route"]').
      find('button[type="submit"]').first().click();
      cy.wait(10000);

      cy.get('body').should('be.visible');

      // Filter via DataTables search if present
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(name);
        }
      });

      cy.contains(name, { timeout: 10000 }).should('exist');
      // cy.screenshot('admin_transport_route_created');

      // 2. Edit / Update
      cy.contains('tr', name).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[href*="/transport-route/"]').click({ force: true });
      });

      cy.wait(10000);
      cy.get('body').should('be.visible');
      cy.get('input[name="title"]').clear().type(updatedName);
      cy.get('input[name="far"]').clear().type(updatedfare);
      cy.get('form[action*="/transport-route/"]').
      find('button[type="submit"]').first().click();

      cy.wait(10000);
      cy.get('body').should('be.visible');
      // Filter via DataTables search
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 10000 }).should('exist');
      // cy.screenshot('admin_transport_route_updated');

      // 3. Delete
      cy.contains('tr', updatedName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[data-target*="deleteRouteModal"]').click({ force: true });
      });

      cy.contains('tr', updatedName)
          .find('a[data-target*="deleteRouteModal"]')
          .invoke('attr', 'data-target')
          .then((targetValue) => {
            targetValue = targetValue.replace('#', '');
            cy.get('#' + targetValue).should('be.visible').within(() => {
              cy.get('button[type="submit"]').click({ force: true });
            });
          });

      cy.wait(10000);
      cy.get('body').should('be.visible');
      // Filter via DataTables search
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 10000 }).should('not.exist');

      // cy.screenshot('admin_transport_route_deleted');
    });
  });

  // ─────────────────── ASSIGN VEHICLE ───────────────────────────
  describe('Assign Vehicle', () => {
    it('affiche la page d\'affectation de véhicule', () => {
      cy.visit('/assign-vehicle');
      cy.url().should('include', '/assign-vehicle');
      cy.get('form, table, .card').should('exist');

      cy.get('form[action*="/assign-vehicle"]').first().within(()=>{
        cy.get('div.nice-select.primary_select').first().click();
        cy.get('div.primary_select.open ul.list li').eq(1).click();
        cy.get('input[type="radio"]').eq(1).next('label').click();
        cy.get('button.submit').click();
      });

      cy.wait(10000);
      cy.get('body').should('be.visible');
      cy.get('div.QA_table').find('table#table_id').first().find('tbody > tr').first()
          .within(()=>{
            cy.get('button[id="dropdownMenu2"]').should('be.visible').click();
            cy.get('a[href*="/assign-vehicle/"]').click();
          });

      cy.get('form[action*="/assign-vehicle/"]').first().within(()=>{
        cy.get('input[type="radio"]').eq(2).next('label').click();
        cy.get('button.submit').click();
      });

      cy.wait(10000);
      cy.get('body').should('be.visible');
      cy.get('div.QA_table').find('table#table_id').first().find('tbody > tr').first()
          .within(()=>{
            cy.get('button[id="dropdownMenu2"]').should('be.visible').click();
            cy.get('a[data-target*="#deleteAssignVehicle"]').click();
          });

      cy.get('body').should('be.visible');
      cy.get('div.QA_table').find('table#table_id').first().find('tbody > tr').first()
          .find('a[data-target*="deleteAssignVehicle"]')
          .invoke('attr', 'data-target')
          .then((targetValue) => {
            targetValue = targetValue.replace('#', '');
            // cy.log(targetValue);

            cy.get('#' + targetValue).should('be.visible').within(() => {
              cy.get('button[type="submit"]').click({ force: true });
            });
          });

      cy.wait(10000);
      cy.get('body').should('be.visible');
      cy.wait(1000);

      //cy.screenshot('admin_transport_assign_vehicle');
    });
  });

});
