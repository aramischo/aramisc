import { php } from './portefeuille/helpers';
// cypress/e2e/admin/library.cy.js

describe('Admin – Library', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  // ─────────────────── BOOK CATEGORY (FULL CRUD) ────────────────────

  describe('Book Category - Full CRUD Lifecycle', () => {
    it('effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d\'une catégorie de livre', () => {
      const name = `BookCat_${Date.now()}`;
      const updatedName = `${name}_Updated`;

      // 1. Visit & Create
      cy.visit('/book-category-list');
      cy.get('[name="category_name"]').first().clear().type(name);
      cy.get('form[action*="/book-category-list"]').first().submit();

      // Filter via DataTables search if present
      cy.wait(30000);
      cy.get('body').should('be.visible');
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(name);
        }
      });

      cy.contains(name, { timeout: 30000 }).should('exist');
      cy.screenshot('admin_bookcategoryfullcrudlifecycle');

      // 2. Edit / Update
      cy.contains('tr', name).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[href*="/book-category-list/"]').click({ force: true });
      });

      cy.wait(30000);

      cy.get('input[name="category_name"]').first().clear().type(updatedName);
      cy.get('form[action*="/book-category-list/"]').first().submit();

      // Filter via DataTables search
      cy.wait(30000);
      cy.get('body').should('be.visible');
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 30000 }).should('exist');
      cy.screenshot('admin_bookcategoryfullcrudlifecycle2');

      // 3. Delete
      cy.contains('tr', updatedName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[data-target*="deleteCategoryModal"]').click({ force: true });
      });

      cy.contains('tr', updatedName)
        .find('a[data-target*="deleteCategoryModal"]')
        .invoke('attr', 'data-target')
        .then((targetValue) => {
          targetValue = targetValue.replace('#', '');
          cy.log(targetValue);

          // Recherche globale sur la page (body), plus scopée au <tr>
          cy.get('#' + targetValue).should('be.visible').within(() => {
            cy.get('button[type="submit"]').first().click({ force: true });
          });
        });

      cy.wait(30000);
      cy.get('body').should('be.visible');
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 30000 }).should('not.exist');
      cy.screenshot('admin_bookcategoryfullcrudlifecycle3');
    });
  });

  // ─────────────────── LIBRARY SUBJECT (FULL CRUD) ──────────────────

  describe('Library Subject - Full CRUD Lifecycle', () => {
    it('effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d\'un sujet de bibliothèque', () => {
      const name = `Subject_${Date.now()}`;
      const code = `SUB_${Date.now()}`;
      const updatedName = `${name}_Updated`;
      const updatedCode = `${code}_U`;

      // 1. Visit & Create
      cy.visit('/library-subject');
      cy.get('input[name="subject_name"]').first().clear().type(name);

      cy.get('body').then(($body) => {
        if ($body.find('select[name="category"]').length > 0) {
          cy.get('select[name="category"]').then(($select) => {
            if ($select.find('option').length > 1) {
              cy.wrap($select).select(1, { force: true });
            }
          });
        }
      });

      cy.get('input[name="subject_code"]').first().clear().type(code);
      cy.get('form[action*="library-subject"]').first().submit();

      // Filter via DataTables search if present
      cy.wait(30000);
      cy.get('body').should('be.visible');
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(name);
        }
      });

      cy.contains(name, { timeout: 30000 }).should('exist');
      cy.screenshot('admin_librarysubjectfullcrudlifecycle');

      // 2. Edit / Update
      cy.contains('tr', name).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[href*="library-subject-edit"]').click({ force: true });
      });

      cy.wait(30000);

      cy.get('input[name="subject_name"]').first().clear().type(updatedName);
      cy.get('input[name="subject_code"]').first().clear().type(updatedCode);
      cy.get('form[action*="library-subject"]').first().submit();

      // Filter via DataTables search
      cy.wait(30000);
      cy.get('body').should('be.visible');
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 30000 }).should('exist');
      cy.screenshot('admin_librarysubjectfullcrudlifecycle2');

      // 3. Delete
      cy.contains('tr', updatedName).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[data-target*="deleteSubjectModal"]').click({ force: true });
      });

      cy.contains('tr', updatedName)
        .find('a[data-target*="deleteSubjectModal"]')
        .invoke('attr', 'data-target')
        .then((targetValue) => {
          targetValue = targetValue.replace('#', '');
          cy.log(targetValue);

          // Recherche globale sur la page (body), plus scopée au <tr>
          cy.get('#' + targetValue).should('be.visible').within(() => {
            cy.get('a[href*="library-subject-delete"], button[type="submit"]').first().click({ force: true });
          });
        });

      cy.wait(30000);
      cy.get('body').should('be.visible');
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedName);
        }
      });

      cy.contains(updatedName, { timeout: 30000 }).should('not.exist');
      cy.screenshot('admin_librarysubjectfullcrudlifecycle');
    });
  });

  // ─────────────────── BOOK LIST (FULL CRUD) ────────────────────
  describe('Book List - Full CRUD Lifecycle', () => {
    it('effectue le cycle de vie complet (Création, Lecture, Modification, Suppression) d\'un livre', () => {
      const catName = `Cat_${Date.now()}`;
      const subName = `Sub_${Date.now()}`;
      const subCode = `SC_${Date.now()}`;
      const title = `Book_${Date.now()}`;
      const bookNo = `BK_${Date.now()}`;
      const isbn = Date.now().toString().slice(-7);
      const updatedTitle = `${title}_Updated`;
      const updatedBookNo = `${bookNo}_U`;

      // 1. Visit & Create via /add-book
      cy.visit('/add-book');
      cy.get('input[name="book_title"]').first().clear().type(title);

      cy.get('body').then(($body) => {
        if ($body.find('select[name="book_category_id"]').length > 0) {
          cy.get('select[name="book_category_id"]').then(($select) => {
            if ($select.find('option').length > 1) {
              cy.wrap($select).select(1, { force: true });
            }
          });
        }
        if ($body.find('select[name="subject"]').length > 0) {
          cy.get('select[name="subject"]').then(($select) => {
            if ($select.find('option').length > 1) {
              cy.wrap($select).select(1, { force: true });
            }
          });
        }
      });

      cy.get('input[name="book_number"]').first().clear().type(bookNo);
      cy.get('input[name="isbn_no"]').first().clear().type(isbn);
      cy.get('input[name="quantity"]').first().clear().type('10');
      cy.get('input[name="book_price"]').first().clear().type('50');
      cy.get('form[action*="save-book-data"]').first().submit();

      // 2. Read / Verify in /book-list
      cy.wait(30000);
      cy.get('body').should('be.visible');
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(title);
        }
      });
      cy.contains(title, { timeout: 30000 }).should('exist');

      // 3. Edit / Update
      cy.contains('tr', title).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[href*="edit-book"]').click({ force: true });
      });

      cy.wait(30000);
      cy.get('input[name="book_title"]').first().clear().type(updatedTitle);
      cy.get('input[name="book_number"]').first().clear().type(updatedBookNo);
      cy.get('form[action*="update-book-data"]').first().submit();

      cy.wait(30000);
      cy.get('body').should('be.visible');
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedTitle);
        }
      });
      cy.contains(updatedTitle, { timeout: 30000 }).should('exist');

      // 4. Delete
      cy.contains('tr', updatedTitle).within(() => {
        cy.get('.dropdown-toggle, [id^="dropdownMenu"]').click({ force: true });
        cy.get('a[href*="delete-book-view"], a.deleteUrl').click({ force: true });
      });

      cy.wait(3000);
      cy.get('body').then(($body) => {
        if ($body.find('#showDetaildModal').length > 0) {
          cy.get('#showDetaildModal').should('be.visible').within(() => {
            cy.get('a[href*="delete-book"]').first().click({ force: true });
          });
        }
      });

      cy.wait(30000);
      cy.get('body').should('be.visible');
      cy.get('body').then(($body) => {
        if ($body.find('input[type="search"]').length > 0) {
          cy.get('input[type="search"]').first().clear().type(updatedTitle);
        }
      });
      cy.contains(updatedTitle, { timeout: 30000 }).should('not.exist');
    });
  });

  // ─────────── MEMBRE → EMPRUNT → RETOUR → ANNULATION D'ADHÉSION (sécurisé) ───────────
  // ⚠ Effets de bord de l'appli :
  //   - notifications Add_Library_Member et Issue/Return_Book (Email + SMS + App réels) → coupées pendant le test ;
  //   - saveIssueBookData() / returnBook() appellent en plus send_sms() directement (passerelle SMS active)
  //     → passerelles SMS désactivées pendant le test ;
  //   - un emprunt décrémente aramisc_books.quantity, le retour la ré-incrémente.
  // Sécurisation : before() sauvegarde intégralement aramisc_library_members, aramisc_book_issues et aramisc_books
  // dans un fichier du dossier temporaire système (hors projet) ; after() les restaure à l'identique, supprime les
  // notifications créées, rétablit réglages de notification et passerelles SMS, et efface le fichier.
  // Données utilisées : un élève de Class2 / section 2 qui n'est pas encore membre, un livre disponible.
  describe('Membre, emprunt et retour de livre - sécurisé', () => {
    const TABLES = "['aramisc_library_members','aramisc_book_issues','aramisc_books']";
    const EVENTS = "['Add_Library_Member','Issue/Return_Book']";
    const SNAP = "sys_get_temp_dir().'/cy_library_snapshot.json'";
    const CLASS_ID = '2';
    const SECTION_ID = '2';
    const memberUdId = `MEM_${Date.now()}`;
    let ctx; // { studentUserId, bookId, quantity, dueDate }

    before(() => {
      php(`
        $snap = ['tables' => [], 'maxNotif' => (int) DB::table('aramisc_notifications')->max('id'),
                 'settings' => DB::table('aramisc_notification_settings')->whereIn('event', ${EVENTS})->pluck('destination', 'id'),
                 'gateways' => DB::table('aramisc_sms_gateways')->pluck('active_status', 'id')];
        foreach (${TABLES} as $t) { $snap['tables'][$t] = DB::table($t)->get()->map(fn($r) => (array) $r)->all(); }
        if (!file_exists(${SNAP})) { file_put_contents(${SNAP}, json_encode($snap)); }
        foreach ($snap['settings'] as $id => $dest) {
          $x = json_decode($dest, true); $x['Email'] = 0; $x['SMS'] = 0; $x['App'] = 0;
          DB::table('aramisc_notification_settings')->where('id', $id)->update(['destination' => json_encode($x)]);
        }
        DB::table('aramisc_sms_gateways')->update(['active_status' => 0]);
        $members = DB::table('aramisc_library_members')->pluck('student_staff_id');
        $student = DB::table('student_records as r')->join('aramisc_students as s', 's.id', '=', 'r.student_id')
          ->where('r.class_id', ${CLASS_ID})->where('r.section_id', ${SECTION_ID})->where('s.active_status', 1)
          ->whereNotIn('s.user_id', $members)->orderBy('s.id')->value('s.user_id');
        $book = DB::table('aramisc_books')->where('active_status', 1)->where('quantity', '>', 0)->orderBy('id')->first(['id', 'quantity']);
        echo 'CY<<'.base64_encode(json_encode([
          'studentUserId' => $student, 'bookId' => $book->id ?? null, 'quantity' => $book->quantity ?? null,
          'dueDate' => dateConvert(date('Y-m-d', strtotime('+7 days'))),
          'smsActive' => DB::table('aramisc_sms_gateways')->where('active_status', 1)->count(),
        ])).'>>CY';
      `).then((r) => {
        expect(r.smsActive, 'passerelles SMS coupées').to.eq(0);
        expect(r.studentUserId, 'élève de Class2/sec2 non membre').to.not.be.null;
        expect(r.bookId, 'livre disponible').to.not.be.null;
        ctx = { ...r, studentUserId: String(r.studentUserId), bookId: String(r.bookId), quantity: Number(r.quantity) };
      });
    });

    after(() => {
      php(`
        if (file_exists(${SNAP})) {
          $snap = json_decode(file_get_contents(${SNAP}), true);
          Schema::disableForeignKeyConstraints();
          foreach ($snap['tables'] as $t => $rows) {
            DB::table($t)->delete();
            foreach (array_chunk($rows, 200) as $chunk) { DB::table($t)->insert($chunk); }
          }
          Schema::enableForeignKeyConstraints();
          DB::table('aramisc_notifications')->where('id', '>', $snap['maxNotif'])->delete();
          foreach ($snap['settings'] as $id => $dest) { DB::table('aramisc_notification_settings')->where('id', $id)->update(['destination' => $dest]); }
          foreach ($snap['gateways'] as $id => $st) { DB::table('aramisc_sms_gateways')->where('id', $id)->update(['active_status' => $st]); }
          $ok = true;
          foreach ($snap['tables'] as $t => $rows) { $ok = $ok && DB::table($t)->count() === count($rows); }
          unlink(${SNAP});
          echo 'CY<<'.base64_encode(json_encode($ok)).'>>CY';
        }
      `).then((ok) => expect(ok, 'tables restaurées').to.eq(true));
    });

    const state = () =>
      php(`echo 'CY<<'.base64_encode(json_encode([
        'member' => DB::table('aramisc_library_members')->where('member_ud_id', '${memberUdId}')->first(['id', 'student_staff_id', 'member_type', 'active_status']),
        'issues' => DB::table('aramisc_book_issues')->where('member_id', ${ctx.studentUserId})->where('book_id', ${ctx.bookId})->get(['id', 'issue_status']),
        'quantity' => DB::table('aramisc_books')->where('id', ${ctx.bookId})->value('quantity'),
      ])).'>>CY';`);

    it("ajoute un élève comme membre, lui prête un livre, enregistre le retour puis annule l'adhésion", () => {
      // 1. CREATE : nouveau membre (type Élève → classe → section → élève, chargés en AJAX)
      cy.visit('/library-member');
      cy.get('form[action$="/library-member"]').within(() => {
        cy.get('#member_type').select('2', { force: true });
        cy.get('#class_library_member').select(CLASS_ID, { force: true });
        cy.get(`#select_section_member option[value="${SECTION_ID}"]`, { timeout: 20000 }).should('exist');
        cy.get('#select_section_member').select(SECTION_ID, { force: true });
        cy.get(`#select_student option[value="${ctx.studentUserId}"]`, { timeout: 20000 }).should('exist');
        cy.get('#select_student').select(ctx.studentUserId, { force: true });
        cy.get('input[name="member_ud_id"]').clear().type(memberUdId);
      });
      // cy.screenshot('admin/library/library_member/01-create-formulaire');
      cy.get('form[action$="/library-member"] button.submit').click();
      // cy.screenshot('admin/library/library_member/02-create-apres-submit');
      cy.get('#table_id', { timeout: 20000 }).contains('tr', memberUdId).should('exist');
      state().then((s) => {
        expect(s.member, 'membre créé').to.not.be.null;
        expect(String(s.member.student_staff_id)).to.eq(ctx.studentUserId);
        expect(Number(s.member.active_status)).to.eq(1);
      });

      // 2. EMPRUNT : Liste des membres > Emprunter / rendre un livre
      cy.visit('/member-list');
      cy.get(`a[href$="/issue-books/2/${ctx.studentUserId}"]`, { timeout: 20000 }).first().click({ force: true });
      cy.url().should('include', `/issue-books/2/${ctx.studentUserId}`);
      cy.get('form[action$="/save-issue-book-data"]').within(() => {
        cy.get('select[name="book_id"]').select(ctx.bookId, { force: true });
        cy.get('input[name="due_date"]').invoke('val', ctx.dueDate).should('have.value', ctx.dueDate);
      });
      // cy.screenshot('admin/library/issue_book/01-issue-formulaire');
      cy.get('form[action$="/save-issue-book-data"] button.primary-btn').click();
      // cy.screenshot('admin/library/issue_book/02-issue-apres-submit');
      state().then((s) => {
        expect(s.issues, 'emprunt enregistré').to.have.length(1);
        expect(s.issues[0].issue_status).to.eq('I');
        expect(Number(s.quantity), 'quantité décrémentée').to.eq(ctx.quantity - 1);

        // 3. RETOUR : bouton « Retour » → modale → confirmation
        cy.visit(`/issue-books/2/${ctx.studentUserId}`);
        cy.get(`a.modalLink[href$="/return-book-view/${s.issues[0].id}"]`, { timeout: 20000 }).click({ force: true });
        cy.get('#showDetaildModal', { timeout: 20000 }).should('be.visible')
          .find(`a[href$="/return-book/${s.issues[0].id}"]`).click({ force: true });
        // cy.screenshot('admin/library/issue_book/03-retour-apres-confirmation');
      });
      state().then((s) => {
        expect(s.issues[0].issue_status, 'livre rendu').to.eq('R');
        expect(Number(s.quantity), 'quantité rétablie').to.eq(ctx.quantity);
      });

      // 4. « DELETE » : annulation de l'adhésion (active_status = 0)
      cy.visit('/library-member');
      state().then((s) => {
        cy.get('#table_id', { timeout: 20000 }).contains('tr', memberUdId)
          .find(`a[href$="/cancel-membership/${s.member.id}"]`).click({ force: true });
      });
      // cy.screenshot('admin/library/library_member/03-annulation-apres-confirmation');
      state().then((s) => expect(Number(s.member.active_status), 'adhésion annulée').to.eq(0));
    });
  });

});