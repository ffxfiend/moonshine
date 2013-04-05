<?php

/**
 * This file defines a set of functions needed
 * to bootstrap the framework. Any function that
 * might be required before the core of the
 * framework is loaded should be put here.
 *
 * @author Jeremiah Poisson <jpoisson@igzactly.com>
 */


function parse_configuration_file($path) {

    $temp = parse_ini_file($path);

    $configuration = array();
    foreach ($temp as $k => $v) {

        $keys = explode(".",$k);

        $configuration[$keys[0]][$keys[1]] = $v;

    }

    return $configuration;

}
