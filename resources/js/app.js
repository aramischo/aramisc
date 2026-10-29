/**
 * Chargement des dépendances JS du projet (lodash, axios, Laravel Echo/Pusher…).
 *
 * NB : Vue.js a été retiré (option C) car il n'était pas utilisé — aucune vue
 * Blade ne référence de composant ni de directive Vue (v-model, v-for, mustache
 * @{{ }}). Le projet est servi en Blade + jQuery ; le temps réel passe par
 * Laravel Echo/Pusher, configurés dans ./bootstrap. Les composants .vue du
 * dossier components/ étaient du scaffolding résiduel non câblé.
 */

require('./bootstrap');
