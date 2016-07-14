<?php

/**
 * @name adminapidispatcher.php
 * @package SDAW_Classes
 * @author mckoch - 28.04.2011
 * @copyright emcekah@gmail.com 2011
 * @version 1.1.1.1
 * @license No GPL, no CC. Property of author.
 * 
 * SDAW Classes:adminapidispatcher
 *
 * 
 */
try {
    /**
     * var $command
     */
    if (isset($_GET['command'])) {
        $do = $_GET['command'];
    } else
        $do='default';

    /**
     * neuen Dispatcher erzeugen
     */
    $d = new DISP;

    /**
     * Auswahl der Aktion für Dispatcher $d
     */
    switch ($do) {
        /**
         * Datensätze in STA zählen
         */
        case 'info':


            return  print $d->exec('info');

            break;


        /**
         * Import einer STA Datei
         * (Anfügen)
         */
        case '1':
            $sdawfile = SDAWDIR . $_GET['SDAW'];

            ini_set('max_execution_time', 600);
            ini_set('memory_limit', '1024M');
            /**
             * erzeugt neues TextFileImport-Objekt
             */
            $insert = $d->exec('insert');
            /**
             * Definitionsdatei laden
             */

            $insert->loadDefsFile(DEFSDIR . strtoupper(substr($_GET['SDAW'],0,3)) . '.xml');
            /**
             * Daten aus SDAW Textdatei laden
             * und Tabelle schreiben.
             */
            $insert->loadSdawFile($sdawfile);
            unset($insert);

            trigger_error("Datenimport $sdawfile ausgeführt.", E_USER_NOTICE);
            break;

        case '2':
            /**
             * importiert die Standardauswahl (Tabelle STA_DEFAULT)
             * aus StellenDefault.xml
             * (siehe application.ini.php)
             */
            /**
             * neues XmlTableStructureImport erzeugen
             */
            $newtable = $d->exec('newtable');
            /**
             * XML-Datei $defsfile einlesen
             */
            $newtable->getXmlFile($GLOBALS['StellenFileDefault']);
            /**
             * leere Tabelle in Datenbank erzeugen
             */
            $newtable->writeNewTable();
            unset($newtable);

            /**
             * erzeugt neues TextFileImport-Objekt
             */
            $insert = $d->exec('insert');
            /**
             * Definitionsdatei laden
             */
            $insert->loadDefsFile($GLOBALS['StellenFileDefault']);
            /**
             * Daten aus SDAW Textdatei laden
             * und Tabelle schreiben.
             */
            $sdawfile = $_GET['SDAW'];
            $insert->loadSdawFile(SDAWDIR . $sdawfile);
            unset($insert);

            trigger_error("Tabelle neu angelegt, Datenimport $sdawfile für Standardauswahl ausgeführt.", E_USER_NOTICE);


            break;
        case '3':
                require_once('application.helper.install.php');
            break;
    }
} catch (Exception $e) {
    echo "<pre>" . $e . "</pre>";
}
?>
