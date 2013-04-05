<?php

/* ***** DEFINE THE HTTP ROOT ***** */
$url_cpanel = '';
if (strstr($_SERVER['PHP_SELF'],'~' . $configuration['cpanel']['username'])) {
    $url_cpanel = '~' . $configuration['cpanel']['username'] . '/';
}

define ('HTTP_ROOT', "http://" . $_SERVER['HTTP_HOST'] . "/" . $url_cpanel);
define ('HTTPS_ROOT', "https://" . $_SERVER['HTTP_HOST'] . "/" . $url_cpanel);

/* ***** TEMPLATE DEFINES ***** */
define ('ADMIN_CONTROLLER_PATH',SITE_PATH . '/app/controller/admin');
define ('PUBLIC_CONTROLLER_PATH',SITE_PATH . '/app/controller');
define ('ADMIN_TEMPLATE_PATH',PUBLIC_SITE_PATH . '/view/admin');
define ('PUBLIC_TEMPLATE_PATH',PUBLIC_SITE_PATH . '/view');
define ('HTTP_TEMPLATE_PATH',HTTP_ROOT . 'view');

/**
 * Misc Defines
 */
define ('SITE_NAME', $configuration['pj']['project_name']);
define ('USE_SEO_URL', $configuration['bc']['url_at_subdomain']);
define ('ADMIN_AT_SUBDOMAIN', $configuration['bc']['admin_at_subdomain']);

/* ***** SET THE VERSION NUMBER ***** */
define('MOONSHINE', $configuration['pj']['framework_version']);
/* ********************************** */