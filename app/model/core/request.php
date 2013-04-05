<?php
/**
 * This class will parse the request data, both _POST and
 * _GET and place it in an array. You can then retrieve the
 * data. The class can be used as a singleton.
 *
 * This class will also handle page redirects and other
 * functions related to page requests.
 *
 * @author Jeremiah Poisson
 * @version 1.0
 */
class request {

    static $data = array();

    /**
     * Call the static parseRequest() function.
     *
     * @author Jeremiah Poisson
     */
    public function __construct() {
        request::parseRequest();
    }

    /**
     * Parse the incoming request and place it in
     * an array. Once everything is set we will assign
     * it to the static request::data variable.
     *
     * @author Jeremiah Poisson
     * TODO: Add header information to data array
     */
    static function parseRequest() {

        // We only want to parse and set
        // this once per request.
        if (empty(request::$data)) {
            $data = array();

            // Check for _POST data
            if (isset($_POST)) {
                foreach ($_POST as $k => $v) {
                    $data['post'][$k] = $v;
                }
            }

            // Now check for _GET data
            if (isset($_GET)) {
                foreach ($_GET as $k => $v) {
                    $data['get'][$k] = $v;
                }
            }

            // Check for Session data.
            if (isset($_SESSION)) {
                foreach ($_SESSION as $k => $v) {
                    $data['session'][$k] = $v;
                }
            }

            request::$data = $data;
        }

    }

    static function getData($type) {
        if (isset(request::$data[$type])) {
            return request::$data[$type];
        }
        return request::$data;
    }

    /**
     * This will redirect the page to the uri passed
     * into the function.
     *
     * @param $uri
     */
    static function redirect($uri) {
        header('Location: ' . $uri);
        exit();
    }

    static function getRoute($index = null) {

        if (isset(request::$data['get']['rt'])) {

            if ($index == null) {
                return request::$data['get']['rt'];
            }

            $route = explode('/',request::$data['get']['rt']);

            return isset($route[$index]) ? $route[$index] : false;
        }

        return '';
        // return false;

    }

}