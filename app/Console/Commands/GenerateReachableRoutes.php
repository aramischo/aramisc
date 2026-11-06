<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Chantier permissions — génère la fixture des routes atteignables par rôle.
 *
 * Pour chaque rôle (hors Super admin, qui bypasse tout), calcule la liste des
 * routes GET sans paramètre, gardées par `userRolePermission:<param>`, que le
 * rôle est AUTORISÉ à atteindre (reproduit exactement la logique de décision du
 * middleware UserRolePermission). Exclut les routes à effet de bord (logout,
 * suppression, envoi, sauvegarde, export/pdf…) qu'on ne doit pas « prober » en
 * aveugle avec un vrai GET.
 *
 * Sert de source aux specs d'autorisation (cypress/e2e/autorisation/acces_routes.cy.js),
 * qui vérifient sous enforce=true que chacune de ces routes ne renvoie PAS 403.
 *
 *   php artisan permissions:reachable-routes
 *
 * À relancer après toute modification des permissions/assignations.
 */
class GenerateReachableRoutes extends Command
{
    protected $signature = 'permissions:reachable-routes {--out=cypress/fixtures/reachable_routes.json}';

    protected $description = 'Génère la fixture Cypress des routes GET atteignables par rôle (chantier permissions).';

    /** slug (= suffixe loginAs<Slug>) => role_id */
    protected $roles = [
        'admin'        => 5,
        'teacher'      => 4,
        'accountant'   => 6,
        'student'      => 2,
        'parent'       => 3,
        'receptionist' => 7,
        'librarian'    => 8,
        'driver'       => 9,
    ];

    /** Fragments (dans l'uri OU le nom de route) marquant une route à effet de bord : exclues. */
    protected $dangerous = [
        'logout', 'signout', 'sign-out', 'delete', 'destroy', 'remove', 'trash',
        'cancel', 'approve', 'reject', 'send', 'resend', 'reset', 'clear', 'flush',
        'backup', 'restore', 'sync', 'import', 'truncate', 'activate', 'deactivate',
        'enable', 'disable', 'impersonate', 'switch', 'login-as', 'mark-', 'read-all',
        'generate', 'regenerate', 'migrate', 'seed', 'install', 'download', 'export',
        'print', 'pdf', 'excel', 'csv', 'save',
    ];

    public function handle()
    {
        // 1) Routes GET sans paramètre, gardées par userRolePermission:X => [uri => param]
        //    (middleware lu au niveau route, sans instancier le contrôleur : évite la
        //     cascade des modules désactivés).
        $guarded = [];
        foreach (app('router')->getRoutes() as $r) {
            if (!in_array('GET', $r->methods())) continue;
            $uri = $r->uri();
            if (strpos($uri, '{') !== false) continue;

            $param = null;
            foreach ($r->middleware() as $mw) {
                if (is_string($mw) && strpos($mw, 'userRolePermission:') === 0) {
                    $param = substr($mw, strlen('userRolePermission:'));
                    break;
                }
            }
            if ($param === null || $param === '') continue;

            // Exclusion effet de bord.
            $hay = strtolower($uri . ' ' . (string) $r->getName());
            $skip = false;
            foreach ($this->dangerous as $frag) {
                if (strpos($hay, $frag) !== false) { $skip = true; break; }
            }
            if ($skip) continue;

            $guarded[$uri] = $param;
        }

        // 2) Par rôle : garder les routes dont la décision middleware == « autoriser ».
        $out = [];
        $stats = [];
        foreach ($this->roles as $slug => $roleId) {
            $allowed = $this->allowedRoutes($roleId);
            $allowed = array_flip($allowed);

            $uris = [];
            foreach ($guarded as $uri => $param) {
                if ($this->isAllowed($param, $allowed)) {
                    $uris[] = $uri;
                }
            }
            sort($uris);
            $out[$slug] = $uris;
            $stats[$slug] = count($uris);
        }

        $out['_meta'] = [
            'generated_at'      => now()->toDateTimeString(),
            'guarded_get_total' => count($guarded),
            'note'              => 'Routes GET sans param gardées, hors routes à effet de bord. Assertion attendue côté spec : statut != 403.',
        ];

        $path = base_path($this->option('out'));
        file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Fixture écrite : {$this->option('out')} ({$out['_meta']['guarded_get_total']} routes gardées probables)");
        foreach ($stats as $slug => $n) {
            $this->line(sprintf('  %-13s : %d routes atteignables', $slug, $n));
        }

        return self::SUCCESS;
    }

    /** assign_permissions ∩ permissions.route pour le rôle (logique du middleware). */
    protected function allowedRoutes($roleId)
    {
        $isSaas = (int) DB::table('aramisc_roles')->where('id', $roleId)->value('is_saas');
        $q = DB::table('assign_permissions')->where('role_id', $roleId);
        if ($isSaas === 0) {
            $q->where('school_id', 1);
        }
        $ids = $q->pluck('permission_id')->all();
        if (empty($ids)) return [];
        return DB::table('permissions')->whereIn('id', $ids)
            ->whereNotNull('route')->pluck('route')->all();
    }

    /** Reproduit UserRolePermission::routeDecision() == true. */
    protected function isAllowed($param, array $allowedFlip)
    {
        if (ctype_digit((string) $param)) {
            $routes = DB::table('permissions')->where('old_id', (int) $param)
                ->whereNotNull('route')->pluck('route')->all();
            foreach ($routes as $rr) {
                if (isset($allowedFlip[$rr])) return true;
            }
            return false; // non résolu => toléré, mais pas « autorisé » : on ne le teste pas
        }
        $known = DB::table('permissions')->where('route', $param)->exists();
        if (!$known) return false; // orpheline => tolérée, pas testée
        return isset($allowedFlip[$param]);
    }
}
