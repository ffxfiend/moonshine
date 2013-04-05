<?php
class pagemenu extends modelBase {

    protected $id;
    protected $parent_id;
    protected $page_id;
    protected $url;
    protected $title;
    protected $target;
    protected $the_order;
    protected $position;
    protected $level;

    /**
     * Page object for internal use.
     * @var null
     */
    protected $page = null;

    /**
     * Internal private variable used
     * when building the menu.
     * @var int
     */
    private $menu_iterator = 0;

    const table = 'PAGE_MENU';
    const primary_key = 'id';

    public function getTable() { return pagemenu::table; }
    public function getPrimaryKey() { return pagemenu::primary_key; }

    // !ACCESSOR METHODS
    public function getID() { return $this->id; }
    public function getParentID() { return $this->parent_id; }
    public function getPageID() { return $this->page_id; }
    public function getURL() { return $this->url; }
    public function getTitle() { return $this->title; }
    public function getTarget() { return $this->target; }
    public function getOrder() { return $this->the_order; }
    public function getPosition() { return $this->position; }
    public function getLevel() { return $this->level; }

    /**
     * Returns the page object.
     * @return page
     */
    public function getPage() { return $this->page; }

    public function load($id) {

        $t = new page();
        if ($t->loadByMenuID($id)) { $this->page = $t; }
        $t = null;
        unset($t);

        return parent::load($id);

    }

    function buildMenu($parent_id) {

        // Get all the menu items with the passed parent ID
        $sSQL = sprintf("SELECT id FROM DOCUMENT_MENU WHERE parent_id = %d ORDER BY the_order",$parent_id);
        $results = mysql_db::getInstance()->query($sSQL,__FILE__,__LINE__);

        $str = '';
        $this->menu_iterator++;
        if ($results) {




            $display = isset($_GET['m']) && (int) $_GET['m'] !== 0 && (int) $parent_id == 3 ? 'style="display: block"' : (isset($_GET['m']) && (int) $_GET['m'] == (int) $parent_id ? 'style="display: block"' : '');
            $str .= '<ul id="igz_menu_item_' . $parent_id . '_' . $this->menu_iterator . '" ' . $display . ' >' . "\n";



            foreach ($results as $v) {
                $t = new pagemenu();
                $t->load($v['id']);
                $link_url = $t->getPage() != null ? HTTP_ROOT . $t->getPage()->getURL() : $t->getURL();
                $link_target = $t->getTarget() != '' ? 'target="' . $t->getTarget() . '"' : '';
                $link_url .= $t->getTarget() != '_blank' ? "?m=" . $t->getParentID() : '';

                $str .= "\t" . '<li>';

                // Check to see if the menu item has children... if so we do not want to have a lin. just a click to open the menu.
                $sSQL = sprintf("SELECT id FROM DOCUMENT_MENU WHERE parent_id = %d ORDER BY the_order",$t->getID());
                $child_results = mysql_db::getInstance()->query($sSQL,__FILE__,__LINE__);

                if($child_results && $child_results['recordCount'] < 1) {
                    $str .= '<a href="' . $link_url . '" ' . $link_target . '>' . $t->getTitle() . '</a>';
                } else {
                    $str .= '<a href="Javascript://" onclick="$(\'#igz_menu_item_' . $t->getID() . '_' . $this->menu_iterator . '\').toggle();">' . $t->getTitle() . '</a>';
                }

                $str .= $this->buildMenu($t->getID());
                $str .= '</li>' . "\n";
            }
            $str .= '</ul>';
        }

        return $str;

    }




}
