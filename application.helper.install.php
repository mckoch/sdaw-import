<?php

/**
 * @name application.helper.install.php
 * @package SDAW_Classes
 * @author mckoch - 03.05.2011
 * @copyright emcekah@gmail.com 2011
 * @version 1.1.1.1
 * @license No GPL, no CC. Property of author.
 * 
 * SDAW Classes:application
 *
 * 
 */
require_once('application.ini.php');
if ($_GET['adminkey'] != $GLOBALS['validadminkey']) {
    print "Kein g&uuml;ltiger Schl&uuml;ssel. ";
    die;
}

try {
    $d = new DISP;

    /**
     * todo: $GLOBALS['...'] aus ini benutzen!
     */
    $newtable = $d->exec('newtable');
    $newtable->getXmlFile(DEFSDIR . 'Gemeindezuordnung.xml');
    $newtable->writeNewTable();

    $newtable->getXmlFile(DEFSDIR . 'NielsengebietePLZ.xml');
    $newtable->writeNewTable();

    $newtable->getXmlFile(DEFSDIR . 'STA-Kopfsatz.xml');
    $newtable->writeNewTable();



    unset($newtable);
    trigger_error("Hilfstabellen Gemeindezuordung, Nielsengebiete und Kopfsatz-Versionierung erfolgreich angelegt.", E_USER_NOTICE);
} catch (Exception $e) {
    echo "<pre>" . $e . "</pre>";
}
/**
 * var $csvfile
 *
 * Erster Durchlauf: PLZ, Einwohnerzahl, Gemeindekennzahl, Klebeblock...
 */
$csvfile = DEFSDIR . "Gemeindezuordnung.csv";
$dbi = new DBI;

try {
    $table = "GEMEINDE_ZUORDNUNG";
    $handle = fopen($csvfile, 'r');
    if ($handle) {
        set_time_limit(0);

        //the top line is the field names
        $fields = fgetcsv($handle, 4096, ',', "\"");

        //loop through one row at a time
        $i = 0;
        while (($data = fgetcsv($handle, 4096, ',')) !== FALSE) {
            $i++;
            $data = array_combine($fields, $data);
            $sql = "INSERT INTO " . $table . " VALUES (NULL," . $dbi->gqstr($data['BL']) . "," . $dbi->gqstr($data['RGB'])
                    . "," . $dbi->gqstr($data['Kreis']) . "," . $dbi->gqstr($data['Gkz']) . "," . $dbi->gqstr($data['Wirtschaftsraum'])
                    . ",". $dbi->gqstr($data['Ort']) . "," . $data['Einwohner'] . "," . $dbi->gqstr($data['Plz']) . ","
                    . $dbi->gqstr($data['Block neu']) . ")";
            $dbi->sqldebug(FALSE);
            //if ($i < 10) {$dbi->sqldebug(TRUE);print $sql . "<br/>";}
            $dbi->exec($sql);
        }

        fclose($handle);
    } trigger_error("$csvfile in $table importiert.", E_USER_NOTICE);
} catch (Exception $e) {
    echo "<pre>" . $e . "</pre>";
}

/**
 * zweiter Durchlauf: PLZ und Nielsenzuordnung
 */
try {
    $csvfile = "definitionfiles/NielsengebietePLZ.csv";
    $table = "NIELSEN_PlZ";

    $handle = fopen($csvfile, 'r');
    if ($handle) {
        set_time_limit(0);

        //the top line is the field names
        $fields = fgetcsv($handle, 4096, ',', "\"");

        //loop through one row at a time
        $i = 0;
        while (($data = fgetcsv($handle, 4096, ',')) !== FALSE) {
            $i++;
            $data = array_combine($fields, $data);
            $sql = "INSERT INTO " . $table . " VALUES (NULL ," . $dbi->gqstr($data['nielsen']) . "," . $dbi->gqstr($data['plz']) . "," . $dbi->gqstr($data['ort']) . ")";
            $dbi->sqldebug(FALSE);
            //if ($i < 10) {$dbi->sqldebug(TRUE);print $sql . "<br/>";}
            $dbi->exec($sql);
        }

        fclose($handle);
    }
    trigger_error("$csvfile in $table importiert.", E_USER_NOTICE);
} catch (Exception $e) {
    echo "<pre>" . $e . "</pre>";
}
?>
