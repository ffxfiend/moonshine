<?php
/**
 * Document Object
 *
 * This object encapsulates what a page is within
 * the application. This class will utilize the
 * pageMeta, pageContent, pageMenu and pageTemplate
 * classes to build the page.
 *
 * @author Jeremiah Poisson <jpoisson@igzactly.com>
 * @version 1.0
 */
class page extends modelBase {

    protected $id;
    protected $template_id;
    protected $name;
    protected $title;
    protected $url;

    protected $created_at;
    protected $created_by;
    protected $updated_at;
    protected $updated_by;

    private $content = array();
    private $meta = null;
    private $template = null;

    const table = 'PAGE';
    const primary_key = 'id';

    public function getTable() { return page::table; }
    public function getPrimaryKey() { return page::primary_key; }

    // !ACCESSOR METHODS
    public function getID() { return $this->id; }
    public function getTemplateID() { return $this->template_id; }
    public function getName() { return $this->name; }
    public function getTitle() { return $this->title; }
    public function getURL() { return $this->url; }

    public function getCreatedAt() { return $this->created_at; }
    public function getCreatedBy() { return $this->created_by; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getUpdatedBy() { return $this->updated_by; }

    public function getCleanContent($id = '') { return $id == '' ? $this->content : (isset($this->content[$id - 1]) ? $this->content[$id - 1]->getCleanContent() : ''); }
    public function getContent($id = '') { return $id == '' ? $this->content : (isset($this->content[$id - 1]) ? $this->content[$id - 1]->getContent() : ''); }
    public function getTemplate() { return $this->template; }

    /**
     * This will return an array with the meta informaiton
     * for this page. If the meta object is null (could not laod)
     * we will return an array with empty elements.
     * 
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     * @version 1.0
     * 
     * @return array
     */
    public function getMetaInfo() {

        $meta = array('title' => '','keywords' => '','description' => '');

        if ($this->meta != null) {
            $meta['title'] = $this->meta->getMetaTitle();
            $meta['keywords'] = $this->meta->getMetaKeywords();
            $meta['description'] = $this->meta->getMetaDescription();
        }

        return $meta;

    } 

    /**
     * This will load a page object and any
     * page content objects related to the
     * page.
     *
     * @param int $id
     * @return bool
     *
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     */
    public function load($id) {

        $t = new pagecontent();
        $this->content = $t->loadByPage($id);

        return parent::load($id);
    }

    /**
     * This will take a URL string, retrieve the ID
     * of the page associated with the URL, then
     * continue to load the page object.
     *
     * @param string $url
     * @return bool
     *
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     */
    public function loadByURL($url) {

        $sSQL = sprintf("SELECT %s FROM %S WHERE url = '%s'", $this->getPrimaryKey(), $this->getTable(), $url);
        $results = mysql_db::getInstance()->query($sSQL,__FILE__,__LINE__);

        if ($results) {
            return $this->load($results[0]['id']);
        }
        
        return false;
        
    }

    /**
     * In this function we grab the page ID from
     * an associated menu ID. We then take the page
     * ID and load the page object with the base
     * load method.
     *
     * @param int $id
     * @return bool
     *
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     */
    public function loadByMenuID($id) {
        $sSQL = sprintf("SELECT page_id FROM PAGE_MENU WHERE id = %d",$id);
        $results = mysql_db::getInstance()->query($sSQL,__FILE__,__LINE__);

        if ($results) {
            return $this->load($results[0]['page_id']);
        }
        
        return false;
        
    }


    /**
     * This will load the template object associated with
     * the page.
     *
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     */
    public function loadTemplate() {
        $this->template = new pagetemplate();
        $this->template->load($this->getTemplateID());
    }

    /**
     * This will first delete any content records associated with the
     * page. After that we will delete the page itself.
     *
     * @TODO: Should reverse this logic, delete the page first, then the content records
     *
     * @param int $pk
     * @param string $fk
     * @return mixed
     */
    public function delete($pk,$fk = '') {

        // delete the content records
        foreach ($this->getContent() as $v) {
            $v->delete($v->getID());
        }

        // delete the parent record
        return parent::delete($pk);
    }

    /**
     * This will retrieve all the menu items associated with
     * this page.
     *
     * @return array
     *
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     */
    public function getMenu() {

        $sSQL = sprintf("SELECT id FROM DOCUMENT_MENU WHERE parent_id = 0 ORDER BY parent_id, the_order");
        $results = mysql_db::getInstance()->query($sSQL,__FILE__,__LINE__);

        if ($results && $results['recordCount'] >= 1) {
            array_pop($results);
            $return = array();
            foreach ($results as $v) {
                $t = new pagemenu();
                if ($t->load($v['id'])) { array_push($return,$t); }
            }
            return $return;
        } else {
            return array();
        }

    }

}

