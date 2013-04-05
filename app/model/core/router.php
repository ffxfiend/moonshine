<?php
/**
 * Router Object - router.class.php
 *
 * This object handles the routing needed
 * by the application. If a controller is
 * not found it will redirect to the appropriate
 * error page.
 *
 * @version 1.0
 * @author Jeremiah Poisson
 *
 *
 * @param object $registry
 * @param string $path
 * @param string $route
 * @param array  $custom_routes
 * @param string $file
 * @param string $controller
 * @param string $cms_action
 * @param string $action
 * @param array  $params
 * @param string $subAction
 * @param string $otherParam
 * @param string $otherParamPB
 */
class router {

    private static $instance = null;

    private $registry;

    private $path;

    private $route;

    public $file;
    public $controller;
    public $cms_action = '';
    public $action;
    public $params = array();

    public $subAction;
    public $otherParam;
    public $otherParamPB;

    /**
     * Object constructor class. This
     * will grab the Registry Instance
     * and set the route passed into the
     * object at creation.
     *
     * @throws Exception Cannot create more then one instance
     */
    private function __construct() {

        /**
         * Throw an exception if we are trying to create
         * a second instance of the router class.
         */
        if (self::$instance != null) {
            throw new Exception('Cannot create multiple router instances.');
        }

        $this->registry = Registry::getInstance();

        request::parseRequest();
        $this->route = request::getRoute();

    }

    /**
     * This function will get the current
     * instance of the router class. When
     * you call this for the first time in
     * your application you must pass it the
     * route to be used. If no route is passed
     * we will try to use whats in the $_GET['rt']
     * variable if it exists. Otherwise this
     * will break when trying to load any kind
     * of controller in turn causing an error
     * page to load.
     *
     * @return object
     */
    public static function getInstance() {

        if (self::$instance == NULL) {
            self::$instance = new router();
        }
        return self::$instance;

    }

    /**
     * Sets the controller directory path
     *
     * @param $path
     * @throws Exception
     */
    public function setPath($path) {
        /*** check if path is a directory ***/
        if (!is_dir($path)) {
            throw new Exception ('Invalid controller path: `' . $path . '`');
        }
        /*** set the path ***/
        $this->path = $path;
    }

    /**
     * @load the controller
     *
     * @access public
     * @return void
     */
    public function loader() {
        /*** check the route ***/
        $this->getController();

        // dump($this->controller,false);
        // dump($this->file);

        /*** if the file is not there check if this is a cms page or return an error page ***/
        if (!is_readable($this->file)) {
            $document = new document();
            if ($document->loadByURL($this->controller)) {
                // Set a new path to the cms controller
                $this->cms_action = $this->controller;
                $this->controller = "cms";
                $this->file = $this->path . "/cms.php";
            } else {
                $this->controller = "error";
                $this->file = $this->path . '/error.php';
            }

        }

        /*** include the controller ***/
        include $this->file;

        /*** a new controller class instance ***/
        $class = $this->controller . 'Controller';
        $controller = new $class();

        /*** check if the action is callable ***/
        if (is_callable(array($controller, $this->action)) == false) {
            $action = 'index';
        } else {
            $action = $this->action;
        }

        /*** run the action ***/
        $controller->$action();
    }

    /**
     * This function will determine what
     * controller we are going to use. It
     * does this by examining the route and
     * seeing if it matches any preset route
     * in the configuration OR if the file
     * exists in the controllers folder. If
     * neither of these are present we will set
     * the route to 'index' and load the root
     * of the application.
     *
     * @access private
     * @return void
     */
    private function getController() {

        $controller = 'index';
        $action = 'index';

        if ($this->route) {

            /**
             * Get the controller and/or action from the route.
             *
             * 0: Controller
             * 1: Action
             * 2+: Additional params
             */
            $controller = request::getRoute(0);
            if(request::getRoute(1)) {
                $action = request::getRoute(1);
            }

            /**
             * Loop through and gather any params that
             * might be accompanying the route.
             */
            $done = false;
            $index = 2;
            while (!$done) {
                if (request::getRoute($index)) {
                    array_push($this->params, request::getRoute($index));
                    $index++;
                } else {
                    $done = true;
                }
            }

        }

        // Set the controller and action.
        $this->controller = $controller;
        $this->action = $action;

        // set the file path
        $this->file = $this->path .'/'. $this->controller . '.php';
    }


}

?>