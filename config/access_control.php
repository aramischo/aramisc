<?php

/*
|--------------------------------------------------------------------------
| Contrôle d'accès par rôle (chantier permissions)
|--------------------------------------------------------------------------
|
| Interrupteurs du ré-armement progressif de l'enforcement des permissions
| par rôle (middleware UserRolePermission). Voir cypress/NOTES_PASSATION.md,
| section « CHANTIER — Réactiver le contrôle d'accès par rôle ».
|
| Rappel : par défaut TOUT est OFF => comportement identique à l'existant
| (aucun rôle n'est bloqué par-route ; le Super admin reste bypassé).
|
|  - enforce : si true, un rôle (hors Super admin) qui atteint une route dont
|              la permission ne lui est PAS assignée reçoit un 403.
|  - audit   : si true, chaque accès qui SERAIT refusé est journalisé
|              (sans blocage si enforce=false). Sert à cartographier l'usage
|              réel avant d'activer enforce (Phase 1 du chantier).
|
| Un paramètre de garde qui ne correspond à AUCUNE permission connue
| (param numérique sans old_id, sous-action CRUD orpheline…) est TOLÉRÉ
| (jamais 403) et seulement journalisé : une garde mal câblée ne doit pas
| casser l'accès. La résolution de ces gardes est un travail de Phase 0/2.
|
*/

return [
    'enforce' => env('ACCESS_CONTROL_ENFORCE', false),
    'audit'   => env('ACCESS_CONTROL_AUDIT', false),

    // Canal de log utilisé pour l'audit (doit exister dans config/logging.php ;
    // 'stack' par défaut pour ne rien présumer de la config de logging).
    'log_channel' => env('ACCESS_CONTROL_LOG_CHANNEL', 'stack'),
];
