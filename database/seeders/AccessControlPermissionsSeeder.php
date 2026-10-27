<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Chantier permissions — Phase 2.
 *
 * Accorde (dans assign_permissions) les permissions légitimes qui manquaient
 * à chaque rôle, repérées en Phase 1 (mode audit : entrées access_control.deny
 * — permission connue mais non assignée, exercée par les suites Cypress du rôle).
 * Voir cypress/NOTES_PASSATION.md, section « CHANTIER … Phase 1/2 ».
 *
 * IDEMPOTENT : n'insère que les lignes manquantes (role_id + permission_id + school_id),
 * ne touche à rien d'existant. Rejouable sans effet de bord.
 *
 *   php artisan db:seed --class=Database\\Seeders\\AccessControlPermissionsSeeder
 *
 * NB : ne concerne PAS le Super admin (role 1, bypass). Les gardes « unresolved »
 * (params numériques sans old_id, sous-actions orphelines, module Global Academics)
 * sont traitées à part (création de permission ou correction de garde), pas ici.
 */
class AccessControlPermissionsSeeder extends Seeder
{
    /** Rôles de type System rattachés à l'école 1. */
    protected $schoolId = 1;

    /**
     * role_id => routes à accorder (résolues en permission_id au runtime,
     * pour rester correct quel que soit l'environnement).
     */
    protected $grants = [
        // Admin (5) : frais, bibliothèque (matières), transport, dortoir, planning d'examen.
        5 => [
            'collect_fees', 'search_fees_due', 'search_fees_payment', 'fees_forward',
            'fees-master', 'fees-master-store', 'fees-master-edit',
            'fees_group', 'fees_group_store', 'fees_group_edit', 'fees_group_delete',
            'fees_type', 'fees_type_store', 'fees_type_edit', 'fees_type_delete',
            'fees_discount', 'fees_discount_store', 'fees_discount_edit', 'fees_discount_delete',
            'library_subject', 'library_subject_store', 'library_subject_edit', 'library_subject_delete',
            'transport-route-edit', 'transport-route-delete',
            'vehicle-edit', 'vehicle-delete',
            'dormitory-list-store',
            'exam_schedule', 'exam_schedule_print',
        ],
        // Accountant (6) : comptes, dépenses/revenus, inventaire, annuaire du personnel.
        6 => [
            'add-expense', 'add_income', 'bank-account', 'chart-of-account', 'profit',
            'item-category', 'item-list', 'item-receive-list', 'item-sell-list', 'suppliers',
            'staff_directory',
        ],
        // Student (2) : tableau d'affichage, mise à jour de profil.
        2 => [
            'student_noticeboard', 'update-my-profile',
        ],
        // Parents (3) : examens / examens en ligne.
        3 => [
            'parent_examination', 'parent_online_examination',
        ],
        // Teacher (4) : planning d'examen.
        4 => [
            'exam_schedule',
        ],
        // Librarian (8) : évènements, liste des avis.
        8 => [
            'event', 'notice-list',
        ],
    ];

    public function run()
    {
        $now = now();
        $inserted = 0;
        $skipped = 0;
        $missing = [];

        foreach ($this->grants as $roleId => $routes) {
            foreach ($routes as $route) {
                $permissionIds = DB::table('permissions')->where('route', $route)->pluck('id')->all();

                if (empty($permissionIds)) {
                    $missing[] = "role $roleId : route '$route' (aucune permission)";
                    continue;
                }

                foreach ($permissionIds as $pid) {
                    $exists = DB::table('assign_permissions')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $pid)
                        ->where('school_id', $this->schoolId)
                        ->exists();

                    if ($exists) {
                        $skipped++;
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
                    $inserted++;
                }
            }
        }

        $this->command->info("AccessControlPermissionsSeeder : $inserted insérées, $skipped déjà présentes.");
        if (!empty($missing)) {
            $this->command->warn("Routes sans permission (ignorées) :");
            foreach ($missing as $m) {
                $this->command->warn("  - $m");
            }
        }
    }
}
