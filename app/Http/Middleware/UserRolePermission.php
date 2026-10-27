<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Modules\RolePermission\Entities\AramiscRole;
use Modules\RolePermission\Entities\Permission;
use Modules\RolePermission\Entities\AramiscModuleInfo;
use Modules\RolePermission\Entities\AramiscPermissionAssign;
use Modules\RolePermission\Entities\AramiscModuleStudentParentInfo;

class UserRolePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $route = null)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        // Restriction congés impayés (parent, role 3) — inchangé.
        if (auth()->user()->role_id == 3 && Cache::get('have_due_fees_' . auth()->user()->id)) {
            $url = explode('/', $request->getRequestUri());
            $param = end($url);
            $students = Cache::get('have_due_fees_' . auth()->user()->id);

            if (in_array($param, $students) && !in_array(Route::currentRouteName(), ["parent_fees", "fees.student-fees-list-parent"])) {
                abort(403);
            } else {
                return $next($request);
            }
        }

        // Garde niveau "plan / package" école (indépendante du rôle) — inchangé.
        if (!$this->hasPermission($route)) {
            abort(403);
        }

        // ──────────────────────────────────────────────────────────────────
        // Contrôle d'accès par RÔLE (chantier permissions).
        // Piloté par config/access_control.php. Par défaut TOUT est OFF
        // => aucun changement de comportement (on tombe sur le return final).
        // ──────────────────────────────────────────────────────────────────
        $user = auth()->user();

        // Super admin : toujours autorisé (par construction).
        if ((int) $user->role_id === 1) {
            return $next($request);
        }

        $enforce = (bool) config('access_control.enforce', false);
        $audit   = (bool) config('access_control.audit', false);

        if ($enforce || $audit) {
            $decision = $this->routeDecision($route, $this->allowedRoutesForCurrentRole($user));

            if ($decision === false) {
                // Permission connue mais NON assignée à ce rôle.
                if ($audit) {
                    $this->auditLog('access_control.deny', [
                        'user'  => $user->id,
                        'role'  => $user->role_id,
                        'param' => $route,
                        'uri'   => $request->path(),
                    ]);
                }
                if ($enforce) {
                    abort(403);
                }
            } elseif ($decision === null && $audit) {
                // Garde non résolue (param numérique sans old_id, sous-action
                // orpheline…) : TOLÉRÉE, jamais 403 ; seulement journalisée.
                $this->auditLog('access_control.unresolved', [
                    'role'  => $user->role_id,
                    'param' => $route,
                    'uri'   => $request->path(),
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Décision d'accès pour un paramètre de garde donné :
     *   true  = permission connue ET assignée au rôle       -> autoriser
     *   false = permission connue mais NON assignée          -> refuser (si enforce)
     *   null  = paramètre non résolu (garde mal câblée)      -> tolérer + logger
     *
     * Les params numériques (ancien schéma) sont résolus via permissions.old_id.
     *
     * @param  string|null  $param
     * @param  array        $allowed  routes autorisées pour le rôle courant
     * @return bool|null
     */
    protected function routeDecision($param, array $allowed)
    {
        if ($param === null || $param === '') {
            return null;
        }

        // Param numérique => ancien id (permissions.old_id).
        if (ctype_digit((string) $param)) {
            $routes = Cache::remember('ac_oldid_' . $param, 300, function () use ($param) {
                return DB::table('permissions')->where('old_id', (int) $param)
                    ->whereNotNull('route')->pluck('route')->all();
            });
            if (empty($routes)) {
                return null; // non résolu -> toléré
            }
            foreach ($routes as $r) {
                if (in_array($r, $allowed, true)) {
                    return true;
                }
            }
            return false;
        }

        // Param chaîne => doit correspondre à une permissions.route connue.
        $known = Cache::remember('ac_known_' . md5($param), 300, function () use ($param) {
            return DB::table('permissions')->where('route', $param)->exists();
        });
        if (!$known) {
            return null; // orpheline -> tolérée
        }
        return in_array($param, $allowed, true);
    }

    /**
     * Liste des routes autorisées pour le rôle de l'utilisateur courant
     * (assign_permissions ∩ permissions.route), mise en cache 60s par rôle+école.
     * Reproduit l'intention du singleton app('permission') d'AppServiceProvider
     * (qui, lui, ne retourne rien — cf. NOTES_PASSATION.md).
     *
     * @return array
     */
    protected function allowedRoutesForCurrentRole($user)
    {
        return Cache::remember('ac_allowed_' . $user->role_id . '_' . $user->school_id, 60, function () use ($user) {
            $isSaas = (int) DB::table('aramisc_roles')->where('id', $user->role_id)->value('is_saas');

            $q = DB::table('assign_permissions')->where('role_id', $user->role_id);
            if ($isSaas === 0) {
                $q->where('school_id', $user->school_id);
            }
            $ids = $q->pluck('permission_id')->all();

            if (empty($ids)) {
                return [];
            }
            return DB::table('permissions')->whereIn('id', $ids)
                ->whereNotNull('route')->pluck('route')->all();
        });
    }

    /**
     * Journalise un évènement d'audit du contrôle d'accès, sans jamais
     * interrompre la requête si le canal de log est mal configuré.
     */
    protected function auditLog($event, array $context)
    {
        try {
            Log::channel(config('access_control.log_channel', 'stack'))->info($event, $context);
        } catch (\Throwable $e) {
            // Audit non bloquant.
        }
    }

    public function hasPermission($route)
    {
        $permissions = Permission::with(['subModule'])->get();
        $parent_module = $permissions->where('route', $route)->first();
        if (!$parent_module) {
            foreach ($permissions as $permission) {
                $children_module = $permission->subModule->where('route', $route)->first();
                if ($children_module) {
                    $parent_module = $permission;
                    break;
                }
            }
        }

        if ($parent_module) {
            $parent_module_id = $parent_module->id;
            // get permission name
            $school_permissions = planPermissions('menus', true);
            $key = false;
            foreach ($school_permissions as $permission => $id) {
                if ($id == $parent_module_id) {
                    $key = $permission;
                    break;
                }
            }

            if ($key) {
                return isMenuAllowToShow($key);
            }
        }
        return true;
    }
}
