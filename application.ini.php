<?php

/**
 * @name application.ini.php
 * @package SDAW
 * @author mckoch
 * @copyright emcekah@gmail.com 2011
 * @version 1.1.1.1
 *
 * Konfiguration für SDAW Klassenbibliothek
 *
 * beinhaltet Includes, Konstanten und globale Variablen
 * für Pfadangaben, Datenbank und Konfigurationsdateien
 * der SDAW-Klassen.
 *
 */

/**
 * Diagnostik: Laufzeit
 */
if (!isset($_SESSION['sessiontime'])) {$_SESSION['sessiontime'] = 0;}
$_SESSION['fullscriptstart'] = microtime(true); 

/*
 *  Pfade
 */
define('INCLUDEDIR', '/srv/www/vhosts/cm.inextsolutions.net/httpdocs/sdaw/include/');
define('DEFSDIR', '/srv/www/vhosts/cm.inextsolutions.net/httpdocs/sdaw/definitionfiles/');
define('SDAWDIR', '/srv/www/vhosts/cm.inextsolutions.net/httpdocs/sdaw/sdawfiles/');
define('ARCHIVEDIR', '/srv/www/vhosts/cm.inextsolutions.net/httpdocs/sdaw/.archiv/');
define('OUTPUTDIR', '/srv/www/vhosts/cm.inextsolutions.net/httpdocs/sdaw/.outfiles/');
define('ORIGINAL_IMAGEPATH', '/srv/www/cl.testimages/');

/*
 * Logindaten für Datenbank
 */

$GLOBALS['dbhost'] = '127.0.0.1';
$GLOBALS['dbuname'] = 'sdawsqldev';
$GLOBALS['dbpass'] = 'hertel';
$GLOBALS['dbname'] = 'SDAWSQLdev';

/*
 * Includes
 */
require_once (INCLUDEDIR . 'ADODB/adodb.inc.php');
require_once (INCLUDEDIR . 'DBI.inc.php');
require_once(INCLUDEDIR . 'DISP.inc.php');
require_once(INCLUDEDIR . 'generalHelper.inc.php');
require_once(INCLUDEDIR . 'INIT.inc.php');
require_once(INCLUDEDIR . 'VERSION.inc.php');
require_once (INCLUDEDIR . 'exceptionErrorGeneric.inc.php');
$oErr = new exceptionErrorHandler(false);

/*
 * Defs für Hilfstabellen
 */
$GLOBALS['StellenArtenFile'] = DEFSDIR . 'Stellenarten.xml';
$GLOBALS['StellenFileDefault'] = DEFSDIR . 'StellenDefault.xml';
$GLOBALS['STAKopfDatenFile'] = DEFSDIR . 'STA-Kopfsatz.xml';

/** 
 * Zugriff auf Admin-Funktionen
 */
$GLOBALS['validadminkey']='musik';
?>