<?php

/**
 * This function will handle the auto loading
 * of classes. It will look into the following
 * folder for classes and use recursive logic
 * to dig down and try and find the class.
 *
 * /model/
 *
 * The class file must use the following naming
 * convention.
 *
 * [ALL LOWERCASE CLASS NAME].php
 *
 * @author Jeremiah Poisson
 */
function igz_autoload($class_name) {

    $filename = strtolower($class_name) . '.php';
    $dir = SITE_PATH . '/app/model/';

    $file_to_load = '';
    if (is_dir($dir)) {

        $file_to_load = parse_directory($dir . '/',$filename);
    }

    if ($file_to_load == '') { return false; }

    include ($file_to_load);
    return true;

}

function parse_directory($dir,$filename) {

    $file_to_load = '';

    if ($dh = opendir($dir)) {

        while (($file = readdir($dh)) != false) {
            if (is_dir($dir . $file) && $file != "." && $file !=  '..') {
                // Parse the directory
                $file_to_load = parse_directory($dir . $file . '/',$filename);
            } else if ($file != "." && $file !=  '..') {
                if (filetype($dir . $file) == 'file' && $file == $filename) {
                    $file_to_load = $dir . $file;
                }
            }
        }
        closedir($dh);
    }

    return $file_to_load;

}

spl_autoload_register('igz_autoload');
