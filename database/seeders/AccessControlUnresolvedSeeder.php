<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Chantier permissions — traitement des gardes « unresolved ».
 *
 * En Phase 3 (mode test, enforce=true), 23 gardes `userRolePermission:<param>`
 * sont restées « unresolved » : le paramètre ne correspond à AUCUNE
 * permission connue (permissions.route). Elles sont TOLÉRÉES par le middleware
 * (jamais de 403), mais pour les rendre enforce-ables proprement on crée ici la
 * permission manquante (ligne FEUILLE non-menu, calquée sur fees_group_store :
 * type=3, is_menu=0, menu_status=0 → n'apparaît pas dans le sidebar et laisse
 * hasPermission() neutre) puis on l'assigne au(x) rôle(s) qui l'exerce(nt)
 * réellement (relevés en audit Phase 1/3). Voir cypress/NOTES_PASSATION.md.
 *
 * IDEMPOTENT : ne crée/assigne que ce qui manque (clé route, et old_id pour les
 * gardes numériques). Rejouable sans effet de bord.
 *
 *   php artisan db:seed --class=Database\\Seeders\\AccessControlUnresolvedSeeder
 *
 * NB : ne concerne PAS le Super admin (role 1, bypass).
 */
class AccessControlUnresolvedSeeder extends Seeder
{
    protected $schoolId = 1;

    /**
     * Permissions feuilles à créer si absentes.
     * route => [name, parent_route, old_id|null]
     */
    protected $permissions = [
        // ── Groupe A : sous-actions / pages avec rôle identifié ───────────────
        'exam_schedule_create'   => ['Exam Schedule Create', 'exam_schedule',   null],
        'delete-lesson-plan'     => ['Delete Lesson Plan',   'lesson-plan',     null],
        'delete-supplier'        => ['Delete Supplier',      'suppliers',       null],
        'delete-holiday-data'    => ['Delete Holiday',       'holiday',         null],
        // Garde NUMÉRIQUE 147 (route /search-account) : résolue via old_id.
        'search-account'         => ['Search Account',       'accounts',        147],
        'subjectMarkSheet'       => ['Subject Mark Sheet Report', 'report',     null],
        'wallet.approve-payment' => ['Wallet Approve Payment', 'wallet',        null],
        'wallet.reject-payment'  => ['Wallet Reject Payment',  'wallet',        null],
        'student_fine_report'    => ['Student Fine Report',  'accounts-report', null],
        'student_study_material' => ['Student Study Material', 'study_material', null],
        'parent_fees'            => ['Children Fees',        'parent',          null],

        // ── Groupe B : module Global Academics (27 gardes, 0 permission) ──────
        'global_subject'                => ['Global Subject',               'global-academics', null],
        'global_subject_store'          => ['Global Subject Store',         'global-academics', null],
        'global_subject_edit'           => ['Global Subject Edit',          'global-academics', null],
        'global_subject_update'         => ['Global Subject Update',        'global-academics', null],
        'global_subject_delete'         => ['Global Subject Delete',        'global-academics', null],
        'global_assign_subject'         => ['Global Assign Subject',        'global-academics', null],
        'global_assign_subject_create'  => ['Global Assign Subject Create', 'global-academics', null],
        'global_assign_subject_search'  => ['Global Assign Subject Search', 'global-academics', null],
        'global-assign-subject-create'  => ['Global Assign Subject Create (alt)', 'global-academics', null],
        'global_assign-subject-store'   => ['Global Assign Subject Store',  'global-academics', null],
        'global_assign-subject'         => ['Global Assign Subject (find)', 'global-academics', null],
        'global-assign'                 => ['Global Assign',                'global-academics', null],
        'global-upload-content'         => ['Global Upload Content',        'global-academics', null],
        'global-save-upload-content'    => ['Global Save Upload Content',   'global-academics', null],
        'global-upload-content-edit'    => ['Global Upload Content Edit',   'global-academics', null],
        'global-upload-content-view'    => ['Global Upload Content View',   'global-academics', null],
        'global-delete-upload-content'  => ['Global Delete Upload Content', 'global-academics', null],
        'global_exam-type'              => ['Global Exam Type',             'global-academics', null],
        'global_exam_type_edit'         => ['Global Exam Type Edit',        'global-academics', null],
        'global_exam_type_store'        => ['Global Exam Type Store',       'global-academics', null],
        'global_exam_type_update'       => ['Global Exam Type Update',      'global-academics', null],
        'global_exam_type_delete'       => ['Global Exam Type Delete',      'global-academics', null],
        'global-exam'                   => ['Global Exam',                  'global-academics', null],
        'global-exam-edit'              => ['Global Exam Edit',             'global-academics', null],
        'global-exam-update'            => ['Global Exam Update',           'global-academics', null],
        'global-exam-delete'            => ['Global Exam Delete',           'global-academics', null],
        'global-examView'               => ['Global Exam View',            'global-academics', null],
    ];

    /**
     * Assignations à créer. role_id => [routes]
     */
    protected $assign = [
        5 => [ // Admin
            'exam_schedule_create', 'delete-lesson-plan', 'delete-supplier', 'delete-holiday-data',
            'search-account', 'subjectMarkSheet', 'wallet.approve-payment', 'wallet.reject-payment',
            'global_subject', 'global_subject_store', 'global_subject_edit', 'global_subject_update',
            'global_subject_delete', 'global_assign_subject', 'global_assign_subject_create',
            'global_assign_subject_search', 'global-assign-subject-create', 'global_assign-subject-store',
            'global_assign-subject', 'global-assign', 'global-upload-content', 'global-save-upload-content',
            'global-upload-content-edit', 'global-upload-content-view', 'global-delete-upload-content',
            'global_exam-type', 'global_exam_type_edit', 'global_exam_type_store', 'global_exam_type_update',
            'global_exam_type_delete', 'global-exam', 'global-exam-edit', 'global-exam-update',
            'global-exam-delete', 'global-examView',
        ],
        6 => ['student_fine_report'],      // Accountant
        2 => ['student_study_material'],   // Student
        3 => ['parent_fees'],              // Parents
    ];

    public function run()
    {
        $now = now();
        $createdPerm = 0;
        $skippedPerm = 0;

        // 1) Créer les permissions feuilles manquantes.
        foreach ($this->permissions as $route => [$name, $parentRoute, $oldId]) {
            $exists = DB::table('permissions')
                ->where('route', $route)
                ->when($oldId !== null, fn ($q) => $q->orWhere('old_id', $oldId))
                ->exists();

            if ($exists) {
                $skippedPerm++;
                continue;
            }

            DB::table('permissions')->insert([
                'module'       => 'access_control',
                'sidebar_menu' => null,
                'old_id'       => $oldId,
                'section_id'   => 1,
                'parent_id'    => 0,
                'name'         => $name,
                'route'        => $route,
                'parent_route' => $parentRoute,
                'type'         => 3,     // sous-action / action, pas un menu
                'status'       => 1,
                'menu_status'  => 0,     // jamais dans le sidebar
                'position'     => 9000,
                'is_saas'      => 0,
                'is_menu'      => 0,
                'school_id'    => $this->schoolId,
                'created_by'   => 1,
                'updated_by'   => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
            $createdPerm++;
        }

        // 2) Assigner aux rôles observés.
        $insertedAssign = 0;
        $skippedAssign = 0;
        $missing = [];

        foreach ($this->assign as $roleId => $routes) {
            foreach ($routes as $route) {
                $pid = DB::table('permissions')->where('route', $route)->value('id');
                if (!$pid) {
                    $missing[] = "role $roleId : route '$route' (permission introuvable)";
                    continue;
                }

                $has = DB::table('assign_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $pid)
                    ->where('school_id', $this->schoolId)
                    ->exists();

                if ($has) {
                    $skippedAssign++;
                    continue;
                }

                DB::table('assign_permissions')->insert([
                    'permission_id' => $pid,
                    'role_id'       => $roleId,
                    'status'        => 1,
                    'menu_status'   => 1,
                    'school_id'     => $this->schoolId,
                    'created_by'    => 1,
                    'updated_by'    => 1,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
                $insertedAssign++;
            }
        }

        $this->command->info("AccessControlUnresolvedSeeder : permissions +$createdPerm (déjà présentes : $skippedPerm) ; assignations +$insertedAssign (déjà présentes : $skippedAssign).");
        foreach ($missing as $m) {
            $this->command->warn("  - $m");
        }
    }
}
