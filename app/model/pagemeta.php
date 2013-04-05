<?php

/**
 * Class: pagemeta
 *
 * This class holds the meta informaiton for a single page.
 * 
 * @author Jeremiah Poisson <jpoisson@igzactly.com>
 * @version 1.0
 */
class pagemeta extends modelBase {

	protected $id;
	protected $page_id;
  	protected $meta_title;
  	protected $meta_description;
  	protected $meta_keywords;
  	protected $created_at;
  	protected $created_by;
  	protected $updated_at;
  	protected $updated_by;

    const table = 'PAGE_META';
    const primary_key = 'id';

    public function getTable() { return pagemeta::table; }
    public function getPrimaryKey() { return pagemeta::primary_key; }

    public function getID() { return $this->id; }
    public function getPageID() { return $this->page_id; }
    public function getMetaTitle() { return $this->meta_title; }
    public function getMetaDescription() { return $this->meta_description; }
    public function getMetaKeywords() { return $this->meta_keywords; }
    public function getCreatedAt() { return $this->created_at; }
    public function getCreatedBy() { return $this->created_by; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getUpdatedBy() { return $this->updated_by; }

    /**
     * This will load the meta object associated with 
     * the passed page ID
     * 
     * @param int $page_id The ID of the page we want to load the meta object for
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     */
    public function loadByPage($page_id) {
    	$sql = sprintf("SELECT %s FROM %s WHERE page_id = %d", $this->getPrimaryKey(), $this->getTable(), $page_id);
    	$results = mysql_db::getInstance()->query($sql,__FILE__,__LINE__);

    	if ($results) {
    		return $this->load($results[0][$this->getPrimaryKey()]);
    	}

    	return false;

    }

}