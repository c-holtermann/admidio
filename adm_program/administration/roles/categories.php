<?php
/******************************************************************************
 * Uebersicht und Pflege aller Kategorien
 *
 * Copyright    : (c) 2004 - 2007 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Module-Owner : Markus Fassbender
 * License      : http://www.gnu.org/licenses/gpl-2.0.html GNU Public License 2
 *
 * Uebergaben:
 *
 * type : (Pflichtuebergabe) 
 *        Typ der Kategorien, die gepflegt werden sollen
 *        ROL = Rollenkategorien
 *        LNK = Linkkategorien
 *
 ****************************************************************************/
 
require("../../system/common.php");
require("../../system/login_valid.php");

// lokale Variablen der Uebergabevariablen initialisieren
$req_type = "";

// Uebergabevariablen pruefen

// Modus und Rechte pruefen
if(isset($_GET['type']))
{
    if($_GET['type'] != "ROL" && $_GET['type'] != "LNK" && $_GET['type'] != "USF")
    {
        $g_message->show("invalid");
    }
    if($_GET['type'] == "ROL" && $g_current_user->assignRoles() == false)
    {
        $g_message->show("norights");
    }
    if($_GET['type'] == "LNK" && $g_current_user->editWeblinksRight() == false)
    {
        $g_message->show("norights");
    }
    if($_GET['type'] == "USF" && $g_current_user->editUser() == false)
    {
        $g_message->show("norights");
    }
    $req_type = $_GET['type'];
}
else
{
    $g_message->show("invalid");
}

$_SESSION['navigation']->addUrl($g_current_url);
unset($_SESSION['categories_request']);

// Html-Kopf ausgeben
$g_layout['title']  = "Kategorien";
$g_layout['header'] = "
    <script type=\"text/javascript\" src=\"$g_root_path/adm_program/system/ajax.js\"></script>
    <script src=\"$g_root_path/adm_program/libs/script.aculo.us/prototype.js\" type=\"text/javascript\"></script>
    <script src=\"$g_root_path/adm_program/libs/script.aculo.us/scriptaculous.js?load=effects,dragdrop\" type=\"text/javascript\"></script>
    
    <style type=\"text/css\">
        .drag {
            background-color: #e9ec79;
        }
    </style>
    
    <script type=\"text/javascript\">
        var resObject     = createXMLHttpRequest();
        
        function updateDB(element)
        {
            var childs = element.childNodes;

            for(i=0;i < childs.length; i++)
            {
                var id = childs[i].getAttribute('id');
                var cat_id = id.substr(4);
                var sequence = i + 1;
                // Synchroner Request, da ansonsten Scriptaculous verrueckt spielt
                resObject.open('GET', '$g_root_path/adm_program/administration/roles/categories_function.php?cat_id=' + cat_id + '&type=". $_GET['type']. "&mode=4&sequence=' + sequence, false);
                resObject.send(null);
            }
        }
    </script>";
    
require(SERVER_PATH. "/adm_program/layout/overall_header.php");

// Html des Modules ausgeben
echo "
<h1 class=\"moduleHeadline\">Kategorien</h1>

<ul class=\"iconTextLink\">
    <li>
        <a href=\"$g_root_path/adm_program/administration/roles/categories_new.php?type=$req_type\"><img 
        src=\"$g_root_path/adm_program/images/add.png\" alt=\"Kategorie anlegen\"></a>
        <a href=\"$g_root_path/adm_program/administration/roles/categories_new.php?type=$req_type\">Kategorie anlegen</a>
    </li>
</ul>

<table class=\"tableList\" style=\"width: 300px;\" cellspacing=\"0\">
    <thead>
        <tr>
            <th colspan=\"2\">Bezeichnung</th>
            <th><img class=\"iconInformation\" src=\"$g_root_path/adm_program/images/user_key.png\" alt=\"Kategorie nur f&uuml;r eingeloggte Benutzer sichtbar\" title=\"Kategorie nur f&uuml;r eingeloggte Benutzer sichtbar\"></th>
            <th>&nbsp;</th>
        </tr>
    </thead>";
    
    $sql = "SELECT * FROM ". TBL_CATEGORIES. "
             WHERE (  cat_org_id  = ". $g_current_organization->getValue("org_id"). "
                   OR cat_org_id IS NULL )
               AND cat_type   = '$req_type'
             ORDER BY cat_sequence ASC ";
    $cat_result = $g_db->query($sql);
    $write_tbody = false;

    while($cat_row = $g_db->fetch_array($cat_result))
    {
        // da bei USF die Kategorie Stammdaten nicht verschoben werden darf, muss hier ein bischen herumgewurschtelt werden
        if($cat_row['cat_name'] == "Stammdaten" && $_GET['type'] == "USF")
        {
            $drag_icon = "&nbsp;";
            echo "<tbody id=\"cat_stammdaten\">";
        }
        else
        {
            if($write_tbody == false)
            {
                $write_tbody = true;
                if($_GET['type'] == "USF")
                {
                    echo "</tbody>";
                }
                echo "<tbody id=\"cat_list\">";
            }
            $drag_icon = "<img class=\"dragable\" src=\"$g_root_path/adm_program/images/arrow_out.png\" alt=\"Reihenfolge &auml;ndern\" title=\"Reihenfolge &auml;ndern\">";
        }
        echo "
        <tr id=\"row_". $cat_row['cat_id']. "\" class=\"listMouseOut\" onmouseover=\"this.className='listMouseOver'\" onmouseout=\"this.className='listMouseOut'\">
            <td style=\"width: 5%;\">$drag_icon</td>
            <td><a href=\"$g_root_path/adm_program/administration/roles/categories_new.php?cat_id=". $cat_row['cat_id']. "&amp;type=$req_type\">". $cat_row['cat_name']. "</a></td>
            <td>";
                if($cat_row['cat_hidden'] == 1)
                {
                    echo "<img class=\"iconInformation\" src=\"$g_root_path/adm_program/images/user_key.png\" alt=\"Kategorie nur f&uuml;r eingeloggte Benutzer sichtbar\" title=\"Kategorie nur f&uuml;r eingeloggte Benutzer sichtbar\">";
                }
                else
                {
                    echo "&nbsp;";
                }
            echo "</td>
            <td style=\"text-align: right; width: 45px;\">
                <a href=\"$g_root_path/adm_program/administration/roles/categories_new.php?cat_id=". $cat_row['cat_id']. "&amp;type=$req_type\">
                <img src=\"$g_root_path/adm_program/images/edit.png\" border=\"0\" alt=\"Bearbeiten\" title=\"Bearbeiten\"></a>&nbsp;";

                if($cat_row['cat_system'] == 1)
                {
                    echo "<img src=\"$g_root_path/adm_program/images/dummy.gif\" border=\"0\" alt=\"dummy\" style=\"width: 16px; height: 16px;\">";
                }
                else
                {
                    echo "<a href=\"$g_root_path/adm_program/administration/roles/categories_function.php?cat_id=". $cat_row['cat_id']. "&amp;mode=3&amp;type=$req_type\"><img
                    src=\"$g_root_path/adm_program/images/cross.png\" border=\"0\" alt=\"L&ouml;schen\" title=\"L&ouml;schen\"></a>";
                }
            echo "</td>
        </tr>";
    }
    echo "</tbody>
</table>

<ul class=\"iconTextLink\">
    <li>
        <a href=\"$g_root_path/adm_program/system/back.php\"><img
        src=\"$g_root_path/adm_program/images/back.png\" alt=\"Zur&uuml;ck\"></a>
        <a href=\"$g_root_path/adm_program/system/back.php\">Zur&uuml;ck</a>
    </li>
</ul>

<script type=\"text/javascript\"><!--
    Sortable.create('cat_list',{tag:'tr',onUpdate:updateDB,ghosting:true,dropOnEmpty:true,containment:['cat_list'],hoverclass:'drag'});
--></script>";

require(SERVER_PATH. "/adm_program/layout/overall_footer.php");

?>