# Analyse de Couverture des Tests Cypress (CRUD) sur le Menu Aramisc

## 📊 Résumé Exécutif (mis à jour le 07/10/2026)
Passe « CRUD complet » sur toutes les pages admin qui le permettent (hors Examen en ligne — bloqué, anomalie 16 — et Frontend CMS — hors périmètre) :
- Chaque page avec ajout / modification / suppression a un spec dédié, rangé par rubrique du menu (`cypress/e2e/admin/<rubrique>/`).
- Les pages sans CRUD (rapports, impressions, saisies de présence, génération de paie, envoi SMS / e-mail) restent en visite / recherche (section 🟡).
- Anomalies trouvées et correctifs appliqués : voir `cypress/NOTES_PASSATION.md` (anomalies 1 à 51).

---

## 🟢 1. Liens avec Test CRUD Complet (Création, Lecture, Modification, Suppression)

### 🏛️ Universitaires (`cypress/e2e/admin/academics.cy.js`, `universitaires/`)
- ✅ **Section**, **Classe**, **Matières**, **Salle de classe** — CRUD complet (`academics.cy.js`)
- ✅ **Affectation de matières / professeur principal**, **Emploi du temps** — sécurisés (`academics.cy.js`)
- ✅ **Configuration des matières optionnelles** (`/optional-subject-setup`) — CRUD complet (`universitaires/matiere_optionnelle.cy.js`)

### 🚌 Transports (`cypress/e2e/admin/transport.cy.js`)
- ✅ **Routes**, **Véhicule**, **Affecter un véhicule** — CRUD complet

### 🏢 Dortoir (`cypress/e2e/admin/dormitory.cy.js`)
- ✅ **Type de chambre**, **Dortoir**, **Chambres** — CRUD complet

### 📚 Bibliothèque (`cypress/e2e/admin/library.cy.js`)
- ✅ **Catégories de livres**, **Sujet**, **Livres**, **Membre / emprunt / retour** — CRUD complet

### ⚙️ Paramètres (`cypress/e2e/admin/settings.cy.js`, `parametres/`)
- ✅ **Année académique**, **Rôle** — CRUD complet sécurisé (`settings.cy.js`)
- ✅ **Devise** (`/manage-currency`) — CRUD complet (`parametres/devise.cy.js`)

### 📥 Centre de Téléchargement (`cypress/e2e/admin/centre_telechargement/`)
- ✅ **Type de contenu** (`/download-center/content-type`) — Full CRUD (`type_de_contenu.cy.js`)
- ✅ **Liste de contenu** (`/download-center/content-list`) — Full CRUD (`liste_de_contenu.cy.js`)
- ✅ **Liste de vidéos** (`/download-center/video-list`) — Full CRUD (`liste_de_videos.cy.js`)
- ✅ **Liste de contenu partagé** (`/download-center/content-share-list`) — Création, Lecture, Suppression + lien public (`liste_de_contenu_partage.cy.js`)

### 📖 Plan de Cours (`cypress/e2e/admin/plan_de_cours/`)
- ✅ **Leçon** (`/lesson`) — Full CRUD (`lecon.cy.js`)
- ✅ **Sujet / Topic** (`/lesson/topic`) — Full CRUD (`sujet.cy.js`)
- ✅ **Aperçu du sujet** (`/lesson/topic-overview`) — Affichage + filtres (`apercu_du_sujet.cy.js`)
- ✅ **Plan de cours** (`/lesson/lesson-plan`) — Full CRUD (`plan_de_cours.cy.js`)
- ✅ **Présentation du plan de leçon** (`/lesson/lessonPlan-overiew`) — Affichage + filtres (`presentation_du_plan_de_lecon.cy.js`)

### 🖨️ Imprimé en Vrac (`cypress/e2e/admin/imprime_en_vrac/`)
- ✅ **Carte d'identité vrac** (`/bulkprint/student-id-card-bulk-print`) — Validation + aperçu d'impression, modèle de carte créé puis supprimé (`carte_identite_vrac.cy.js`)
- ✅ **Certificat vrac** (`/bulkprint/certificate-bulk-print`) — Validation + aperçu d'impression (`certificat_vrac.cy.js`)
- ✅ **Paie vrac** (`/bulkprint/payroll-bulk-print`) — Période sans paie + aperçu d'impression (`paie_vrac.cy.js`)
- ✅ **Frais de facturation vrac** (`/bulkprint/fees-invoice-bulk-print`) — Aperçu d'impression (`frais_facturation_vrac.cy.js`)

### 👛 Portefeuille (`cypress/e2e/admin/portefeuille/`)
- ✅ **Dépôt en attente** (`/wallet/pending-diposit`) — Dépôt élève → liste en attente (`depot_approuve.cy.js`, `depot_rejete.cy.js`)
- ✅ **Approuver le dépôt** (`/wallet/approve-diposit`) — Approbation + solde (`depot_approuve.cy.js`)
- ✅ **Rejeter le dépôt** (`/wallet/reject-diposit`) — Rejet avec note + solde inchangé (`depot_rejete.cy.js`)
- ✅ **Transaction de portefeuille** (`/wallet/wallet-transaction`) — Transaction approuvée listée (`depot_approuve.cy.js`)
- ✅ **Demande de remboursement** (`/wallet/wallet-refund-request`) — Demande élève + approbation admin (`demande_de_remboursement.cy.js`) — rejet non testé (envoie un vrai e-mail)

### 💬 Chat (`cypress/e2e/admin/chat/`)
- ✅ **Boîte de discussion** (`/chat/open`) — Envoi admin → lecture élève → suppression (`boite_de_discussion.cy.js`)
- ✅ **Invitation** (`/chat/invitation/index`) — Connexion via recherche « Démarrer » (`invitation.cy.js`) — demande en attente / accepter / rejeter inaccessibles (invitation non requise dans les réglages)
- ✅ **Utilisateur bloqué** (`/chat/users/blocked`) — Blocage → liste → déblocage (`utilisateur_bloque.cy.js`)

### 🖥️ Examen en Ligne (`cypress/e2e/admin/examen_en_ligne/`)
- ✅ **Groupe de questions** (`/question-group`) — CRUD complet (`groupe_de_questions.cy.js`)
- ✅ **Banque de questions** (`/question-bank`) — CRUD complet Vrai/Faux, Texte à trou, Choix multiple ; bonne réponse vérifiée en base (`banque_de_questions.cy.js`)

### 🪑 Plan d'Examen (`cypress/e2e/admin/plan_examen/`)
- ✅ **Carte d'admission** (`/examplan/admitcard`) — Validation → recherche → génération pour 2 élèves → aperçu + contrôle en base (`carte_admission.cy.js`)
- ✅ **Plan de siège** (`/examplan/seatplan`) — Validation → recherche → génération → aperçu → relecture (élèves pré-cochés) (`plan_de_siege.cy.js`)

### ⚙️ Champs personnalisés (`cypress/e2e/admin/champs_personnalises/`)
- ✅ **Inscription des étudiants** (`/student-registration-custom-field`) — Texte 2–50 obligatoire → modifié en liste déroulante (2 valeurs) → supprimé, contrôles en base (`inscription_etudiants.cy.js`)
- ✅ **Inscription au personnel** (`/staff-reg-custom-field`) — Même scénario (`inscription_personnel.cy.js`)

### 🗂️ Administration (`cypress/e2e/admin/administration/`)
- ✅ **Requête d'admission** (`/admission-query`) — CRUD complet + suivi (ajout / suppression), date du suivi contrôlée en base (`requete_admission.cy.js`)
- ✅ **Carnet de visiteurs** (`/visitor`) — CRUD complet (`visiteurs.cy.js`)
- ✅ **Plainte** (`/complaint`) — CRUD complet + fenêtre de détail (`plaintes.cy.js`)
- ✅ **Réception postale** (`/postal-receive`) — CRUD complet (`courrier_recu.cy.js`)
- ✅ **Envoi postal** (`/postal-dispatch`) — CRUD complet (`courrier_envoye.cy.js`)
- ✅ **Journal des appels** (`/phone-call`) — CRUD complet, type d'appel contrôlé en base (`journal_appels.cy.js`)
- ✅ **Configuration d'admin** (`/setup-admin`) — CRUD complet d'un type de plainte (`configuration_admin.cy.js`)

- ✅ **Carte d'identité** (`/create-id-card`, `/student-id-card`) — modèle créé → modifié → supprimé (`carte_identite.cy.js`)
- ✅ **Certificat** (`/student-certificate`) — modèle créé → modifié → supprimé (`certificat.cy.js`)

### 🎓 Info-étudiant (`cypress/e2e/admin/info_etudiant/`)
- ✅ **Ajouter un étudiant / Liste des étudiants / Étudiants désactivés** — ajout (élève + tuteur) → liste → modification → désactivation → suppression définitive, contrôles en base, notifications / e-mail / SMS coupés (`eleve.cy.js`)
- ✅ **Catégorie d'étudiant** (`/student-category`) — CRUD complet (`categorie_etudiant.cy.js`)
- ✅ **Groupe d'étudiants** (`/student-group`) — CRUD complet (`groupe_etudiant.cy.js`)

### 👥 Ressources humaines & Congés (`cypress/e2e/admin/rh/`)
- ✅ **Ajouter du personnel / Répertoire du personnel** — ajout → répertoire → modification → suppression, contrôles en base, e-mail / SMS coupés (`personnel.cy.js`)
- ✅ **Département** (`/department`) — CRUD complet (`departement.cy.js`)
- ✅ **Désignation** (`/designation`) — CRUD complet (`poste.cy.js`)
- ✅ **Type de congé** (`/leave-type`) — CRUD complet (`type_de_conge.cy.js`)
- ✅ **Définir les congés** (`/leave-define`) — création (teacher1) → modification → ajout de jours → suppression (`definition_conge.cy.js`)
- ✅ **Demander un congé / Congés en attente / Approuver** — demande → modification → approbation → suppression (`demande_conge.cy.js`)

### 💳 Comptes (`cypress/e2e/admin/comptes/`)
- ✅ **Compte bancaire** (`/bank-account`) — création → transactions → suppression ; pas de modification dans l'interface (`compte_bancaire.cy.js`)
- ✅ **Transfert de fonds** (`/fund-transfer`) — transfert Bank → Bank, soldes contrôlés ; ni modification ni suppression dans l'interface (`transfert_fonds.cy.js`)
- ✅ **Ajouter un revenu** (`/add-income`) — CRUD complet payé par banque, solde du compte contrôlé à chaque étape (`revenu.cy.js`, après correctif anomalie 35)
- ✅ **Ajouter une dépense** (`/add-expense`) — idem (`depense.cy.js`, après correctif anomalie 36)

- ✅ **Plan comptable** (`/chart-of-account`) — CRUD complet (`plan_comptable.cy.js`, remplace l'ancien bloc de accounts.cy.js)

### 💰 Frais (`cypress/e2e/admin/frais/`)
- ✅ **Groupe de frais** (`/fees-group`) — CRUD complet (`groupe_frais.cy.js`)
- ✅ **Type de frais** (`/fees-type`) — CRUD complet (`type_frais.cy.js`)
- ✅ **Réduction des frais** (`/fees-discount`) — CRUD complet (`reduction.cy.js`)
- ✅ **Frais principaux** (`/fees-master`, frais directs) — création (Class2 / sec2, échéance, affectation aux 5 élèves) → modification → suppression complète (`frais_principaux.cy.js`, après correctifs anomalies 37, 38)

### 📦 Inventaire (`cypress/e2e/admin/inventaire/`)
- ✅ **Magasin d'articles** (`/item-store`) — CRUD complet (`magasin.cy.js`)
- ✅ **Fournisseur** (`/suppliers`) — CRUD complet (`fournisseur.cy.js`)
- ✅ **Liste d'articles** (`/item-list`) — CRUD complet (`article.cy.js`)
- ✅ **Recevoir des articles / Liste des réceptions** — réception → modification → suppression, stock et dépense contrôlés (`reception.cy.js`, après correctif anomalie 42)
- ✅ **Vendre des articles / Liste des ventes** — vente → modification → suppression, stock et revenu contrôlés (`vente.cy.js`, après correctifs anomalies 42, 44)
- ✅ **Émettre un article** (`/item-issue`) — émission → retour, stock contrôlé ; pas de modification / suppression dans l'interface (`emission.cy.js`)
- ✅ **Catégorie d'articles** (`/item-category`) — CRUD complet (`categorie_article.cy.js`, remplace inventory.cy.js)

### 📝 Examen (`cypress/e2e/admin/examen/`)
- ✅ **Type d'examen** (`/exam-type`) — CRUD complet, examen moyen (`type_examen.cy.js`)
- ✅ **Barème de notes** (`/marks-grade`) — CRUD complet (`bareme_notes.cy.js`)
- ✅ **Configuration d'examen** (`/exam`, mode multiple) — création (Class1 / Math, répartition) → modification → suppression (`configuration_examen.cy.js`)
- ✅ **Calendrier d'examen** (`/exam-schedule-create`, `/exam-schedule`) — épreuve créée → lue → modifiée → supprimée (`planning_examen.cy.js`, après correctif anomalie 47)

### 📚 Devoirs (`cypress/e2e/admin/devoirs/`)
- ✅ **Ajouter un devoir / Liste des devoirs** — création (Class1 / sec1 / Math) → liste → modification → suppression, notification coupée (`devoir.cy.js`)

### 📢 Communiquer (`cypress/e2e/admin/communiquer/`)
- ✅ **Panneau d'avis** (`/add-notice`, `/notice-list`) — CRUD complet, notification coupée (`avis.cy.js`)
- ✅ **Événement** (`/event`) — CRUD complet, notification coupée (`evenement.cy.js`)
- ✅ **Jour férié** (`/holiday`) — CRUD complet (`jour_ferie.cy.js`)


---

## 🟡 2. Liens avec Couverture Partielle (Visite / Création Seule / Formulaire)

Ces liens ont un test Cypress qui visite la page et/ou effectue une création ou recherche, mais le cycle **Update/Delete** n'est pas testé :

- **Info-étudiant** : Présence des étudiants (saisie / rapport, pas de CRUD).
- **Ressources humaines** : Paie, Présence du personnel (génération / saisie, pas de CRUD).
- **Communiquer** : Envoyer SMS / e-mail et journal (envoi réel, pas de CRUD — visites seulement).
- **Paramètres généraux** : Paramètres généraux, permissions, préchargeur, signatures d'examen, génération des cartes et certificats (réglages / génération, pas de CRUD).

---

## 🔴 3. Liens Non Testés (Restants à créer)

Ces sous-menus ne possèdent actuellement **aucun test Cypress** dans la suite :

### 📝 Examen en Ligne & Plan d'Examen
- ⛔ Examen en ligne (`/online-exam`) — **bloqué** : erreur 500, contrôleur admin tronqué (voir NOTES_PASSATION, anomalie 16)

### 🌐 Frontend CMS & Autres — ⏸ hors périmètre (front non utilisé pour le moment)
- ❌ Slider accueil, PageBuilder, Galerie photos/vidéos, Nouvelles, Témoignages, Messages contact, Pages.
