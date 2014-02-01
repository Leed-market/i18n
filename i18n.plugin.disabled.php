<?php
/*
@name i18n
@author Cobalt74 <cobalt74@gmail.com>
@link http://www.cobestran.com
@licence CC by nc sa http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
@version 1.0.0
@description Le plugin i18n permet d'effectuer une traduction de Leed et des plugins en générant les fichiers Json souhaités
*/


// affichage d'un lien dans le menu "Gestion"
function i18n_plugin_AddLink(){
	echo '<li><a class="toggle" href="#i18n">Plugin de Traduction de Leed</a></li>';
}

// affichage des option de recherche et du formulaire
function i18n_plugin_AddForm(){
    $test = array();

    echo '<section id="i18n" name="i18n" class="i18n">
			<h2>Plugin de traduction de Leed</h2>';

    /* -------------------------------------------------------- */
    // Gestion des retours des formulaires
    /* -------------------------------------------------------- */
    // Cas validation de la création d'une langue sur Leed.
    $newLanguage = '';
    if(isset($_POST['plugin_i18n_newLanguage'])){
        $newLanguage = $_POST['plugin_i18n_newLanguage'];
        if (is_file('./locale/'.$newLanguage.'.json')){
            $test['Erreur'][]='Fichier déjà existant ./locale/'.$newLanguage.'.json';
        } else {
            file_put_contents('./locale/'.$newLanguage.'.json', '');
            $test['Info'][]='Création du fichier de langue ./locale/'.$newLanguage.'.json : OK';
        }
    }
    // Cas validation d'une MAJ d'un fichier de langue
    if(isset($_POST['0123456789MAJLanguage'])){

        $_ = array_map('addslashes',array_merge($_GET, $_POST));
        //$_ = array_merge($_GET, $_POST);
        $ModifLanguage = $_['0123456789MAJLanguage'];
        unset($_['0123456789MAJLanguage']);
        //print_r($_);
        if(is_writable($ModifLanguage.'.json')){
            file_put_contents($ModifLanguage.'.json', plugin_i18n_json_encode($_));
            $test['Info'][]='Fichier de langue :'.$_POST['0123456789MAJLanguage'].'.json mis à jour.';
        } else {
            $test['Erreur'][]='Le fichier '.$_POST['0123456789MAJLanguage'].'.json n\'est pas accessible en écriture. Veuillez ajouter les droits nécessaire';
        }

    }

    // Gestion des erreurs PHP possible permettant l'écriture de fichier dans les répertoires de Leed
    if(!is_writable('./locale/')){
        $test['Erreur'][]='Écriture impossible dans le répertoire /locale/, veuillez ajouter les permissions en écriture sur le dossier.';
    }
    if (!@function_exists('file_get_contents')){
        $test['Erreur'][] = 'La fonction requise "file_get_contents" est inaccessible sur votre serveur, vérifiez votre version de PHP.';
    }
    if (!@function_exists('file_put_contents')){
        $test['Erreur'][] = 'La fonction requise "file_put_contents" est inaccessible sur votre serveur, vérifiez votre version de PHP.';
    }
    if (@version_compare(PHP_VERSION, '5.1.0') <= 0){
        $test['Erreur'][] = 'Votre version de PHP ('.PHP_VERSION.') est trop ancienne, il est possible que certaines fonctionnalités du script comportent des dysfonctionnements.';
    }
    if(ini_get('safe_mode') && ini_get('max_execution_time')!=0){
        $test['Erreur'][] = 'Le script ne peux pas gérer le timeout tout seul car votre safe mode est activé,<br/> dans votre fichier de configuration PHP, mettez la variable max_execution_time à 0 ou désactivez le safemode.';
    }

    if (count($test)!=0){
        echo '<div id="result_i18n" class="result_i18n">
                  <table>
                      <th class="i18n_border i18n_th">Message(s)</th>';

        foreach($test as $type=>$messages){
            echo '<tr>';
            foreach($messages as $message){
                echo '<td class="i18n_border">'.$message.'</td>';
            }
            echo '</tr>';
        }

        echo '    </table>
              </div>';
    }

    // Sélectionner la langue ou saisir une nouvelle langue
    echo '<h3>Gestion  des fichiers de langue de Leed</h3>';

    echo '<form action="settings.php#i18n" method="POST">
              <input type="text" value="" name="plugin_i18n_newLanguage">
              <input type="submit" name="plugin_i18n_saveButton" value="Créer un fichier" class="button">
          </form>
          <form action="settings.php#i18n" method="POST">
              <select name="plugin_i18n_selectLanguage">';

                $filesLeed = glob('./locale/*.json');
                foreach($filesLeed as $file){
                    $file = str_replace('.json', '', $file);
                    echo '<option value="'.$file.'">'.$file.'</option>';
                }

    echo '    </select>
              <input type="submit" value="Charger fichier" class="button">
          </form>';

    // sélection d'un langage à charger
    if (isset($_POST['plugin_i18n_selectLanguage'])){
        $selectLanguage = $_POST['plugin_i18n_selectLanguage'];
        echo '<hr><h3>Modification du fichier : '.$selectLanguage.'</h3>
                <pre>Le caractère " (double cote) n\'est pas autorisé dans les traductions.</pre>';

        // On scan tous les tags de Leed
        $foundTags = array();
        $foundTags = plugin_i18n_scanTags('./');
        // On charge le fichier de langue existant
        $currentLanguage = json_decode(file_get_contents($selectLanguage.'.json'),true);
        ksort($currentLanguage);

        echo '<hr><h4>Clés présentes</h4>
              <form action="settings.php#i18n" method="POST">
              <input type="hidden" name="0123456789MAJLanguage" value="'.$selectLanguage.'">
              <table class="diffTab">
                <tr>
                    <th class="i18n_border i18n_th">Fichier Langue ( '.count($currentLanguage).' Tags)</th>
                    <th class="i18n_border i18n_th">Leed ( '.count($foundTags).' Tags)</th>
                </tr>';

        foreach($currentLanguage as $key=>$value){
        echo ' <tr>
                    <td class="i18n_border i18n_textcenter">'.$key.'</td>
                    <td class="i18n_border i18n_textcenter">';
                    $value = htmlentities($value,ENT_COMPAT,'UTF-8');
                    if(strlen($value)>100){
                        echo '<textarea name="'.$key.'">'.$value.'</textarea>';
                    }else{
                        echo '<input type="text" name="'.$key.'" value="'.$value.'">';
                    }
        echo '      </td>
              </tr>';
        }
        echo '</table>';

        echo '<hr><h4>Clés absentes / obsolètes</h4>
              <table class="diffTab">
                <tr>
                    <th class="i18n_border i18n_th">Fichier Langue ( '.count($currentLanguage).' Tags)</th>
                    <th class="i18n_border i18n_th">Leed ( '.count($foundTags).' Tags)</th>
                </tr>';

        // recherche des tags existant mais non trouvé dans la recherche du code
        foreach ($currentLanguage as $key => $value) {
            if(!in_array($key, $foundTags, true)){
                echo '<tr><td class="i18n_border i18n_textcenter">'.$key.'</td>
                          <td class="i18n_border i18n_textcenter">'.$value.'<br />(non trouvé dans le code)</td>
                      </tr>';
            }
        }

        // Recherche des tags existants dans le code mais non trouvé dans la traduction
        foreach ($foundTags as $key => $value) {
            if(!isset($currentLanguage[$value])){
                echo '<tr><td class="i18n_border i18n_textcenter">'.$value.'</td>
                          <td class="i18n_border i18n_textcenter"><input type="text" name="'.$value.'" value="">+</td></tr>';
            }
        }
        echo '</table>
              <input type="submit" value="Modifier la traduction" class="button">
              </form>';

    }
    echo '</section>';
}

// scanner les tags de traduction dans Leed
function plugin_i18n_scanTags($dir){
    $return = array();
    $extensions = array('html','php','js');
    $leedFiles = scandir($dir);
    foreach($leedFiles as $file){
        if($file!='.' && $file!='..' && $file!='.git'){
            if(is_dir($dir.$file)){
                $return = array_merge($return,plugin_i18n_scanTags($dir.$file.'/'));
            }else{
                $ext = str_replace('.rtpl.php','.wrongphp',$file);
                $ext = strtolower(substr($ext,strrpos($ext,'.')+1));
                if(in_array($ext, $extensions)){
                    $content = file_get_contents($dir.$file);
                    if(preg_match_all("#_t\(([\'\\\"])([a-zA-Z0-9\_ \?\-]+)([\'\\\"])(?:,?.?)\)?#", $content, $match)){
                        //var_dump($dir.$file.'-->',$match[2]);
                        $return = array_merge($return,$match[2]);
                    }
                }
            }
        }
    }
    $return = array_unique($return);
    return $return;
}


function plugin_i18n_json_encode($json) {
    array_map('html_entity_decode',$json);
    ksort($json);
    array_walk_recursive($json,
        function (&$item, $key) {
            if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
        });
    $json = mb_decode_numericentity(json_encode($json), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
    $json = stripslashes(stripslashes($json));
    $result = '';
    $pos = 0;
    $strLen = strlen($json);
    $indentStr = ' ';
    $newLine = "\n";
    $prevChar = '';
    $outOfQuotes = true;
    for ($i=0; $i<=$strLen; $i++) {
        $char = substr($json, $i, 1);
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        } else if (($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        $result .= $char;
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        $prevChar = $char;
    }
    return $result;
}

// Ajout de la fonction au Hook situé avant l'affichage des évenements
$myUser = (isset($_SESSION['currentUser'])?unserialize($_SESSION['currentUser']):false);
if($myUser!=false) {
    Plugin::addHook("setting_post_link", "i18n_plugin_AddLink");
    Plugin::addHook("setting_post_section", "i18n_plugin_AddForm");
}

?>