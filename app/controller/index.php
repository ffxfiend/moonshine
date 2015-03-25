<?php

class indexController extends baseController {

    public function index() {

        $tpl = Template::getInstance();

        /* ***** GET THE DOCUMENT INFORMATION ***** */
        /********************************************/
        $oPage = new page();
        if ($oPage->loadByURL('index')) {
            $meta = $oPage->getMetaInfo();
            $oPage->loadTemplate();
            $template = $oPage->getTemplate()->getFilename();
        } else {
            $meta = array(
                'title' => SITE_NAME,
                'keywords' => SITE_NAME,
                'description' => SITE_NAME
            );
            $template = 'index';
        }

        $tpl->meta       = $meta;
        $tpl->document   = $oPage;

        /*** load the template ***/
        $tpl->show('layouts/' . $template);

    }

}
