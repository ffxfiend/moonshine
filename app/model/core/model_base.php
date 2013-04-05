<?php
/**
 * File: model_base.class.php
 *
 * This class will give each model a core set
 * of common function that can be used in all
 * models.
 *
 * @todo Fully document each class method.
 * @author Jeremiah Poisson
 *
 * extends modelDB
 *
 */
class modelBase {

    protected $classDataReference = array();

    protected $images = array();
    protected $files = array();

    public function getTable() { return ''; }
    public function getPrimaryKey() { return ''; }

    function __construct() {

    }

    /* ***** LOAD/DB/ADMIN METHODS ***** */
    /*************************************/
    function load($id) {
        $sql = sprintf("SELECT * FROM %s WHERE %s = %d", $this->getTable(), $this->getPrimaryKey(), $id);
        $results = mysql_db::getInstance()->query($sql,__FILE__,__LINE__);

        if ($results) {
            foreach ($results[0] as $k => $v) {
                $this->{$k} = $v;
                array_push($this->classDataReference,$k);
            }
            return true;
        }

        return false;

    }

    /* ***** GET ALL ***** */
    /* function getAll($active = true) {

        if ($active) {
            $results = $this->getRecords(false);
        } else {
            $results = $this->getRecords(false,false);
        }

        if ($results && $results['recordCount'] >= 1) {
            $return = array();
            $className = get_class($this);
            for ($i = 0; $i < $results['recordCount']; $i++) {
                $temp = new $className($this->registry);
                $temp->load($results[$i][$this->getDBPK()]);
                array_push($return,$temp);
            }
            return $return;
        } else {
            return array();
        }

    } */
    /* ******************* */

    function getListData($var) {

        return $this->{$var};

    }


    /**
     * This function returns all the configuration necessary for the list page.
     *
     * @param $VEDPK
     * @return array
     * @author Jeremiah Poisson
     **/
    /* function getListPageConf($VEDPK) {

        // Figure out if their are any specified widths
        // and if so add them together to subtrack them
        // from the other col width

        $temp['listFields'] = $this->getDBListFields();
        $temp['fields']		= $this->getDBFields();
        $miscWidth = 0;
        $numColums = sizeof($temp['listFields']);
        $otherColums = 0;
        foreach ($temp['listFields'] as $k => $v)
        {
            foreach ($temp['fields'] as $k2 => $v2)
            {
                if ($v2['fieldName'] == $v)
                {
                    if (isset($v2['listColWidth']) && $v2['listColWidth'] != '0') {
                        $miscWidth += ($v2['listColWidth'] + 6);
                        $numColums--;
                        $otherColums++;
                    }
                }
            }
        }

        $baseWidth = 750;
        $actionColWidth = 85;
        $otherColWidth = ceil((((($baseWidth - $actionColWidth) - 6) - $miscWidth) / $numColums) - 8);

        $return = array(
            'listPermissions' 	=> $this->getDBListPermissions(),
            'addBtnText' 		=> $this->getDBAddBtnText(),
            'noRecordText' 		=> $this->getDBNoRecordText(),
            'fields' 			=> $this->getDBFields(),
            'db_table' 			=> $this->getDBTable(),
            'db_pk' 			=> $this->getDBPK(),
            'db_list_fields' 	=> $this->getDBListFields(),
            'db_active_field' 	=> $this->getDBActiveField(),
            'modelName' 		=> $this->getModelName(),
            'tableName' 		=> $this->getDBProcTableName(),
            'viewEditDeletePK' 	=> $VEDPK,
            'baseWidth' 		=> $baseWidth,
            'actionColWidth' 	=> $actionColWidth,
            'otherColWidth' 	=> $otherColWidth
        );

        return $return;

    } */

    function dumpObject() {
        $temp = array();
        foreach ($this->classDataReference as $k => $v) {
            $temp[$v] = $this->{$v};
        }
        return $temp;
    }

    /**
     * This function will retrieve the field data array for
     * use in the add/edit pages
     *
     * @param array $fl
     * @param array $c
     *
     * @return array
     * @author Jeremiah Poisson
     **/
    // function getFieldData($fl = array(),$c = array()) { return $this->getDBFieldData($fl,$c); }

    /**
     * This function returns the basic configuration data
     * needed for the add/edit forms
     *
     * @param int $fkID This is the ID of the FK is one is present
     *
     * @return array
     * @author Jeremiah Poisson
     **/
    // function getFormConfig($fkID = 0) { return $this->getAddEditFormConfig($fkID); }

    /**
     * This record will add a new record to the DB. It will handle file/image uploads
     * as well as backing out of the save process if needed.
     *
     * @param array $data   This is an associative array of data that will be added.
     * @param array $files  This is an array of the files in the _FILES array. It is used
     *                      to upload any files if there are any associated with the model.
     *
     * @return array
     *
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     **/
    function add($data,$files) {

        $error = false;
        $steps_stack = array();

        // Upload any files that need to be uploaded.
        // Right now this is stub functionality.
        if (!empty($this->images)) {
            if (!$this->uploadImages($files)) {
                $error = true;
            } else {
                array_push($steps_stack,'image_upload');
            }
        }

        if (!empty($this->files)) {
            if (!$this->uploadFiles($files)) {
                $error = true;
            } else {
                array_push($steps_stack,'file_upload');
            }
        }

        if ($error) {
            // Lets back out now if any of the file uploads failed.
            $this->removeFilesFromServer($files);
            return array(false,'Error Uploading Files.');
        }

        // Looks like any files/images uploaded without error. Let's update
        // the DB table.
        $db = mysql_db::getInstance();

        // Attempt to generate the sql statement.
        try {
            $sql = $db->prepareInsertSQL($data,$this->getTable());
        } catch (Exception $e) {
            // Back out of any file uploads already completed.
            $this->removeFilesFromServer($files);
            return array(false,$e->getMessage());
        }

        $results = $db->query($sql,__FILE__,__LINE__);

        if ($results) {
            return array(true,$db->getLastInsertID());
        } else {
            // The insert failed. We need to back out of any file uploads
            // then return false.
            $this->removeFilesFromServer($files);
            return array(false,'Error Editing Record.');
        }

    }

    /**
     * This function will edit the current record. It first sees if
     * their are any images/files associated with the model. If so
     * we will attempt to upload each in turn. If no errors occur
     * during upload we will proceed to edit the main record.
     *
     * @param array $data   This is an associative array of data that will be updated.
     *                      It must include the primary key as one of the array elements.
     * @param array $files  This is an array of the files in the _FILES array. It is used
     *                      to upload any files if there are any associated with the model.
     *
     * @return array
     *
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     **/
    function edit($data,$files = array()) {

        $error = false;
        $steps_stack = array();

        // Upload any files that need to be uploaded.
        // Right now this is stub functionality.
        if (!empty($this->images)) {
            if (!$this->uploadImages($files)) {
                $error = true;
            } else {
                array_push($steps_stack,'image_upload');
            }
        }

        if (!empty($this->files)) {
            if (!$this->uploadFiles($files)) {
                $error = true;
            } else {
                array_push($steps_stack,'file_upload');
            }
        }

        if ($error) {
            // Lets back out now if any of the file uploads failed.
            $this->removeFilesFromServer($files);
            return array(false,'Error Uploading Files.');
        }

        // Looks like any files/images uploaded without error. Let's update
        // the DB table.
        $db = mysql_db::getInstance();

        // Attempt to generate the sql statement.
        try {
            $sql = $db->prepareUpdateSQL($data,$this->getTable(),$this->getPrimaryKey());
        } catch (Exception $e) {
            // Back out of any file uploads already completed.
            $this->removeFilesFromServer($files);
            return array(false,$e->getMessage());
        }

        $results = $db->query($sql,__FILE__,__LINE__);

        if ($results) {
            return array(true);
        } else {
            // The insert failed. We need to back out of any file uploads
            // then return false.
            $this->removeFilesFromServer($files);
            return array(false,'Error Editing Record.');
        }

    }

    /**
     * This function will delete a record.
     *
     * @param int    $primary_key The primary key of the record to be deleted
     * @param string $foreign_key The foreign key of the record to be deleted
     *
     * @return mixed
     *
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     *
     * @TODO: Add functionality to actual use the foreign key when deleting a record.
     * @TODO: Move sql generation into the mysql_db class to live with the update/add sql generation functions.
     **/
    function delete($primary_key,$foreign_key = '') {

        $db = mysql_db::getInstance();

        $sql = sprintf("DELETE FROM %s WHERE %s = %d", $this->getTable(), $this->getPrimaryKey(), $primary_key);
        $results = $db->query($sql,__FILE__,__LINE__);

        if ($results) {
            // Check to see if this object has images/files.
            // If so we want to remove delete them.
            if (!empty($this->images) || !empty($this->files)) { $this->_deleteFiles(); }

            return array(true);
        } else {
            return array(false,"Error Deleting Record");
        }

    }

    /**
     * This function will take an array of MySQL results
     * and attempt to load objects based on the primary
     * key. If will return an array of loaded objects.
     *
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     *
     * @param array $results
     * @return array
     */
    public function loadObjectsByResults($results) {

        $objects = array();
        if ($results) {
            foreach ($results as $v) {
                $class = get_called_class();
                $t = new $class();
                if ($t->load($v[$this->getPrimaryKey()])) { array_push($objects,$t); }
            }
        }

        dump($objects);
        return $objects;

    }

    public function uploadFiles($files) {
        /* STUB FUNCTION */
        return true;
    }

    public function uploadImages($files) {
        /* STUB FUNCTION */

        /**
         * /* GET THE IMAGE NAME *
        $sSQLCurrentImage = "SELECT " . $this->fields[$i]['fieldName'] . " FROM " . $this->db_table . " WHERE " . $this->db_pk . " = " . $data[$this->db_pk];
        $fileName = $this->oDB->query($sSQLCurrentImage,__FILE__,__LINE__);

        /* UPLOAD THE IMAGE *
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

        /* DELETE THE CURRENT THUMBIMAGE IF IT EXISTS *
        if ($error == "" && $fileName['recordCount'] >= 1) {
        if ($fileName[0][$this->fields[$i]['fieldName']] != "") {
        $path = dirname($_SERVER['DOCUMENT_ROOT']) . $this->db_default_image_path . $this->db_table . "/";
        if(file_exists($path . "th_" . $fileName[0][$this->fields[$i]['fieldName']]) && !@unlink($path . "th_" . $fileName[0][$this->fields[$i]['fieldName']])) {
        /* ERROR DELETING THE FILE *
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

        /* DELETE THE CURRENT IMAGE IF IT EXISTS *
        if ($error == "" && $fileName['recordCount'] >= 1) {
        if ($fileName[0][$this->fields[$i]['fieldName']] != "") {
        $path = dirname($_SERVER['DOCUMENT_ROOT']) . $this->db_default_image_path . $this->db_table . "/";
        if(file_exists($path . $fileName[0][$this->fields[$i]['fieldName']]) && !@unlink($path . $fileName[0][$this->fields[$i]['fieldName']])) {
        /* ERROR DELETING THE FILE *
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
         */

        return true;
    }

    public function removeFilesFromServer($files) {
        /* STUB FUNCTION */
        return true;
    }

    private function _deleteFiles() {

    }


}
