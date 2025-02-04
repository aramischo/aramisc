<?php

require '../vendor/autoload.php';

use Stichoza\GoogleTranslate\GoogleTranslate;

// Définir les répertoires des fichiers de langue source et cible
$sourceDir = __DIR__ . '/../resources/lang/en';  // Répertoire source (anglais)
$targetDir = __DIR__ . '/../resources/lang/fr';  // Répertoire cible (arabe)

// Créer une instance de Google Translate pour la langue cible (arabe)
$translator = new GoogleTranslate('fr');
echo '<pre>';
echo "Début de la traduction...\n";
echo '<pre/><br/>';
// Récupérer tous les fichiers PHP dans le répertoire source
$langFiles = glob($sourceDir . "/*.php");

if (empty($langFiles)) {
    echo '<pre>';
    echo "Aucun fichier de langue trouvé dans le répertoire $sourceDir.\n";
    echo '<pre/>';
    exit;
}

// Fonction récursive pour traduire les valeurs de traduction
function translateArray(&$array, $translator) {
    foreach ($array as $key => &$value) {
        if (is_array($value)) {
            // Si la valeur est un tableau, appeler la fonction récursivement
            translateArray($value, $translator);
        } else {
            // Sinon, traduire la valeur si c'est une chaîne
            try {
                $value = $translator->translate($value);
            } catch (Exception $e) {
                echo '<pre>';
                echo "Erreur lors de la traduction de '$value': " . $e->getMessage() . "\n";
                echo '<pre/><br/>';
            }
        }
    }
}

foreach ($langFiles as $file) {
    echo '<pre>';
    echo "Traduction du fichier : " . basename($file) . "\n";
    echo '<pre/><br/>';

    // Charger le fichier de langue (anglais)
    $translations = include $file;

    if (empty($translations)) {
        echo '<pre>';
        echo "Le fichier $file est vide ou invalide.";
        echo '<pre/><br/>';
        continue;
    }

    // Appliquer la fonction de traduction récursive
    translateArray($translations, $translator);

    // Créer le répertoire cible s'il n'existe pas encore
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Sauvegarder le fichier traduit dans le répertoire cible
    $targetFile = $targetDir . '/' . basename($file);
    file_put_contents($targetFile, '<?php return ' . var_export($translations, true) . ';');
    echo '<pre>';
    echo "Fichier traduit et sauvegardé : " . basename($file) . " dans $targetDir";
    echo '<pre/><br/>';
}

echo '<pre>';
echo "Traduction terminée";
echo '<pre/><br/>';
