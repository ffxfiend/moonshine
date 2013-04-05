<?php
/**
 *
 */
Abstract Class baseController {

    /*
    * @registry object
    */
    protected $registry;
    protected $template;
    protected $router;

    protected $use_seo_url;
    protected $admin_at_subdomain;

    function __construct() {
        $this->registry = Registry::getInstance();
        $this->template = Template::getInstance();
        $this->router = router::getInstance();
        $this->use_seo_url = USE_SEO_URL ? "" : "?rt=";
        $this->admin_at_subdomain = ADMIN_AT_SUBDOMAIN ? "" : (USE_SEO_URL ? "?cns=1" : "&amp;cns=1");
    }

    /**
     * @all controllers must contain an index method
     */
    abstract function index();


}

?>