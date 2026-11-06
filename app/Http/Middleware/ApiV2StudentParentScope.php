<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * API v2 (application mobile) : limite les élèves et les parents à leurs propres données.
 *
 * Les contrôleurs v2 prennent student_id, record_id, parent_id... tels qu'envoyés, sans vérifier
 * à qui ils appartiennent ni le rôle de l'appelant. Ici :
 *  - un élève ou un parent n'accède qu'aux routes de la liste STUDENT_PARENT_ROUTES ;
 *  - chaque identifiant reçu doit désigner l'élève lui-même ou un enfant du parent.
 * Les autres rôles (personnel) ne sont pas filtrés ici.
 */
class ApiV2StudentParentScope
{
    const STUDENT_ROLE = 2;
    const PARENT_ROLE = 3;

    /** Routes ouvertes aux élèves et parents (URI sans le préfixe api/v2/). */
    const STUDENT_PARENT_ROUTES = [
        'general-settings', 'auth/logout', 'teacher-about', 'set-fcm-token',
        'change-password', 'user-delete', 'language-list', 'user-language-list',
        // Profil
        'student-profile-details/{id}', 'student-profile-edit', 'student-profile-update', 'student-profile-img-update',
        'student-record', 'profile-personal', 'profile-parents', 'profile-transport', 'profile-others',
        'profile-documents', 'profile-documents-store', 'profile-documents-delete',
        'student-profile-personal', 'student-profile-parents', 'student-profile-transport',
        'student-profile-others', 'student-profile-documents', 'parent-childrens',
        // Scolarité
        'student-homework', 'parent-homework', 'student-homework-view', 'student-homework-file-download',
        'upload-homework-content', 'student-assignment', 'student-assignment-file-download/{id}',
        'upload-content-student-view', 'student-syllabus', 'student-others-download',
        'student-attendance', 'subject-wise-attendance', 'record-wise-all-subjects',
        'all-notification-list', 'view/all/notification', 'timeline', 'student-teacher', 'student-subject',
        'student-library', 'student-book-issue', 'student-transport', 'student-dormitory',
        'student-class-routine', 'student-exam', 'student-exam-type', 'student-exam-schedule',
        'student-noticeboard', 'student-single-noticeboard', 'exam-result', 'exam-result-search',
        'student-online-exam', 'student-view-result', 'student-lesson-plan', 'view-lesson-plan-lesson',
        // Congés
        'remaining-leave', 'apply-leave', 'leave-type', 'student-leave-store', 'student-leave-edit', 'student-leave-update',
        // Frais et paiements
        'student-fees', 'add-fees-payment', 'gateway-service-charge', 'student-fees-payment-stores',
        'fees-invoice-view', 'student-fees-installment', 'student-single-fees-installment',
        'direct-fees-generate-add-child', 'child-bank-slip-store', 'fees-payment-print',
        'my-wallet', 'add-wallet-amount', 'add-amount-methods', 'add-amount-banks', 'handle-payment-request',
        // Classes virtuelles
        'jitsi/virtual-class', 'jitsi/meetings', 'bbb/virtual-class', 'bbb/meetings', 'bbb/meeting-join',
        'g-meet/virtual-class', 'g-meet/virtual-meeting',
        // Messagerie (chaque action porte sur les conversations de l'utilisateur connecté)
        'admin-chat-users', 'admin-chat-send', 'admin-chat-list', 'admin-chat-user-search',
        'change-admin-chat-user-status', 'delete-admin-chat-single-message', 'forward-admin-chat-single-message',
        'admin-single-chat-files', 'chat-user-block-action', 'blocked-chat-users', 'single-user-chat-status',
        'admin-chat-groups', 'admin-group-chat-send', 'admin-group-members', 'admin-group-leave',
        'admin-group-chats', 'admin-group-chat-files', 'broadcasting/auth',
    ];

    /** Paramètres désignant un élève (aramisc_students.id). */
    const STUDENT_PARAMS = ['student_id', 'student_timeline_id'];

    /** Routes de messagerie où user_id désigne l'interlocuteur, pas un élève. */
    const CHAT_ROUTES_PREFIX = ['admin-', 'chat-', 'blocked-chat', 'single-user-chat', 'change-admin-chat', 'delete-admin-chat', 'forward-admin-chat'];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || !in_array((int) $user->role_id, [self::STUDENT_ROLE, self::PARENT_ROLE], true)) {
            return $next($request);
        }

        $uri = preg_replace('#^api/v2/#', '', (string) optional($request->route())->uri());
        if (!in_array($uri, self::STUDENT_PARENT_ROUTES, true)) {
            return $this->deny();
        }

        $scope = $this->scope($user);

        foreach (self::STUDENT_PARAMS as $param) {
            if (!$this->allowed($request->input($param), $scope['students'])) {
                return $this->deny();
            }
        }
        if ($uri === 'student-profile-details/{id}' && !$this->allowed($request->route('id'), $scope['students'])) {
            return $this->deny();
        }

        $checks = [
            'record_id' => $scope['records'],
            'parent_id' => $scope['parents'],
        ];
        if (!$this->isChatRoute($uri)) {
            $checks['user_id'] = $scope['users'];
        }
        foreach ($checks as $param => $ids) {
            if (!$this->allowed($request->input($param), $ids)) {
                return $this->deny();
            }
        }

        // Objets rattachés à un élève
        $owned = [
            'document_id' => ['aramisc_student_documents', 'student_staff_id', $scope['students']],
            'fees_invoice_id' => ['fm_fees_invoices', 'student_id', $scope['students']],
            'fees_payment_id' => ['aramisc_fees_payments', 'student_id', $scope['students']],
            'installment_id' => ['direct_fees_installment_assigns', 'student_id', $scope['students']],
            'leave_request_id' => ['aramisc_leave_requests', 'staff_id', $scope['users']],
        ];
        if ($uri === 'student-leave-update') {
            $owned['id'] = ['aramisc_leave_requests', 'staff_id', $scope['users']];
        }
        foreach ($owned as $param => [$table, $column, $ids]) {
            $values = $this->values($request->input($param));
            if (!$values) {
                continue;
            }
            $foreign = DB::table($table)->whereIn('id', $values)
                ->where(function ($q) use ($column, $ids) {
                    $q->whereNotIn($column, $ids ?: [0])->orWhereNull($column);
                })->exists();
            if ($foreign) {
                return $this->deny();
            }
        }

        return $next($request);
    }

    /** Élèves, inscriptions, parents et comptes utilisateurs accessibles à l'appelant. */
    private function scope($user)
    {
        if ((int) $user->role_id === self::PARENT_ROLE) {
            $parents = DB::table('aramisc_parents')->where('user_id', $user->id)->pluck('id')->all();
            $students = DB::table('aramisc_students')->whereIn('parent_id', $parents ?: [0])->get(['id', 'user_id']);
        } else {
            $students = DB::table('aramisc_students')->where('user_id', $user->id)->get(['id', 'user_id', 'parent_id']);
            $parents = $students->pluck('parent_id')->filter()->all();
        }
        $studentIds = $students->pluck('id')->all();

        return [
            'students' => $studentIds,
            'records' => DB::table('student_records')->whereIn('student_id', $studentIds ?: [0])->pluck('id')->all(),
            'parents' => $parents,
            'users' => array_merge([$user->id], $students->pluck('user_id')->filter()->all()),
        ];
    }

    private function isChatRoute($uri)
    {
        foreach (self::CHAT_ROUTES_PREFIX as $prefix) {
            if (strpos($uri, $prefix) === 0) {
                return true;
            }
        }

        return $uri === 'broadcasting/auth';
    }

    private function values($value)
    {
        $values = is_array($value) ? $value : [$value];

        return array_values(array_filter($values, function ($v) {
            return $v !== null && $v !== '' && !is_array($v);
        }));
    }

    /** Valeur absente : rien à vérifier. Sinon chaque identifiant doit être dans la liste. */
    private function allowed($value, array $ids)
    {
        foreach ($this->values($value) as $v) {
            if (!in_array((string) $v, array_map('strval', $ids), true)) {
                return false;
            }
        }

        return true;
    }

    private function deny()
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Accès refusé',
        ], 403);
    }
}
