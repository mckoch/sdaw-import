<?php

/**
 * @name adminapi.php
 * @package SDAW_Classes
 * @author mckoch - 28.04.2011
 * @copyright emcekah@gmail.com 2011
 * @version 1.1.1.1
 * @license No GPL, no CC. Property of author.
 * 
 * SDAW Classes:adminapi
 *
 * 
 */
/**
 * Header falls erforderlich: HTTP-Aufruf oder include()
 */
if (!headers_sent()) {
    header('Content-Type: text/html;charset=windows-1252');
}



require_once('application.ini.php');
if ($_GET['adminkey'] != $GLOBALS['validadminkey']) {
    print "Kein g&uuml;ltiger Schl&uuml;ssel.<hr/>";
    die;
}

require_once(INCLUDEDIR . 'adminapidispatcher.inc.php');

/**
 * Statistik zum Script
 */
$_SESSION['fullscriptend'] = microtime(true);
$jobtime = ($_SESSION['fullscriptend'] - $_SESSION['fullscriptstart']);

print $jobtime . " sec.<br>";
print "<br>" . (memory_get_peak_usage(true) / 1024 / 1024) . " megabytes used. ";
print "; running on " . php_uname() . ". <hr> ";
?>
