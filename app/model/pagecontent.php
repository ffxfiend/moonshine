<?php
/**
 * Object: Page Content
 *
 * This object holds a single content row associated
 * with a particular page.
 *
 * @package ADMIN
 * @subpackage CMS
 * @version 1.0
 *
 * @author Jeremiah Poisson <jpoisson@igzactly.com>
 */
class pagecontent extends modelBase {

    protected $id;
    protected $page_id;
    protected $content_id;
    protected $content;

    const table = 'PAGE_CONTENT';
    const primary_key = 'id';

    public function getTable() { return pagecontent::table; }
    public function getPrimaryKey() { return pagecontent::primary_key; }

    // !ACCESSOR METHODS
    public function getID() { return $this->id; }
    public function getPageID() { return $this->page_id; }
    public function getContentID() { return $this->content_id; }
    public function getCleanContent() { return $this->content; }
    public function getContent() {
        // search the content for short codes...
        return igz_doShortcode($this->content);
    }

    /**
     * This will load all content records associated to a single
     * page in the system.
     *
     * @param int $id
     * @return array 
     */
    public function loadByPage($id) {

        $sql = sprintf("SELECT %s FROM %s WHERE page_id = %d",pagecontent::primary_key,pagecontent::table,$id);
        $results = mysql_db::getInstance()->query($sql,__FILE__,__LINE__);

        return $this->loadObjectsByResults($results);

    }

    /**
     * This function will update the content associated with
     * a single page record on the site. It first checks to
     * see if their is an existing record. If not we need to
     * add a content row for the table.
     *
     * The only reason something may not be present is when
     * a page is first created. In this case their should not
     * be rows for each content section.
     *
     * @param array, $data
     * @return array
     */
    public function updateContent($data) {

        // lets see if the content record already exists.
        $sql = sprintf("SELECT %s FROM %s WHERE page_id = %d AND content_id = %d",
            pagecontent::primary_key,
            pagecontent::table,
            $data['page_id'],
            $data['content_id']);
        $results = mysql_db::getInstance()->query($sql,__FILE__,__LINE__);

        if ($results) {
            // the record exists, edit it
            $data['id'] = $results[0]['id'];
            return $this->edit($data,array());
        } else {
            // the record does not exist, add it
            return $this->add($data,array());
        }

    }

    /**
     * This function will take in an integer value representing
     * the ID of the primary_key row for the DB row representing
     * a single object and delete the row from the database.
     *
     * @param int $primary_key
     * @return mixed
     */
    public function deleteContent($primary_key) {
        return $this->delete($primary_key);
    }

}
