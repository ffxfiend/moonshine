<?php

/**
 * Set the current time. Used for debug purposes.
 */
$pExTimeStart = microtime(true);

/*** define the site path constant ***/
$site_path = realpath(dirname(__FILE__));
define ('SITE_PATH', str_replace('/app', '', $site_path));
define ('PUBLIC_SITE_PATH', str_replace('/app', '', $site_path));

// Include the bootstrap functions
include_once SITE_PATH . '/app/lib/bootstrap.php';

$configuration = parse_configuration_file(SITE_PATH . '/app/config/core.ini');
// echo "<pre>" . print_r($configuration,true) . "</pre>";
// exit();

/**
 * Set the default timezone.
 */
date_default_timezone_set($configuration['bc']['timezone']);

/**
 * Error reporting. Only turn on in the development
 * environment.
 */
if ($configuration['bc']['debug']) {
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', '1');
}


/**
 * Start the session. set the current session save path.
 */
if ($configuration['bc']['set_session_save_path']) {
    session_save_path($configuration['bc']['session_save_path']);
}
session_start();

/**
 * INCLUDE ALL NECESSARY FRAMEWORK AND APPLICATION FILES
 *
 * NOTE: This will also create the database object $oDB.
 */
include SITE_PATH . "/app/core/includes.php";

/*** a new registry object ***/
$registry = Registry::getInstance();
$registry->configuration = $configuration;
$registry->domain = HTTP_ROOT;

/* Create and get the DB object instance */
$oDB = mysql_db::getInstance();

if (!$oDB) {
    // Something went wrong with the
    // connecting to the database. kill
    // the script and email me.
    echo 'The site is currently undergoing maintenance. Please check back later.';
    mail($configuration['email']['error'], 'Database Error', 'There was an error connecting to the database on ' . $configuration['pj']['project_name'] . '. The site is currently down. Please fix.');
    exit();
}

/* **** SET USE MAGIC QUOTES ***** */
if ($registry->configuration['bc']['use_magic_quotes']) {
    $oDB->setUseMagicQuotes();
}

/* ***** SITE CONFIG VARIABLES ***** */
/* ********************************* */
$sSQL = "SELECT * FROM SITECONFIG";
$temp = $oDB->query($sSQL, __FILE__, __LINE__);
if ($temp && sizeof($temp) >= 1) {
    $registry->maintenance_mode = $temp[0]['sitIsMaintenance'];
    $registry->debug_mode 	= $temp[0]['sitIsDebug'];
    $registry->theme            = $temp[0]['site_theme'];
} else {
    $registry->maintenance_mode = $configuration['bc']['maintenance_mode'];
    $registry->debug_mode 	= $configuration['bc']['debug'];
    $registry->theme            = $configuration['bc']['theme'];
}

$registry->errorEmail = $configuration['email']['error'];

/*** get the router instance ***/
$router = router::getInstance();

/*** get the template instance ***/
$template = Template::getInstance();

/*** set the path to the controllers directory ***/
if ((ADMIN_AT_SUBDOMAIN && strstr($_SERVER['HTTP_HOST'],'cns')) || (isset($_GET['cns']) && $_GET['cns'] == '1')) {
    $router->setPath(ADMIN_CONTROLLER_PATH);
    $template->setPath(ADMIN_TEMPLATE_PATH);
    define ('THEME', 'admin');
} else {
    $router->setPath(PUBLIC_CONTROLLER_PATH);
    $template->setPath(PUBLIC_TEMPLATE_PATH . "/" . $registry->theme);
    define ('THEME', $registry->theme);
}

/*** load the controller ***/
$router->loader();

$pExTimeEnd = microtime(true);

/* ***** INCLUDE THE DEBUG FILE ***** */
include_once SITE_PATH . '/app/core/debug.php';
/* ********************************** */