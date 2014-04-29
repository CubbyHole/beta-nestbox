<?php
header('Content-Type: text/html; charset=utf-8');
require_once('controller/functions.php');
if(isset($_GET['filepath']))
{
    download($_GET['filepath']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script type="text/javascript" src="content/js/navigation.js"></script>

    <style type="text/css">
        * {
            font-size: 10pt;
        }

        a:link, a:hover, a:active, a:visited {
            color: #0000FF;
        }
    </style>
</head>

<body>
<?php
function RepEfface($dir)
{
    $handle = opendir($dir);
    while($elem = readdir($handle))
    //ce while vide tous les repertoires et sous rep
    {
        if(is_dir($dir.'/'.$elem) && substr($elem, -2, 2) !== '..' && substr($elem, -1, 1) !== '.') //si c'est un repertoire
        {
            RepEfface($dir.'/'.$elem);
        }
        else
        {
            if(substr($elem, -2, 2) !== '..' && substr($elem, -1, 1) !== '.') // si c'est un fichier
            {
                unlink($dir.'/'.$elem);
            }
        }

    }

    $handle = opendir($dir);
    while($elem = readdir($handle)) //ce while efface tous les dossiers
    {
        if(is_dir($dir.'/'.$elem) && substr($elem, -2, 2) !== '..' && substr($elem, -1, 1) !== '.') //si c'est un repertoire
        {
            RepEfface($dir.'/'.$elem);
            rmdir($dir.'/'.$elem);
        }

    }
    rmdir($dir); //ce rmdir efface le repertoire principal
}


if(isset($_GET['delete']))
{ //si c'est un delete pour un fichier
    unlink(utf8_decode($_GET['delete']));
}

if(isset($_GET['delete_folder']))
{ // si c'est un delete pour un dossier
    RepEfface(utf8_decode($_GET['delete_folder']));
}



$order = isset($_GET['order']) ? $_GET['order'] : '';
$order0 = isset($_GET['order0']) ? $_GET['order0'] : '';
$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$asc = isset($_GET['asc']) ? $_GET['asc'] : '';
//$user = "css";

/* racine */
//$BASE = './'.$user;
$BASE = './Bellod';




/** Infos sur les fichiers/dossiers  **/
function addScheme($entry, $base, $type)
{
    $encode_entry = utf8_decode($entry);
    $encode_base = utf8_decode($base);

    $tab['name'] = $entry;
    $tab['type'] = filetype($encode_base . '/' . $encode_entry);
    $tab['date'] = filemtime($encode_base . '/' . $encode_entry);
    $tab['size'] = filesize($encode_base . '/' . $encode_entry);
    $tab['perms'] = fileperms($encode_base . '/' . $encode_entry);
    $tab['access'] = fileatime($encode_base . '/' . $encode_entry);
    $t = explode('.', $entry);
    $tab['ext'] = $t[count($t) - 1];
    //echo $base . '/' . $entry . '/' .$type. '+  '. filetype(utf8_decode($base) . '/' . utf8_decode($entry));
    return $tab;
}


/** Listes des dossiers pour l'arborescence **/
function list_dir($base, $cur, $level = 0)
{
    global $BASE, $order, $asc;
    if ($dir = opendir($base))
    {
        $tab = array();
        while ($entry = readdir($dir))
        {
            if (is_dir($base . '/' . $entry) && !in_array($entry, array('.', '..')))
            {
                $tab[] = addScheme(utf8_encode($entry), utf8_decode($base), 'dir');
            }
        }
        /* tri */
        usort($tab, 'cmp_name');
        foreach ($tab as $elem)
        {
            //echo '<div id="arbo">';
            $entry = $elem['name'];
            /* chemin relatif à la racine */
            $file = $base . '/' . $entry;
            /* marge gauche */
            for ($i = 1; $i <= (5 * $level); $i++)
            {
                echo '&nbsp;';
            }

            /** on vérifie si c'est le dossier courant **/
            if ($file == $cur)
            {
                echo "<img src=\"content/img/icon_dir_open.png\" width='18px' height='18px'/> $entry<br />\n";
            }
            else
            {

                echo
                    "  <img src=\"content/img/icon_dir_close.png\" width='18px' height='18px' /> <a href=\"$_SERVER[PHP_SELF]?dir=" .
                    $file . "\">".$entry."</a><br />\n";
            }

            /** récursivité pour lister les sous-dossiers **/
            if (ereg($file . '/', $cur . '/'))
            {
                list_dir($file, $cur, $level + 1);
            }
           // echo "</div>";
        }
        closedir($dir);
    }
}


/** liste des fichiers du répertoire courant**/
function list_file($cur)
{
    $current = utf8_decode($cur);

    global $order, $asc, $order0;
    if ($dir = opendir($current))
    {
        /* tableaux */
        $tab_dir = array();
        $tab_file = array();
        /* extraction */
        echo '<table cellspacing="0" cellpadding="0" border="0">';

        while ($file = readdir($dir))
        {

            if (is_dir($current . '/' . $file))
            {
                if (!in_array($file, array('.', '..')))
                {
                    $tab_dir[] = addScheme(utf8_encode($file), $cur, 'dir');
                }
            }
            else
            {
                $tab_file[] = addScheme(utf8_encode($file), $cur, 'file');
            }
        }
        /* tri */
        usort($tab_dir, 'cmp_' . $order);
        usort($tab_file, 'cmp_' . $order);
        /* affichage */


        echo '<tr style="font-size:8pt;font-family:arial;">
    <th>' . (($order == 'name') ? (($asc == 'a') ? '/\\ ' : '\\/ ') : '') .
            "<a href=\"$_SERVER[PHP_SELF]?dir=" . rawurlencode($current) .
            "&order=name&asc=$asc&order0=$order\">Nom</a></th><td> </td>
    <th>" . (($order == 'size') ? (($asc == 'a') ? '/\\ ' : '\\/ ') : '') .
            "<a href=\"$_SERVER[PHP_SELF]?dir=" . rawurlencode($current) .
            "&order=size&asc=$asc&order0=$order\">Taille</a></th><td> </td>
    <th>" . (($order == 'date') ? (($asc == 'a') ? '/\\ ' : '\\/ ') : '') .
            "<a href=\"$_SERVER[PHP_SELF]?dir=" . rawurlencode($current) .
            "&order=date&asc=$asc&order0=$order\">Dernière modification</a></th><td>" .
            " </td>
                <th>" . (($order == 'type') ? (($asc == 'a') ? '/\\ ' : '\\/ ') : '') .
            "<a href=\"$_SERVER[PHP_SELF]?dir=" . rawurlencode($current) .
            "&order=type&asc=$asc&order0=$order\">Type</a></th><td> </td>
    <th>" . (($order == 'ext') ? (($asc == 'a') ? '/\\ ' : '\\/ ') : '') .
            "<a href=\"$_SERVER[PHP_SELF]?dir=" . rawurlencode($current) .
            "&order=ext&asc=$asc&order0=$order\">Extention</a></th><td> </td>
    <th>" . (($order == 'perms') ? (($asc == 'a') ? '/\\ ' : '\\/ ') : '') .
            "<a href=\"$_SERVER[PHP_SELF]?dir=" . rawurlencode($current) .
            "&order=perms&asc=$asc&order0=$order\">Permissions</a></th><td> </td>
    <th>" . (($order == 'access') ? (($asc == 'a') ? '/\\ ' : '\\/ ') : '') .
            "<a href=\"$_SERVER[PHP_SELF]?dir=" . rawurlencode($current) .
            "&order=access&asc=$asc&order0=$order\">Dernier accès</a></th></tr>";


        foreach ($tab_dir as $elem)
        {
            if(is_empty_dir($elem))
            {
                echo "vide";
            }
            else
            {
                echo "pas vide";
            }
            $entry = $elem['name'];
            echo '<tr><td><img src="content/img/icon_dir_not_empty.png" width="18px" height="18px" /><a href="'.$_SERVER[PHP_SELF] .'?dir=' .
                $current . '/'. $entry .'">'. $entry.'</a></td>';
            echo '<td> </td>
                      <td> </td><td>  </td>
                      <td>' . date("d/m/Y H:i:s", $elem['date']) . '</td><td> </td>
                      <td>' . assocType($elem['type']) . '</td><td> </td>
                      <td> </td><td>  </td>
                      <td>' . $elem['perms'] . '</td><td>  </td>
                      <td>' . date("d/m/Y", $elem['access']) . '</td>
                      <td><a href="'.$_SERVER['PHP_SELF'].'?dir='.$cur.'&delete_folder='. $cur . '/' .$elem['name'] .'">delete</a></td>
                      </tr>';
        }

        foreach ($tab_file as $elem)
        {
            if(assocExt($elem['ext']) == "inconnu")
                $elem['ext'] = "txt";
            echo '<tr><td><img src="content/img/' . imageExt($elem['ext']) . '" width="18px" height="18px" /> ' . $elem['name'] .
                '</td><td> </td>
                  <td align="right">' . formatSize($elem['size']) . '</td><td> </td>
                  <td>' . date("d/m/Y H:i:s", $elem['date']) . '</td><td>  </td>
                  <td>' . assocType($elem['type']) . '</td><td>  </td>
                  <td>' . assocExt($elem['ext']) . '</td><td>  </td>
                  <td>' . $elem['perms'] . '</td><td>  </td>
                  <td>' . date("d/m/Y", $elem['access']) . '</td>
                  <td><a href="'.$_SERVER['PHP_SELF'].'?dir='.$cur.'&delete='. $cur . '/' .$elem['name'] .'">delete </a></td>

      <td><a href="'.$_SERVER['PHP_SELF'].'?filepath='.$cur.'/'.$elem['name'].'" onclick.window.open(this.href); return confirm("Do you want to upload ?");>/download</a></td>
      <td><button onclick="window.open(http://www.google.com)">Click me</button></td>
      <tr>';
        }
        //echo "</div>";
        echo "</table>";
        closedir($dir);
    }
}

// contenu du répertoire
function is_empty_dir($dir)
{
    if (is_dir($dir))
    {
        if ($Pointeur = opendir($dir))
        {
            while (($file = readdir($Pointeur)) !== false)
            {
                if ($file!="." && $file!=".." ) $OK=0;
                if ($file=="." || $file==".." ) $OK=1;
            }
            closedir($Pointeur);
        }
    }
    if( $OK==99) echo ("Le répertoire n'existe pas");
    if( $OK==1) echo ("Le répertoire existe et est vide");
    if( $OK==0) echo ("Le répertoire n'est pas vide");
}

/* formatage de la taille */
function formatSize($s)
{
    /* unités */
    $u = array('oct', 'Ko', 'Mo', 'Go', 'To');
    /* compteur de passages dans la boucle */
    $i = 0;
    /* nombre à afficher */
    $m = 0;
    /* division par 1024 */
    while ($s >= 1)
    {
        $m = $s;
        $s /= 1024;
        $i++;
    }
    if (!$i) $i = 1;
    $d = explode('.', $m);
    /* s'il y a des décimales */
    if ($d != $m)
    {
        $m = number_format($m, 2, ',', ' ');
    }
    return $m . ' ' . $u[$i - 1];
}


/* formatage du type */
function assocType($type)
{
    /* tableau de conversion */
    $t = array(
        'fifo' => 'file',
        'char' => 'fichier spécial en mode caractère',
        'dir' => 'dossier',
        'block' => 'fichier spécial en mode bloc',
        'link' => 'lien symbolique',
        'file' => 'fichier',
        'unknown' => 'inconnu'
    );
    return $t[$type];
}

/* récupération de l'image en fonction de l'extension */
function imageExt($ext)
{
    $i = array(
        '' => "icon_unknow.png",

        'sh' => "icon_script_x.png",
        'bsh' => "icon_script_x.png",
        'mak' => "icon_script_x.png",
        'cmake' => "icon_script_x.png",
        'cmd' => "icon_script_x.png",
        'nt' => "icon_script_x.png",
        'bat' => "icon_bat.png",
        'exe' => "icon_exe.png",
        'ps' => "icon_ps.png",
        'py' => "icon_script_x.png",
        'pym' => "icon_script_x.png",
        'sql' => "icon_sql.png",
        'js' => "icon_js.png",

        'bmp','gif' => "icon_word.png",

    );
    if (in_array($ext, array_keys($i)))
    {
        return $i[$ext];
    }
    else
    {
        return $i[''];
    }

}

/* description de l'extension */
function assocExt($ext)
{
    $e = array(
        '' => "inconnu",
        'doc' => "Microsoft Word",
        'xls' => "Microsoft Excel",
        'ppt' => "Microsoft Power Point",
        'pdf' => "Adobe Acrobat",
        'zip' => "Archive WinZip",
        'txt' => "Document texte",
        'gif' => "Image GIF",
        'jpg' => "Image JPEG",
        'png' => "Image PNG",
        'php' => "Script PHP",
        'php3' => "Script PHP",
        'htm' => "Page web",
        'html' => "Page web",
        'css' => "Feuille de style",
        'js' => "JavaScript"
    );
    if (in_array($ext, array_keys($e)))
    {
        return $e[$ext];
    }
    else
    {
        return $e[''];
    }
}


function cmp_name($a, $b)
{
    global $asc;
    if ($a['name'] == $b['name']) return;
    if ($asc == 'a')
    {
        return ($a['name'] < $b['name']) ? -1 : 1;
    }
    else
    {
        return ($a['name'] > $b['name']) ? -1 : 1;
    }
}

function cmp_size($a, $b)
{
    global $asc;
    if ($a['size'] == $b['size']) return cmp_name($a, $b);
    if ($asc == 'a')
    {
        return ($a['size'] < $b['size']) ? -1 : 1;
    }
    else
    {
        return ($a['size'] > $b['size']) ? -1 : 1;
    }
}

function cmp_date($a, $b)
{
    global $asc;
    if ($a['date'] == $b['date']) return cmp_name($a, $b);
    if ($asc == 'a')
    {
        return ($a['date'] < $b['date']) ? -1 : 1;
    }
    else
    {
        return ($a['date'] > $b['date']) ? -1 : 1;
    }
}

function cmp_access($a, $b)
{
    global $asc;
    if ($a['access'] == $b['access']) return cmp_name($a, $b);
    if ($asc == 'a')
    {
        return ($a['access'] < $b['access']) ? -1 : 1;
    }
    else
    {
        return ($a['access'] > $b['access']) ? -1 : 1;
    }
}

function cmp_perms($a, $b)
{
    global $asc;
    if ($a['perms'] == $b['perms']) return cmp_name($a, $b);
    if ($asc == 'a')
    {
        return ($a['perms'] < $b['perms']) ? -1 : 1;
    }
    else
    {
        return ($a['perms'] > $b['perms']) ? -1 : 1;
    }
}

function cmp_type($a, $b)
{
    global $asc;
    if ($a['type'] == $b['type']) return cmp_name($a, $b);
    if ($asc == 'a')
    {
        return ($a['type'] < $b['type']) ? -1 : 1;
    }
    else
    {
        return ($a['type'] > $b['type']) ? -1 : 1;
    }
}

function cmp_ext($a, $b)
{
    global $asc;
    if ($a['ext'] == $b['ext']) return cmp_name($a, $b);
    if ($asc == 'a')
    {
        return ($a['ext'] < $b['ext']) ? -1 : 1;
    }
    else
    {
        return ($a['ext'] > $b['ext']) ? -1 : 1;
    }
}


?>
<div id="contenu">
<table border="1" cellspacing="0" cellpadding="10" bordercolor="gray">
    <tr valign="top">

        <td>

            <div id="arbo">
            <!-- liste des répertoires
            et des sous-répertoires -->
            <?php
            if (!in_array($order, array('name', 'date', 'size', 'perms', 'ext', 'access', 'type')))
            {
                $order = 'name';
            }
            if (($order == $order0) && ($asc != 'b'))
            {
                $asc = 'b';
            }
            else
            {
                $asc = 'a';
            }
            /* lien sur la racine */
            if (!$dir)
            {
                echo "<img src=\"content/img/icon_dir_open.png\" width='18px' heigth='18px' /> Root <br />\n";
            }
            else
            {
                echo
                    "<div id=\"arbo\"><img src=\"content/img/icon_dir_close.png\" width='18px' heigth='18px' /> <a href=\"$_SERVER[PHP_SELF]\">Root</a></div>" .
                    "\n";
            }
            list_dir($BASE,$dir, 1);
            ?>
    </div>

        </td>

        <td>

<div id="arbo">
            <!-- liste des fichiers -->
            <?php

            /* répertoire initial à lister */
            if (!$dir)
            {
                $dir = $BASE;
            }

            list_file($dir);

            ?>
   </div>
        </td>
    </tr>
</table>
</div>
</body>
</html>
