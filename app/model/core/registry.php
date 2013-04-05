<?php
/**
 * Registry Object - registry.class.php
 *
 * This object holds everything needed
 * throughout the site. It is basically
 * a global repository of data.
 *
 * @version 1.0
 * @author Jeremiah Poisson
 *
 * @param array $vars
 *
 */
Class Registry {

    private static $instance = null;

    private $vars = array();

    /**
     * Creates an empty registry object.
     */
    private function __construct() {

        /**
         * Check to see if we are creating this object
         * a second time. If so we want to throw an
         * exception and return.
         */
        if (self::$instance != null) {
            throw new Exception('Cannot not create more then one registry object.');
        }

        // make sure the vars is empty when the object is created.
        $this->vars = array();
    }

    /**
     * Returns the static object instance.
     *
     * @return object
     */
    public static function getInstance() {

        if (self::$instance == null) {
            self::$instance = new Registry();
        }
        return self::$instance;

    }

    /**
     *
     * @set undefined vars
     * @param string $index
     * @param mixed $value
     * @return void
     *
     */
    public function __set($index, $value) { $this->vars[$index] = $value; }

    /**
     *
     * @get variables
     * @param mixed $index
     * @return mixed
     *
     */
    public function __get($index) { return $this->vars[$index]; }

}


?>