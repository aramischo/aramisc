<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Modèles de notification (email, SMS, web, application) traduits en français.
 * Remplacement phrase par phrase : un modèle déjà personnalisé reste tel quel.
 */
return new class extends Migration
{
    const SENTENCES = [
        'For any query, please contact with admin.' => "Pour toute question, contactez l'administration.",
        'A Assignment: [assignment] has been created for Class: [class], Section: [section], Subject: [subject].' => 'Un travail à rendre : [assignment] a été créé pour la classe : [class], section : [section], matière : [subject].',
        "A Dormitory: [dormitory], Room [room] has been assigned for your Child: [student_name]'s accomodation." => "L'internat : [dormitory], chambre [room], a été attribué à votre enfant : [student_name].",
        'A Dormitory: [dormitory], Room [room] has been assigned for your accomodation.' => "L'internat : [dormitory], chambre [room], vous a été attribué.",
        'A Student: [student_name] has Issued/Returned the Book: [book] at [date].' => "L'élève [student_name] a emprunté ou rendu le livre : [book] le [date].",
        'A Student: [student_name] have a fees payment due for the Fees: [fees].' => "L'élève [student_name] a un paiement en attente pour les frais : [fees].",
        'A Student: [student_name] have been assigned to multiple classes [class]([section]), [class]([section]).' => "L'élève [student_name] a été inscrit dans plusieurs classes : [class]([section]), [class]([section]).",
        "A Student: [student_name]'s account has been Enabled/Disabled." => "Le compte de l'élève [student_name] a été activé ou désactivé.",
        'A deposit refund from [student_name]/[parent_name] for Amount: [amount] has been requested.' => 'Un remboursement de dépôt de [student_name]/[parent_name] d\'un montant de [amount] a été demandé.',
        'A deposit request from [student_name]/[parent_name] for Amount: [amount] has been approved.' => 'La demande de dépôt de [student_name]/[parent_name] d\'un montant de [amount] a été approuvée.',
        'A deposit request from [student_name]/[parent_name] for Amount: [amount] has been rejected.' => 'La demande de dépôt de [student_name]/[parent_name] d\'un montant de [amount] a été refusée.',
        'A fees payment was successful for the Fees: [fees] of the Student: [student_name].' => "Le paiement des frais : [fees] de l'élève [student_name] a été effectué.",
        'A fund transfer of Amount: [amount] was successful.' => "Un transfert d'un montant de [amount] a été effectué.",
        'A new Fees: [fees] has been assigned for you.' => 'De nouveaux frais : [fees] vous ont été attribués.',
        'A new Fees: [fees] has been assigned for your child [student_name].' => 'De nouveaux frais : [fees] ont été attribués à votre enfant [student_name].',
        'A new Lesson Plan: [lesson_plan] has been created for Class: [class], Section: [section], Subject: [subject].' => 'Un nouveau plan de cours : [lesson_plan] a été créé pour la classe : [class], section : [section], matière : [subject].',
        'A new Online Exam has been published for Subject: [subject].' => 'Un nouvel examen en ligne a été publié pour la matière : [subject].',
        'A new Other Downloads: [other_downloads] has been created for Class: [class], Section: [section], Subject: [subject].' => 'Un nouveau document : [other_downloads] a été ajouté pour la classe : [class], section : [section], matière : [subject].',
        'A new Syllabus: [syllabus] content has been created for Class: [class], Section: [section], Subject: [subject].' => 'Un nouveau contenu de programme : [syllabus] a été ajouté pour la classe : [class], section : [section], matière : [subject].',
        'A subject [subject] has been assigned to the Teacher: [teacher_name] for you child [student_name].' => "La matière [subject] a été confiée à l'enseignant : [teacher_name] pour votre enfant [student_name].",
        'A subject [subject] has been assigned to the Teacher: [teacher_name].' => "La matière [subject] a été confiée à l'enseignant : [teacher_name].",
        'A subject [subject] has been assigned to you.' => 'La matière [subject] vous a été confiée.',
        "A wallet transaction from [student_name]/[parent_name]'s account for Amount: [amount] was complete." => "Une opération de portefeuille du compte de [student_name]/[parent_name] d'un montant de [amount] a été effectuée.",
        'An Item: [item] was recieved grand total: [grand_total], total: [total_paid], item: [item], qty: [quantity]' => "L'article : [item] a été reçu. Total général : [grand_total], payé : [total_paid], article : [item], quantité : [quantity]",
        'An Item: [item] was recieved grand total:[grand_total], total: [total_paid], item: [item], qty: [quantity]' => "L'article : [item] a été reçu. Total général : [grand_total], payé : [total_paid], article : [item], quantité : [quantity]",
        'An Item: [item] was sold total: [total_paid], qty: [quantity]' => "L'article : [item] a été vendu. Total : [total_paid], quantité : [quantity]",
        'Check out the details.' => 'Consultez les détails.',
        "The Online Exam: [online_exam]'s result has been published for Subject: [subject]." => "Le résultat de l'examen en ligne : [online_exam] a été publié pour la matière : [subject].",
        'The Staff Attendance for today has been generated.' => 'Les présences du personnel du jour ont été générées.',
        'The Staff Payroll for this month has been generated.' => 'La paie du personnel du mois a été générée.',
        'The Student: [student_name] has been promoted to Class: [class], Section: [section].' => "L'élève [student_name] passe en classe : [class], section : [section].",
        "There's a new successful Student Admission." => 'Une nouvelle inscription d\'élève a été enregistrée.',
        'This Class: [class], Section: [section] has been assigned to the Teacher: [teacher_name] for your child [student_name].' => "La classe : [class], section : [section] de votre enfant [student_name] a été confiée à l'enseignant : [teacher_name].",
        'This Class: [class], Section: [section] has been assigned to the Teacher: [teacher_name].' => "La classe : [class], section : [section] a été confiée à l'enseignant : [teacher_name].",
        'Vehicle No: [vehicle_no] has been assigned to Route: [route] for transport purpose.' => "Le véhicule n° [vehicle_no] a été affecté à l'itinéraire : [route].",
        'You have Issued/Returned the Book: [book] at [date].' => 'Vous avez emprunté ou rendu le livre : [book] le [date].',
        'You have a fees payment due for the Fees: [fees].' => 'Vous avez un paiement en attente pour les frais : [fees].',
        'You have a new Event: [event].' => 'Nouvel événement : [event].',
        'You have a new Exam Schedule: [exam_schedule] for Subject: [subject].' => "Nouveau calendrier d'examen : [exam_schedule] pour la matière : [subject].",
        'You have a new Homework for Subject: [subject].' => 'Nouveau devoir pour la matière : [subject].',
        'You have a new Notice: [notice].' => 'Nouvelle annonce : [notice].',
        'You have a new message from a visitor.' => "Vous avez reçu un nouveau message d'un visiteur.",
        'You have applied for a leave for your child [student_name] from [from_date] to [to_date].' => 'Vous avez demandé un congé pour votre enfant [student_name] du [from_date] au [to_date].',
        'You have applied for a leave from [from_date] to [to_date].' => 'Vous avez demandé un congé du [from_date] au [to_date].',
        'You have been assigned to Class: [class], Section: [section].' => 'Vous avez été affecté à la classe : [class], section : [section].',
        'You have been assigned to multiple classes [class]([section]), [class]([section]).' => 'Vous avez été inscrit dans plusieurs classes : [class]([section]), [class]([section]).',
        'You have been promoted to Class: [class], Section: [section].' => 'Vous passez en classe : [class], section : [section].',
        'You have been registered as a library member today.' => "Vous êtes inscrit à la bibliothèque depuis aujourd'hui.",
        'Your account has been Enabled/Disabled.' => 'Votre compte a été activé ou désactivé.',
        'Your admission was successful.' => 'Votre inscription a été enregistrée.',
        'Your admit card has been generated.' => "Votre convocation d'examen a été générée.",
        'Your attendance was listed [attendance_type] today for Class: [class], Section: [section].' => "Votre présence d'aujourd'hui a été notée [attendance_type] pour la classe : [class], section : [section].",
        'Your attendance was listed [attendance_type] today for Subject: [subject].' => "Votre présence d'aujourd'hui a été notée [attendance_type] pour la matière : [subject].",
        'Your child [student_name] has a new published Online Exam for Subject: [subject].' => 'Un nouvel examen en ligne a été publié pour votre enfant [student_name], matière : [subject].',
        'Your child [student_name] has been registered as a library member today.' => "Votre enfant [student_name] est inscrit à la bibliothèque depuis aujourd'hui.",
        'Your child [student_name] have a fees payment due for the Fees: [fees].' => 'Votre enfant [student_name] a un paiement en attente pour les frais : [fees].',
        'Your child [student_name] have a new Exam Schedule: [exam_schedule] for Subject: [subject].' => "Votre enfant [student_name] a un nouveau calendrier d'examen : [exam_schedule] pour la matière : [subject].",
        'Your child [student_name] have a new Homework for Subject: [subject].' => 'Votre enfant [student_name] a un nouveau devoir pour la matière : [subject].',
        "Your child [student_name]'s Online Exam: [online_exam] result has been published for Subject: [subject]." => "Le résultat de l'examen en ligne : [online_exam] de votre enfant [student_name] a été publié pour la matière : [subject].",
        "Your child [student_name]'s admit card has been generated." => "La convocation d'examen de votre enfant [student_name] a été générée.",
        "Your child [student_name]'s exam attendance for Subject: [subject] has been generated." => "La présence à l'examen de votre enfant [student_name] pour la matière : [subject] a été enregistrée.",
        "Your child [student_name]'s leave from [from_date] to [to_date] has been approved." => 'Le congé de votre enfant [student_name] du [from_date] au [to_date] a été accepté.',
        "Your child [student_name]'s leave from [from_date] to [to_date] has been declined." => 'Le congé de votre enfant [student_name] du [from_date] au [to_date] a été refusé.',
        "Your child [student_name]'s seat plan has been generated." => "Le plan de placement d'examen de votre enfant [student_name] a été généré.",
        'Your child has Issued/Returned the Book: [book] at [date].' => 'Votre enfant a emprunté ou rendu le livre : [book] le [date].',
        'Your child has been promoted to Class: [class], Section: [section].' => 'Votre enfant passe en classe : [class], section : [section].',
        'Your child have been assigned to multiple classes [class]([section]), [class]([section]).' => 'Votre enfant a été inscrit dans plusieurs classes : [class]([section]), [class]([section]).',
        "Your child's account has been Enabled/Disabled." => 'Le compte de votre enfant a été activé ou désactivé.',
        "Your child's admission was successful." => "L'inscription de votre enfant a été enregistrée.",
        "Your child's attendance was listed [attendance_type] today for Class: [class], Section: [section]." => "La présence de votre enfant aujourd'hui a été notée [attendance_type] pour la classe : [class], section : [section].",
        "Your child's attendance was listed [attendance_type] today for Subject: [subject]." => "La présence de votre enfant aujourd'hui a été notée [attendance_type] pour la matière : [subject].",
        'Your deposit refund request for Amount: [amount] has been accepted.' => "Votre demande de remboursement d'un montant de [amount] a été acceptée.",
        'Your deposit request for Amount: [amount] has been approved.' => "Votre demande de dépôt d'un montant de [amount] a été approuvée.",
        'Your deposit request for Amount: [amount] has been rejected.' => "Votre demande de dépôt d'un montant de [amount] a été refusée.",
        'Your exam attendance for Subject: [subject] has been generated.' => "Votre présence à l'examen pour la matière : [subject] a été enregistrée.",
        'Your fees payment was successful for the Fees: [fees] of your child [student_name].' => 'Le paiement des frais : [fees] de votre enfant [student_name] a été effectué.',
        'Your fees payment was successful for the Fees: [fees].' => 'Le paiement des frais : [fees] a été effectué.',
        'Your leave from [from_date] to [to_date] has been approved.' => 'Votre congé du [from_date] au [to_date] a été accepté.',
        'Your leave from [from_date] to [to_date] has been declined.' => 'Votre congé du [from_date] au [to_date] a été refusé.',
        'Your seat plan has been generated.' => "Votre plan de placement d'examen a été généré.",
        'Your wallet transaction for Amount: [amount] was successful.' => "Votre opération de portefeuille d'un montant de [amount] a été effectuée.",
        '[name] has applied for a leave from [from_date] to [to_date].' => '[name] a demandé un congé du [from_date] au [to_date].',
        "[name]'s leave request from [from_date] to [to_date] has been approved." => 'La demande de congé de [name] du [from_date] au [to_date] a été acceptée.',
        "[name]'s leave request from [from_date] to [to_date] has been declined." => 'La demande de congé de [name] du [from_date] au [to_date] a été refusée.',
    ];

    const SUBJECTS = [
        'Approve Deposit.' => 'Dépôt approuvé',
        'Assign Dormitory.' => 'Attribution d\'internat',
        'Assign Vehicle.' => 'Affectation de véhicule',
        'Assigned Classes For Teachers' => 'Classes confiées aux enseignants',
        'Contact Us' => 'Nous contacter',
        'Enable/Disable Student.' => 'Activation ou désactivation d\'un élève',
        'Exam Admit Card.' => 'Convocation d\'examen',
        'Exam Attendance.' => 'Présence à l\'examen',
        'Exam Seat Plan.' => 'Plan de placement d\'examen',
        'Fees Reminder.' => 'Rappel de frais',
        'Fund Transfer.' => 'Transfert de fonds',
        'Issue/Return Book.' => 'Emprunt ou retour de livre',
        'Item Recieved.' => 'Article reçu',
        'Item Sold.' => 'Article vendu',
        'Leave Apply.' => 'Demande de congé',
        'Leave Approved.' => 'Congé accepté',
        'Leave Declined.' => 'Congé refusé',
        'Multi class assigned.' => 'Inscription dans plusieurs classes',
        'New Event.' => 'Nouvel événement',
        'New Exam Schedule.' => 'Nouveau calendrier d\'examen',
        'New Fees Assigned.' => 'Nouveaux frais',
        'New Fees Payment.' => 'Nouveau paiement de frais',
        'New Homework.' => 'Nouveau devoir',
        'New Library Member.' => 'Nouvel inscrit à la bibliothèque',
        'New Notice.' => 'Nouvelle annonce',
        'New Student Admission.' => 'Nouvelle inscription',
        'New assigned subject.' => 'Nouvelle matière confiée',
        'New assignment.' => 'Nouveau travail à rendre',
        'New downloadables.' => 'Nouveau document',
        'New lesson plan.' => 'Nouveau plan de cours',
        'New syllabus content.' => 'Nouveau contenu de programme',
        'Online Exam Published.' => 'Examen en ligne publié',
        'Online Exam Result.' => 'Résultat d\'examen en ligne',
        'Refund Deposit.' => 'Remboursement de dépôt',
        'Reject Deposit.' => 'Dépôt refusé',
        'Staff Attendance.' => 'Présences du personnel',
        'Staff Payroll.' => 'Paie du personnel',
        'Student Attendance.' => 'Présences des élèves',
        'Student Promotion.' => 'Passage en classe supérieure',
        'Subject Wise Attendance.' => 'Présences par matière',
        'Wallet Add.' => 'Rechargement du portefeuille',
    ];

    public function up(): void
    {
        $this->translate(self::SENTENCES, self::SUBJECTS, true);
    }

    public function down(): void
    {
        $this->translate(array_flip(self::SENTENCES), array_flip(self::SUBJECTS), false);
    }

    private function translate(array $sentences, array $subjects, bool $toFrench): void
    {
        foreach (DB::table('aramisc_notification_settings')->get(['id', 'template', 'subject']) as $row) {
            $template = json_decode((string) $row->template, true);
            if (is_array($template)) {
                foreach ($template as $role => $channels) {
                    foreach ((array) $channels as $channel => $text) {
                        if (!is_string($text)) {
                            continue;
                        }
                        // Indentation des modèles d'origine (retour à la ligne + espaces) ramenée à un seul retour à la ligne
                        $text = preg_replace('/,\s*\n\s+/', ",\n", $text);
                        $text = preg_replace('/[ \t]{2,}/', ' ', $text);
                        $text = $toFrench
                            ? preg_replace('/^Dear (\[\w+\]),/', 'Bonjour $1,', $text)
                            : preg_replace('/^Bonjour (\[\w+\]),/', 'Dear $1,', $text);
                        $template[$role][$channel] = strtr($text, $sentences);
                    }
                }
            }

            $subject = json_decode((string) $row->subject, true);
            if (is_array($subject)) {
                foreach ($subject as $role => $text) {
                    if (is_string($text) && isset($subjects[$text])) {
                        $subject[$role] = $subjects[$text];
                    }
                }
            }

            DB::table('aramisc_notification_settings')->where('id', $row->id)->update([
                'template' => is_array($template) ? json_encode($template, JSON_UNESCAPED_UNICODE) : $row->template,
                'subject' => is_array($subject) ? json_encode($subject, JSON_UNESCAPED_UNICODE) : $row->subject,
            ]);
        }
    }
};
