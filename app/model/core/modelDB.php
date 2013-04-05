<?php
/**
 * File: modelDB.class.php
 *
 * This class will give all the models a way
 * to interact with the database. It will read
 * in a configuration file that will describe
 * the data model for each object. This will be
 * the parent of the model base class and ultimately
 * the model itself.
 *
 * EXAMPLE
 * --------------
 *
 * |------------|
 * |            |
 * |  MODEL DB  |
 * |            |
 * |------------|
 *    |
 *    | extends
 * |--------------|
 * |              |
 * |  MODEL BASE  |
 * |              |
 * |--------------|
 *    |
 *    | extends
 * |----------|
 * |          |
 * |  OBJECT  |
 * |          |
 * |----------|
 *
 * @todo Document Class Functions
 * @todo Change the configuration file to either a true .ini file or use XML
 *
 * @author Jeremiah Poisson
 */
Class modelDB {

    /*
    * @registry object
    */
    // protected $registry;

    /*
     * @mysql object
     */
    // protected $oDB;

    /* MODEL NAME */
    protected $MODEL_NAME;

    /* DB INFORMATION */
    protected $db_conf_file;
    protected $db_table;
    protected $db_pk;
    protected $db_fk = '';
    protected $db_additionalRestraint = '';
    protected $db_active_field;
    protected $db_order_field = '';
    protected $db_group_by = '';
    protected $db_join_clause = '';
    protected $db_default_image_path;
    protected $db_overide_image_path = '';
    protected $db_validate_fields;
    protected $db_special_proc;
    protected $db_allow_add		= false;
    protected $db_allow_edit	= false;
    protected $db_allow_delete 	= false;
    protected $db_allow_view	= false;

    protected $db_edit_fields;
    protected $db_list_fields;
    protected $db_limit = '';
    protected $db_filter = '';
    protected $db_selectWhere = '';

    /* TABLE FIELD DEFS */
    protected $fields	= array();

    /* Add Button Value */
    protected $db_add_btn_text;

    /* No Record Text */
    protected $db_no_records_text;

    protected $db_proc_table_name;

    protected $db_initialized = false;

    /* CONSTRUCTOR FUNCTION */
    function __construct() {
        // $this->registry = Registry::getInstance();
        // $this->oDB = mysql_db::getInstance();
    }

    /* ***** SETTER METHODS ***** */
    /******************************/
    function setDBFilter($f) { $this->db_filter = $f; }
    function setDBLimit($l) { $this->db_limit = $l; }
    function setDBGroupBy($l) { $this->db_group_by = $l; }
    function setDBJoin($l) { $this->db_join_clause = $l; }
    function setDBTable($t) { $this->db_table = $t; }
    function setDBPK($pk) { $this->db_pk = $pk; }
    function setDBFK($fk) { $this->db_fk = $fk; }
    function setDBOrder($df) { $this->db_order_field = $df; }
    function setDBEditFields($ef) { $this->db_edit_fields = $ef; }
    function setDBListFields($lf) { $this->db_list_fields = $lf; }
    function setDBConfFile($f) { $this->db_conf_file = $f; }
    function setDBValidateFields($f) { $this->db_validate_fields = $f; }
    function setDBSelectWhere($f) { $this->db_selectWhere = $f; }
    function setDBImageDefaultPath($p) { $this->db_default_image_path = $p; }
    function setDBOverideImagePath($p) { $this->db_overide_image_path = $p; }

    function setDBFieldAttribute($field,$attr,$value) {

        for ($i = 0; $i < sizeof($this->fields); $i++) {
            if ($this->fields[$i]['fieldName'] == $field) {
                $this->fields[$i][$attr] = $value;
                return true;
            }
        }


        return false;
    }

    /* ***** ACCESSOR METHODS ***** */
    /********************************/
    function getModelName() { return $this->MODEL_NAME; }
    function getDBProcTableName() { return $this->db_proc_table_name; }
    function getDBValidateFields() { return $this->db_validate_fields; }
    function getDBFields() { return $this->fields; }
    function getDBNoRecordText() { return $this->db_no_records_text; }
    function getDBAddBtnText() { return $this->db_add_btn_text; }
    function getDBTable() { return $this->db_table; }
    function getDBPK() { return $this->db_pk; }
    function getDBFK() { return $this->db_fk; }
    function getDBAdditionalRestraint() { return $this->db_additionalRestraint; }
    function getDBListFields() { return explode(',',$this->db_list_fields); }
    function getDBEditFields() { return explode(',',$this->db_edit_fields); }
    function getDBActiveField() { return $this->db_active_field; }
    function getDBAllowAdd() { return $this->db_allow_add; }
    function getDBAllowEdit() { return $this->db_allow_edit; }
    function getDBAllowDelete() { return $this->db_allow_delete; }
    function getDBAllowView() { return $this->db_allow_view; }

    function getDBListPermissions() {
        /* Build an array of all the permissions */
        $return = array(
            'allowAdd' => $this->db_allow_add,
            'allowEdit' => $this->db_allow_edit,
            'allowDelete' => $this->db_allow_delete,
            'allowView' => $this->db_allow_view
        );

        return $return;
    }

    /**
     * This function will return the basic config variables
     * needed to display the add/edit template as an array.
     *
     * @param string fkID [The ID of the foreign key if present]
     * @return array
     */
    function getAddEditFormConfig($fkID = '') {

        $config = array(
            "db_pk" => $this->db_pk,
            "db_fk" => $this->db_fk,
            "db_validate_fields" => $this->db_validate_fields,
            "db_default_image_path" => $this->db_default_image_path,
            "db_table" => $this->db_table
        );

        /**
         * Determine the order count.
         *
         * This is determined dynamically by an internal
         * function that will return false if their is
         * no way to determine the order count.
         */
        $order_count = $this->getOrderCount($fkID);

        if ($order_count !== false) {
            $config['order_count'] = $order_count;
        }

        return $config;

    }

    /**
     * This function will determine the order count
     * based on the db configuration. If there is no
     * order field then we will return false or if
     * the configuration has not been loaded we will
     * return false.
     *
     * @param string fdID [ID of the foreign key if one is present]
     * @return int/boolean false
     *
     */
    function getOrderCount($fkID = '') {

        if ($this->db_order_field == '' || !stristr($this->db_order_field,"order") || $this->db_table == '') { return false; }

        $fk_where = ' WHERE 1 = 1';
        if ($this->db_fk != '' && $fkID != '') {
            $fk_where .= " AND " . $this->db_fk . " = '" . $fkID . "' ";
        }

        if (isset($this->db_additionalRestraint) && $this->db_additionalRestraint != '' && $fkID != '') {
            $fk_where .= " AND " . $this->db_additionalRestraint . " = " . $fkID;
        }

        // We should have enough to get the order count.
        $sSQL = sprintf("SELECT count(*) as order_count FROM %s %s",$this->db_table,$fk_where);
        $getCount = $this->oDB->query($sSQL,__FILE__,__LINE__);

        return $getCount[0]['order_count'];
    }

    /**
     * This function will return the total count of returned
     * records depending on the where clause.
     */
    function getRecordCount($where = '') {

        if ($this->db_table == '') {
            return '0';
        }

        if ($where != '') {
            $where = "AND " . $where;
        }

        $filter = '';
        if ($this->db_filter != '') {
            $filter = $this->db_filter;
        }

        $sSQL = sprintf("SELECT count(*) as totalCount FROM %s WHERE 1 = 1 %s %s",$this->db_table,$where,$filter);
        $getCount = $this->getDBResults($sSQL);

        return $getCount[0]['totalCount'];

    }

    /**
     * This function will parse the models .ini file
     * and fill in the class variables needed to allow
     * the model to interact with the DB.
     *
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     * @param $class
     */
    protected function loadIniFile($class) {

        // Search for a config file
        $filename = get_class($this) . '.ini';
        $dir = realpath(dirname(__FILE__)) . "/";
        $fileToParse = '';
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_dir($dir . $file) && $file != "." && $file != '..') {
                        // Open the directory
                        if ($dh2 = opendir($dir . $file)) {
                            while (($file2 = readdir($dh2)) !== false) {
                                if (filetype($dir . $file . "/" . $file2) == 'file' && $file2 == $filename) {
                                    $fileToParse = $dir . $file . "/" . $file2;
                                }
                            }
                            closedir($dh2);
                        }
                    }
                }
                closedir($dh);
            }
        }

        $modelConfiguration = Ini_Struct::parse($fileToParse,true);

        // Table Config
        $tableDefaults = $modelConfiguration['TABLE']['table'];
        $this->db_table = $tableDefaults['table'];
        $this->db_pk = $tableDefaults['pk'];
        $this->db_fk = $tableDefaults['fk'];
        $this->db_active_field = $tableDefaults['activeField'];
        $this->db_order_field = $tableDefaults['orderField'];
        $this->db_allow_add = (int) $tableDefaults['allowAdd'] === 1 ? true : false;
        $this->db_allow_edit = (int) $tableDefaults['allowEdit'] === 1 ? true : false;
        $this->db_allow_delete = (int) $tableDefaults['allowDelete'] === 1 ? true : false;
        $this->db_allow_view = (int) $tableDefaults['allowView'] === 1 ? true : false;
        $this->db_edit_fields = $tableDefaults['editFields'];
        $this->db_list_fields = $tableDefaults['listFields'];
        $this->db_validate_fields = $tableDefaults['validateFields'];

        // Text Config
        $textDefaults = $modelConfiguration['TEXT']['text'];
        $this->db_add_btn_text = $textDefaults['add'];
        $this->db_no_records_text = $textDefaults['noRecords'];

        // Fields Config
        $fieldsConfig = $modelConfiguration['FIELDS']['field'];
        foreach ($fieldsConfig as $k => $v) {

            $temp = array($k => $this->parseFieldData($v));
            array_push($this->fields,$temp);

        }

        $this->db_initialized = true;

    }

    /**
     * Parses the individual field data from
     * the models .ini file.
     *
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     * @param $data
     * @return array
     */
    protected function parseFieldData($data) {

        $replace = array('[HTTP_ROOT]');
        $replaceWith = array(HTTP_ROOT);

        $temp = array();
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $temp[$k] = $this->parseFieldData($v);
            } else {

                switch ($k) {

                    case 'allowedFileType':
                        $aft = explode(" ",$v);
                        $aft_a = array();
                        foreach ($aft as $k2 => $v2) {
                            array_push($aft_a,trim($v2));
                        }
                        $value = $aft_a;
                        break;

                    case 'required':
                    case 'static':
                    case 'richText':
                        $value = (int) $v === 1 ? true : false;
                        break;

                    default:
                        $value = trim(str_replace($replace,$replaceWith,$v));


                }

                $temp[$k] = $value;
            }
        }

        return $temp;

    }

    /**
     * This function will retrieve a single record from the DB. If the
     * data array is not passed then it will construct the most basic
     * select query their is using the table and PK defined in the class.
     * If these are not defined and no data array is passed the function
     * will return false.
     *
     * @param string $id [The record ID to be retrieved]
     * @param array  $data [Array holding at least the table and pk for the query.]
     * @return array|boolean false
     */
    function getSingleRecord($id,$data = array()) {

        if (empty($id)) {
            return false;
        }

        if (empty($data)) {
            // Make sure we have the table and pk defined
            if ($this->db_table == '' || $this->db_pk == '') {
                return false;
            } else {
                // Populate the data array with the table and pk value
                $data['table'] = $this->db_table;
                $data['pk'] = $this->db_pk;
            }
        } else {
            // Make sure the data array has at least the table and pk defined
            // If they are not we will default to the classes values if present
            if (!isset($data['table']) || $data['table'] == '') {
                if ($this->db_table == '') {
                    return false;
                } else {
                    $data['table'] = $this->db_table;
                }
            }

            if (!isset($data['pk']) || $data['pk'] == '') {
                if ($this->db_pk == '') {
                    return false;
                } else {
                    $data['pk'] = $this->db_pk;
                }
            }
        }

        /* If we made it here then we are set to continue to build the query */
        if ($this->db_list_fields != '') {
            $selectList = $this->db_list_fields;
        } else {
            $selectList = "*";
        }


        $sSQL = sprintf("SELECT %s FROM %s WHERE %s = '%s'",$selectList,$data['table'],$data['pk'],$id);

        $getRecord = $this->oDB->query($sSQL,__FILE__,__LINE__);

        return $getRecord;

    }


    function getRecords($all = true, $active = true) {
        /* Make sure we have the table, pk, active field and view field list defined. */
        if ($this->db_table == '' || $this->db_pk == '' || $this->db_list_fields == '') {
            return false;
        } else {

            /* Build the query */
            $listFields = explode(',',$this->db_list_fields);
            $selectFields = '';
            for ($i = 0; $i < sizeof($listFields); $i++) {
                $selectFields .= ", " . $listFields[$i];
            }

            /* Build the where based on if were getting all, active or inactive records */
            $selectWhere = "WHERE 1 = 1 ";
            if ($all) {
                $selectWhere = "";
            } else {
                if ($this->db_active_field != '') {
                    if ($active) {
                        $selectWhere .= " AND " . $this->db_active_field . " = '1'";
                    } else {
                        $selectWhere .= " AND " . $this->db_active_field . " = '0'";
                    }
                }
            }

            $join = "";
            if ($this->db_join_clause != '') {
                $join = $this->db_join_clause;
            }

            $orderBy = "";
            if ($this->db_order_field != '') {
                $orderBy = " ORDER BY " . $this->db_order_field;
            }

            $groupBy = "";
            if ($this->db_group_by != '') {
                $groupBy = " GROUP BY " . $this->db_group_by;
            }

            $limit = '';
            if ($this->db_limit != '') {
                $limit = $this->db_limit;
            }

            $filter = '';
            if ($this->db_filter != '') {
                $filter = $this->db_filter;
            }

            $sSQL = sprintf("SELECT %s %s FROM %s %s %s %s %s %s",$this->db_pk,$selectFields,$this->db_table,$join,$selectWhere,$filter,$groupBy,$orderBy,$limit);
            // echo $sSQL . "<br />";

            $results = $this->oDB->query($sSQL,__FILE__,__LINE__);
            // print_a($results);
            if ($results) {
                return $results;
            } else {
                return false;
            }


        }
    }

    /**
     * This function will get the field data and fill it all in
     * an array that will be used to fill in the  data on the
     * add/edit template.
     *
     * This function required that the field array be filled. If
     * it has not been populated it will return an empty array.
     *
     * If fieldList is empty it will use the list associated with
     * the object. If this is empty it will return an empty array.
     *
     * If data is successfully populated it will return in the order
     * the fields are listed in the fieldList array.
     *
     * @param  array fieldList [List of the fields you want to get the data for]
     * @param  array content
     * @return array
     */
    function getDBFieldData($fieldList = array(),$content = array()) {

        $data = array();

        if (empty($this->fields)) {
            return $data;
        }

        if (empty($fieldList)) {
            $fieldList = $this->getDBEditFields();
            if (empty($fieldList)) {
                return $data;
            }
        }

        foreach ($fieldList as $k => $v) {
            foreach ($this->fields as $k2 => $v2) {
                if ($v2['fieldName'] == $v) {
                    // print_a($v2);
                    $temp = $v2;

                    $temp['value'] = isset($content[$v2['fieldName']]) ? $content[$v2['fieldName']] : "";

                    $temp[$this->db_pk] = isset($content[$this->db_pk]) ? $content[$this->db_pk] : "";


                    if ($v2['type'] == 'select') {
                        if (!isset($v2['static']) || $v2['static'] == 0) {
                            // Get the select values and place them in the array
                            $selectValues = $this->getDBSelectValues($v2);
                            $temp['selectValues'] = $selectValues;
                        }
                    }

                    // populate the data needed to fill in the template.
                    $data[] = $temp;

                }
            }
        }

        return $data;

    }

    /**
     * This function will get the select values
     * for a drop down list based on the field
     * passed to it.
     */
    function getDBSelectValues($field) {

        $where = "";
        if (isset($field['selectWhere']) && $field['selectWhere'] != '') {
            $where = " WHERE " . $field['selectWhere'] . " ";
        } else if ($this->db_selectWhere != '') {
            $where = $this->db_selectWhere;
        }

        $sSQL = sprintf("SELECT %s, %s FROM %s %s ORDER BY %s",$field['selectValue'],$field['selectDisplay'],$field['selectTable'],$where,$field['selectOrder']);
        $getSelectValues = $this->oDB->query($sSQL,__FILE__,__LINE__);

        return $getSelectValues;
    }

    /**
     * This function will insert a record into the DB. The class must be initialized
     * or this function will return false.
     */
    function insertRecord($data,$files) {

        if (!$this->db_initialized) {
            return false;
        }

        /* ***** PROCESS AND BUILD THE DB QUERY ***** */
        /**********************************************/
        $error = "";
        $edit_fields = $this->getDBEditFields();

        /* LOOP THROUGH THE FIELDS */
        $sqlFields = array();
        $sqlValues = array();
        foreach ($this->fields as $k => $v) {
            if (in_array($k,$edit_fields)) {
                /* SWITCH ON THE FIELD TYPE */
                switch ($v['type'])
                {
                    case 'image':
                        /* ADD THE FILED NAME TO THE BEGINNING SQL STATEMENT */
                        array_push($sqlFields,$k);
                        // $sSQLStart .= $k;

                        /* UPLOAD THE IMAGE */
                        $newImageName = "";
                        if ($files[$k]['name'] != "") {
                            // Upload the new image
                            $msUploader = new ms_uploader();
                            $msUploader->setFileTypes($v['allowedFileType']);
                            $imagePath = PUBLIC_SITE_PATH;
                            $imagePath .= $this->db_overide_image_path != '' ? $this->db_overide_image_path : $this->db_default_image_path . $this->db_table;
                            if (!$msUploader->uploadFile($files,$k,$imagePath,$newImageName)) {
                                $error .= "<li>" . $msUploader->getLastError(true) . "</li>";
                            }

                            // Check is we need to create a thumbnail...
                            if ($error == "" && ((isset($v['maxThumbWidth']) && $v['maxThumbWidth'] != "") || (isset($v['maxThumbHeight']) && $v['maxThumbHeight'] >= 1))) {
                                $mWidth = isset($v['maxThumbWidth']) && $v['maxThumbWidth'] != "" ? $v['maxThumbWidth'] : 60;
                                $mHeight = isset($v['maxThumbHeight']) && $v['maxThumbHeight'] != "" ? $v['maxThumbHeight'] : 60;
                                $keepAspect = isset($v['thumbKeepAspect']) && $v['thumbKeepAspect'] == "0" ? false : true;

                                if (!$msUploader->resize_uploaded_image($imagePath,$newImageName,'th_' . $newImageName,$mWidth,$mHeight,$keepAspect,true)) {
                                    $error .= "<li>" . $msUploader->getLastError(true) . "</li>";
                                }
                            }
                            ## now check to see if the original images needs to be resized ##
                            if ($error == "" && ((isset($v['maxWidth']) && $v['maxWidth'] != "") || (isset($v['maxHeight']) && $v['maxHeight'] >= 1))) {
                                $mWidth = isset($v['maxWidth']) && $v['maxWidth'] != "" ? $v['maxWidth'] : 1000;
                                $mHeight = isset($v['maxHeight']) && $v['maxHeight'] != "" ? $v['maxHeight'] : 1000;

                                if (!$msUploader->resize_uploaded_image($imagePath,$newImageName,$newImageName,$mWidth,$mHeight)) {
                                    $error .= "<li>" . $msUploader->getLastError(true) . "</li>";
                                }
                            }
                        }

                        /* Build the SQL statement for this item */
                        if ($newImageName != "") {
                            array_push($sqlValues,"'" . $this->oDB->sqlize($newImageName) . "'");
                        } else {
                            array_push($sqlValues,"''");
                        }
                        break;

                    case 'order':
                        /* DO NOTHING - WE WILL PROCESS THE ORDER FIELD LATER */
                        break;

                    case 'date':
                        array_push($sqlFields,$k);
                        /* ADD THE FILED NAME TO THE BEGINNING SQL STATEMENT */

                        /* Build the SQL statement for this item */
                        $date = date("Y-m-d",strtotime($data[$k]));
                        array_push($sqlValues,"'" . $this->oDB->sqlize($date) . "'");
                        break;

                    case 'password':

                        if (isset($v['isEncrypted']) && $v['isEncrypted']) {
                            $valueToUse = $data[$v['fieldName']];
                        } else {
                            $valueToUse = md5($data[$v['fieldName']]);
                        }

                        /* ADD THE FILED NAME TO THE BEGINNING SQL STATEMENT */
                        array_push($sqlFields,$k);

                        /* Build the SQL statement for this item */
                        array_push($sqlValues,"'" . $this->oDB->sqlize($valueToUse) . "'");
                        break;

                    case 'checkbox':
                        array_push($sqlFields,$k);
                        /* ADD THE FILED NAME TO THE BEGINNING SQL STATEMENT */

                        /* Build the SQL statement for this item */
                        $isActive = isset($data[$k]) ? "1" : "0";
                        array_push($sqlValues,"'" . $isActive . "'");
                        break;

                    case 'select':
                        $value = "";
                        if (isset($k['multiple']) && $k['multiple']) {
                            if (isset($data[$k])) {
                                // We need to lop through and build a string seperated by ','s
                                // to insert into the DB
                                for ($loop = 0; $loop < sizeof($data[$k]); $loop++) {
                                    if ($value == "") {
                                        $value .= $data[$k][$loop];
                                    } else {
                                        $value .= "," . $data[$k][$loop];
                                    }
                                }
                            } else {
                                $value = "0";
                            }
                        } else {
                            $value = $data[$k];
                        }

                        array_push($sqlFields,$k);

                        /* Build the SQL statement for this item */
                        array_push($sqlValues,"'" . $this->oDB->sqlize($value) . "'");
                        break;

                    case 'dynamic':
                        ## we need to make a dynamic string ##
                        $fieldsToUse = explode(',',$v['dynamicFields']);
                        $valueToUse = '';
                        if (is_array($fieldsToUse)) {
                            foreach ($fieldsToUse as $v2) {
                                $valueToUse .= $data[$v2];
                            }
                        }
                        else {
                            $valueToUse .= $data[$fieldsToUse];
                        }

                        $dynamicValue = substr(md5($valueToUse),5,4);

                        /* ADD THE FILED NAME TO THE BEGINNING SQL STATEMENT */
                        array_push($sqlFields,$k);

                        /* Build the SQL statement for this item */
                        array_push($sqlValues,"'" . $this->oDB->sqlize($dynamicValue) . "'");
                        break;

                    case 'foreignKey':
                        $valueToUse = $data[$k];

                        /* ADD THE FILED NAME TO THE BEGINNING SQL STATEMENT */
                        array_push($sqlFields,$k);

                        /* Build the SQL statement for this item */
                        array_push($sqlValues,"'" . $this->oDB->sqlize($valueToUse) . "'");
                        break;

                    default:
                        /* ADD THE FILED NAME TO THE BEGINNING SQL STATEMENT */
                        array_push($sqlFields,$k);

                        /* Build the SQL statement for this item */
                        array_push($sqlValues,"'" . $this->oDB->sqlize($data[$k]) . "'");
                }
            }
        }

        /* FINISH THE SQL STATEMENT */

        $sSQL = sprintf("INSERT INTO %s (%s) VALUES (%s)",$this->db_table,implode(',',$sqlFields),implode(',',$sqlValues));

        if ($error == "") {
            /* ADD THE RECORD */
            if (!$rStmt = $this->oDB->query($sSQL,__FILE__,__LINE__)) {
                $error .= "<li>There was a general error inserting the data. Please try again or contact the dev team to have the error corrected. (FILE: " . __FILE__ . " LINE: " . __LINE__ . ")</li>";
            }
            /* SET THE PRIMARY KEY VALUE */
            $data[$this->db_pk] = mysql_insert_id();

            /* STILL NO ERROR... PROCESS THE ORDER FIELD IF ONE IS PRESENT */
            if ($error == "" && $this->db_order_field != "" && stristr($this->db_order_field,"order")) {
                if ($data[$this->db_order_field] != $data['maxOrder']) {
                    $getIds = "SELECT " . $this->db_pk . ", " . $this->db_order_field . " FROM " . $this->db_table . " WHERE " . $this->db_order_field . " >= " . $data[$this->db_order_field];

                    if ($this->db_fk != '') {
                        $getIds .= " AND " . $this->db_fk . " = " . $data[$this->db_fk];
                    }

                    if (isset($this->db_additionalRestraint) && $this->db_additionalRestraint != '') {
                        $getIds .= " AND " . $this->db_additionalRestraint . " = " . $data[$this->db_additionalRestraint];
                    }

                    // $results = $this->oDB->query($getIds,__FILE__,__LINE__);
                    $temp = $this->oDB->query($getIds,__FILE__,__LINE__);
                    if ($temp['recordCount'] >= 1) {
                        for ($i = 0; $i < $temp['recordCount']; $i++) {
                            /* UPDATE THE ORDER */
                            $updateOrder = "UPDATE " . $this->db_table . " SET " . $this->db_order_field . " = " . ($temp[$i][$this->db_order_field] + 1) . " WHERE " . $this->db_pk . " = " . $temp[$i][$this->db_pk];
                            $this->oDB->query($updateOrder,__FILE__,__LINE__);
                        }
                        /* UPDATE THE CURRENT RECORDS ORDER */
                        $updateOrder = "UPDATE " . $this->db_table . " SET " . $this->db_order_field . " = " . $data[$this->db_order_field] . " WHERE " . $this->db_pk . " = " . $data[$this->db_pk];
                        $this->oDB->query($updateOrder,__FILE__,__LINE__);
                    } else {
                        $error .= "<li>There was a error updating the order. Please try again or contact the dev team to have the error corrected. (FILE: " . __FILE__ . " LINE: " . __LINE__ . ")</li>";
                    }
                } else {
                    $updateTeamOrder = "UPDATE " . $this->db_table . " SET " . $this->db_order_field . " = " . $data[$this->db_order_field] . " WHERE " . $this->db_pk . " = " . $data[$this->db_pk];
                    $this->oDB->query($updateTeamOrder,__FILE__,__LINE__);
                }
            }

            ## if there are any special processing that needs to be done do it here ##
            if ($this->db_special_proc != '') {
                ## include the special process file ##
                include SITE_PATH . $this->db_special_proc;
            }
        }

        if ($error != '') {
            return array(0,$error);
        } else {
            return array(1,$data[$this->db_pk]);
        }

    }

    /**
     * This function will update a record (admin process). If the class
     * has not been initialized with the db configuration it will return
     * false.
     */
    function updateRecord($data,$files) {

        if (!$this->db_initialized) {
            return false;
        }

        /* ***** PROCESS AND BUILD THE DB QUERY ***** */
        /**********************************************/
        $error = "";
        $edit_fields = $this->getDBEditFields();

        /* CREATE THE BEGINNING SQL STATEMENT */
        $sSQLUpdate = "UPDATE " . $this->db_table . " SET ";

        /* LOOP THROUGH THE FIELDS */
        for ($i = 0; $i < sizeof($this->fields); $i++) {
            if (in_array($this->fields[$i]['fieldName'],$edit_fields)) {
                /* SWITCH ON THE FIELD TYPE */
                switch ($this->fields[$i]['type']) {
                    case 'image':
                        /* GET THE IMAGE NAME */
                        $sSQLCurrentImage = "SELECT " . $this->fields[$i]['fieldName'] . " FROM " . $this->db_table . " WHERE " . $this->db_pk . " = " . $data[$this->db_pk];
                        $fileName = $this->oDB->query($sSQLCurrentImage,__FILE__,__LINE__);

                        /* UPLOAD THE IMAGE */
                        $newImageName = "";
                        //print_a($files);
                        if ($files[$this->fields[$i]['fieldName']]['name'] != "") {
                            // Upload the new image
                            $msUploader = new ms_uploader();
                            $msUploader->setFileTypes($this->fields[$i]['allowedFileType']);
                            $imagePath = PUBLIC_SITE_PATH;
                            $imagePath .= $this->db_overide_image_path != '' ? $this->db_overide_image_path : $this->db_default_image_path . $this->db_table;

                            if (!$msUploader->uploadFile($files,$this->fields[$i]['fieldName'],$imagePath,$newImageName)) {
                                $error .= "<li>" . $msUploader->getLastError(true) . "</li>";
                            }

                            // Check is we need to create a thumbnail...
                            if ($error == "" && ((isset($this->fields[$i]['maxThumbWidth']) && $this->fields[$i]['maxThumbWidth'] != "") || (isset($this->fields[$i]['maxThumbHeight']) && $this->fields[$i]['maxThumbHeight'] >= 1))) {
                                $mWidth = isset($this->fields[$i]['maxThumbWidth']) && $this->fields[$i]['maxThumbWidth'] != "" ? $this->fields[$i]['maxThumbWidth'] : 60;
                                $mHeight = isset($this->fields[$i]['maxThumbHeight']) && $this->fields[$i]['maxThumbHeight'] != "" ? $this->fields[$i]['maxThumbHeight'] : 60;
                                $keepAspect = isset($this->fields[$i]['thumbKeepAspect']) && $this->fields[$i]['thumbKeepAspect'] == "0" ? false : true;

                                if (!$msUploader->resize_uploaded_image($imagePath,$newImageName,'th_' . $newImageName,$mWidth,$mHeight,$keepAspect,true)) {
                                    $error .= "<li>" . $msUploader->getLastError(true) . "</li>";
                                }

                                /* DELETE THE CURRENT THUMBIMAGE IF IT EXISTS */
                                if ($error == "" && $fileName['recordCount'] >= 1) {
                                    if ($fileName[0][$this->fields[$i]['fieldName']] != "") {
                                        $path = dirname($_SERVER['DOCUMENT_ROOT']) . $this->db_default_image_path . $this->db_table . "/";
                                        if(file_exists($path . "th_" . $fileName[0][$this->fields[$i]['fieldName']]) && !@unlink($path . "th_" . $fileName[0][$this->fields[$i]['fieldName']])) {
                                            /* ERROR DELETING THE FILE */
                                            $message = "Could not delete an image (".$path . "th_" . $fileName[0][$this->fields[$i]['fieldName']].") from the server. FILE: ";
                                            $message .=__FILE__ . " LINE: " .__LINE__;
                                            $subject = "Error Deleting an image";
                                            $from = "From: jpoisson@igzactly.com";
                                            mail("jpoisson@igzactly.com",$subject,$message,$from);
                                            $error .= "<li>There was a error deleting the <strong>" . $this->fields[$i]['label'] . "(".$path . "th_" . $fileName[$this->fields[$i]['fieldName']].")</strong>. Please try again or contact the dev team to have the error corrected. (FILE: " . __FILE__ . " LINE: " . __LINE__ . ")</li>";
                                        }
                                    }
                                }

                            }
                            ## now check to see if the original images needs to be resized ##
                            if ($error == "" && ((isset($this->fields[$i]['maxWidth']) && $this->fields[$i]['maxWidth'] != "") || (isset($this->fields[$i]['maxHeight']) && $this->fields[$i]['maxHeight'] >= 1))) {
                                $mWidth = isset($this->fields[$i]['maxWidth']) && $this->fields[$i]['maxWidth'] != "" ? $this->fields[$i]['maxWidth'] : 1000;
                                $mHeight = isset($this->fields[$i]['maxHeight']) && $this->fields[$i]['maxHeight'] != "" ? $this->fields[$i]['maxHeight'] : 1000;

                                if (!$msUploader->resize_uploaded_image($imagePath,$newImageName,$newImageName,$mWidth,$mHeight)) {
                                    $error .= "<li>" . $msUploader->getLastError(true) . "</li>";
                                }
                            }

                            /* DELETE THE CURRENT IMAGE IF IT EXISTS */
                            if ($error == "" && $fileName['recordCount'] >= 1) {
                                if ($fileName[0][$this->fields[$i]['fieldName']] != "") {
                                    $path = dirname($_SERVER['DOCUMENT_ROOT']) . $this->db_default_image_path . $this->db_table . "/";
                                    if(file_exists($path . $fileName[0][$this->fields[$i]['fieldName']]) && !@unlink($path . $fileName[0][$this->fields[$i]['fieldName']])) {
                                        /* ERROR DELETING THE FILE */
                                        $message = "Could not delete an image (".$path . $fileName[0][$this->fields[$i]['fieldName']].") from the server. FILE: ";
                                        $message .=__FILE__ . " LINE: " .__LINE__;
                                        $subject = "Error Deleting an image";
                                        $from = "From: jpoisson@igzactly.com";
                                        mail("jpoisson@igzactly.com",$subject,$message,$from);
                                        $error .= "<li>There was a error deleting the <strong>" . $this->fields[$i]['label'] . "(".$path . $fileName[$this->fields[$i]['fieldName']].")</strong>. Please try again or contact the dev team to have the error corrected. (FILE: " . __FILE__ . " LINE: " . __LINE__ . ")</li>";
                                    }
                                }
                            }
                        }

                        /* Build the SQL statement for this item */
                        if ($newImageName != "")
                        {
                            $sSQLUpdate .= $this->fields[$i]['fieldName'] . " = '" . $this->oDB->sqlize($newImageName) . "'";
                            /* ADD THE CORRECT SEPERATOR IF NEEDED */
                            if ($i != (sizeof($this->fields) - 1))
                            {
                                $sSQLUpdate .= ", ";
                            }
                        }
                        break;
                    case 'order':
                        /* DO NOTHING - WE WILL PROCESS THE ORDER FIELD LATER */
                        break;
                    case 'password':
                        if (isset($this->fields[$i]['isEncrypted']) && $this->fields[$i]['isEncrypted'] == '0') {
                            $valueToUse = $data[$this->fields[$i]['fieldName']];
                        } else {
                            $valueToUse = md5($data[$this->fields[$i]['fieldName']]);
                        }

                        $sSQLUpdate .= $this->fields[$i]['fieldName'] . " = '" . $this->oDB->sqlize($valueToUse) . "'";
                        /* ADD THE CORRECT SEPERATOR IF NEEDED */
                        if ($i != (sizeof($this->fields) - 1)) {
                            $sSQLUpdate .= ", ";
                        }
                        break;

                    case 'select':
                        $value = "";
                        if (isset($this->fields[$i]['multiple']) && $this->fields[$i]['multiple'] == 1) {
                            if (isset($data[$this->fields[$i]['fieldName']])) {
                                // We need to loop through and build a string seperated by ','s
                                // to insert into the DB
                                for ($loop = 0; $loop < sizeof($data[$this->fields[$i]['fieldName']]); $loop++) {
                                    if ($value == "") {
                                        $value .= $data[$this->fields[$i]['fieldName']][$loop];
                                    } else {
                                        $value .= "," . $data[$this->fields[$i]['fieldName']][$loop];
                                    }
                                }
                            } else {
                                $value = "0";
                            }
                        } else {
                            $value = $data[$this->fields[$i]['fieldName']];
                        }

                        $sSQLUpdate .= $this->fields[$i]['fieldName'] . " = '" . $this->oDB->sqlize($value) . "' ";

                        /* ADD THE CORRECT SEPERATOR IF NEEDED */
                        if ($i != (sizeof($this->fields) - 1))
                        {
                            $sSQLUpdate .= ", ";
                        }

                        break;
                    case 'checkbox':
                        /* Build the SQL statement for this item */
                        $isActive = isset($data[$this->fields[$i]['fieldName']]) ? "1" : "0";
                        $sSQLUpdate .= $this->fields[$i]['fieldName'] . " = '" . $isActive . "' ";

                        /* ADD THE CORRECT SEPERATOR IF NEEDED */
                        if ($i != (sizeof($this->fields) - 1))
                        {
                            $sSQLUpdate .= ", ";
                        }
                        break;


                    case 'date':
                        /* Build the SQL statement for this item */
                        $date = date("Y-m-d",strtotime($data[$this->fields[$i]['fieldName']]));
                        $sSQLUpdate .= $this->fields[$i]['fieldName'] . " = '" . $this->oDB->sqlize($date) . "'";

                        /* ADD THE CORRECT SEPERATOR IF NEEDED */
                        if ($i != (sizeof($this->fields) - 1)) {
                            $sSQLUpdate .= ", ";
                        }
                        break;

                    default:
                        /* Build the SQL statement for this item */
                        $sSQLUpdate .= $this->fields[$i]['fieldName'] . " = '" . $this->oDB->sqlize($data[$this->fields[$i]['fieldName']]) . "'";
                        /* ADD THE CORRECT SEPERATOR IF NEEDED */
                        if ($i != (sizeof($this->fields) - 1))
                        {
                            $sSQLUpdate .= ", ";
                        }
                }
            }
        }

        /* FINISH THE SQL STATEMENT */
        if (substr($sSQLUpdate,-2,2) == ", ") {
            $sSQLUpdate = substr_replace($sSQLUpdate," ",-2);
        }

        /* ADD THE WHERE STATEMENT */
        $sSQLUpdate .= " WHERE " . $this->db_pk . " = " . $data[$this->db_pk];

        /* MAKE THE ONE SQL STATEMENT */
        $sSQL = $sSQLUpdate;

        if ($error == "") {
            /* ADD THE RECORD */
            if (!$rStmt = $this->oDB->query($sSQL,__FILE__,__LINE__)) {
                $error .= "<li>There was a general error inserting the data. Please try again or contact the dev team to have the error corrected. (FILE: " . __FILE__ . " LINE: " . __LINE__ . ")</li>";
            }

            /* STILL NO ERROR... PROCESS THE ORDER FIELD IF ONE IS PRESENT */
            if ($error == "" && $this->db_order_field != "" && stristr($this->db_order_field,"order") && isset($data[$this->db_order_field])) {
                if ($data[$this->db_order_field] != $data['currentOrder']) {
                    if ($data[$this->db_order_field] > $data['currentOrder']) {
                        $getIds = "SELECT " . $this->db_pk . ", " . $this->db_order_field . " FROM " . $this->db_table . " WHERE " . $this->db_order_field . " >= " . $data['currentOrder'] . " AND " . $this->db_order_field . " <= " . $data[$this->db_order_field];
                    } else if ($data[$this->db_order_field] < $data['currentOrder']) {
                        $getIds = "SELECT " . $this->db_pk . ", " . $this->db_order_field . " FROM " . $this->db_table . " WHERE " . $this->db_order_field . " >= " . $data[$this->db_order_field] . " AND " . $this->db_order_field . " < " . $data['currentOrder'];
                    }

                    if (isset($this->db_fk) && $this->db_fk != '') {
                        $getIds .= " AND " . $this->db_fk . " = " . $data[$this->db_fk];
                    }

                    if (isset($this->db_additionalRestraint) && $this->db_additionalRestraint != '') {
                        $getIds .= " AND " . $this->db_additionalRestraint . " = " . $data[$this->db_additionalRestraint];
                    }

                    $temp = $this->oDB->query($getIds,__FILE__,__LINE__);
                    if ($temp['recordCount'] >= 1) {
                        for ($i = 0; $i < $temp['recordCount']; $i++) {
                            /* UPDATE THE ORDER */
                            if ($data[$this->db_order_field] < $data['currentOrder']) {
                                $updateOrder = "UPDATE " . $this->db_table . " SET " . $this->db_order_field . " = " . ($temp[$i][$this->db_order_field] + 1) . " WHERE " . $this->db_pk . " = " . $temp[$i][$this->db_pk];
                            } else {
                                $updateOrder = "UPDATE " . $this->db_table . " SET " . $this->db_order_field . " = " . ($temp[$i][$this->db_order_field] - 1) . " WHERE " . $this->db_pk . " = " . $temp[$i][$this->db_pk];
                            }

                            $this->oDB->query($updateOrder,__FILE__,__LINE__);
                        }
                        /* UPDATE THE CURRENT RECORDS ORDER */
                        $updateOrder = "UPDATE " . $this->db_table . " SET " . $this->db_order_field . " = " . $data[$this->db_order_field] . " WHERE " . $this->db_pk . " = " . $data[$this->db_pk];
                        $this->oDB->query($updateOrder,__FILE__,__LINE__);
                    } else {
                        $error .= "<li>There was a error updating the order. Please try again or contact the dev team to have the error corrected. (FILE: " . __FILE__ . " LINE: " . __LINE__ . ")</li>";
                    }
                }
            }

            ## if there are any special processing that needs to be done do it here ##
            if (isset($this->db_special_proc) && $this->db_special_proc != '') {
                ## include the special process file ##
                include $this->db_special_proc;
            }
        }

        if ($error != '') {
            return array(0,$error);
        } else {
            return array(1);
        }

    }

    function deleteRecord($id,$fk = '',$additionalRestraintOrder = '') {

        $error = "";

        /* GET THE IMAGE/FILE NAMES SO WE CAN DELETE THEM */
        $aFile = array();
        $sFileNames = "";
        for ($i = 0; $i < sizeof($this->fields); $i++) {
            if ($this->fields[$i]['type'] == "image" || $this->fields[$i]['type'] == "file") {
                $aFile[] = $this->fields[$i]['fieldName'];
                $sFileNames .= $this->fields[$i]['fieldName'] . ", ";
            }
        }

        if (substr($sFileNames,-2,2) == ", ") {
            $sFileNames = substr_replace($sFileNames,"",-2);
        }

        if ($sFileNames != "") {
            $sSQL = sprintf("SELECT %s FROM %s WHERE %s = '%s'",$sFileNames,$this->db_table,$this->db_pk,$id);
            $aFileNames = $this->oDB->query($sSQL,__FILE__,__LINE__);
        }

        /* GET THE ORDER IF ONE IS PRESENT */
        if ($this->db_order_field != "") {
            $sSQL = sprintf("SELECT %s FROM %s WHERE %s = '%s'",$this->db_order_field,$this->db_table,$this->db_pk,$id);
            $aOrderField = $this->oDB->query($sSQL,__FILE__,__LINE__);
        }

        /* DELETE THE RECORD */
        $sSQL = sprintf("DELETE FROM %s WHERE %s = '%s'",$this->db_table,$this->db_pk,$id);
        if (!$rStmt = $this->oDB->query($sSQL,__FILE__,__LINE__)) {
            $error .= "<li>There was a error deleting the record. Please try again or contact the dev team to have the error corrected (FILE: " . __FILE__ . " LINE: " . __LINE__ . ").</li>";
        }

        /* DELETE ANY FILES */
        if ($error == "" && $sFileNames != "") {
            $path = PUBLIC_SITE_PATH;
            $path .= $this->db_overide_image_path != '' ? $this->db_overide_image_path : $this->db_default_image_path . $this->db_table . "/";
            foreach ($aFile as $k => $v) {
                if ($aFileNames[0][$v] != "") {
                    if(!@unlink($path . $aFileNames[0][$v])) {
                        /* ERROR DELETING THE FILE */
                        $error .= "<li>There was a error deleting the <strong>file (".$path . $aFileNames[0][$v].")</strong>. Please try again or contact the dev team to have the error corrected. (FILE: " . __FILE__ . " LINE: " . __LINE__ . ")</li>";
                    }
                    if(file_exists($path . "th_" . $aFileNames[0][$v]) && !@unlink($path . "th_" . $aFileNames[0][$v])) {
                        /* ERROR DELETING THE FILE */
                        $error .= "<li>There was a error deleting the <strong>file (".$path . "th_" . $aFileNames[0][$v].")</strong>. Please try again or contact the dev team to have the error corrected. (FILE: " . __FILE__ . " LINE: " . __LINE__ . ")</li>";
                    }
                }
            }
        }

        /* STILL NO ERROR... PROCESS THE ORDER FIELD IF ONE IS PRESENT */
        if ($error == "" && $this->db_order_field != "" && stristr($this->db_order_field,"order")) {
            $getIds = "SELECT " . $this->db_pk . ", " . $this->db_order_field . " FROM " . $this->db_table . " WHERE " . $this->db_order_field . " >= " . $aOrderField[0][$this->db_order_field];
            if (isset($this->db_fk) && $this->db_fk != '') {
                $getIds .= " AND " . $this->db_fk . " = " . $fk;
            }

            if ($additionalRestraintOrder != '') {
                $getIds .= " AND " . $additionalRestraintOrder;
            }

            $temp = $this->oDB->query($getIds,__FILE__,__LINE__);
            if ($temp['recordCount'] >= 1) {
                for ($i = 0; $i < $temp['recordCount']; $i++) {
                    /* UPDATE THE ORDER */
                    $updateOrder = "UPDATE " . $this->db_table . " SET " . $this->db_order_field . " = " . ($temp[$i][$this->db_order_field] - 1) . " WHERE " . $this->db_pk . " = " . $temp[$i][$this->db_pk];
                    $this->oDB->query($updateOrder,__FILE__,__LINE__);
                }
            }
        }

        ## if there are any special processing that needs to be done do it here ##
        if (isset($this->db_special_proc) && $this->db_special_proc != '') {
            ## include the special process file ##
            include $this->db_special_proc;
        }

        if ($error != '') {
            return array(0,$error);
        } else {
            return array(1);
        }

    }

}


?>