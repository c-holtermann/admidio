<?php
/******************************************************************************
 * Ankuendigungen anlegen und bearbeiten
 *
 * Copyright    : (c) 2004 - 2007 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Module-Owner : Markus Fassbender
 * License      : http://www.gnu.org/licenses/gpl-2.0.html GNU Public License 2
 *
 * Uebergaben:
 *
 * ann_id    - ID der Ankuendigung, die bearbeitet werden soll
 * headline  - Ueberschrift, die ueber den Ankuendigungen steht
 *             (Default) Ankuendigungen
 *
 *****************************************************************************/

require("../../system/common.php");
require("../../system/login_valid.php");
require("../../system/announcement_class.php");

// pruefen ob das Modul ueberhaupt aktiviert ist
if ($g_preferences['enable_announcements_module'] != 1)
{
    // das Modul ist deaktiviert
    $g_message->show("module_disabled");
}

if(!$g_current_user->editAnnouncements())
{
    $g_message->show("norights");
}

// lokale Variablen der Uebergabevariablen initialisieren
$req_ann_id   = 0;
$req_headline = "Ank&uuml;ndigungen";

// Uebergabevariablen pruefen

if(isset($_GET['ann_id']))
{
    if(is_numeric($_GET['ann_id']) == false)
    {
        $g_message->show("invalid");
    }
    $req_ann_id = $_GET['ann_id'];
}

if(isset($_GET['headline']))
{
    $req_headline = strStripTags($_GET["headline"]);
}

$_SESSION['navigation']->addUrl($g_current_url);

// Ankuendigungsobjekt anlegen
$announcement = new Announcement($g_db);

if($req_ann_id > 0)
{
    $announcement->getAnnouncement($req_ann_id);
    
    // Pruefung, ob der Termin zur aktuellen Organisation gehoert bzw. global ist
    if($announcement->getValue("ann_org_shortname") != $g_organization
    && $announcement->getValue("ann_global") == 0 )
    {
        $g_message->show("norights");
    }
}

if(isset($_SESSION['announcements_request']))
{
    // durch fehlerhafte Eingabe ist der User zu diesem Formular zurueckgekehrt
    // nun die vorher eingegebenen Inhalte auslesen
    foreach($_SESSION['announcements_request'] as $key => $value)
    {
        if(strpos($key, "ann_") == 0)
        {
            $announcement->setValue($key, stripslashes($value));
        }        
    }
    unset($_SESSION['announcements_request']);
}

// Html-Kopf ausgeben
$g_layout['title'] = $req_headline;

require(SERVER_PATH. "/adm_program/layout/overall_header.php");

// Html des Modules ausgeben
echo "
<form name=\"form\" method=\"post\" action=\"$g_root_path/adm_program/modules/announcements/announcements_function.php?ann_id=$req_ann_id&amp;headline=". $_GET['headline']. "&amp;mode=1\">
<div class=\"formLayout\" id=\"edit_announcements_form\">
    <div class=\"formHead\">";
        if($req_ann_id > 0)
        {
            echo "$req_headline &auml;ndern";
        }
        else
        {
            echo "$req_headline anlegen";
        }
    echo "</div>
    <div class=\"formBody\">
        <ul class=\"formFieldList\">
            <li>
                <dl>
                    <dt><label for=\"ann_headline\">&Uuml;berschrift:</label></dt>
                    <dd>
                        <input type=\"text\" id=\"ann_headline\" name=\"ann_headline\" style=\"width: 350px;\" tabindex=\"1\" maxlength=\"100\" value=\"". htmlspecialchars($announcement->getValue("ann_headline"), ENT_QUOTES). "\">
                        <span class=\"mandatoryFieldMarker\" title=\"Pflichtfeld\">*</span>
                    </dd>
                </dl>
            </li>
            <li>
                <dl>
                    <dt><label for=\"ann_description\">Beschreibung:</label>";
                        if($g_preferences['enable_bbcode'] == 1)
                        {
                          echo "<br><br>
                          <a href=\"#\" onclick=\"window.open('$g_root_path/adm_program/system/msg_window.php?err_code=bbcode','Message','width=600,height=500,left=310,top=200,scrollbars=yes')\" tabindex=\"6\">Text formatieren</a>";
                        }
                    echo "</dt>
                    <dd>
                        <textarea id=\"ann_description\" name=\"ann_description\" style=\"width: 350px;\" tabindex=\"2\" rows=\"10\" cols=\"40\">". htmlspecialchars($announcement->getValue("ann_description"), ENT_QUOTES). "</textarea>
                        <span class=\"mandatoryFieldMarker\" title=\"Pflichtfeld\">*</span>
                    </dd>
                </dl>
            </li>";

            // besitzt die Organisation eine Elternorga oder hat selber Kinder, so kann die Ankuendigung auf "global" gesetzt werden
            if($g_current_organization->getValue("org_org_id_parent") > 0
            || $g_current_organization->hasChildOrganizations())
            {
                echo "
                <li>
                    <dl>
                        <dt>&nbsp;</dt>
                        <dd>
                            <input type=\"checkbox\" id=\"ann_global\" name=\"ann_global\" tabindex=\"3\" ";
                            if($announcement->getValue("ann_global") == 1)
                            {
                                echo " checked=\"checked\" ";
                            }
                            echo " value=\"1\" />
                            <label for=\"ann_global\">$req_headline f&uuml;r mehrere Organisationen sichtbar</label>
                            <img class=\"iconHelpLink\" src=\"$g_root_path/adm_program/images/help.png\" alt=\"Hilfe\" title=\"Hilfe\"
                            onclick=\"window.open('$g_root_path/adm_program/system/msg_window.php?err_code=termin_global','Message','width=400,height=350,left=310,top=200,scrollbars=yes')\">
                        </dd>
                    </dl>
                </li>";
            }
        echo "</ul>

        <hr />

        <div class=\"formSubmit\">
            <button name=\"speichern\" type=\"submit\" tabindex=\"5\" value=\"speichern\" tabindex=\"4\">
                <img src=\"$g_root_path/adm_program/images/disk.png\" alt=\"Speichern\">
                &nbsp;Speichern</button>
        </div>
    </div>
</div>
</form>

<ul class=\"iconTextLink\">
    <li>
        <a href=\"$g_root_path/adm_program/system/back.php\"><img 
        src=\"$g_root_path/adm_program/images/back.png\" alt=\"Zur&uuml;ck\"></a>
        <a href=\"$g_root_path/adm_program/system/back.php\">Zur&uuml;ck</a>
    </li>
</ul>
        
<script type=\"text/javascript\"><!--
    document.getElementById('ann_headline').focus();
--></script>";

require(SERVER_PATH. "/adm_program/layout/overall_footer.php");

?>