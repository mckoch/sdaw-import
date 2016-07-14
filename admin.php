<?php
/**
 * @name admin.php
 * @package SDAW_Classes
 * @author mckoch - 28.04.2011
 * @copyright emcekah@gmail.com 2011
 * @version 1.1.1.1
 * @license No GPL, no CC. Property of author.
 *
 * SDAW Classes:admin
 *
 *
 */
require_once('application.ini.php');
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
        <title></title>
        <style type="text/css">
            a {float: none; clear: both; margin:.5em;}
            #DEFSFILES, #STAFILES, #LIBS {width: 220px; float: left; margin: .5em; padding: .5em}
            #UTILITIES {width: 100%; padding: 1em;}
        </style>
    </head>
    <body>
        <div id="screen" class="ui-state-highlight" style="float: none; clear: both; width: auto; padding: 1em"></div>

        <input id="adminkey" type="password" class="ui-state-highlight" style="float: none; clear: both; width: auto;"/>Schl&uuml;ssel eingeben

        <div id="UTILLINKS">
            <a href="adminapi.php?command=info&SDAW=" class="inline">Datenbank-Info</a>

            <a href="adminapi.php?command=1&SDAW=" class="inline">SDAW Datei importieren (Stammdaten STA/VMS hinzuf&uuml;gen)</a>
            <a href="adminapi.php?command=2&SDAW=" class="inline">STA als initiale Hauptauswahl importieren (Kiosk)</a>
            <a href="adminapi.php?command=3&SDAW=" class="inline">Hilfstabellen importieren</a>
            <a href="adminapi.php?command=config" class="inline">Anwendung konfigurieren / Standardwerte setzen</a>
            
            <a href="application.php?STA=" class="inline">INIT / AppTest (l&ouml;scht komplette Datenbank, NICHT empfehlenswert!!)</a>
        </div>
        <div>

            <div id="LIBS" class="ui-state-active">
                Verf&uuml;gbare Bibliotheken: <br/>
                <?php
                if ($handle = opendir(INCLUDEDIR)) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                            echo "<li>$file</li>";
                        }
                    }
                    closedir($handle);
                }
                ?>
            </div>
            <div id="STAFILES" class="ui-state-active">
                Verf&uuml;gbare SDAW-Dateien: <br/>
                <?php
                if ($handle = opendir(SDAWDIR)) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                            echo "<li>$file</li>";
                        }
                    }
                    closedir($handle);
                }
                ?>
                <a href="ftp://sdaw-upload@citylight-bonn.de/" id="FTP">Datei hochladen (ftp)</a>
            </div>

            <div id="DEFSFILES" class="ui-state-active">
                Verf&uuml;gbare Defs-Files: <br/>
                <?php
                if ($handle = opendir(DEFSDIR)) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                            echo "<li>$file </li>";
                        }
                    }
                    closedir($handle);
                }
                ?>
            </div>
            <div id="STAFILES_registered" class="ui-state-active">
                Registrierte SDAW-Dateien: <br/>
                <?php
//                if ($handle = opendir(SDAWDIR)) {
//                    while (false !== ($file = readdir($handle))) {
//                        if ($file != "." && $file != "..") {
//                            echo "<li>$file</li>";
//                        }
//                    }
//                    closedir($handle);
//                }
                ?>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function(){

                var selected;

                $('#UTILLINKS a.inline').die('click');

                $('#UTILLINKS a, #STAFILES a').button();
                $('#UTILLINKS a.inline').live('click', function(){
                    var api = $(this).attr('href') + selected + '&adminkey=' + $('#adminkey').val();
                    var tmp = $('#screen').html();
                    $('#screen').load(api, function(){
                        $('#screen').prepend(tmp);
                });
                    
                    return false;
                });
                $('#STAFILES li').live('click', function(){
                    selected = $(this).html();
                    
                    $('#STAFILES li').removeClass('ui-state-highlight');
                    $(this).toggleClass('ui-state-highlight');
                });

                $('#STAFILES_registered').load('dbedit/index.php?-table=STAKOPFSAETZE&-action=list&-cursor=0&-skip=0&-limit=50&-mode=list&-sort=count+desc #result_list');
                $('#FTP, #STAFILES_registered a, #bloglink').die('click').live('click', function(){
                    window.open(this.href);return false;
                });
            });
        </script>

    </body>
</html>
