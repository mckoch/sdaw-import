<?php

/**
 * @name apidispatcher.inc.php
 * @package SDAW_Classes
 * @author mckoch - 14.04.2011
 * @copyright emcekah@gmail.com 2011
 * @version 1.1.1.2
 * @license No GPL, no CC. Property of author.
 * 
 * SDAW Classes:apidispatcher
 * HTTP Request Dispatcher für SADW API Abfragen.
 * Erzeugt Anwendungs Dispatcher aus DISP:: und formatiert Ausgabe
 * (z.Zt. nur HTML aus GH::)
 *
 * @todo XML und JSON Ausgabe zusätzlich
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
         * Konfigurierbarer Report aus $_POST (!!)
         * dossier: Report erzeugen und ausgeben
         */
        case 'graphics':
            //print_r($_GET);
            $d->setparams($_GET);
            //return print $d->exec('plzpolygons');
            return print GH::makePlzPolyPointData($d->exec('plzpolygons'));

            break;
        /**
         * Initialisierung wenn keine weiteren Parameter (Suchauswahl) gegeben.
         * 1: explizite Initialisierung - lädt Standardauswahl
         */
        case '1':
            $d->setparams('default');
            //return

            return print GH::makeMapResultsDiv($d->exec('find'));
            break;
        /**
         * Aufruf mit Suchparametern aus $_GET
         * AUS _BESTANDSSSUCHE_ IN ALLEN FELDERN!!
         * Prüfung übernimmt von var $d erzeugtes object FIND
         * 2: Suchparameter werden verarbeitet
         */
        case '2':
            $d->setparams($_GET);

            return print GH::makeDynamicSearchJSON($d->exec('find'));
            break;
        /**
         * Aufruf mit longitude und latitude:
         * Umkreissuche var distance in METERN!
         * /deprecated!/
         */
        case '3':
            $d->setparams($_GET);
            return print GH::makeMapResultsDiv($d->exec('gpos'));
            break;
        /**
         * Siehe 3, returns JSON encoded
         */
        case '4':
            $d->setparams($_GET);
            return print GH::makeDynamicSearchJSON($d->exec('gpos'));
            break;
        /**
         * JSON Initialisierung
         */
        case '5':
            $d->setparams('default');
            return print GH::makeDynamicSearchJSON($d->exec('find'));
            break;
        /**
         * Rechtecksuche
         */
        case '6':
            $d->setparams($_GET['bounds']);
            return print GH::makeDynamicSearchJSON($d->exec('rectangle'));
            break;
        /**
         * Polygonsuche
         */
        case '7':
            $d->setparams($_GET['bounds']);
            //return print ($_GET['bounds']);
            return print GH::makeDynamicSearchJSON($d->exec('polygon'));
            break;
        /**
         * erweiterte Suche aus CSV oder JSON Array
         * - Rendern von Benutzerdaten
         */
        case '8':
            $d->setparams($_GET);
            return print GH::makeDynamicSearchJSON($d->exec('userdata'));
            //return print $d->exec('userdata');
            break;
        /**
         * default: leerer Aufruf
         * gibt Standardauswahl zurück
         */
        default:
            /**
             * Prüfung auf dynamische Suche
             */
            if (isset($_GET['term'])) {
                $d->setparams($_GET['term']);
                return print GH::makeDynamicSearchInputFieldJSON($d->exec('dynamicsearch'));
            } else {
                /**
                 * Standardausgabe ohne Parameter = Initialisierung.
                 * Keine HTTP-Header Ausgabe!!!!!!
                 *
                 */
                $d->setparams('default');
                return print GH::makeMapResultsDiv($d->exec('find'));
            }
            break;
    }
} catch (Exception $e) {
    echo "<pre>" . $e . "</pre>";
}
?>
