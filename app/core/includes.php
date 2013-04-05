<?php

/* ***** Check for haxor's ***** */
include_once SITE_PATH . "/app/core/b_hxr.php";

/* ***** DEFINES ***** */
include_once SITE_PATH . "/app/core/defines.php";

/* ***** CORE FRAMEWORK ***** */
include_once SITE_PATH . '/app/model/core/request.php';             // Request Object
include_once SITE_PATH . '/app/model/core/controller_base.php';     // Controller
include_once SITE_PATH . '/app/model/core/registry.php';            // Registry
include_once SITE_PATH . '/app/model/core/router.php';              // Router
include_once SITE_PATH . '/app/model/core/template.php';            // Template

/* ***** DATABASE/MODEL ***** */
include_once SITE_PATH . '/app/lib/mysql_db.php';                   // Database Object
include_once SITE_PATH . "/app/model/core/modelDB.php";             // DB -> Model Connector
include_once SITE_PATH . "/app/model/core/model_base.php";          // Base Model class

/* ***** FUNCTIONS ***** */
include_once SITE_PATH . "/app/core/autoload.php";                  // Autoload
include_once SITE_PATH . '/app/lib/ms_functions.php';               // Moonshine Packages Functions
include_once SITE_PATH . '/app/core/error_mng.php';                 // Error Manager

/* ***** 3RD PARTY ***** */
include_once SITE_PATH . '/app/lib/swiftmailer/swift_required.php'; // Swift Mailer
include_once SITE_PATH . '/app/lib/ini_struct.php';                 // Ini_Struct

if ($configuration['3rd']['zipcodeclass']) {
    /**
     * @todo replace with new version: http://www.micahcarrick.com/php5-zip-code-range-and-distance.html
     */
    include_once SITE_PATH . '/app/lib/zipcode.php';              // Zip Code Class
}


/* ***** LOAD ANY FUNCTION FILES IN MODULES OR THEME FOLDERS ***** */
$dir = PUBLIC_TEMPLATE_PATH . '/';

// Open a known directory, and proceed to read its contents
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if (is_dir($dir . $file)) {

                if ($file == 'modules') {
                    if ($dh2 = opendir($dir . $file)) {
                        while (($file2 = readdir($dh2)) !== false) {
                            if (is_dir($dir . $file . "/" . $file2)) {
                                if ($dh3 = opendir($dir . $file . "/" . $file2)) {
                                    while (($file3 = readdir($dh3)) !== false) {
                                        if (filetype($dir . $file . "/" . $file2 . "/" . $file3) == 'file' && $file3 == 'functions.php') {
                                            // include the functions file
                                            include $dir . $file . "/" . $file2 . "/" . $file3;
                                        }
                                    }
                                    closedir($dh3);
                                }
                            }
                        }
                    }
                } else if ($dh2 = opendir($dir . $file)) {
                    while (($file2 = readdir($dh2)) !== false) {
                        if (filetype($dir . $file . "/" . $file2) == 'file' && $file2 == 'functions.php') {
                            // include the functions file
                            include $dir . $file . "/" . $file2;
                        }
                    }
                    closedir($dh2);
                }
            }
        }
        closedir($dh);
    }
}
