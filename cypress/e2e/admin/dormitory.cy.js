// cypress/e2e/admin/dormitory.cy.js

describe('Admin – Dormitory', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // ─────────────────── DORMITORY LIST ───────────────────────────
  describe('Dormitory List', () => {
    it('affiche la liste des dortoirs', () => {
      const name = `Dorm_${Date.now()}`;
      const updatedName = `Dorm_${Date.now()}_updated`;
      const address = `address_${Date.now()}`;
      const updatedAddress = `address_${Date.now()}_updated`;

      cy.visit('/dormitory-list');
      cy.url().should('include', '/dormitory-list');
      cy.get('table, .card').should('exist');
      // cy.screenshot('admin_dormitory_list');

      // crée un nouveau dortoir
      cy.get('input[name="dormitory_name"]').first().clear().type(name);
      cy.get('input[name="address"]').first().clear().type(address);
      cy.get('input[name="intake"]').first().clear().type(name);
      cy.get('select[name="type"]').parent().within(()=>{
        cy.get('div.primary_select').click();
        cy.get('div.primary_select.open > ul > li').eq(1).click();
      });

      cy.get('form[action*="/dormitory-list"]').first().submit();
      cy.get('body').should('be.visible');

      cy.wait(10000);
      cy.url().should('include', '/dormitory-list');
      cy.get('body').should('be.visible');

      // Filter via DataTables search if present
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(name);
        }
      });

      cy.contains(name, { timeout: 10000 }).should('exist');

      //cy.screenshot('admin_dormitory_room_type_created');

      // 2. Edit / Update
      cy.contains('tr', name).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[href*="/dormitory-list/"]').click({ force: true });
      });

      cy.wait(10000);
      cy.url().should('include', '/dormitory-list/');
      cy.get('body').should('be.visible');

      cy.get('input[name="dormitory_name"]').first().clear().type(updatedName);
      cy.get('input[name="address"]').first().clear().type(updatedAddress);
      cy.get('form[action*="/dormitory-list/"]').first().submit();

      // Filter via DataTables search
      cy.wait(10000);
      cy.url().should('include', '/dormitory-list');
      cy.get('body').should('be.visible');

      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 10000 }).should('exist');

      //cy.screenshot('admin_dormitory_room_type_updated');

      // 3. Delete
      cy.contains('tr', updatedName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[data-target*="deleteDormitoryListModal"]').click({ force: true });
      });

      cy.contains('tr', updatedName)
          .find('a[data-target*="deleteDormitoryListModal"]')
          .invoke('attr', 'data-target')
          .then((targetValue) => {
            targetValue = targetValue.replace('#', '');
            cy.get('#' + targetValue).should('be.visible').within(() => {
              cy.get('button[type="submit"]').click({ force: true });
            });
          });

      cy.wait(10000);
      cy.get('body').should('be.visible');
      cy.wait(1000);

      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 10000 }).should('not.exist');

      // cy.screenshot('admin_dormitory_created');
    });
  });

  // ─────────────────── ROOM TYPE (FULL CRUD) ────────────────────────────────
  describe('Room Type - Full CRUD Lifecycle', () => {
    it('effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d\'un type de chambre', () => {
      const name = `RoomType_${Date.now()}`;
      const updatedName = `${name}_Updated`;

      // 1. Visit & Create
      cy.visit('/room-type');
      cy.get('input[name="room_type"], input[name="type"], input[name="name"]').first().clear().type(name);
      cy.get('form[action*="/room-type"]').first().submit();

      cy.wait(10000);
      cy.url().should('include', '/room-type');
      cy.get('body').should('be.visible');

      // Filter via DataTables search if present
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(name);
        }
      });

      cy.contains(name, { timeout: 10000 }).should('exist');

      //cy.screenshot('admin_dormitory_room_type_created');

      // 2. Edit / Update
      cy.contains('tr', name).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[href*="/room-type/"]').click({ force: true });
      });

      cy.wait(10000);
      cy.url().should('include', '/room-type/');
      cy.get('body').should('be.visible');

      cy.get('input[name="room_type"], input[name="type"], input[name="name"]').first().clear().type(updatedName);
      cy.get('form[action*="/room-type/"]').first().submit();

      // Filter via DataTables search
      cy.wait(10000);
      cy.url().should('include', '/room-type');
      cy.get('body').should('be.visible');

      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 10000 }).should('exist');

      //cy.screenshot('admin_dormitory_room_type_updated');

      // 3. Delete
      cy.contains('tr', updatedName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[data-target*="deleteRoomTypeModal"], a[href*="delete-room-type"]').click({ force: true });
      });

      cy.contains('tr', updatedName)
          .find('a[data-target*="deleteRoomTypeModal"]')
          .invoke('attr', 'data-target')
          .then((targetValue) => {
            targetValue = targetValue.replace('#', '');

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

      //cy.screenshot('admin_dormitory_room_type_deleted');

    });
  });

  // ─────────────────── ROOM LIST ────────────────────────────────
  describe('Room List', () => {
    it('affiche la liste des chambres', () => {
      const name= `ch_${Date.now()}`;
      const updatedName = `ch_${Date.now()}_updated`;
      const beds = 2;
      const price = 100;

      cy.visit('/room-list');
      cy.url().should('include', '/room-list');
      cy.get('table, .card').should('exist');

      // cy.screenshot('admin_dormitory_list');

      // crée un nouveau dortoir
      cy.get('select[name="dormitory"]').parent().within(()=>{
        cy.get('div.primary_select').click();
        cy.get('div.primary_select.open > ul > li').eq(1).click();
      });
      cy.get('select[name="room_type"]').parent().within(()=>{
        cy.get('div.primary_select').click();
        cy.get('div.primary_select.open > ul > li').eq(1).click();
      });

      cy.get('input[name="name"]').first().clear().type(name);
      cy.get('input[name="number_of_bed"]').first().clear().type(beds);
      cy.get('input[name="cost_per_bed"]').first().clear().type(price);

      cy.get('form[action*="/room-list"]').first().submit();
      cy.get('body').should('be.visible');

      cy.wait(10000);
      cy.url().should('include', '/room-list');
      cy.get('body').should('be.visible');

      // Filter via DataTables search if present
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(name);
        }
      });

      cy.contains(name, { timeout: 10000 }).should('exist');

      //cy.screenshot('admin_dormitory_room_type_created');

      // 2. Edit / Update
      cy.contains('tr', name).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[href*="/room-list/"]').click({ force: true });
      });

      cy.wait(10000);
      cy.url().should('include', '/room-list/');
      cy.get('body').should('be.visible');

      cy.get('input[name="name"]').first().clear().type(updatedName);
      cy.get('input[name="number_of_bed"]').first().clear().type(beds+1);
      cy.get('input[name="cost_per_bed"]').first().clear().type(price+50);
      cy.get('form[action*="/room-list/"]').first().submit();

      // Filter via DataTables search
      cy.wait(10000);
      cy.url().should('include', '/room-list');
      cy.get('body').should('be.visible');

      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 10000 }).should('exist');

      //cy.screenshot('admin_dormitory_room_type_updated');

      // 3. Delete
      cy.contains('tr', updatedName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[data-target*="deleteRoomTypeModal"]').click({ force: true });
      });

      cy.contains('tr', updatedName)
          .find('a[data-target*="deleteRoomTypeModal"]')
          .invoke('attr', 'data-target')
          .then((targetValue) => {
            targetValue = targetValue.replace('#', '');
            cy.get('#' + targetValue).should('be.visible').within(() => {
              cy.get('button[type="submit"]').click({ force: true });
            });
          });

      cy.wait(10000);
      cy.get('body').should('be.visible');
      cy.wait(10000);

      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 10000 }).should('not.exist');

      // cy.screenshot('admin_dormitory_created');
    });
  });

});
