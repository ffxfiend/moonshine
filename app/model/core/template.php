<?php
/**
 * Template Object - template.class.php
 *
 * This object handles the display of the template.
 * The controller class will call the show() function
 * when it is ready to display a template.
 *
 * @version 1.0
 * @author Jeremiah Poisson
 *
 * @param object $registry
 * @param array  $vars
 * @param string $dir_path
 */

Class Template {

    private static $instance = null;

    private $registry;
    private $vars = array();
    private $dir_path = '';


    /**
     * These set of variables pertain to *.tpl files
     * and their chunks.
     */
    private $template_file = '';
    private $chunks = array();

    /**
     * @access private
     */
    private function __construct() {
        $this->registry = Registry::getInstance();
    }


    /**
     * Grabs the current instance of the template
     * object.
     *
     * @return object
     */
    public static function getInstance() {

        if (self::$instance == NULL) {
            self::$instance = new Template();
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
    public function __set($index, $value) {
        $this->vars[$index] = $value;
    }

    public function getVars() {
        return $this->vars;
    }

    public function setPath($p) {
        $this->dir_path = $p;
    }

    public function getPath() {
        return $this->dir_path;
    }

    function show($name) {

        // Load variables
        foreach ($this->vars as $key => $value) { ${$key} = $value; }

        $path = $this->dir_path . '/' . $name . '.php';

        if (!file_exists($path)) {
            $path = $this->dir_path . '/template_not_found.php';
        }

        $additional_css = isset($additional_css) ? $additional_css : '';
        if ($this->registry->debug_mode) {
            $additional_css .= "\n" . '<link rel="stylesheet" type="text/css" href="' . HTTP_ROOT . 'view/common/css/debug.css" />' . "\n";
        }

        $header = isset($customHeader) ? $this->dir_path . "/" . $customHeader : $this->dir_path . '/header-common.php';
        $footer = isset($customFooter) ? $this->dir_path . "/" . $customFooter : $this->dir_path . '/footer-common.php'; 

        include $header;
        include $path;
        include $footer;

    }

    /**
     * The following set of functions deal with template chunks. Stored with
     * the views will be a series of *.tlp files. These files have special
     * formatting wrapped in the following tags:
     *
     * <tmpl:TEMPLATE-NAME></tmpl:TEMPLATE-NAME>
     *
     * Any code wrapped in these tags can be pulled out and used individually
     * having any variables placed in them replaced within the php controller
     * file.
     */

    public function loadTemplate($template) {

        $template = '';

    }


}

?>