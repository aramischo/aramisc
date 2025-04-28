<?php
use Nwidart\Modules\Facades\Module;
require '../vendor/autoload.php';

use Stichoza\GoogleTranslate\GoogleTranslate;

// Définir les répertoires des fichiers de langue source et cible
$sourceDir = __DIR__ . '/../resources/lang/en';  // Répertoire source (anglais)
//$targetDir = __DIR__ . '/../resources/lang/fr';  // Répertoire cible (arabe)

// Créer une instance de Google Translate pour la langue cible (arabe)
$translator = new GoogleTranslate('fr');
echo '<pre>';
echo "Début de la traduction...\n";
echo '<pre/><br/>';
// Récupérer tous les fichiers PHP dans le répertoire source
$langFiles = glob($sourceDir . "/*.php");
$modules =  __DIR__ . '/../Modules';
$msourceDir = $modules . "/*/Resources/lang/en/";
$msrclangFiles = glob($msourceDir."*.php");
$langFiles = array_merge($langFiles,$msrclangFiles);

if (empty($langFiles)) {
    echo '<pre>';
    echo "Aucun fichier de langue trouvé dans le répertoire $sourceDir.\n";
    echo '<pre/>';
    exit;
}

$translated_files=['last_update'=>date('Y-m-d H:i')];
$translatedfile = './translated_files.php';
$content = '<?php return[];';
if(!file_exists($translatedfile)){
    file_put_contents($translated_files, '<?php return ' . var_export($translated_files, true) . ';');
}else{
    $translated_files = include $translatedfile;
    if(isset($translated_files['last_update'])){
        $now = new dateTime();
        $upadtedate = dateTime::createFromFormat('Y-m-d H:i',$translated_files['last_update']);
        if(!$upadtedate || $upadtedate && ($now->diff($upadtedate)->days>0)){
            $translated_files=['last_update'=>date('Y-m-d H:i')];
        }
    }
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
    if(in_array($file,$translated_files)){
        continue;
    }

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
    $targetDir = str_replace('en','fr',str_replace(basename($file),'',$file));
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Sauvegarder le fichier traduit dans le répertoire cible
    $targetFile = $targetDir . '/' . basename($file);
    file_put_contents($targetFile, '<?php return ' . var_export($translations, true) . ';');

    // include file into treated files list
    $translated_files[date('Y-m-s-H:i')] = $file;
    file_put_contents($translatedfile, '<?php return ' . var_export($translated_files, true) . ';');
    echo '<pre>';
    echo "Fichier traduit et sauvegardé : " . basename($file) . " dans $targetDir";
    echo '<pre/><br/>';
}


echo '<pre>';
echo "Traduction terminée";
echo '<pre/><br/>';

