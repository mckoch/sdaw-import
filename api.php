<?php

/**
 * @name api.php
 * @package SDAW_Classes
 * @author mckoch - 14.04.2011
 * @copyright emcekah@gmail.com 2011
 * @version 1.1.1.1
 * @license No GPL, no CC. Property of author.
 *
 * SDAW Classes:api
 *
 *
 */
/**
 * <html><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
  <title>Testpage GoogleMaps % SDAW Integration</title>
  </head>
  <body>
 * <?php
 */

/**
 * Header falls erforderlich: HTTP-Aufruf oder include()
 */
if (!headers_sent()){header('Content-Type: text/html;charset=windows-1252');}
header('Access-Control-Allow-Origin: *');

require_once('application.ini.php');
require_once(INCLUDEDIR . 'apidispatcher.inc.php');

/**
 * ?></body>
 */