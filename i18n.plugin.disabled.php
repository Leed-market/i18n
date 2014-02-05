<?php
/*
@name i18n
@author Cobalt74 <cobalt74@gmail.com>
@link http://www.cobestran.com
@licence CC by nc sa http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
@version 2.3.1
@description Le plugin i18n permet d'effectuer une traduction de Leed et des plugins en générant les fichiers Json souhaités
*/


// affichage d'un lien dans le menu "Gestion"
function i18n_plugin_AddLink(){
	echo '<li><a class="toggle" href="#i18n">'._t('P_I18N_PREF_TITLE').'</a></li>';
}

// affichage des option de recherche et du formulaire
function i18n_plugin_AddForm(){
    $test = array();

    echo '<section id="i18n" name="i18n" class="i18n">
			<h2>'._t('P_I18N_PREF_TITLE').'</h2>';

    /* -------------------------------------------------------- */
    // Gestion des retours des formulaires
    /* -------------------------------------------------------- */
    // cas de changement de la langue de Leed
    if(isset($_POST['plugin_i18n_changeLngLeed'])){
        $langue = substr(basename($_POST['plugin_i18n_changeLngLeed']),0,2);
        $content = file_get_contents('constant.php');
        $content = preg_replace('#define\(\'LANGUAGE\',\'([a-z]+)\'\);?#','define(\'LANGUAGE\',\''.$langue.'\')', $content);
        if (is_writable('constant.php')){
            file_put_contents('constant.php', $content);
            $test['info'][]=_t('P_I18N_MSG_CHG_LNG_LEED');
        } else {
            $test['Erreur'][]=_t('P_I18N_UPD_LNG_FILE_ERR',array('<b>constant.php</b>'));
        }
    }
    // Cas validation de la création d'une langue sur Leed.
    $newLanguage = '';
    if(isset($_POST['plugin_i18n_newLanguage'])){
        $newLanguage = $_POST['plugin_i18n_newLanguage'];
        if (is_file($newLanguage)){
            $test['Erreur'][]=_t('P_I18N_NEW_LNG_FILE_EXIST').$newLanguage;
        } else {
            file_put_contents($newLanguage, '');
            $test['Info'][]=_t('P_I18N_NEW_LNG_FILE_OK', array($newLanguage));
            $_POST['plugin_i18n_selectLanguage']=$newLanguage;
        }
    }
    // Cas d'une copy de fichier
    if(isset($_POST['plugin_i18n_copyLanguage'])){
        $copyLanguage = $_POST['plugin_i18n_copyLanguage'];
        $fileDest = $_POST['plugin_i18n_copyFileDest'];

        if (is_file($fileDest)){
            $test['Erreur'][]=_t('P_I18N_NEW_LNG_FILE_EXIST').$fileDest;
        } else {
            if (is_writable(dirname($fileDest))){
                copy($copyLanguage, $fileDest);
                $test['Info'][]=_t('P_I18N_NEW_LNG_FILE_OK', array($fileDest));
                $_POST['plugin_i18n_selectLanguage']=$fileDest;
            } else {
                $test['Erreur'][]=_t('P_I18N_VERIF_ERR1').' '.$fileDest;
            }
        }
    }
    // Cas validation d'une MAJ d'un fichier de langue
    if(isset($_POST['0123456789MAJLanguage'])){

        $_ = array_map('addslashes',array_merge($_GET, $_POST));
        ksort($_);
        $ModifLanguage = $_['0123456789MAJLanguage'];
        unset($_['0123456789MAJLanguage']);
        if(is_writable($ModifLanguage)){
            file_put_contents($ModifLanguage, plugin_i18n_json_encode($_));
            $test['Info'][]=_t('P_I18N_UPD_LNG_FILE_OK', array($_POST['0123456789MAJLanguage']));
            $_POST['plugin_i18n_selectLanguage']=$ModifLanguage;
        } else {
            $test['Erreur'][]=_t('P_I18N_UPD_LNG_FILE_ERR', array($_POST['0123456789MAJLanguage']));
        }

    }

    // Gestion des erreurs PHP possible permettant l'écriture de fichier dans les répertoires de Leed
    if(!is_writable('./locale/')){
        $test['Erreur'][]=_t('P_I18N_VERIF_ERR1');
    }
    if (!@function_exists('file_get_contents')){
        $test['Erreur'][] = _t('P_I18N_VERIF_ERR2');
    }
    if (!@function_exists('file_put_contents')){
        $test['Erreur'][] = _t('P_I18N_VERIF_ERR2');
    }
    if (@version_compare(PHP_VERSION, '5.1.0') <= 0){
        $test['Erreur'][] = _t('P_I18N_VERIF_ERR3', array(PHP_VERSION));
    }
    if(ini_get('safe_mode') && ini_get('max_execution_time')!=0){
        $test['Erreur'][] = _t('P_I18N_VERIF_ERR4');
    }

    if (count($test)!=0){
        echo '<div id="result_i18n" class="result_i18n">
                  <table>
                      <th class="i18n_border i18n_th">'._t('P_I18N_MESSAGES').'</th>';

        foreach($test as $type=>$messages){
            echo '<tr>';
            foreach($messages as $message){
                echo '<td class="i18n_border '.($type=='Erreur'?'i18n_warn':'i18n_info').'">'.$message.'</td>';
            }
            echo '</tr>';
        }

        echo '    </table>
              </div>';
    }

    // Sélectionner la langue ou saisir une nouvelle langue
    echo '<h3>'._t('P_I18N_MANAGE_LNG_TITLE').'</h3>';

    echo '<form action="settings.php#i18n" method="POST">
              <select name="plugin_i18n_changeLngLeed">';

                $filesLeed = glob('./locale/*.json');
                foreach($filesLeed as $file){
                    if ($file=='./locale/'.LANGUAGE.'.json')
                    {
                        echo '<option selected=selected value="'.$file.'">'.$file.'</option>';
                    } else {
                        echo '<option value="'.$file.'">'.$file.'</option>';
                    }
                }

    echo'     </select>
              <input type="submit" name="plugin_i18n_chgLngLeed" value="'._t('P_I18N_BTN_CHG_LNG_LEED').'" class="button">
          </form>
          <form action="settings.php#i18n" method="POST">
              <input type="text" value="" placeholder="ex : ./locale/xx.json" name="plugin_i18n_newLanguage">
              <input type="submit" name="plugin_i18n_saveButton" value="'._t('P_I18N_BTN_CREATE_FILE').'" class="button">
          </form>
          <form action="settings.php#i18n" method="POST">
              <select name="plugin_i18n_copyLanguage">';

                $filesLeed = glob('./locale/*.json');
                $filesLeed = array_merge($filesLeed,glob('./plugins/*/locale/*.json'));
                foreach($filesLeed as $file){
                    if ($_POST['plugin_i18n_selectLanguage']==$file)
                    {
                        echo '<option selected=selected value="'.$file.'">'.$file.'</option>';
                    } else {
                        echo '<option value="'.$file.'">'.$file.'</option>';
                    }
                }

    echo '    </select> '._t('P_I18N_COPY_TO').'
              <input type="text" value="" placeholder="ex: ./locale/xx.json" name="plugin_i18n_copyFileDest">
              <input type="submit" value="'._t('P_I18N_BTN_COPY_FILE').'" class="button">
          </form>
          <form action="settings.php#i18n" method="POST">
              <select name="plugin_i18n_selectLanguage">';

                //$filesLeed = glob('./locale/*.json');
                //$filesLeed = array_merge($filesLeed,glob('./plugins/*/locale/*.json'));
                foreach($filesLeed as $file){
                    if ($_POST['plugin_i18n_selectLanguage']==$file)
                    {
                        echo '<option selected=selected value="'.$file.'">'.$file.'</option>';
                    } else {
                        echo '<option value="'.$file.'">'.$file.'</option>';
                    }
                }

    echo '    </select>
              <input type="submit" value="'._t('P_I18N_BTN_LOAD_FILE').'" class="button">
          </form>';

    // sélection d'un langage à charger
    if (isset($_POST['plugin_i18n_selectLanguage'])){
        $selectLanguage = $_POST['plugin_i18n_selectLanguage'];
        echo '<hr><h3>'._t('P_I18N_UPD_FILE_TITLE', array($selectLanguage)).'</h3>
                <span>'._t('P_I18N_MSG_ALERT').'</span>';

        // On scan tous les tags de Leed
        $foundTags = array();
        $foundTags = plugin_i18n_scanTags(dirname($selectLanguage).'/../', 'plugins');
        // On charge le fichier de langue existant
        $currentLanguage = json_decode(file_get_contents($selectLanguage),true);
        ksort($currentLanguage);

        echo '<hr><h4>'._t('P_I18N_KEY_INFILE_TITLE').'</h4>
              <form action="settings.php#i18n" method="POST">
              <input type="hidden" name="0123456789MAJLanguage" value="'.$selectLanguage.'">
              <table class="diffTab">
                <tr>
                    <th class="i18n_border i18n_th">'._t('P_I18N_KEY_FILE_NB_KEY',array(count($currentLanguage))).'</th>
                    <th class="i18n_border i18n_th">'._t('P_I18N_KEY_CODE_NB_KEY',array(count($foundTags))).'</th>
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

        echo '<hr><h4>'._t('P_I18N_KEY_INFILE_NOTFND_TITLE').'</h4>
              <table class="diffTab">
                <tr>
                    <th class="i18n_border i18n_th">'._t('P_I18N_KEY_FILE_NB_KEY',array(count($currentLanguage))).'</th>
                    <th class="i18n_border i18n_th">'._t('P_I18N_KEY_CODE_NB_KEY',array(count($foundTags))).'</th>
                </tr>';

        // recherche des tags existant mais non trouvé dans la recherche du code
        foreach ($currentLanguage as $key => $value) {
            if(!in_array($key, $foundTags, true)){
                echo '<tr><td class="i18n_border i18n_textcenter">'.$key.'</td>
                          <td class="i18n_border i18n_textcenter">'.$value.'<br />'._t('P_I18N_MSG_NOT_FND_CODE').'</td>
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
              <input type="submit" value="'._t('P_I18N_BTN_UPD_FILE').'" class="button">
              </form>';

    }
    echo '</section>';
}

// scanner les tags de traduction dans Leed
function plugin_i18n_scanTags($dir, $exclude=''){
    $return = array();
    $extensions = array('html','php','js');
    $leedFiles = scandir($dir);
    //var_dump($leedFiles);
    foreach($leedFiles as $file){
        if($file!='.' && $file!='..' && $file!='.git' && $file!=$exclude){
            if(is_dir($dir.$file)){
                $return = array_merge($return,plugin_i18n_scanTags($dir.$file.'/', $exclude));
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