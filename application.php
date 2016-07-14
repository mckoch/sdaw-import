<?php

/**
 * @name application.php
 * @package SDAW
 * @author mckoch
 * @copyright emcekah@gmail.com 2011
 * @version 1.1.1.1
 *
 * 
 *
 * Testanwendung für SDAW Klassenbibliothek
 *
 * Dieses Programm initialisiert eine bestehende Datenbank und
 * legt eine Datenbank-Tabelle aus einer beliebigen SDAW-Datei
 * nach Vorlage einer SDAW Definitionsdatei neu an.
 * Hilfstabellen werden installiert.
 *
 * Experimentell: SDAW Dateien schreiben.
 *
 */
require_once('application.ini.php');

if ($_GET['adminkey'] != $GLOBALS['validadminkey']) {print "Kein g&uuml;ltiger Schl&uuml;ssel. ";die;}

/**
 * $defsfile = die XML-Definition der zu importierenden SDAW-Formate
 * $sdawfile = die ungepackte SDAW-Datei
 */
$defsfile = DEFSDIR . 'STA.xml';
if (!$_GET['STA']) {
    $sdawfile = SDAWDIR . 'STA66611.txt';
} else
    $sdawfile = SDAWDIR . $_GET['STA'];

try {
    try {
        /**
         * neuer Dispatcher
         */
        $d = new DISP;
        /**
         * neues INI erzeugen
         * (nur bei
         */
        $i = $d->exec('init');
        /**
         * Initialisieren der bestehenden!! Datenbank
         */
        $i->initialize();
    } catch (Exception $e) {
        echo "<pre>" . $e . "</pre>";
    }


    try {
        /**
         * neues XmlTableStructureImport erzeugen
         */
        $newtable = $d->exec('newtable');
        /**
         * XML-Datei $defsfile einlesen
         */
        $newtable->getXmlFile($defsfile);
        /**
         * leere Tabelle in Datenbank erzeugen
         */
        $newtable->writeNewTable();
        unset($newtable);
        /**
         * Meldung an Errorhandler wenn OK
         */
        trigger_error("Tabelle $defsfile erfolgreich angelegt.", E_USER_NOTICE);
    } catch (Exception $e) {
        echo "<pre>" . $e . "</pre>";
    }

    try {
        /**
         * neues TextFileImport erzeugen
         */
        $insert = $d->exec('insert');
        /**
         * Definitionsdatei laden
         */
        $insert->loadDefsFile($defsfile);
        /**
         * Daten aus SDAW Textdatei laden
         * und Tabelle schreiben.
         */
        $insert->loadSdawFile($sdawfile);
        unset($insert);
        /**
         * Meldung an Errorhandler wenn OK
         */
        trigger_error("Datenimport $sdawfile ausgef�hrt.", E_USER_NOTICE);
    } catch (Exception $e) {
        echo "<pre>" . $e . "</pre>";
    }

    try {
        /**
         * neues TextFileExport
         */
        $export = $d->exec('export');
        /**
         * XML-Datei $defsfile einlesen und SDAW-Textdatei mit Daten (D) schreiben
         */
        $export->doexport($defsfile);
        /**
         * Meldung an Errorhandler wenn OK
         */
        trigger_error("Tabelle $defsfile erfolgreich exportiert.", E_USER_NOTICE);
    } catch (Exception $e) {
        echo "<pre>" . $e . "</pre>";
    }
    $print = print "<hr>EOS.";
    /**
     * Statistik zum Script
     */
    $_SESSION['fullscriptend'] = microtime(true);
    $jobtime = ($_SESSION['fullscriptend'] - $_SESSION['fullscriptstart']);

    print $jobtime . " sec.<br>";
    print "<br>" . (memory_get_peak_usage(true) / 1024 / 1024) . " megabytes used. ";
    print "; running on " . php_uname() . ". <hr> ";
} catch (Exception $e) {
    echo "<pre>" . $e . "</pre>";
}
?>