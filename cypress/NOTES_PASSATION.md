# Passation — Tests Cypress CRUD du menu Aramisc

Document de reprise pour Claude Code (rédigé le 30/09/2026 à la fin d'une session Cowork).

## Contexte

- Projet : Aramisc, ERP de gestion d'école — Laravel 11 + MySQL, sous WAMP64 (Windows).
- URL de l'appli : `http://dev.aramisc.com` (baseUrl Cypress). Aussi accessible via `http://localhost/aramisc`.
- Base de données : **base de test** (copie de la prod). `scripts/run-cypress.js` bascule `.env` → `.env.cypress` pendant les tests puis restaure.
- Connexion admin : `cy.loginAsAdmin()` (identifiants dans `cypress/fixtures/users.json`).
- Liste des liens à tester et état d'avancement : `analyse_menu_cypress_crud.md` (racine du projet).

## Lancer les tests

Toujours depuis la **racine** du projet (`C:\wamp64\www\aramisc`), pas depuis `scripts/` :

```
node scripts/run-cypress.js run --browser chrome --headed --spec "cypress/e2e/admin/<dossier>/<fichier>.cy.js"
node scripts/run-cypress.js open    # mode interactif
```

## Règles à respecter (demandées par l'utilisateur)

1. **Ne jamais créer de fichier dans le projet sans demander la permission.**
2. **Pas de vidéos** : `video: false` dans `cypress.config.js` (déjà fait).
3. Captures `// cy.screenshot(...)` **en commentaire** (règle revue le 01/10/2026) :
   - **une seule capture par formulaire**, tous champs remplis, juste **avant** le submit (`NN-<étape>-formulaire`),
     puis une capture **après** chaque submit / update / delete (`NN-<étape>-apres-submit`, `NN-delete-apres-confirmation`) ;
   - chemin = même arborescence que les specs : `const shot = '<rôle>/<rubrique>/<page>'`
     (ex. `admin/examen_en_ligne/banque_de_questions`) → fichier `cypress/screenshots/admin/examen_en_ligne/banque_de_questions/01-create-formulaire.png` ;
   - le hook `after:screenshot` de `cypress.config.js` range toute capture dont le nom commence par `admin/`, `student/`,
     `teacher/` ou `parent/` exactement à ce chemin (sinon Cypress la mettrait sous un dossier dépendant du lot de specs lancé) ;
   - les helpers partagés reçoivent `shot` en paramètre (ex. `studentDeposit(amount, note, shot)`).
   - Les anciens specs à la racine de `cypress/e2e/admin/` (`academics.cy.js`…) gardent leurs noms plats, non concernés.
4. **Supprimer toutes les données créées** par le test (base de test) — chaque spec nettoie à la fin.
5. **Un dossier par rubrique parente** du menu, sous `cypress/e2e/admin/` (ex. `centre_telechargement/`).
6. Méthode par lien : faire le scénario comme un utilisateur dans le navigateur → écrire le spec → le lancer dans Chrome → s'il échoue, revérifier le scénario et corriger.
7. Quand un test passe : le passer **en vert** dans `analyse_menu_cypress_crud.md` (section 🟢) et le retirer de la section 🔴.

## Conventions techniques observées dans l'appli

- Les `<select>` sont habillés par **nice-select** : piloter le `<select>` natif avec `.select(valeur, { force: true })`.
- Beaucoup de listes sont des **DataTables responsive** : à 1280 px la colonne Action est repliée → cliquer `.dropdown-toggle` et les `a.dropdown-item` avec `{ force: true }`.
- Les modales de suppression contiennent `<a href=".../delete/..."><button>` : cibler **uniquement** le bouton (`a[href*="...-delete"] button`), sinon « subject contained 2 elements ».
- Les modales chargées en AJAX (`a.modalLink`) s'ouvrent dans `#showDetaildModal`.
- Les chargements en cascade (classe → section → sujet) sont en AJAX : attendre que les `<option>` apparaissent avant de sélectionner.
- Exceptions JS de l'appli : ignorées par `cypress/support/e2e.js` et journalisées dans `cypress/logs/cypress_errors<date>.log`.

## État d'avancement

### ✅ Validés dans Cypress (verts dans le .md) — 13 specs, tous passent (30/09/2026)
`cypress/e2e/admin/centre_telechargement/`
- `type_de_contenu.cy.js`, `liste_de_contenu.cy.js`, `liste_de_contenu_partage.cy.js`, `liste_de_videos.cy.js`

`cypress/e2e/admin/plan_de_cours/` (+ `helpers.js`)
- `lecon.cy.js`, `sujet.cy.js`, `apercu_du_sujet.cy.js`, `plan_de_cours.cy.js`, `presentation_du_plan_de_lecon.cy.js`
- Corrigé : `helpers.js > readFirstSlot` mélangeait commandes cy et valeur synchrone (« mixing up async and sync code »).

`cypress/e2e/admin/imprime_en_vrac/` — pages « critères → aperçu d'impression », pas de CRUD
- `carte_identite_vrac.cy.js` : crée un modèle de carte (logo + signature en PNG généré), aperçu, puis supprime le modèle
- `certificat_vrac.cy.js`, `paie_vrac.cy.js` (paie de mai 2025), `frais_facturation_vrac.cy.js` (facture de Class1) :
  lecture seule, s'appuient sur des données de la base de test (prérequis notés en tête de chaque spec)
- Les aperçus appellent `window.print()` au chargement : neutralisé via `cy.on('window:before:load')`.
- Libellés des boutons : « Recherche » (pas « Rechercher ») ; « Imprimer » pour les factures.
  Cibler le formulaire par son `action` (la page contient plusieurs `<form>`).

`cypress/e2e/admin/portefeuille/` (+ `helpers.js`) — validés le 30/09/2026
- `depot_approuve.cy.js`, `depot_rejete.cy.js`, `demande_de_remboursement.cy.js` (élève student1 + admin)
- ⚠ Chaque action wallet déclenche Email + SMS + push App RÉELS (base de test : SMTP Gmail et TextLocal actifs).
  `setupWallet()` coupe Email/SMS/App des 4 événements wallet via `tinker` (tâche `artisan`, code PHP passé en base64),
  `teardownWallet()` (dans `after()`) rétablit les réglages, supprime transactions + notifications créées et remet le solde.
  Si Cypress plante avant le `after()`, les notifications wallet restent coupées : remettre Email/SMS/App à 1
  dans `aramisc_notification_settings` (événements Wallet_Add, Approve_Deposit, Reject_Deposit, Refund_Deposit).
- Instabilité vue le 01/10/2026 (lot de 5 specs) : sur `/wallet/wallet-transaction`, DataTables ne s'est pas chargé
  (`Cannot read properties of undefined (reading 'ext' / 'pipeline')`) → tableau vide, `depot_approuve` en échec ;
  relancé seul, il passe. Problème de chargement JS de la page, pas du test.
- Rejet de remboursement NON testé : `walletRejectRefund()` appelle `send_mail()` directement (vrai e-mail).
- Ne pas toucher au dépôt existant (500, Bank, student1, mai 2025) : les lignes du test sont repérées par `data-note`.

`cypress/e2e/admin/chat/` — validés le 30/09/2026 (utilisent `php()` de `portefeuille/helpers.js`)
- `boite_de_discussion.cy.js` (admin → student1 → suppression), `invitation.cy.js` (admin ↔ teacher1 via « Démarrer »),
  `utilisateur_bloque.cy.js` (bloquer / débloquer student1)
- Envoyer un message notifie via le canal `fcm` : le spec vide les `device_token` (admin, student1) et les restaure dans `after()`.
- Réglages du chat = FICHIER `storage/app/chat/school_settings.json` (partagé avec le dev, ne pas modifier) :
  `chatting_method = log`, `chat_invitation_requirement = none` → pas de parcours « demande → accepter / rejeter ».
- Précondition de `invitation.cy.js` : aucune invitation admin ↔ teacher1 (sinon le spec s'arrête dès le `before()`).

`cypress/e2e/admin/examen_en_ligne/` — validés le 01/10/2026
- `groupe_de_questions.cy.js` (CRUD du titre), `banque_de_questions.cy.js` (3 tests : Vrai/Faux, Texte à trou, Choix multiple)
- La base de test n'a aucun groupe de questions : la banque crée le sien en `before()` (tinker) et le supprime en `after()`.
- Classe/section utilisées : Class1 (id 1) / sec1 (id 1). Les sections arrivent en AJAX dans `#selectMultiSections` (multi-select natif).
- Le QCM vérifie en base (`aramisc_question_bank_mu_options.status`) que la bonne réponse est bien celle cochée.
- Le navigateur Claude in Chrome n'a pas pu ouvrir `dev.aramisc.com` (retour à « Nouvel onglet ») :
  scénarios déduits du code puis validés directement dans Cypress/Chrome.

`cypress/e2e/admin/plan_examen/` — validés le 01/10/2026 (pages « critères → génération → aperçu »)
- `carte_admission.cy.js`, `plan_de_siege.cy.js` : Class2 / section 2 (5 élèves), génération pour les 2 premiers.
- La base de test n'a AUCUN type d'examen ni planning : chaque spec crée un examen temporaire (`aramisc_exam_types`)
  en `before()` et supprime en `after()` l'examen, les lignes `admit_cards` / `seat_plans` et les notifications Web créées.
- Coupe Email/SMS/App de `Exam_Admit_Card` / `Exam_Seat_Plan` pendant le test, réglage rétabli dans `after()`
  (si plantage avant : remettre `{"Email":1,"SMS":1,"Web":1,"App":1}` dans `aramisc_notification_settings`).
- Formulaire « Générer » en `target=_blank` : l'attribut est retiré pour rester dans l'onglet Cypress.

`cypress/e2e/admin/champs_personnalises/` (+ `helpers.js`) — validés le 01/10/2026
- `inscription_etudiants.cy.js`, `inscription_personnel.cy.js` : même parcours via `customFieldCrud()` (même formulaire
  `_custom_form.blade.php`, même contrôleur `AramiscCustomFieldController`) ; aucune notification.
- Bouton « Ajouter » des valeurs : avec `cy.click()` après une saisie, la 2e ligne reste en `d-none` (artefact Cypress,
  deux clics natifs l'affichent bien) → clic natif `$btn[0].click()`.
- Modale de suppression placée dans le `<tbody>` : le navigateur vide le `<form>`, le bouton « Supprimer » est hors du form
  mais y reste rattaché (`button.form`) → cibler `.modal.show button[type=submit]`.

Notifications à couper pendant les tests (e-mails / push réels sinon) :
- Wallet_Add, Approve_Deposit, Reject_Deposit, Refund_Deposit → `portefeuille/helpers.js`
- Exam_Admit_Card, Exam_Seat_Plan → dans `plan_examen/carte_admission.cy.js` / `plan_de_siege.cy.js`
- Lesson_Plan → `plan_de_cours/helpers.js` (`muteLessonPlanNotifications` / `restoreLessonPlanNotifications`),
  branché dans `plan_de_cours.cy.js` et `presentation_du_plan_de_lecon.cy.js`
- À vérifier pour chaque nouvelle rubrique : `grep sent_notifications` / `->notify(` dans le contrôleur concerné.

Bonne pratique ajoutée : un `after()` « filet de sécurité » qui supprime les données créées seulement si elles existent encore
(ex. `liste_de_contenu_partage.cy.js`, `carte_identite_vrac.cy.js`), pour nettoyer même si le test échoue.

Astuce : pour une vérification ponctuelle sans créer de fichier dans le projet, lancer un spec placé hors du projet :
`node scripts/run-cypress.js run --browser chrome --config "specPattern=<dossier>/*.cy.js" --spec "<dossier>/x.cy.js"`.

### 🧹 Doublons — ✅ supprimés le 06/10/2026 (accord de l'utilisateur)
- `admin/download_center_content_type.cy.js`, `admin/download_center_content_list.cy.js`, `scripts/cypress-agent.js`, `cypress/agent/`.
- `skills-lock.json` conservé (skills Cypress installées).

### 🧹 Anciens blocs remplacés — ✅ retirés le 06/10/2026 (accord de l'utilisateur)
- Supprimés : `admin/admission.cy.js`, `admin/hr.cy.js` (tous leurs tests sont couverts par `administration/` et `rh/`).
- Blocs retirés : `communicate.cy.js` (Visitor Log, Complaint, Postal Receive / Dispatch, Phone Call Log, Admission Query),
  `students.cy.js` (Student Admission / Category / Group), `teachers.cy.js` (Staff Directory), `settings.cy.js` (Setup Admin),
  `accounts.cy.js` (Bank Account, Add Income / Expense, Fund Transfer), `fees.cy.js` (Fees Discount, Fees Master).
- Règle pour la suite : à chaque rubrique terminée, retirer les blocs plats remplacés (accord donné pour cette passe).
- Aussi retirés : `accounts.cy.js` › Chart of Account, `fees.cy.js` › Fees Group / Fees Type (ils soumettaient `form:first`, le
  formulaire de recherche de l'en-tête → toujours en échec) → remplacés par `comptes/plan_comptable`, `frais/groupe_frais`, `frais/type_frais`.
- Anciens tests à URL inexistantes (404) **corrigés le 06/10/2026** (choix de l'utilisateur) : `communicate.cy.js` (`/events` → `/event`,
  `/send-sms` → `/send-email-sms-view`, `/send-email` → `/email-sms-log`), `teachers.cy.js` réécrit (pas de page enseignants :
  `/staff-directory` + `/add-staff` ; le bloc évaluation faisait doublon avec `teacher_evaluation.cy.js`), `accounts.cy.js` › Transaction
  (tableau affiché seulement après recherche → contrôle du formulaire `transaction-search`).

### 🔁 Passe « CRUD complet » sur les anciens specs admin plats — lancée le 06/10/2026
Demande de l'utilisateur : liste / ajout / modification / suppression pour toutes les pages admin qui le permettent
(hors examen en ligne et Frontend CMS ; les rapports restent en visite / recherche).
Structure validée : nouveaux specs **par rubrique** (`admin/<rubrique>/<page>.cy.js`) ; les anciens blocs plats correspondants
seront retirés **après accord** de l'utilisateur. Ordre : Administration, puis Info-étudiant, RH, Comptes, Frais, Inventaire,
Examen, Devoirs, Communiquer, Paramètres, Dortoir / Universitaires (chambre, salle de classe, matière optionnelle).
- ✅ `administration/` (+ `helpers.js` : `dtSearch`, `findRow`, `rowAction`, `shouldBeGone`, ré-export de `php`) — 7 specs verts :
  `requete_admission`, `visiteurs`, `plaintes`, `courrier_recu`, `courrier_envoye`, `journal_appels`, `configuration_admin`.
  Aucune notification dans ces contrôleurs. Listes DataTables serveur → filtrer par la recherche rapide avant de chercher la ligne.
  Base de test sans « type de plainte » : `plaintes.cy.js` en crée un (`aramisc_setup_admins` type 2) en `before()`.
  Dates de test volontairement > 12 (15/09/2026) pour couvrir l'anomalie 27.
  Remplacent : `admission.cy.js` (entier) et, dans `communicate.cy.js`, les blocs Visitor Log, Complaint, Postal Receive,
  Postal Dispatch, Phone Call Log, Admission Query (+ Setup Admin dans `settings.cy.js`) → à retirer avec accord.
  `dtSearch()` relit le champ de recherche entre clear et type (la liste des élèves se redessine et remplace le champ).
- ✅ `info_etudiant/` — 3 specs verts (réutilisent `administration/helpers.js`) :
  - `categorie_etudiant`, `groupe_etudiant` : CRUD simple, listes DataTables côté client.
  - `eleve` : ajout (Class2 / sec2, onglet « Parents et tuteurs » à ouvrir) → liste → modification → désactivation
    (`student-delete` = active_status 0) → suppression définitive depuis `/disabled-student`. Durée ≈ 6 min.
    ⚠ Ajout / désactivation : notifications `Student_Admission`, `Enable/Disable_Student` + `send_mail()` / `send_sms()` directs.
    before() coupe Email/SMS/App de ces événements, **désactive `aramisc_email_settings` et `aramisc_sms_gateways`** ;
    after() rétablit (contrôle d'égalité) et supprime si besoin élève, tuteur, comptes, congés (`aramisc_leave_defines`),
    lignes des tables `student_id` (`tableList::ONLY_TABLE_LIST`). Si plantage avant after() : réactiver le SMTP (id 1),
    TextLocal et Email/SMS/App de ces 2 événements à la main.
  Remplacent dans `students.cy.js` : Student Admission, Student Category, Student Group → à retirer avec accord.
- ✅ `rh/` (+ `helpers.js` : `singleFieldCrud()` pour les pages « un champ + liste ») — 6 specs verts :
  - `departement`, `poste`, `type_de_conge` : via `singleFieldCrud()` (modale DELETE dans le <tbody> → `.modal.show button[type=submit]`).
  - `definition_conge` : type de congé créé en before() (tinker), défini pour teacher1 (rôle 4, `staffNameByRole` en AJAX),
    modification, « Ajouter des jours » (modale `#addLeaveDayModal`, 2e champ `days` → cibler `:not(#showDays)`), suppression.
  - `demande_conge` : type + définition pour l'admin créés en before() ; demande → modification → approbation (modale
    `view-leave-details-approve`) → suppression depuis `/approve-leave`. Recherche rapide inopérante sur ces pages (anomalie 30).
  - `personnel` : rôle Comptable (6), département 1, poste 1 ; formulaire de modification à valeurs entourées d'espaces (trim).
  Envois réels coupés par **`muteMessaging(events, userEmails)` / `restoreMessaging(backup)`** (ajoutés à `administration/helpers.js`) :
  Email/SMS/App des événements, `aramisc_email_settings`, `aramisc_sms_gateways`, `device_token` des utilisateurs donnés.
  → à utiliser pour toutes les rubriques suivantes (eleve.cy.js a sa propre version, équivalente).
  Remplacent dans `hr.cy.js` : Department, Designation, Leave Type, Leave Define, Apply / Approve / Pending Leave
  et dans `teachers.cy.js` : Staff Directory → à retirer avec accord.
- ✅ `comptes/` (+ `helpers.js` : comptes bancaires de test `createBank` / `deleteBanks` / `bankState`) et `frais/` :
  - verts : `compte_bancaire` (création → transactions → suppression ; pas de modification dans l'interface, anomalie 33),
    `transfert_fonds` (Bank A → Bank B, soldes contrôlés ; ni modification ni suppression dans l'interface), `reduction`
    (pas de type Une fois / Annuel en mode frais directs).
  - `revenu`, `depense`, `frais_principaux` : d'abord ROUGES à cause de l'application (anomalies 35 à 38), **verts après correctifs du 06/10/2026**
    (accord de l'utilisateur). `frais_principaux` supprime en 1920 px (anomalie 40).
  Remplacent dans `accounts.cy.js` : Bank Account, Add Income, Add Expense, Fund Transfer ; dans `fees.cy.js` : Fees Discount, Fees Master.
- ✅ `inventaire/` — 6 specs verts (07/10/2026) : `magasin`, `fournisseur`, `article` (CRUD simples, suppression par fenêtre AJAX
  `.deleteUrl` → `#showDetaildModal`), `reception` et `vente` (stock / dépense / revenu contrôlés en base ; opérations non payées pour
  pouvoir les supprimer), `emission` (émission → retour). Données de test (catégorie, article, fournisseur, magasin) créées en before().
  `toastSuccess()` et `numberShouldBe()` ajoutés à `administration/helpers.js` (attente du toast avant contrôle en base ; champs
  calculés en AJAX). Remplacent dans `inventory.cy.js` : Item List, Item Store, Item Receive, Item Sell & Issue, Suppliers (retirés).
- ✅ `inventaire/categorie_article` (remplace `inventory.cy.js`, supprimé) ; `examen/type_examen`, `examen/bareme_notes` verts
  (blocs Exam Type / Marks Grade retirés de `examination.cy.js`). Barème : GPA unique par année (test : 9.75) ; création refusée
  par l'application si des résultats existent pour l'année (contrôle en before()).
- ✅ `examen/configuration_examen` (mode multiple ; type d'examen de test créé en before()) et `examen/planning_examen`
  (type + examen + répartition Class1 / sec1 / Math créés en before() ; la saisie se fait sur `/exam-schedule-create` →
  `exam_schedule_new_update`, lecture sur `/exam-schedule`). Blocs Exam Setup / Exam Schedule retirés de `examination.cy.js`
  (le bloc Online Exam est laissé tel quel : examen en ligne hors périmètre).
- ✅ `devoirs/devoir` (Class1 / sec1 / Math, note unique 73 pour filtrer la liste ; Assign_homework coupé) et `communiquer/`
  `avis` (Notice coupé ; liste en accordéon), `evenement` (Event coupé ; destinataires Teacher + Admin à cause de l'anomalie 51 ;
  liste `#eventDataTable`), `jour_ferie` — verts le 07/10/2026. Retirés : `communicate.cy.js` › Events / Holidays,
  `homework.cy.js` › liste et formulaire d'ajout.
- ✅ `administration/carte_identite` (modèle de carte, PNG générés ; même formulaire `.savaIdCard` en création / modification),
  `administration/certificat` (A4 portrait remplit et verrouille hauteur / largeur ; cette page n'affiche pas de toast → attente de
  la liste rechargée), `parametres/devise` (jamais activée), `universitaires/matiere_optionnelle` (`/optional-subject-setup`,
  une configuration par classe, Class2 vide au départ) — verts le 07/10/2026. `AramiscStudentCertificateRequest` (`date`) ajouté à
  l'anomalie 27 (constaté, corrigé). Bloc Manage Currency retiré de `settings.cy.js`.
- Vérifiés sans modification : `dormitory.cy.js` (chambres : CRUD complet malgré le titre « affiche la liste ») et `academics.cy.js`
  (salles de classe : idem) — verts le 07/10/2026.
- ✅ **Toutes les rubriques du plan sont couvertes** (07/10/2026). Relance complète des 11 nouveaux dossiers : **44/44 verts**
  en 3 groupes (administration + info_etudiant + rh : 18/18 ; comptes + frais + inventaire : 16/16 ; examen + devoirs + communiquer
  + parametres + universitaires : 10/10).
- ⚠ Un lot unique de ~45 specs s'est figé vers 00 h 37 (Cypress / Chrome inactifs, aucun échec signalé, cause non identifiée) :
  lancer plutôt par groupes de 10 à 18 specs. Après un arrêt forcé, `run-cypress.js` ne restaure pas `.env` : recopier
  `.env.backup` vers `.env`, supprimer `.env.backup`, puis `php artisan config:clear`.
- Ordre restant (ancien libellé, terminé) : Devoirs, Communiquer (événement, jour férié, actualité, avis),
  Paramètres (devise, modèles carte d'identité / certificat), Dortoir / Universitaires (chambre, salle de classe, matière optionnelle).
- Instabilité vue le 06/10/2026 : une page en 500 « SQLSTATE[HY000] [1045] Accès refusé pour l'utilisateur 'forge' » (variables `.env`
  non lues sur une requête, 16 occurrences dans `laravel.log` depuis le 30/09). Relancé : OK. Problème d'environnement WAMP, pas du test.
  Revu en lot RH (`definition_conge`, page suivant une suppression). Hypothèse : PHP thread-safe sous Apache Windows, lecture de `.env`
  non sûre entre requêtes simultanées (page + AJAX DataTables). Piste : `php artisan config:cache` après la bascule de `.env`
  dans `scripts/run-cypress.js` → **appliqué le 06/10/2026** (accord de l'utilisateur) : `config:cache` après la bascule, `config:clear` à la restauration.

### 🧪 Anciens specs (hors campagne) — revue lancée le 01/10/2026
28 specs non écrits pendant la campagne (`cypress/e2e/*.cy.js`, `admin/*.cy.js` à la racine, `student/`, `teacher/`, `parent/`).
- Données laissées en base de test par leurs anciens passages (VH…, Route_…, RoomType_…, Dorm_…, Book_…, BookCat_…,
  Classroom_…, demandes d'admission p_…) : **à conserver** (décision de l'utilisateur, ce ne sont que des données de test).
- ✅ Specs dangereux **sécurisés le 01/10/2026** (blocs réécrits, les autres tests de ces fichiers sont inchangés) :
  - `settings.cy.js` › année académique : année libre 2090–2099 ; après la création (qui bascule l'école), retour à l'année
    d'origine par le sélecteur de l'en-tête `#aramisc_session` (route `change-academic-year`), puis modification et suppression.
    `after()` rétablit `session_id/academic_id/session_year`, supprime l'année + barèmes `aramisc_marks_grades` recopiés
    et vide les sessions Cypress (`Cypress.session.clearAllSavedSessions()`). L'ancien test tapait « 209X-2099 » (refusé : 4 chiffres).
  - `academics.cy.js` › affectation de matières et de professeur principal : Class2 / section 2 (vides au départ), notifications
    coupées ; sauvegarde intégrale de 7 tables (affectations, professeurs principaux, groupes / membres / invitations de chat)
    dans `sys_get_temp_dir()/cy_academics_snapshot.json` (hors projet), restaurées à l'identique dans `after()`.
  - `library.cy.js` › membre → emprunt → retour → annulation d'adhésion (un seul test, contrôles en base) : élève de Class2/sec2
    non membre, notifications `Add_Library_Member` / `Issue/Return_Book` coupées, **passerelles SMS désactivées** (send_sms direct) ;
    sauvegarde/restauration de `aramisc_library_members`, `aramisc_book_issues`, `aramisc_books` (`cy_library_snapshot.json`).
    Date de retour : `invoke('val')` (la saisie clavier dans le datepicker faisait échouer l'emprunt).
  - Si un run plante avant `after()` : le fichier de sauvegarde reste dans le dossier temporaire système ; le prochain `before()`
    ne l'écrase pas (`file_exists`) et le `after()` suivant restaure bien l'état d'origine.
- Anciens tests en échec **corrigés le 01/10/2026** :
  - `settings.cy.js` › General Settings : visait `/general-setting` (route de maquette, vue absente) → `/general-settings`.
  - `settings.cy.js` › Role : visait l'ancienne route `/role` → page du menu `/rolepermission/role`, CRUD réécrit avec contrôles en base.
  - `settings.cy.js` › Setup Admin : la page est en `.white-box` (pas de `table` / `.card`) → contrôle du formulaire `setup-admin`.
  - `academics.cy.js` › Class Routine : réécrit et sécurisé (sauvegarde/restauration de `aramisc_class_routine_updates`,
    `cy_class_routine_snapshot.json`). L'ancien test supprimait la dernière ligne réelle de Class1/sec1. Pièges : la page charge
    d'abord l'onglet actif (document.ready) dont la réponse peut arriver après celle du jour cliqué → attendre sa réponse avant de cliquer
    (alias d'interception propre à chaque ouverture) ; bouton corbeille ciblé par `data-class_routine_id` lu en base.

### ⛔ Mis de côté
- `/online-exam` : BLOQUÉ (anomalie 16), mis de côté à la demande de l'utilisateur le 01/10/2026.
  Nom de spec déjà validé : `examen_en_ligne/examen_en_ligne.cy.js`. ⚠ `store()` déclenche `sent_notifications('Online_Exam_Publish')` : à couper.

### ⏭ Rubriques restantes (dans l'ordre)
- ~~Frontend CMS~~ : **hors périmètre** (partie front non utilisée pour le moment, décision de l'utilisateur le 01/10/2026).
  Le test `settings.cy.js` › Frontend Page Builder est en `it.skip`.

## Anomalies trouvées dans l'application (à signaler / corriger)

1. ✅ **CORRIGÉ le 30/09/2026 — Partage de contenu refusé (422)** — `Modules/DownloadCenter/Http/Requests/ContentShareRequest.php`
   En locale FR, la règle `after:shareDate` lisait les dates `d/m/Y` en `m/d/Y` (422 dès que le jour > 12, partage et « Générer une URL »).
   Correctif : `prepareForValidation()` convertit `shareDate` / `validUpto` avec `toDbDateConvert` avant validation.
2. ✅ **CORRIGÉ le 21/10/2026 — Suppression d'un groupe de leçons : sujets / plans orphelins** — `AramiscLessonController::deleteLesson()`.
   Une ligne de la liste = TOUTES les leçons d'une classe / section / matière (année et école courantes, scope global) : supprimer
   le groupe est voulu par l'interface. Le défaut : seuls les sujets, détails de sujet et plans de la 1re leçon étaient supprimés,
   ceux des autres leçons restaient orphelins. Correctif : suppression de toutes les dépendances du groupe, en transaction, avec
   try/catch. Vérifié par un spec jetable hors projet (2 leçons + sujet / détail / plan chacune → tout supprimé).
   Les tests gardent la suppression unitaire (`lesson-item-delete`, page d'édition).
3. ✅ **CORRIGÉ le 21/10/2026 — Code mort** — `destroyLesson()` (`return $lesson = ...;`) appelle désormais `deleteLesson()`.
4. **Exception JS** sur la page Liste de contenu lors du choix d'un fichier :
   `TypeError: Cannot set properties of null (setting 'placeholder')` (non bloquant).
5. **Avertissements PHP 8 « Deprecated »** affichés dans les pages (paramètre optionnel avant un requis),
   ex. `AramiscLessonController.php` ligne 122, `app/AramiscAssignSubject.php` ligne 86.
6. Données : certains créneaux d'emploi du temps affichent « 10:45 AM - 12:00 AM » (12:00 AM = minuit).
7. ✅ **CORRIGÉ le 30/09/2026 — La suppression d'une carte d'identité effaçait le logo de l'école** —
   `AramiscStudentIdCardController` : une carte créée sans logo reprend `generalSetting()->logo` ; `destroy()` faisait `unlink()`
   de ce fichier, et `update()` (via `fileUpdate`) aussi quand on remplaçait le logo. Les deux ignorent désormais le logo de l'école.
8. **Dates brutes dans les tableaux wallet** — `WalletController::walletDipositDatatable()` : `dateConvert($row->created_at)`
   reçoit un datetime alors que `dateConvert()` attend `Y-m-d` → affichage « 2025-05-04 21:59:15 » au lieu du format FR.
9. **Notifications wallet trop larges (config)** — base de test : Email + SMS + App activés pour Wallet_Add / Approve_Deposit /
   Reject_Deposit / Refund_Deposit (élève, parent, super admin). `walletRejectRefund()` envoie en plus un e-mail en direct.
10. ✅ **CORRIGÉ le 30/09/2026 — Envoi de message de chat en erreur 500** — `app/Services/FcmChannel.php` appelle l'ancienne
    API FCM (`fcm.googleapis.com/fcm/send`, arrêtée par Google en 2024 → 404) sans try/catch : tout message vers un utilisateur
    ayant un `device_token` plantait. Désormais l'échec est journalisé (`FCM push failed`) et l'action continue.
    Reste à faire : migrer vers l'API FCM HTTP v1 pour que les push fonctionnent à nouveau.
11. **Chat : l'expéditeur ne voit pas son message** avant rechargement — `JqueryChatComponent.vue` : `messagePush()` commenté
    après l'envoi, et `userSpecificConversationCollection()` ne renvoie que les messages reçus (partie « envoyés » commentée).
    Correction côté JS → nécessite une recompilation des assets.
12. **Notifications Lesson_Plan** : Email + SMS + Web + App activés. Les runs du 30/09 avant correction ont tenté des e-mails
    (refusés par Gmail : `530 Authentication required`) et des push FCM (404) — rien n'a été reçu. `send_sms()` du trait
    `NotificationSend` commence par `return;` (aucun SMS possible par ce chemin).
13. ✅ **CORRIGÉ le 01/10/2026 — QCM créé sans bonne réponse** — `AramiscQuestionBankController::store()` (type M) :
    la boucle de contrôle avançait `$i` puis la boucle d'enregistrement continuait à partir de là → les options lisaient
    la mauvaise case `option_check_N` (constaté : `[0,0,0]` au lieu de `[0,1,0]`). Remise à zéro de `$i` avant la 2e boucle.
    Les QCM déjà créés avant le correctif peuvent avoir une bonne réponse absente ou fausse (les ré-enregistrer via « Modifier »).
14. **Suppression d'un groupe de questions utilisé** — `AramiscQuestionGroupController::destroy()` vérifie la colonne
    `question_group_id`, mais la banque utilise `q_group_id` (clé étrangère ON DELETE CASCADE) : supprimer un groupe
    efface **silencieusement toutes ses questions** au lieu d'afficher « données déjà utilisées ».
15. ✅ (corrigé le 21/10/2026, voir 53) **Banque de questions — suppression** : `destroy()` ne vérifie pas l'usage des QCM (type M) dans un examen, et ne supprime
    pas les options des questions à images (type MI) → options orphelines.
16. ⛔ **Examen en ligne (admin) inutilisable** — `app/Http/Controllers/Admin/OnlineExam/AramiscOnlineExamController.php`
    ne contient que `index()` et `store()` (tronqué dès son 1er commit `34e750c3` du 10/11/2024, idem dans `aramisc.rar`).
    - `/online-exam` → 500 `Class "...\AramiscOnlineExamModel" not found` (l'alias `use App\AramiscOnlineExam as AramiscOnlineExamModel` est commenté)
    - méthodes absentes appelées par les routes : `edit`, `update`, `delete`, `manageOnlineExamQuestion`, `onlineExamPublish`,
      `onlineExamPublishCancel`, `onlineExamQuestionEdit/Delete/Assign`, `viewOnlineExam`, `onlineExamMarksRegister`,
      `onlineExamResult`, `onlineExamMarking`, `onlineExamMarkingStore`, `onlineExamDatatable`, `onlineExamReport(Search)`
    - les vues existent toutes (`resources/views/backEnd/examination/online_exam*.blade.php`, `manage_online_exam.blade.php`…)
    - `store()` utilise `$this->studentRecordInfo()` (à vérifier) et notifie `Online_Exam_Publish`.
17. ✅ (corrigé le 21/10/2026, voir 53) **Plan d'examen — notifications en rafale** — `AdmitCardSettingController::admitcardGenerate()` et
    `SeatPlanSettingController::seatplanGenerate()` appellent `sent_notifications()` vers **toute la section** à chaque
    carte / place créée : générer pour N élèves envoie N fois la notification à chaque élève et parent (N² envois).
    Le contrôle « Exam shedule is not ready » ne se déclenche jamais (`$exam->get()` renvoie une collection, toujours vraie) :
    on peut générer cartes et places pour un examen sans aucun planning.
18. **Champs personnalisés (personnel) — colonne Largeur vide** — `staffRegistration.blade.php` compare `width` à
    `col-lg-12` / `col-lg-6`… alors que le formulaire enregistre `col-12` / `col-6`… → la liste du personnel n'affiche jamais la largeur.
    Même vue : `update_staff_custom_field()` (et donateur) valide `min_max_length.*` en `integer|min:1` sans `nullable`
    → modifier un champ non texte laissé sans longueurs risque un refus de validation (non vérifié).
19. **Champs personnalisés — HTML invalide** : modales de suppression en `<div>` dans le `<tbody>` (formulaire vidé par le parseur,
    fonctionne par rattachement implicite du bouton). Sortir les modales du tableau.
20. **Route de maquette morte** — `routes/admin_tenant.php` : `Route::view('/general-setting', 'frontEnd.general_setting')` (vue absente → 500),
    comme d'autres `Route::view('...', 'frontEnd....')` voisines. La vraie page est `/general-settings`.
21. **Ancienne page des rôles en 500** — `/role` (`AdminRolePermissionRoleController@index`) : `Route [assign_permission] not defined`
    (route commentée). Le menu utilise `/rolepermission/role` (module) ; l'ancienne route pourrait être supprimée.
22. ✅ **CORRIGÉ le 01/10/2026 — Modifier un professeur principal effaçait TOUTES les invitations de chat** — `app/Listeners/ListenClassTeacherGetAllStudent.php`
    (type 'update') : `Invitation::with([...])->delete()` — `with()` ne filtre que le chargement des relations, la requête
    `delete()` portait sur toute la table `chat_invitations` (les `chat_invitation_types` restaient orphelines).
    Correctif : suppression ciblée (invitations + types de l'ancien professeur principal pour cette section) ;
    contrôle de non-régression dans `academics.cy.js` (les autres invitations restent).
23. ✅ **CORRIGÉ le 21/10/2026 — Emprunt de livre pour un membre non élève** — `AramiscBookController::saveIssueBookData()` lisait
    `$bookIssue->member->studentDetails->studentRecord` sans contrôle : pour un membre personnel / parent, exception après l'enregistrement
    → prêt créé mais stock non décrémenté et message « Operation Failed » (reproduit : stock 3 → 3 avec un prêt actif).
    Correctif : prêt + stock en transaction ; notifications / SMS ensuite, dans leur propre try/catch (un échec est journalisé, le prêt
    reste) ; notification de classe seulement pour un membre élève. Vérifié (spec jetable hors projet, teacher1 membre) : stock 3 → 2.
    Prêt et retour appellent aussi `send_sms()` en direct (en plus des notifications).
24. **Création d'année académique** : bascule toute l'école sur la nouvelle année (réglage global, pas seulement la session de l'admin)
    et recopie systématiquement les barèmes de notes, même si rien n'est coché dans « Copier avec l'année académique ».
25. ✅ **CORRIGÉ le 21/10/2026 — Emploi du temps — perte des cours du jour sur erreur** — `AramiscClassRoutineNewController::addNewClassRoutineStore()`
    supprimait toutes les lignes du jour AVANT les contrôles (« This Time Has another Class ») et sans transaction : en cas de chevauchement,
    les cours du jour déjà supprimés n'étaient pas recréés (reproduit : 2 cours → 1). Le contrôle comparait aussi chaque ligne au 1er cours
    du jour seulement. Correctif : toutes les lignes sont contrôlées d'abord (chaque créneau contre TOUS ceux du jour : jour édité = nouvelles
    lignes ; autres jours cochés = lignes existantes + nouvelles), puis suppression / recréation en transaction. Vérifié (spec jetable,
    Class2 / sec2, lundi) : chevauchement avec le 1er ou le 2e cours → refus, jour intact ; saisie valide → jour remplacé.
    Même correctif dans l'API (`api/ApiAramiscClassRoutineController::addNewClassRoutineStore`, réponses JSON inchangées) : même suppression
    avant contrôle, et son contrôle (`in_array('08:30:00', range(80000, 90000))`) ne détectait JAMAIS rien → chevauchements toujours
    enregistrés (constaté via tinker : 08:00-09:00 + 08:30-09:30 acceptés). Vérifié après correctif : refus, jour intact.
    Prêt de livre côté API (`AramiscApiController::saveIssueBookData`) : pas concerné par l'anomalie 23 (aucune notification, stock décrémenté).
    `app/Http/Controllers/api/AramiscApiController.php` déclare la même classe que `app/Http/Controllers/AramiscApiController.php` : jamais
    chargé (classmap Composer → fichier racine), code mort.
26. **Emploi du temps — exception JS** `Cannot read properties of null (reading 'value')` : `isBusy()` est appelé depuis
    `.selectStartTime` / `.selectEndtTime` avec `data('teacher_row_id')` (attribut absent de ces champs ; `selectEndtTime` mal orthographié).
27. ✅ **CORRIGÉ le 06/10/2026 — Administration : dates refusées dès que le jour dépasse 12** — même cause que l'anomalie 1 :
    les règles `date` / `after` lisaient `j/m/aaaa` en `m/j/aaaa` (« The date is not a valid date. »). Concernés : Plainte, Envoi postal,
    Journal des appels (création + modification), Réception postale (modification ; `store()` ne valide rien), Requête d'admission
    (création, modification, suivi). Correctif : `prepareForValidation()` + `toDbDateConvert` dans `AramiscComplaintRequest`,
    `AramiscPostalReceiveRequest`, `AramiscPostalDispatchRequest`, `AramiscPhoneCallRequest`, `AramiscAdmissionQueryRequest`,
    `AramiscAdmissionQueryFollowUpRequest` (`app/Http/Requests/Admin/AdminSection/`), puis **`AramiscStudentAdmissionRequest`**
    (`date_of_birth`, `admission_date`) : on ne pouvait pas inscrire / modifier un élève né après le 12 d'un mois
    (« The date of birth must be a date after 1900-01-01. », constaté dans l'interface), puis **`Hr/staffRequest`**
    (`date_of_birth`, `date_of_joining` : ajout / modification de personnel refusés, constaté), puis **`Accounts/AramiscAddIncomeRequest`,
    `Accounts/AramiscExpenseRequest`** (revenu / dépense refusés, constaté). Non corrigé : `AramiscAdmissionQuerySearchRequest`
    (recherche par dates de la page Requête d'admission, même défaut probable — non testé).
    Requêtes avec règle de date et SANS conversion (relevé du 06/10/2026, à confirmer page par page avec une date > 12) :
    Accounts (AddIncome, Expense, FineReportSearch), Communicate/Event, FeesCollection/FeesMaster, GeneralSettings (AcademicYear,
    Holiday), Homework, Hr (staffAttendance*), Inventory (ItemIssue, ItemReceive, ItemSell), Leave/AramiscLeaveRequest,
    Library/SaveIssueBook, StudentInfo (attendance*), AdminSection/StudentCertificate, AdmissionQuerySearch.
    (Visitor, ClassTime, ExamTime : règles sur des heures, pas concernées.)
28. ✅ (corrigé le 21/10/2026, voir 53) **Réception postale — `store()` sans validation** : `AramiscPostalReceiveController::store(Request)` n'utilise pas
    `AramiscPostalReceiveRequest` (champs obligatoires non contrôlés à la création, seulement à la modification).
29. **Requête d'admission / suivi — HTML invalide** : modales de suppression des suivis placées dans le `<tbody>` (comme anomalie 19).
    Même défaut sur Département, Désignation, Type de congé (modales DELETE dans le `<tbody>`).
30. **Congés en attente / Approuver la demande de congé — recherche rapide inopérante** : toute saisie (type de congé, nom)
    renvoie « No matching records found » alors que la ligne est affichée sans filtre (`ajaxPendingLeave` / `ajaxApproveLeave`,
    recherche serveur sur des colonnes de relations / calculées ; erreur avalée, rien dans `laravel.log`). Cause non confirmée.
31. **Modification du personnel — valeurs entourées d'espaces** : `editStaff.blade.php` écrit `value=" {{ ... }} "` (constaté sur
    prénom / nom / e-mail / date) ; enregistrer sans retoucher peut stocker ces espaces (non vérifié en base).
32. **Suppression d'un membre du personnel — congés orphelins (lu dans le code, non reproduit)** : `staffStore()` recopie pour le
    nouvel utilisateur les `aramisc_leave_defines` déjà définis pour son rôle ; `delete_staff()` supprime le membre et son compte
    sans ces lignes. Base de test : aucun congé défini pour le rôle Comptable → rien n'est créé (0 orphelin constaté).
    Le spec mémorise l'id du compte et nettoie quand même ces lignes dans after().
33. ✅ (corrigé le 21/10/2026, voir 53) **Compte bancaire non modifiable** : la liste (`bankAccountDatatable`) ne propose que « Transaction » et « Supprimer » ; la route
    `bank-account/{id}` existe mais `AramiscBankAccountRequest` exige `account_number` unique sans ignorer le compte → toute
    modification serait refusée (lu dans le code). Le revenu « Opening Balance » créé à l'ouverture (`item_sell_id` = id du compte)
    n'est pas supprimé avec le compte.
34. **Revenus / dépenses — date brute dans la liste** : « 2026-09-15 00:00:00 » au lieu du format FR (comme anomalie 8). **Corrigée** (`dateConvert` accepte les horodatages) ; specs depense/revenu attendent désormais `15/09/2026`.
35. ✅ **CORRIGÉ le 06/10/2026 — Revenu modifié : solde bancaire faux** (update() annule d'abord l'ancien montant sur l'ancien compte et son relevé) — `AramiscAddIncomeController::update()` ajoute le nouveau montant au solde
    sans retirer l'ancien : compte à 1000, revenu 200 (→ 1200) modifié à 300 → **1500** au lieu de 1300. Changer de compte en
    modification laisse aussi l'ancien compte crédité. (`item_sell_id` est partagé avec les ventes d'inventaire : la suppression des
    relevés `where('item_sell_id', id)` peut toucher un relevé de vente de même id.)
36. ✅ **CORRIGÉ le 06/10/2026 — Dépense modifiée : solde bancaire faux** (même correctif) — `AramiscAddExpenseController::update()` retire le nouveau montant sans
    rendre l'ancien : 1000, dépense 100 (→ 900) modifiée à 150 → **750** au lieu de 850. `destroy()` rend `amount` (OK) mais calcule un
    `$reset_balance` inutilisé sur `item_receive_id = account_id` (mauvaise clé).
37. ✅ **CORRIGÉ le 06/10/2026 — Frais principaux : dates d'échéance fausses** (`toDbDateConvert()` avant `strtotime()`) — `AramiscFeesMasterController::installmentCreate()` :
    `date('Y-m-d', strtotime($date))` sur une date FR `j/m/aaaa` → échéance 15/10/2026 enregistrée **1970-01-01** ; un jour ≤ 12
    inverse jour et mois (06/10 → 10 juin). Utiliser `toDbDateConvert()`.
38. ✅ **CORRIGÉ le 06/10/2026 — Frais principaux : suppression incomplète** (constaté : échéance orpheline ; tout est contrôlé puis supprimé) — `deleteGroup()` (frais directs) sort dès la 1re
    échéance (return dans la boucle) et ne supprime ni le frais principal ni les échéances (seulement type + groupe + affectations).
39. ✅ (corrigé le 21/10/2026, voir 53) **Revenus / dépenses — `required_if:payment_method,Bank`** : `payment_method` vaut l'id (3), jamais « Bank » → le compte bancaire
    n'est jamais exigé ; un paiement « Bank » sans compte plante dans `store()` (lu dans le code).
41. ✅ **CORRIGÉ le 06/10/2026 — Pannes DataTables aléatoires (« Cannot read properties of undefined (reading 'ext') »,
    jsLang « reading 'json' »)** — `app/Http/Middleware/Localization.php` faisait `Cache::forget('translations')` puis `remember()`
    à CHAQUE requête : une page rendue pendant une requête AJAX simultanée (DataTables, `/chat/check/notification`) recevait
    `window._translations = []` → `jsLang()` plantait → DataTables ne s'initialisait pas (cf. instabilité wallet du 01/10, frais
    principaux du 06/10). Correctif (accord de l'utilisateur) : clé par langue `translations_<locale>` (6 h) sans forget par requête
    (`Localization::cacheKey()`), lue par `backEnd/partials/header.blade.php` ; `Localization::forgetTranslations()` appelé par
    `AramiscSystemSettingController` (ajout de langue, langue par défaut, `translationTermUpdate`).
42. ✅ **CORRIGÉ le 07/10/2026 — Réception / vente d'articles et présence aux examens : table inexistante** — `app/AramiscItemReceiveChild.php`,
    `AramiscItemSellChild.php`, `AramiscExamAttendanceChild.php` déclaraient `aramisc_*_childs` (introduit par le commit 34e750c3
    « update BDD & Lag ») alors que les tables s'appellent `aramisc_*_children` → toute réception / vente échouait après l'en-tête
    (« Operation Failed », stock non mis à jour, en-tête orphelin) ; la présence aux examens est touchée de la même façon (non testée).
43. ✅ (corrigé le 21/10/2026, voir 53) **Modification d'une réception — rubrique de dépense non présélectionnée** : `editItemReceive.blade.php` ne resélectionne pas
    `expense_head_id` → « The expense field is required » si l'utilisateur ne la choisit pas à nouveau (constaté).
44. ✅ **CORRIGÉ le 07/10/2026 — Vente d'articles : stock faux** — `UpdateItemSellData()` retirait la nouvelle quantité sans rendre
    l'ancienne (10, vente 3 → 7, modifiée à 4 → **3** au lieu de 6, constaté) ; `deleteItemSale()` ne supprimait que la vente (stock
    non rendu, lignes et revenu « Item Sell » orphelins). Correctif : ancienne quantité rendue avant la mise à jour ; suppression qui
    rend le stock, supprime lignes, revenu « Item Sell » et relevé bancaire (solde corrigé si paiement Bank).
    (Correctifs appliqués sans demande préalable : accord de l'utilisateur du 07/10 pour les bugs évidents confirmés par un test.)
45. Réception / vente d'articles : « Supprimer » n'est proposé que si rien n'a été payé (sinon « Annuler ») — règle de l'application,
    les specs créent donc des opérations non payées. Vente : l'acheteur « personnel » est identifié par l'id `aramisc_staffs`
    (et non l'id utilisateur comme ailleurs).
46. Inventaire — anomalie 27 étendue : `AramiscItemReceiveRequest` (`receive_date`), `AramiscItemSellRequest` (`sell_date`),
    `AramiscItemIssueRequest` (`issue_date`, `due_date`) → ✅ corrigés le 07/10/2026 (constaté sur la réception).
47. ✅ **CORRIGÉ le 07/10/2026 — Calendrier d'examen : date enregistrée en 1970-01-01 (constaté)** —
    `AramiscExamRoutineController::addExamRoutineStore()` : `date('Y-m-d', strtotime($date))` sur `j/m/aaaa` → `toDbDateConvert()` d'abord
    (4 lignes, branches université et standard). Même endroit, **lu dans le code et corrigé** : avant d'enregistrer, la suppression des
    épreuves existantes filtrait sur `$request->section` (champ inexistant, le formulaire envoie `section_id`) → le planning de TOUTES
    les sections de la classe pour ce type d'examen était effacé.
48. **Calendrier d'examen — page `exam-schedule-create` après recherche** : la vue `exam_schedule_create.blade.php` vise la route
    `exam_schedule_store`, commentée dans `routes/admin_tenant.php` (lu dans le code ; le contrôleur rend en pratique
    `exam_schedule_new_update`, qui fonctionne).
49. ✅ (corrigé le 21/10/2026, voir 53) **Configuration d'examen (mode multiple) — contrôle de doublon inopérant** : `store()` cherche un examen existant avec
    `$request->exams_type` / `$request->subject_id`, absents de ce formulaire (`exams_types[]`, `subjects_ids[]`) → doublons possibles
    (lu dans le code). La vue partielle `multi_exam_setup` recharge `custom.js` et `developer.js` à chaque changement de mode
    (gestionnaires liés plusieurs fois, « Script error » parfois au chargement).
50. Anomalie 27 étendue (07/10/2026, constaté puis ✅ corrigé) : `Homework/AramiscHomeworkRequest` (`homework_date`, `submission_date`),
    `Communicate/EventRequest` (`from_date`, `to_date`), `app/Http/Requests/NoticeRequestForm.php` (`notice_date`, `publish_on`).
    Jour férié (`AramiscHolidayRequest`, `before_or_equal`) : accepté avec 15/10 → 16/10 (pas de refus constaté, non modifié).
51. ✅ (corrigé le 21/10/2026, voir 53) **Événements — liste filtrée par le rôle de l'utilisateur** : `getAllEventList()` n'affiche à un utilisateur autre que le Super admin
    (rôle 1) que les événements destinés à SON rôle ; `admin@aramisc.com` a le rôle 5 (Admin) → un admin qui crée un événement pour
    les enseignants ne le voit plus et ne peut ni le modifier ni le supprimer (constaté). Non modifié (choix de conception à valider).
    Aussi : en base de test (APP_DEBUG), les réponses DataTables contiennent les requêtes SQL (`"queries":[...]`).
40. **Frais principaux — suppression impossible à 1280 px** : la colonne Action est repliée par DataTables responsive et la modale
    `#deleteFeesMasterGroup{id}` reste dans la cellule masquée → seul le fond grisé s'affiche (constaté). Sortir les modales du tableau.
    Même risque sur toutes les pages où la modale de suppression est dans le `<tbody>` (anomalies 19, 29).
52. ✅ **CORRIGÉ le 08/10/2026 — Passe « dates » (anomalie 27 étendue à tout le relevé + contrôle multilingue)**
    Principe de l'application : le format de saisie dépend de la LANGUE (`toDbDateConvert` / `dateConvert` : FR = `j/m/aaaa`, toute autre
    langue = `aaaa-mm-j`, cf. datepicker de `main1.js`). La base reste en `Y-m-d`.
    - Requêtes restantes de l'anomalie 27, corrigées par **`validationData()`** (la règle lit la date convertie, la valeur reçue par le
      contrôleur reste inchangée → les formulaires de recherche réaffichent la date saisie) : `AramiscAdmissionQuerySearchRequest`,
      `AramiscHolidayRequest`, `Leave/AramiscLeaveRequest`, `Library/SaveIssueBookRequest`, `AramiscStudentAttendanceSearchRequest`,
      `StudentAttendanceBulkRequest`, `StudentSubjectWiseAttendanceStoreRequest`, `StudentSubjectWiseAttendancSearchRequest`,
      `Hr/staffAttendanceSearchRequest`, `Hr/staffAttendanceBulkStoreRequest`, `AramiscExamFormatSettingsRequest`
      (présence élèves / personnel / par matière, jours fériés, congés, emprunt de livre, paramètres d'examen : tous refusaient un jour > 12).
    - Validations dans les contrôleurs (conversion avant la règle) : panneaux Élève et Parent › congés (`leaveStore`, `update`),
      `Modules/Fees` › `feesInvoiceStore` (`due_date` `after:create_date`).
    - Lectures par `strtotime()` sur une date FR (m/j inversé ou 1970-01-01) : paiement de frais côté Élève / Parent
      (`Student|Parent/AramiscFeesController`), recherche des bordereaux (`AramiscFeesBankPaymentController`, `DatatableQueryController`),
      report de solde (`AramiscFeesCarryForwardController`), rapport de dépenses (`AramiscAccountsController::searchExpenseReportByDate`),
      comparaison de la date d'import des présences élèves / personnel, statut d'un plan de leçon (`LessonPlanController::lessonPlanstatusAjax`,
      date enregistrée sans conversion).
    - **`dateConvert()`** (`app/Helpers/Helper.php`) accepte désormais les horodatages (`created_at`…), chaînes ou objets Carbon : il
      renvoyait la valeur brute `2025-05-04 21:59:15` (anomalies 8 et 34, et une soixantaine d'appels sur `created_at`).
    - Contrôle multilingue : script PHP temporaire (hors projet) qui instancie 28 FormRequest à champs date et les valide en **fr / en / ar**
      avec des dates piégeuses (jour > 12, couples ambigus 10/05 → 5/10, jour sans zéro `2026-10-5`) → 84/84 acceptés ; le même test
      sans conversion reproduit bien le refus. `toDbDateConvert` est idempotent (une valeur déjà en `Y-m-d` ne bouge pas, en FR comme en EN).
    - ✅ **Sélecteurs de plage (`date_range`, `payment_date` de Fees › bordereaux) alignés sur la langue** (demande de l'utilisateur) : ils étaient
      toujours en `MM/DD/YYYY` (aucun `format`, séparateur pris dans la traduction). Maintenant FR = `jj/mm/aaaa - jj/mm/aaaa`, autres langues =
      `aaaa-mm-jj - aaaa-mm-jj` (`partials/date_range_picker_css_js.blade.php`, `Modules/Fees/.../bankPayment.blade.php`). Lecture côté serveur par
      **`parseDateRange()`** (`Helper.php`) : repère les deux dates par motif (les `explode('-')` cassaient avec le format EN) et renvoie `[Y-m-d, Y-m-d]` ;
      branchée dans `AramiscAccountsController` (3), `AramiscCollectionReportController`, `Admin/FeesCollection/AramiscFeesController` (4),
      `Modules/Fees` (`FeesController`, `FeesReportController`), `Modules/Wallet/WalletController`. Les contrôleurs API (`Modules/Fees/.../api`) et les
      contrôleurs racine non routés gardent l'ancien code. Corrigé au passage : `AramiscFineReportSearchRequest` exigeait `date` sur une PLAGE
      (rejet systématique de la recherche, dans toutes les langues) → `required`. `LessonPlanController` : `lesson_date` convertie à l'enregistrement.
      Vérifié dans Chrome (FR, spec jetable hors projet) : sélecteur affiché au bon format + recherche `15/09/2026 - 20/09/2026` OK sur
      `/transaction`, `/transaction-report`, `/fine-report`, `/accounts-payroll-report`, `/profit`, `/wallet/wallet-report`, `/fees/{fine,payment,balance,waiver}-report`,
      `/fees/bank-payment`. Non essayé dans le navigateur en EN (couvert côté PHP : 9 plages fr / en / ar).
    - Constaté, non modifié : (a) `search-account`, `search-income` et `search-expense` utilisent deux champs `date_from` / `date_to` (déjà convertis) ;
      (b) datepicker EN en `yyyy-mm-d` (jour sans zéro, accepté partout) ; (c) `dateConvert('0000-00-00')` donne `30/11/-0001` (antérieur) ;
      (d) contrôleurs racine non routés (`AramiscVisitorController`, `AramiscFeesController`, `AramiscHolidayController`, `AramiscEventController`,
      `teacher/*`) gardent leurs anciens `strtotime($request->…)` : code mort.
53. ✅ **CORRIGÉS le 21/10/2026 — anomalies 15, 17, 28, 33, 39, 43, 49, 51 (+ contrôles d'usage)**
    - **17** `Modules/ExamPlan` (cartes d'admission, plan de siège, branches standard + université) : recherche → `$exam_routine->isNotEmpty()`
      (« Exam shedule is not ready » s'affiche enfin) ; génération → UNE notification après la boucle, aux seuls élèves (et parents) dont la
      carte / place vient d'être créée. Specs `plan_examen/*` : recherche sans planning refusée (POST → 302 vers l'index, pas de formulaire de
      génération), épreuve de planning créée en cours de test, notifications sans intrus ni doublon.
      Le toast n'est pas vérifié : entre la redirection et le rechargement, une requête AJAX de l'ancienne page (`ajaxSelectStudent`,
      `chat/check/notification`) consomme souvent le message flash Toastr → l'utilisateur ne voit parfois pas l'avertissement (défaut
      d'ergonomie de l'app, non corrigé). Lire `response.body` (1,2 Mo) dans une assertion faisait planter le renderer Chrome (run bloqué).
    - **33** Compte bancaire : `AramiscBankAccountRequest` ignore le compte modifié dans `unique` ; `update()` (par `$id`, transaction) décale le
      solde courant de la variation du solde d'ouverture et met à jour le revenu « Opening Balance » ; lien « Modifier » dans la liste (droit
      `bank-account-store`, aucun droit « edit » n'existe). `compte_bancaire.cy.js` : étape de modification (1000 → 1200).
    - **39** Revenus / dépenses : `accounts` exigé si le moyen de paiement (id) est « Bank » (`isBankPayment()`). Specs : étape 0 « Bank sans compte ».
    - **43** `editItemReceive.blade.php` écrivait le texte traduit `@lang('inventory.selected')` (« Choisi ») au lieu de l'attribut `selected`
      → rubrique présélectionnée (avec `old()`). `reception.cy.js` vérifie la présélection au lieu de la re-choisir.
    - **49** `AramiscExamController::store()` (mode multiple, standard + université) : contrôle de doublon sur `$exam_type_id` / `$subject_id`
      de la boucle. `configuration_examen.cy.js` : 2ᵉ soumission identique → toujours 1 examen.
    - **28** `AramiscPostalReceiveController::store()` utilise `AramiscPostalReceiveRequest`. `courrier_recu.cy.js` : étape 0 champs vides.
    - **51** (choix de l'utilisateur : « le rôle Admin voit tout ») `getAllEventList()` : Super admin (1) ET Admin (5) voient tous les événements.
      `evenement.cy.js` : événement destiné aux seuls enseignants, retrouvé / modifié / supprimé par l'admin.
    - **15** (diagnostic revu) : les 3 tables liées (`mu_options`, `online_exam_question_assigns`, `student_take_online_exam_questions`) sont en
      ON DELETE CASCADE → pas d'options orphelines, mais supprimer une question utilisée effaçait en silence son affectation aux examens et les
      réponses des élèves. Ancien code : QCM « M » jamais contrôlé ; « MI » toujours refusé (ses propres options comptaient comme usage).
      `destroy()` contrôle maintenant l'usage dans les 2 tables d'examen pour tous les types, puis supprime question + options en transaction.
      Vérifié via tinker (ancien / nouveau) : MI libre supprimé, M utilisé refusé, M libéré supprimé.
    - **Contrôles « données déjà utilisées » inopérants avec `config:cache`** : `app/tableList.php` lisait `env('DB_DATABASE')`, NULL quand la
      configuration est en cache (production, runs Cypress) → `getTableList()` ne trouvait aucune table et laissait tout supprimer.
      Remplacé par `DB::getDatabaseName()`. Même correction dans `app/deleteData.php`, `Traits/DatabaseTableTrait.php` et
      `AramiscSystemSettingController::updateCreatedDate()` ; `restoreDatabase()` et la sauvegarde (`Shuttle_Dumper`) lisent maintenant
      `config('database.connections.mysql.*')`. Vérifié via tinker config en cache (base, hôte, 38 tables `class_id`, 29 tables `record_id`) ;
      restauration / sauvegarde non testées de bout en bout (actions destructives).

### 🧪 Run complet headless du 08/10/2026 (sans vidéo, 7 groupes)
Groupes 1, 4, 5 : tout vert (45 specs). Groupe 2 : 14/16 → les 2 échecs (`comptes/depense`, `comptes/revenu`) attendaient la date brute
« 2026-09-15 » (anomalie 34, maintenant corrigée) → specs mis à jour (`15/09/2026`), verts. Groupe 3 : `parametres/devise` instable (DataTables), vert au 2ᵉ passage.
Échecs restants, **sans lien avec les dates**, dans les anciens specs :
- `/online-exam` (500, anomalie 16) et `/return_global_exam_view` (500) : bugs connus/en attente.
- Panneau Élève/Parent › livres : `student1`/`parent1` ne sont pas membres de la bibliothèque (table vide) → redirection arrière.
- Panneau Parent (11 tests, 403) : `due_fees_login=1` + facture impayée échue de l'élève 1 (`fm_fees_invoices` #2, échéance 2025-04-30) → accès bloqué par conception.
  ✅ **Réglé le 21/10/2026** (option choisie par l'utilisateur) : `parent_panel.cy.js` coupe `due_fees_login` en before(), vide le cache
  `have_due_fees_<id parent1>` (rememberForever, lu par `UserRolePermission`) et les sessions Cypress (`generalSetting()` est gardé en session
  Laravel) ; after() rétablit la valeur (contrôlé : 1). → 19/20 verts, reste « livres empruntés » (parent1 non membre de la bibliothèque).
- Panneau Enseignant : mot de passe de `cypress/fixtures/users.json` corrigé (`123456`) ; restent 3 URL périmées (`/subject-attendance` 404, `/notice-board` 404, `/class-routine` → `/marks-register`).

---

## 🐘 Migration PHP 8.2 → 8.4 (26/10/2026)

**WAMP64** sert désormais **PHP 8.4.27** (ZTS VS2022 x64). Installé manuellement dans `C:\wamp64\bin\php\php8.4.27\`
(zip officiel, SHA256 vérifié). `php.ini`/`phpForApache.ini` dérivés de 8.3.6 avec : `extension_dir`/opcache → 8.4.27,
`extension=imap` commenté (retiré du cœur en 8.4, désormais PECL), `zend_extension=xdebug` commenté,
`session.sid_length`/`session.sid_bits_per_character` commentés (INI dépréciés), `extension=sodium` activé,
`display_errors = Off` + `display_startup_errors = Off`, `error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT`.
`httpd.conf` (LoadModule), `wampmanager.conf` (phpVersion) et le PATH système pointent sur 8.4.27 ; menu du tray régénéré
(`refresh.php`). **Redémarrage d'Apache nécessaire** après tout changement de `php.ini`.

### Corrections de code pour 8.4 (paramètres implicitement nullables — dépréciés en 8.4)
- `app/Helpers/Basic.php` : 6 signatures corrigées (`?int $user_id = null`, `?string $format = null`, `?int $academic_year = null`,
  `?int $section_id = null`, etc.).
- `app/Http/Controllers/LanguageController.php:63` : `fileName(string $lang, array $fileList = [], ?string $fileName = null): string`.

### Paquets Composer (mis à jour sous 8.4, ciblés uniquement)
- `lcobucci/clock` 3.0.0 → 3.6.0, `lcobucci/jwt` 5.3.0 → 5.6.1, `moneyphp/money` v4.5.1 → v4.9.0 (`--with-dependencies`).
- ⚠️ `lcobucci/clock` 3.6.0 impose `PHP_VERSION_ID >= 80400` (via `vendor/composer/platform_check.php`) → **l'app renvoie 500 tant
  qu'Apache tourne en < 8.4**. La mise à jour complète des 273 paquets a été **écartée** (mélange de majeures Symfony, trop risqué).
  Sauvegardes `composer.json`/`composer.lock` dans le scratchpad de session.

### Piège rencontré (à retenir)
`display_errors = On` injectait le HTML de dépréciation de `vendor/` (Carbon…) dans les réponses → cassait le JSON des DataTables/API.
D'où `display_errors = Off` **et** un doublon `php_flag display_errors Off` dans le `.htaccess` (prise d'effet immédiate, sans
redémarrage, car le vhost aramisc a `AllowOverride All`). Les erreurs restent journalisées.

### Tests de specs sous 8.4 — **10/10 verts (14 tests)** le 26/10/2026
`carte_admission`, `plan_de_siege`, `compte_bancaire`, `revenu`, `depense`, `reception`, `configuration_examen`,
`courrier_recu`, `evenement`, `banque_de_questions` (3). Incidents de données réparés en cours de route (SQL fourni, lancé par
l'utilisateur) : `aramisc01.aramisc_notification_settings` vidée par erreur → recopiée depuis `aramisc` (41 lignes) ;
3 examens `Examen_CY_%` orphelins (+ plannings/cartes) supprimés. Scan exact des 301 tables : aucune autre perte de données.

## 🔒 Correctifs de sécurité (26/10/2026)

Audit défensif sur l'app locale de l'utilisateur. Tous les correctifs vérifiés (403/404 côté HTTP, `php -l` propre).

| # | Faille | Gravité | Correctif |
|---|--------|---------|-----------|
| 1 | Fichiers sensibles exposés (clé Firebase, `.git`, logs, `composer.json`, `config.json`…) | 🔴 | `.htaccess` → 403 |
| 2 | Sauvegardes BDD téléchargeables (`public/Backup`) | 🔴 | `.htaccess` → 403 |
| 3 | Exécution PHP dans `public/uploads` (RCE) | 🔴 | `.htaccess` → 403 |
| 4 | Traversée de répertoire (20 closures de download) | 🔴 | `basename()` dans `routes/{admin_tenant,student,tenant}.php` |
| **+** | **Contournement d'auth `GET /api/v2/user-demo-login/{role_id}`** — renvoyait un **token admin valide sans aucun identifiant** | 🔴🔴 | route commentée (`routes/v2api.php`) |
| 5 | `GET /api/user-demo` + variante SaaS `school/{id}/user-demo` (fuite d'e-mails) | 🟠 | routes commentées (`routes/api.php`) |
| 5 | `GET /api/db-correction` + `public/route-list.json` | 🟠 | route commentée / 403 |
| 6 | `APP_DEBUG=true` / Ignition expose chemins & code sur 500 | 🟠 | **conservé `true` (choix utilisateur)** — acceptable uniquement hors Internet |

**Faille majeure de la passe** : `AuthenticationController::DemoUser` (`app/Http/Controllers/api/v2/Auth/`, ligne 174) faisait
`auth()->login($user)` sur le 1ᵉʳ utilisateur actif d'un rôle et retournait un Bearer token — prise de contrôle totale de l'ERP
sans mot de passe (exploit confirmé en live : `/api/v2/user-demo-login/1` → token admin). Désormais 404.

**Autres points de connexion-démo audités (aucune action requise)** : `view-as-role` / `view-as-parent`
(`StaffAsParentController`, `routes/admin_tenant.php`) lisent `auth()->user()->id` → exigent une session admin, légitimes ;
`aramisc_api.php:230` et `api-list.php:555` ne sont que des constantes/commentaires côté client, pas des routes.

**Note** : le test d'exploit a généré un vrai token admin dans `aramisc01.oauth_access_tokens` (utilisateur
`abassi.hicham@gmail.com`). À révoquer si souhaité : `DELETE FROM oauth_access_tokens WHERE user_id = (SELECT id FROM users WHERE email='abassi.hicham@gmail.com');`

**Fichiers projet modifiés (non committés)** : `.htaccess`, `routes/{admin_tenant,student,tenant,api,v2api}.php`,
`app/Helpers/Basic.php`, `app/Http/Controllers/LanguageController.php`, `composer.json`/`composer.lock`.

---

## 🧑‍🎓 Campagne « specs par rôle » (26/10/2026) — en cours

Objectif (demandé par l'utilisateur) : de vrais specs pour **chaque rôle**, structure **un dossier par rubrique**
(comme admin). Ordre : refaire les partiels (Student → Parents → Teacher), puis créer les absents
(Super admin, Accountant, Receptionist, Librarian, Driver).

### ⚙️ Prérequis d'exécution (IMPORTANT)
Le `php` par défaut du shell est **8.2.18** → `scripts/run-cypress.js` (qui lance `php artisan config:cache`)
plante sur `platform_check` (dépendances exigent ≥ 8.4) et ne régénère pas le cache avec `.env.cypress`.
**Lancer le runner avec PHP 8.4 en tête de PATH :**
```
export PATH="/c/wamp64/bin/php/php8.4.27:$PATH" && node scripts/run-cypress.js run --browser chrome --spec "..."
```
(À terme : corriger le PATH système, ou faire pointer run-cypress.js sur php8.4.27.)

### ✅ Rôle Student — 16 rubriques, 25 tests verts
Ancien `student_panel.cy.js` (visites + screenshots non commentés) **supprimé** et remplacé par
`cypress/e2e/student/<rubrique>/` : tableau_de_bord, profil, frais, presence (recherche), resultat,
emploi_du_temps, planning… → examen_en_ligne, matieres, transport, dortoir, bibliotheque, devoirs,
centre_telechargement (4 pages), enseignants, tableau_affichage, conge.
Captures en commentaire, assertions réelles (URL + authentifié + contenu), aucune donnée laissée.

**Blocages / findings rôle Student :**
- **Présence** : `student1` n'a aucune présence saisie → le rapport mensuel est vide ; le test valide le
  round-trip POST (page 200 authentifiée), pas un calendrier peuplé.
- **Congé (CRUD non automatisable en l'état)** :
  1. `student1` n'a **aucun type de congé** dans `aramisc_leave_defines` → `<select name="leave_type">` vide,
     soumission impossible. Pour activer le chemin d'écriture, définir un type, ex. :
     `INSERT INTO aramisc_leave_defines (role_id,user_id,type_id,days,academic_id,active_status,school_id,created_at,updated_at) VALUES (2,5,1,10,getAcademicId_courant,1,1,NOW(),NOW());`
     (adapter `type_id` à un `aramisc_leave_types` existant et `academic_id`).
  2. Le bouton « Supprimer » du panneau élève pointe vers la route **admin** `apply-leave-delete`
     (permission admin) → un élève ne peut pas nettoyer sa propre demande via l'UI.
  3. **Bug à corriger** : `AramiscStudentPanelController::leaveStore()` (≈1916) et `update()` (≈2055) font
     `$file = $request->file('attach_file'); $fileSize = filesize($file);` **avant** de vérifier la présence
     du fichier → avec un fichier joint, `filesize()` reçoit un objet `UploadedFile` (TypeError) ; sans
     fichier, `filesize(null)` (déprécié). Garder `attach_file` réellement optionnel : n'appeler `filesize()`
     que si `$request->hasFile('attach_file')`, et utiliser `$file->getSize()` plutôt que `filesize($file)`.

### ✅ Rôle Parents — 16 rubriques, 22 tests verts
Ancien `parent_panel.cy.js` (smoke-tests) **supprimé**, remplacé par `cypress/e2e/parent/<rubrique>/` :
tableau_de_bord, mes_enfants, frais, emploi_du_temps, presence (recherche), devoirs, tableau_affichage,
examen_en_ligne, examen, planning_examen, bibliotheque, matieres, enseignants, transport, dortoir, conge.
Socle partagé `cypress/e2e/parent/_support.js` : coupe `due_fees_login` (+ purge cache `have_due_fees_*`
et sessions) pendant le run et rétablit en after(), et récupère dynamiquement l'`id` de l'enfant de parent1
(user_id 6, parent_id 1, enfant student_id 1).

**Findings / points rôle Parents :**
- **Bug filesize() corrigé aussi côté Parent** : `AramiscParentPanelController::leaveStore()` (≈865) et
  `update()` (≈1043) faisaient `filesize($file)` sur l'objet `UploadedFile` (déjà dans un `if fichier présent`,
  donc crash seulement avec pièce jointe) → remplacé par `$file->getSize()`. (Le même motif subsiste ligne ≈436
  dans la mise à jour enfant — hors périmètre congé.)
- **Congé** : aucun type de congé défini pour parent1 → chemin d'écriture ignoré. Mais le parent a bien une
  route de suppression (`parent-leave-delete`) → CRUD complet nettoyable une fois un type défini (user_id 6, role_id 3).
- **Présence** : le `<select name="year">` ne propose pas forcément l'année courante → on choisit une année
  réellement listée. Rapport vide tant qu'aucune présence n'est saisie pour l'enfant.
- **Mise à jour enfant** (my-children-update) : réutilise le formulaire d'admission complet → non testé (destructeur).

### ✅ Rôle Teacher — 13 rubriques, 15 tests verts
Ancien `teacher_panel.cy.js` (smoke-tests) **supprimé**, remplacé par `cypress/e2e/teacher/<rubrique>/` :
tableau_de_bord, eleves, presence_matiere, devoirs, centre_telechargement, notes, emploi_du_temps,
planning_examen, tableau_affichage, conge, evaluation, bibliotheque, mot_de_passe.
L'enseignant (role 4) utilise le back-office admin restreint par permissions ; `teacher1` a accès à toutes ces pages.

**URL périmées corrigées (les 3 échecs historiques du panneau enseignant sont résolus) :**
- `/subject-attendance` (404) → **`/subject-wise-attendance`** (présence par matière)
- `/notice-board` (404) → **`/notice-list`** (tableau d'affichage)
- `/add-homework` (404) → **`/add-homeworks`** (ajout de devoir, pluriel)
- emploi du temps → **`/class-routine`** (et non `/class-routine-new`)
- changement de mot de passe → `/change-password` (routes/tenant.php)

➡️ Trio « partiels » terminé (Student, Parents, Teacher). Reste à créer les rôles sans aucun spec :
Super admin, Accountant, Receptionist, Librarian, Driver — **nécessitent d'abord un compte de test** (aucun n'existe).

### ✅ Rôles staff — Accountant, Receptionist, Librarian, Driver (27/10/2026)
Comptes de test créés via l'UI admin (Ressources humaines → Ajouter du personnel), email `[role]@aramisc.com`.
**Important** : le formulaire ne propose pas de champ mot de passe → le contrôleur force un mot de passe par défaut. Les 4 comptes ont
ensuite été alignés sur le mot de passe de l'admin par SQL (hash bcrypt, base aramisc01). Fixtures + commandes
de login ajoutées (`loginAsAccountant/Receptionist/Librarian/Driver`).

Specs par rubrique, basées sur les **permissions réelles** de chaque rôle (`aramisc_role_permissions` → `aramisc_module_links`) :
- **Accountant** (103 perms) : tableau_de_bord, comptabilite (revenus/dépenses/plan comptable/comptes bancaires/bénéfice),
  frais (collecte/groupes/types/barèmes/rapports), inventaire (articles/réceptions/ventes/fournisseurs), eleves, personnel, mot_de_passe.
- **Receptionist** (44) : tableau_de_bord, admissions, accueil (visiteurs/plaintes/appels), courrier (réception/envoi), eleves, mot_de_passe.
- **Librarian** (44) : tableau_de_bord, bibliotheque (livres/catégories/membres/emprunts), communication (événements/annonces), eleves, mot_de_passe.
- **Driver** (0 permission) : tableau_de_bord + mot_de_passe uniquement (aucun module assigné).
→ **42 tests verts.**

**Findings / points :**
- **Throttle de connexion** : des connexions/déconnexions manuelles rapides au navigateur déclenchent la limitation Laravel
  (même un bon mot de passe est alors refusé, ~1 min). Les specs Cypress (session en cache) n'y sont pas sujettes.
  → Confirmé que Librarian et Driver se connectent bien (le doute venait du throttle, pas du rôle).
- **`/payment-method`** (résolu) : ce n'était pas un bug applicatif visible. La route web `payment-method` et tout son CRUD
  sont **retirés** (commentés dans `admin_tenant.php`), remplacés par `payment-method-settings` (route active). Les menus actifs
  (non-SaaS) pointent déjà vers `payment-method-settings` ; seule `saas_menu.blade.php` (module SaaS inactif) référence encore
  l'ancienne route. Il restait un **vestige de données** : des assignations orphelines dans `aramisc_role_permissions`
  (lien `aramisc_module_links` id 152) pour les rôles 1/5/6, non rendues dans les menus → nettoyées par SQL
  (`active_status=0` sur le lien 152 + suppression des `aramisc_role_permissions` du lien 152). Page exclue des specs (404 attendu).
- **Super admin** : non créable via le formulaire staff (liste limitée à Accountant/Admin/Driver/Librarian/Receptionist/Teacher) ;
  existe déjà via le compte perso et reste un sur-ensemble de l'admin (87 specs admin). Pas de compte `superadmin@aramisc.com` dédié créé.

### 🔧 Correctifs issus du run complet Admin (27/10/2026)
Run admin (87 specs) : 190/202 tests verts au départ, 10 specs en échec. Causes et correctifs :

- **⚠️ Régression `.htaccess` (introduite par le durcissement sécurité de cette session)** : le blocage
  `RewriteRule ^(...|Modules|packages)(/|$) - [F,L]` renvoyait **403** sur les assets servis directement depuis
  `Modules/<Module>/Resources/assets/` (ex. `footer.blade.php` charge `Modules\Wallet\Resources\assets\js\wallet.js`,
  et plusieurs modules chargent leur `app.js` de la même façon : Lesson, Fees, University, BulkPrint…). Conséquences :
  - **Wallet** : `wallet.js` (toggle du moyen de paiement) non chargé → la section bancaire restait `d-none`,
    le champ `#note` du dépôt invisible → specs `portefeuille/*` KO.
  - **Lesson** : `app.js` non chargé → le `<select>` des matières (`#select_subject`, rempli en AJAX) restait vide
    → helper `createLesson` en échec → specs `plan_de_cours/*` KO.
  - **Correctif** : le `.htaccess` laisse désormais passer les extensions statiques (js/mjs/css/map/images/fonts)
    sous `Modules/` et `packages/`, tout en bloquant le code source. Vérifié : `wallet.js`/Lesson `app.js` → 200,
    `WalletController.php`/`composer.json` → 403. **DocumentRoot = racine projet** (le `.htaccess` racine est actif),
    d'où l'impact de ce blocage sur les assets de module.
- **Bibliothèque — recherche DataTable** (`SQLSTATE 42S22 : aramisc_books.category_name inconnu`) :
  `DatatableQueryController::bookListAjax` — la recherche globale Yajra préfixait `category_name`/`subject_name`
  sur `aramisc_books` (colonnes en fait sur les tables jointes). Ajout de `filterColumn` ciblant
  `aramisc_book_categories.category_name` et `aramisc_subjects.subject_name`.
- **Examen global** (`GlobalExamController::examView`, `Undefined array key "code"` → 500) : `$request->only(['code'])`
  puis `$input['code']` sans garde + `$view` indéfini si code ≠ single/multi. Corrigé : `$request->input('code')`,
  retour **422** si absent/invalide. Le spec `global_academics` testait cet endpoint **AJAX** via `cy.visit` →
  réécrit en `cy.request('/return_global_exam_view?code=single')` attendant le JSON `{status, html}`.

Restant : `examination.cy.js` (2 tests) = **`/online-exam` 500** (anomalie 16, contrôleur tronqué) — non traité (plus lourd).

### ✅ Suite Admin 100 % verte — retrait des tests `/online-exam` (27/10/2026)
Sur demande, les 2 tests `/online-exam` et `/online-exam-report` (tous deux **500**, anomalie 16, bug applicatif
non corrigé) ont été **retirés** de `examination.cy.js` (commentaire renvoyant à cette note). La suite admin
(85 specs) est désormais **100 % verte** : 201 passing, 0 failing, 1 skipped. Le bug `/online-exam` lui-même
reste ouvert (non corrigé).

### 👑 Rôle Super admin (role_id 1) — specs d'autorisation (27/10/2026)

**Compte** : `abassi.hicham@gmail.com` (id 2, role_id 1, `is_administrator=yes`). Mot de passe remis à celui des
comptes de test via `php artisan tinker` (`bcrypt`) — ⚠️ **ne pas** le faire via `mysql -e "...$2y$..."` : le shell
(PowerShell/bash) mange les `$` du hash et le tronque. Passer par un fichier .sql ou par tinker (`bcrypt('...')`).
Ajouté à `cypress/fixtures/users.json` (clé `superadmin`, **non versionné**) + commande `cy.loginAsSuperAdmin()`.

**Différence Super admin vs Admin (role 5) — vérifiée dans le code :**
- `userPermission()` (Helper.php:426) : `role_id==1 && is_administrator=='yes'` ⇒ `true` immédiat (accès à tout).
  L'Admin (role 5), bien qu'`is_administrator=yes`, **n'a pas** ce court-circuit (le test porte sur `role_id==1`).
- Middleware `UserRolePermission:50` : `role_id != 1` ⇒ la route doit être dans les permissions assignées ; role 1 passe toujours.
- Dimension **SaaS** : le lien « View as super admin » (`view-as-superadmin` → `superadmin-dashboard`) est conditionné par
  `moduleStatusCheck('Saas')`, **désactivé** ici, et la route `superadmin-dashboard` **n'existe pas**. ⇒ **aucune page
  exclusive au Super admin** dans cette install.
- Branches codées en dur `role_id==1` : congés (voit toutes les demandes), paie (voit tous les paiements), audience notifications.

**⚠️ Finding de sécurité (NON corrigé — documenté sur décision utilisateur) :** le singleton `app('permission')`
(`AppServiceProvider.php:124-134`, commit de 2024) construit `$permissions` mais **ne fait aucun `return`** ⇒ il vaut
**`null`** pour tous. Conséquence : la vérification par-route (`!is_null($permissions) && role_id != 1`) du middleware
et de `userPermission()` est **court-circuitée pour TOUS les rôles**. Sonde Cypress confirmée : le **Driver (0 permission
utile) obtient 200** sur `/global-section`, `/department`, `/bank-account`, `/add-expense`… exactement comme le Super admin.
⇒ L'enforcement des permissions par-rôle est de fait **désactivé à l'échelle de l'app**. Correctif = ajouter
`return $permissions;` ligne 133, mais **risqué** (réactive l'enforcement partout, peut bloquer des accès légitimes si
`assign_permissions` est incomplet, et fausserait les autres suites). À traiter séparément et prudemment.

**Specs créés (`cypress/e2e/superadmin/`, 10 tests verts) :**
- `tableau_de_bord.cy.js` (8) : login role 1 + accès dashboard et écrans de gestion (200). Baseline / garde du chemin role 1.
  N'affirme **pas** de différence 403/200 (impossible, cf. finding ci-dessus).
- `portee_conges.cy.js` (2) : **vraie** différence de comportement. Sème 2 demandes de congé (une « au Driver », une
  « à un autre staff ») via le helper `php()` (tinker), interroge `/approve-leave-request-ajax`
  (`DatatableQueryController@ajaxApproveLeave`) en Super admin puis en Driver, et vérifie que **seul** le Super admin voit
  la demande d'autrui (vue globale role 1) tandis que le Driver ne voit que la sienne. Données supprimées en `after`.
  NB : `aramisc_leave_requests.staff_id` est une **FK vers `users(id)`** (c'est un *user id*, pas un `aramisc_staffs.id`).

### 🏗️ CHANTIER — Réactiver le contrôle d'accès par rôle (préparé 27/10/2026, NON exécuté)

Objectif : corriger le bug `app('permission')` (singleton sans `return`, `AppServiceProvider.php:133`) qui désactive
l'enforcement des permissions par-route pour tous les rôles, **sans casser les accès légitimes**. Big-bang = exclu.

#### Audit de l'existant (base `aramisc01`)
- **973 permissions** ; assignations : Super admin **0** (bypasse, OK), Admin 615, Accountant 220, Teacher 209,
  Receptionist 150, Student 134, Parents 104, Librarian 88, Driver 22.
- **755 routes web admin** gardées par `userRolePermission:<param>`. Le middleware compare `<param>` à la liste des
  `permissions.route` du rôle (`UserRolePermission.php:50`).
- **3 problèmes structurels à régler AVANT d'activer** (sinon 403 pour tous, role 1 excepté) :
  1. **58 params numériques** (`userRolePermission:265`…) : aucune `permissions.route` numérique n'existe → ne résolvent jamais.
  2. **131 params chaîne** (sur 581 distincts) : pas de `permissions.route` correspondante → ne résolvent jamais.
  3. Le `return $permissions;` manquant lui-même.
- **Impact d'un fix brut** (routes actuellement atteintes qui deviendraient 403), par rôle :
  Admin **51**, Teacher 293, Accountant 367, Receptionist 398, Librarian 425, Driver 449 ; Student/Parent **0**
  (panneaux séparés, garde différente → hors périmètre de ce chantier).
  ⚠️ Une grande partie de ces blocages serait **CORRECTE** (un Librarian ne devrait pas atteindre 425 écrans admin).
  Le risque = le **sous-ensemble** de routes dont le rôle a *légitimement* besoin mais qui n'a jamais été assigné.

#### Plan proposé (par phases)
- **Phase 0 — prérequis structurels** : corriger les 58 params numériques + 131 params chaîne orphelins
  (soit créer/mapper les `permissions.route` manquantes, soit corriger les gardes de route). Rendre aussi le
  middleware **tolérant** à un param inconnu (log + allow) pour ne jamais 403 sur une garde mal câblée.
- **Phase 1 — MODE AUDIT (log-only)** : ajouter `return $permissions;` MAIS remplacer temporairement `abort(403)` par
  un **log** (user_id, role_id, route, timestamp) sans bloquer. Déployer, laisser les vrais utilisateurs exercer l'app
  (ou rejouer les suites Cypress de tous les rôles), et **collecter l'ensemble réel** des routes que chaque rôle
  utilise vraiment. C'est ce qui transforme « 425 perdues en théorie » en « N réellement nécessaires ».
- **Phase 2 — réconciliation `assign_permissions`** : à partir des logs Phase 1 (et des menus/`sidebars` comme source
  de vérité), compléter les assignations par rôle via un **seeder idempotent** (versionné), pas à la main en base.
- **Phase 3 — activation + tests** : basculer le middleware en mode bloquant (`abort(403)`), puis valider.

#### Stratégie de test (Cypress)
- **Matrice d'autorisation data-driven** : une spec par rôle lisant deux listes — `allowed` (doit répondre 200) et un
  échantillon `forbidden` (doit répondre 403) — construites depuis les assignations réconciliées. Assertions via
  `cy.request({failOnStatusCode:false})`.
- **Non-régression** : rejouer les suites existantes (admin 85 specs, + student/parent/teacher/staff/superadmin) APRÈS
  activation ; tout échec = soit un accès légitime manquant (à ajouter en Phase 2), soit un test qui s'appuyait sur
  l'absence de contrôle.
- Prévoir un **toggle** (env/config) pour activer/désactiver l'enforcement sans redéployer, le temps de stabiliser.

#### Points d'attention
- Les requêtes **DataTables AJAX** (ex. `*-ajax`) sont des routes séparées, souvent gardées à part : les inclure dans
  la matrice, sinon une page « autorisée » afficherait une table vide/403.
- Ne **pas** confondre avec les autres systèmes de permission présents (`aramisc_role_permissions` + `aramisc_module_links`,
  `sidebars`) : le middleware `userRolePermission` s'appuie sur `assign_permissions` + `permissions` uniquement.
- Role 1 (Super admin) reste bypassé par construction (`role_id != 1`) — ne pas lui assigner de permissions.

#### ✅ Phase 0 réalisée (27/10/2026) — middleware tolérant + toggles, NEUTRE par défaut
Livré (aucun changement de comportement tant que les toggles sont OFF) :
- **`config/access_control.php`** : `enforce` (défaut `false`) et `audit` (défaut `false`), pilotables par env
  `ACCESS_CONTROL_ENFORCE` / `ACCESS_CONTROL_AUDIT` ; `log_channel` (`stack`).
- **`app/Http/Middleware/UserRolePermission.php`** refondu :
  - Contrôle par rôle **gated** par la config. Toggles OFF ⇒ le bloc est ignoré ⇒ `return next` (identique à l'existant).
  - Ne dépend plus du singleton cassé `app('permission')` : calcule lui-même les routes autorisées du rôle
    (`assign_permissions ∩ permissions.route`, cache 60 s) — le singleton d'AppServiceProvider reste **inchangé** (documenté).
  - **Résolution des params numériques** via `permissions.old_id` (ex. `265`→`section`).
  - **Tolérance** : un param non résolu (numérique sans `old_id`, sous-action CRUD orpheline…) ⇒ décision `null`
    ⇒ **jamais 403**, seulement journalisé. Une garde mal câblée ne casse pas l'accès.
  - Super admin (role 1) : bypass conservé. Restriction congés parents (role 3) et garde plan/école : inchangées.
- **Vérifié** : lint PHP 8.4 OK ; config `false/false` ; suite Super admin 10/10 (neutralité) ; logique d'enforcement
  prouvée par réflexion (cas Driver) : `dashboard`→autorisé, `department`/`265`→refusé si enforce, `999999`/`global-exam`→toléré.

**Inventaire Phase 0 (pour Phase 2, listes brutes dans le scratchpad de session) :**
- **Params numériques** : 40 résolus via `old_id`, **11 à mapper manuellement** (147,153,154,155,180,184,193,273,274,275,437 —
  ex. 180→`designation`, 184→`department`).
- **Params chaîne orphelins : 128** — quasi tous des **sous-actions CRUD** (`*-store/-edit/-delete/-delete-modal/-update`
  de donor, expert-teacher, form-download, front-*, home/photo/speech/video-gallery, theme.*) et le module
  **Global Academics** (`global-*`). Décision Phase 2 : soit créer la permission, soit faire hériter la sous-action du parent.

**Prochaine étape (Phase 1)** : passer `ACCESS_CONTROL_AUDIT=true` (sans `enforce`) pour journaliser l'usage réel par rôle,
puis construire le seeder d'assignations (Phase 2). Rien n'est bloquant tant que `enforce` reste `false`.

#### ✅ Phase 1 réalisée (27/10/2026) — collecte d'usage en mode audit
Méthode : canal de log dédié `access_audit` (ajouté à `config/logging.php` → `storage/logs/access_audit.log`),
`ACCESS_CONTROL_AUDIT=true` + `ACCESS_CONTROL_LOG_CHANNEL=access_audit` posés **temporairement** dans `.env.cypress`,
puis rejeu de **toutes** les suites rôles (156 specs). Environnement **restauré** ensuite (`git checkout .env.cypress`,
`config:cache` → web dev `audit=false / enforce=false`, vérifié).

Résultat : **95 entrées** (72 `deny` = permission connue non assignée ; 23 `unresolved` = garde non résolue, tolérée).
Routes exercées par les specs mais **non assignées** (candidates à accorder en Phase 2) :
- **Admin (5)** — 30 deny : bloc **frais** (`fees-master`/`_group`/`_type`/`_discount`, `collect_fees`, `fees_forward`,
  `search_fees_due`/`_payment`), `library_subject*`, `transport-route-edit/-delete`, `vehicle-edit/-delete`,
  `dormitory-list-store`, `exam_schedule*`. Unresolved : `147`, `global-*` (Global Academics), `wallet.approve/reject-payment`,
  `delete-lesson-plan`, `delete-holiday-data`, `delete-supplier`, `subjectMarkSheet`.
- **Accountant (6)** — 11 deny : `add-expense`, `add_income`, `bank-account`, `chart-of-account`, `item-*` (inventaire),
  `profit`, `suppliers`, `staff_directory`. Unresolved : `student_fine_report`.
- **Student (2)** : `student_noticeboard`, `update-my-profile` (+ unresolved `student_study_material`).
- **Parents (3)** : `parent_examination`, `parent_online_examination` (+ unresolved `parent_fees`).
- **Teacher (4)** : `exam_schedule`. **Librarian (8)** : `event`, `notice-list`.
- **Receptionist (7) / Driver (9)** : 0 (leurs specs ne frappent que des routes assignées).

Interprétation : la quasi-totalité des `deny` sont des **assignations légitimes manquantes** (l'Admin doit gérer frais/biblio/transport,
l'Accountant dépenses/revenus/inventaire), pas des accès à refuser. ⚠️ C'est un **minimum reproductible** (couverture des specs) ;
l'usage réel sur le web dev en révélerait d'autres → envisager d'activer l'audit sur `dev.aramisc.com` avant de figer le seeder.

**Prochaine étape (Phase 2)** : seeder idempotent accordant ces routes par rôle (revu avant application) + traitement des
`unresolved` (créer la permission ou corriger la garde). Puis Phase 3 : `enforce=true` + matrice de tests d'autorisation.

#### ✅ Phase 2 réalisée (27/10/2026) — assignations manquantes accordées
**`database/seeders/AccessControlPermissionsSeeder.php`** (idempotent, additif) : accorde dans `assign_permissions`
les routes `deny` légitimes de la Phase 1, par rôle (map `role_id => [routes]`, résolues en `permission_id` au runtime).
Lancé sur `aramisc01` (base web dev + specs) :
```
php artisan db:seed --class=Database\Seeders\AccessControlPermissionsSeeder --force
```
Résultat : **49 lignes insérées** (Admin +31, Accountant +11, Student +2, Parents +2, Teacher +1, Librarian +2).
Totaux `assign_permissions` : Admin 615→646, Accountant 220→231, Student 134→136, Parents 104→106, Teacher 209→210, Librarian 88→90.
Vérifié (réflexion, cache vidé) : les routes auparavant refusées (`fees-master`, `library_subject`, `vehicle-edit`,
`add-expense`, `suppliers`, `profit`…) renvoient désormais `true` (autorisées). Rejouable sans effet (idempotent).

**Reste (hors Phase 2)** : les `unresolved` (params numériques sans `old_id` comme `147`, sous-actions CRUD orphelines,
module Global Academics, `wallet.approve/reject-payment`) — à traiter par création de permission ou correction de garde.
Ils restent **tolérés** (non bloquants). Et la collecte d'usage **web dev** (reportée) affinera le seeder avant Phase 3.

**Prochaine étape (Phase 3, à valider)** : `ACCESS_CONTROL_ENFORCE=true` + matrice de tests d'autorisation (par rôle :
`allowed`→200, échantillon `forbidden`→403) + non-régression des suites existantes.

#### ✅ Collecte d'usage web dev (Chrome) — Admin (27/10/2026)
Audit réactivé temporairement sur `dev.aramisc.com`, connexion **Admin** via l'extension Chrome, extraction des 291 liens
de menu puis balayage en `fetch()` (session réelle). Audit **re-désactivé** ensuite (web dev `audit=false/enforce=false`, vérifié).
Révèle **7 routes Admin légitimes** absentes des specs → ajoutées au seeder (role 5) :
`fees.fees-group`, `fees.fees-type`, `fees.fees-invoice-list`, `fees.bank-payment`, `custom-marksheet-report`,
`student.multi-class-student`, `unassigned_student`. Seeder relancé : +7 (Admin `assign_permissions` 646→653). Vérifié autorisées.

**⚠️ Insight méthodo (important pour la suite)** : l'enforcement étant OFF, le menu latéral **n'est pas filtré par rôle**
(un Accountant voit le même menu qu'un Admin). Donc la collecte web dev est **fiable pour l'Admin** (quasi tout ce qu'il
atteint est légitime) mais **PAS pour les rôles restreints** (impossible de distinguer « légitime » de « ne devrait pas avoir »).
Pour les rôles restreints, s'appuyer sur les **suites Cypress** (qui ne testent que les features propres au rôle) — déjà fait
en Phase 1 — ou sur la définition de menu attendue du rôle, pas sur un balayage du menu non filtré.

Les `unresolved` rencontrés en navigation (`global-exam`, `global-upload-content` = module Global Academics) restent tolérés,
à traiter avec les autres unresolved.

#### ✅ Phase 3 — validation en MODE TEST (27/10/2026)
`ACCESS_CONTROL_ENFORCE=true` + `ACCESS_CONTROL_AUDIT=true` activés **uniquement dans `.env.cypress`** (base de test
`aramisc01`), log d'audit purgé, puis **toutes les suites** relancées (`cypress/e2e/**/*.cy.js`). Le web dev (`.env`) n'a
**jamais** été touché (resté `audit=false/enforce=false`). `.env.cypress` **restauré** après le run (bloc TEMP retiré,
vérifié : plus aucune ligne `ACCESS_CONTROL` ; config cache vidé par le runner).

**Résultat : 321 tests, 320 passants, 1 skippé, 0 échec — `✔ All specs passed!` (1h27).**
- **0 `deny`** : aucune route *légitime* exercée par une suite de rôle n'a reçu de 403 → le seeder Phase 2 (+ complément
  web dev) couvre **exactement** ce dont les rôles ont besoin. L'enforcement ne casse **rien**.
- **23 `unresolved`** (tolérés, jamais 403, seulement journalisés) = les gardes mal câblées déjà cataloguées :
  - module **Global Academics** (`global-exam`, `global-subject`, `global-assign`, `global-assign-subject`,
    `global-assign-subject-create`, `global-exam-type`, `global-upload-content`, `global-examView`, `global_subject`…),
  - `wallet.approve-payment` / `wallet.reject-payment`,
  - sous-actions CRUD orphelines (`delete-lesson-plan`, `delete-supplier`, `delete-holiday-data`,
    `exam_schedule_create`, `global_assign_subject_create`, `subjectMarkSheet`),
  - params chaîne orphelins (`student_fine_report` role 6, `student_study_material` role 2, `parent_fees` role 3),
  - un param numérique sans `old_id` (`147`).

**Conclusion** : l'activation `enforce=true` est **sûre** vis-à-vis des parcours couverts par les suites. Ne reste, avant
une activation réelle sur le web dev, que (a) le traitement optionnel des `unresolved` (créer la permission ou corriger la
garde — non bloquant) et (b) la décision d'activer `enforce` en prod/web dev, **à valider avec l'utilisateur**.

#### ✅ Traitement des `unresolved` (28/10/2026) — seeder `AccessControlUnresolvedSeeder`
Les 23 gardes `unresolved` de la Phase 3 ont été **rendues enforce-ables** : pour chacune, création de la permission
feuille manquante (`database/seeders/AccessControlUnresolvedSeeder.php`, modèle calqué sur `fees_group_store` :
`type=3, is_menu=0, menu_status=0, parent_route=<parent>` → **n'apparaît pas dans le sidebar** et laisse `hasPermission()`
neutre) puis assignation au(x) rôle(s) qui l'exerce(nt) réellement (relevés en audit). Idempotent, data-only.
- **38 permissions** créées (module `access_control`), **0 en menu** (aucune pollution sidebar, vérifié).
- **38 assignations** : Admin (5) = 35 ; Accountant (6) = 1 (`student_fine_report`) ; Student (2) = 1
  (`student_study_material`) ; Parents (3) = 1 (`parent_fees`).
- **Groupe A (11)** : `exam_schedule_create`→`exam_schedule`, `delete-lesson-plan`→`lesson-plan`,
  `delete-supplier`→`suppliers`, `delete-holiday-data`→`holiday`, `search-account` (garde numérique `147` résolue via
  `old_id=147`)→`accounts`, `subjectMarkSheet`, `wallet.approve-payment`/`wallet.reject-payment`→`wallet`,
  `student_fine_report`, `student_study_material`→`study_material`, `parent_fees`.
- **Groupe B — module Global Academics (27 gardes, 0 permission en base)** : le module n'avait **jamais** été branché au
  système de permissions. Décision utilisateur : tout créer + assigner à l'Admin (role 5). Les 27 permissions `global-*`
  sont créées (non-menu) et assignées à l'Admin.

**Revalidation Phase 3 mode test (28/10/2026)** : `enforce=true` + `audit=true` (base de test, `.env.cypress` restauré
après), cache applicatif vidé, toutes les suites relancées (1h21). **Résultat : `✔ All specs passed!`, 320/320, 0 échec ;
log d'audit VIDE → `0 deny, 0 unresolved`.** Les 23 gardes sont désormais résolues (connues + assignées) et **aucun
conflit inter-rôle** n'est apparu (une permission désormais « connue » mais non assignée à un autre rôle l'aurait refusé).
L'enforcement est propre : plus aucune garde tolérée par défaut sur les parcours couverts.

**Reste (à valider avec l'utilisateur)** : l'activation **réelle** de `enforce=true` sur le web dev / prod (`.env`),
idéalement après un passage manuel de quelques parcours métier réels par rôle (les suites ne couvrent pas 100 % des écrans).

#### ✅ Specs d'autorisation data-driven (28/10/2026)
Pour couvrir *systématiquement* l'enforcement (au-delà des specs « consultation » triées à la main), ajout de :
- **Commande** `php artisan permissions:reachable-routes` (`app/Console/Commands/GenerateReachableRoutes.php`) : calcule,
  par rôle, les routes **GET sans paramètre gardées** par `userRolePermission` que le rôle est **autorisé** à atteindre
  (reproduit la décision du middleware), en **excluant les routes à effet de bord** (logout, suppression, envoi, backup,
  export/pdf…). Écrit `cypress/fixtures/reachable_routes.json`. À relancer après toute modif des permissions.
- **Spec** `cypress/e2e/autorisation/acces_routes.cy.js` : lit la fixture et, par rôle (login via `cy.loginAs<Role>`),
  vérifie pour chaque route que le statut **≠ 403** (seul le 403 = échec ; 200/302 OK ; 500 = bug applicatif, pas un refus).
  Tourne toujours (routes destructives déjà exclues → GET en lecture, rejouables) ; prend tout son sens sous `enforce=true`.
- **286** routes gardées probables ; atteignables/rôle : Admin 241, Teacher 72, Accountant 43, Student 24, Receptionist 14,
  Librarian 13, Parent 7, Driver 0 (les pages Driver ne sont pas gardées par `userRolePermission`).

**Validation (28/10/2026)** : run sous `enforce=true` (base de test, `.env.cypress` restauré après). **Résultat :
`✔ All specs passed!`, 414/414, 0 échec ; log d'audit VIDE → 0 `deny`.** Toute route déclarée autorisée est bien
atteignable sous enforce. **Complémentaire** de la collecte audit manuelle : ces specs valident « autorisé → atteignable »,
mais ne découvrent pas un écran légitime *non encore assigné* (ça, seule la navigation réelle en mode audit le révèle).

**État `.env` au 28/10/2026** : web dev en **mode audit-only** (`ACCESS_CONTROL_AUDIT=true`, `ENFORCE=false`) pour la
collecte d'usage manuelle en cours ; `.env.cypress` neutre. Backup de `.env` : `scratchpad/env.webdev.backup`.
