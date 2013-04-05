<?php
class pagetemplate extends modelBase {

    protected $id;
    protected $name;
    protected $filename;
    protected $number_sections;

    const table = 'PAGE_TEMPLATE';
    const primary_key = 'id';

    public function getTable() { return pagetemplate::table; }
    public function getPrimaryKey() { return pagetemplate::primary_key; }

    // !ACCESSOR METHODS
    public function getID() { return $this->id; }
    public function getName() { return $this->name; }
    public function getFilename() { return $this->filename; }
    public function getNumberSections() { return $this->number_sections; }



}