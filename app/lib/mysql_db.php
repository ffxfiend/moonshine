<?php
/**
 * File: mysql_db.class.php
 * Date: 7/2/06
 *
 * Description: This file contains a class with all the database connectivity
 * functions. It is used to make a new connection to a MySQL Database as well
 * as query the database and other useful function
 *
 * Change Log:
 *   Date                       Description
 * 07/02/06                 Initial File Creation
 * 02/08/12                 Update to be used as a singleton class
 * 02/08/12                 Update to use mysqli instead of mysql
 * 02/08/12                 Add documentation to all functions
 * 01/09/13                 Removed $dbConn private variable as it is no longer in use.
 * 01/09/13                 Create two new private methods, _setConfig and _connectAndSelectDB.
 * 01/09/13                 Tidy up the constructor method to use the new private methods.
 * 01/09/13                 Misc updates throughout file.
 *
 * @version 2.0
 * @author Jeremiah Poisson
 *
 *
 * @var mysqli $mysqli_conn
 */

class mysql_db {

    private static $instance = null;

    private $config = array();

    private $mysqli_conn = null;

    private $m_aQueries = array();
    private $m_TotalQueryExTime = 0;

    private $aEscapeThese = array("\\", "'", "\"");
    private $sDBEscape = "\\";

    /**
     * Constructor function. This function will attempt
     * to load the database configuration. If no configuration
     * is found it will return false. This function wil also
     * return false if it cannot connect to mysql or select
     * a database.
     *
     * The function will first look at the registry
     * for the configuration. If it does not find it
     * it will look in the configuration directory for
     * a corresponding database.ini file. If this is not
     * found the function will return false.
     *
     * @author Jeremiah Poisson
     * @param string $sFile
     * @param string $sLine
     */
    private function __construct($sFile, $sLine) {
        $registry = Registry::getInstance();

        /**
         * Get the configuration for the database
         * values. If no configuration is found return
         * false.
         */
        if (is_array($registry->configuration)) {
            $configuration = $registry->configuration;
        } else if (file_exists(SITE_PATH . '/app/config/database.ini')) {
            $configuration = parse_ini_file(SITE_PATH . '/app/config/database.ini');
        } else {
            return false;
        }

        $this->_setConfig($configuration);
        return $this->_connectAndSelectDB($sFile, $sLine);

    }

    /**
     * Parses the configuration file and stores it for later
     * use within the object.
     *
     * @param $configuration
     */
    private function _setConfig($configuration) {
        foreach ($configuration['database'] as $k => $v) {
            $this->config[$k] = $v;
        }
    }

    /**
     * This will connect and select a DB.
     *
     * @param $sFile
     * @param $sLine
     * @return bool
     */
    private function _connectAndSelectDB($sFile, $sLine) {

        // attempt to connect to the mysql server
        if (!$this->connect($sFile, $sLine)) { return false; }

        // attempt to select the database
        if (!$this->select_db('',$sFile, $sLine)) { return false; }

        // if we made it here return true
        return true;

    }

    /**
     * Grabs the database instance. When called for the
     * first time you should pass it the current file
     * and line number if you want more robust error
     * reporting.
     *
     * @author Jeremiah Poisson
     * @param string $sFile
     * @param string $sLine
     * @return self
     */
    public static function getInstance($sFile = '',$sLine = '') {

        if (self::$instance == NULL) {
            self::$instance = new mysql_db($sFile, $sLine);
        }
        return self::$instance;

    }

    /**
     * This function will attempt to connect to the mysql
     * server. If it cannot connect an error is generated,
     * logged and then emailed (if configured to do so).
     * Otherwise it will return the link created.
     *
     * @author Jeremiah Poisson
     *
     * @param string $sFile
     * @param string $sLine
     * @return mixed
     */
    function connect($sFile, $sLine) {

        $mysqli_conn = new mysqli($this->config['host'], $this->config['username'], $this->config['password'], $this->config['database']);
		echo $mysqli_conn->connect_error;
        if ($mysqli_conn->connect_error != null) {

            $private_error = "mysql_db::connect: could not open connection to mysqli:]";
            $private_error .= "<ul>";
            $private_error .= "<li>errno: " . $mysqli_conn->errno . "</li>";
            $private_error .= "<li>error: " . $mysqli_conn->error . "</li>";
            $private_error .= "<li>Error File: " . $sFile . "</li>";
            $private_error .= "<li>Error Line: " . $sLine . "</li>";
            $private_error .= "</ul>";
			echo $private_error;
            error_log($private_error, 0);

            // Send an error email
            if ($this->config['send_error_email']) {
                $this->send_error($private_error);
            }

            return false;
        }

        $this->mysqli_conn = $mysqli_conn;

        return true;
    }

    /**
     * Attempts to select a database. If it cannot
     * select the database it will generate an error
     * then return false.
     *
     * @author Jeremiah Poisson
     * @param string $db
     * @param string $sFile
     * @param string $sLine
     * @return bool
     */
    function select_db($db = '', $sFile = '', $sLine = '') {

        $db = $db == '' ? $this->config['database'] : $db;
        if (!$this->mysqli_conn->select_db($db)) {
            $private_error = "mysql_db::select_db: could not select database:";
            $private_error .= "<ul>";
            $private_error .= "<li>errno: " . $this->mysqli_conn->errno . "</li>";
            $private_error .= "<li>error: " . $this->mysqli_conn->error . "</li>";
            $private_error .= "<li>Error File: " . $sFile . "</li>";
            $private_error .= "<li>Error Line: " . $sLine . "</li>";
            $private_error .= "</ul>";

            error_log($private_error, 0);

            // Send an error email
            if ($this->config['send_error_email']) {
                $this->send_error($private_error);
            }

            return false;
        }

        return true;
    }

    /**
     * Takes a MySQL query and runs it against the current
     * database. If it fails an error is logged and/or emailed.
     * Otherwise it will process the results and return an array
     * back to the script.
     *
     * The function will also time the query if the debug option
     * is turned on in the config file.
     *
     * @author Jeremiah Poisson
     * @param string $query
     * @param string $sFile
     * @param string $sLine
     * @param bool $email
     * @return mixed
     */
    function query($query, $sFile, $sLine,$email = false)  {
        if (empty($query)) {
            return false;
        }

        // If we are in debug mode we want to calculate the
        // time it took to run the query and then store it in
        // an array of all queries run. If we are not in debug
        // mode we will just run the query.
        if ($this->config['debug']) {
            $start = microtime(true);
        }

        $result = $this->mysqli_conn->query($query);
        
        if ($this->config['debug']) {
            $end = microtime(true);
            $totalTime = $end - $start;
            $this->m_TotalQueryExTime += $totalTime;
            $temp = array("file" => $sFile, "line" => $sLine, "query" => $query, "executionTime" => number_format($totalTime,5));
            array_push($this->m_aQueries, $temp);
        }

        if ($result === false) {
            // if there was an error executing the query. write out the
            // details to the error log

            $private_error = "ack! query failed: ";
            $private_error .= "<ul>";
            $private_error .= "<li>errorno= "  . $this->mysqli_conn->errno . "</li>";
            $private_error .= "<li>error= " . $this->mysqli_conn->error . "</li>";
            $private_error .= "<li>Error File: " . $sFile . "</li>";
            $private_error .= "<li>Error Line: " . $sLine . "</li>";
            $private_error .= "<li>query= " . $query . "</li>";
            $private_error .= "</ul>";

            error_log($private_error, 0);

            // Send an error email
            if ($this->config['send_error_email']) {
                $this->send_error($private_error);
            }

            return false;

        }

        $t = array();
        if ($result === true) {
            $t[0]['affectedRows'] = $this->mysqli_conn->affected_rows;
        } else {
            while ($aRow = $this->query_get($result)) { $t[] = $aRow; }
        }

        // free the resource.
        // $this->freeResults($result);

        // Email the query and the results.
        // This will only happen when the debug options are turned on.
        if ($this->config['debug'] && $email) {
            $this->email_query($temp,$t);
        }

        return $t;

    }

    /**
     * This will process a MySQLi results object and return the
     * next row in teh result set. It will return NULL if there
     * are no more rows left.
     *
     * This will return an associative array when returning the
     * result row.
     *
     * @todo Make returned array type a variable so MYSQLI_ASSOC is not hard coded.
     *
     * @author Jeremiah Poisson
     * @param object $rStmt
     * @return mixed
     */
    function query_get(&$rStmt) { return $rStmt->fetch_array(MYSQLI_ASSOC); }

    /**
     * This will send an error email out to the designated
     * email within the config file.
     *
     * @todo Convert to plain text. Do not use HTML emails.
     *
     * @author Jeremiah Poisson
     * @param string $error
     * @return void
     */
    function send_error($error) {


        // multiple recipients
        $to = $this->config['error_email'];

        // subject
        $subject = "A MySQL Error has occured SITE: " . $_SERVER['HTTP_HOST'];

        // message
        $message = '
	<html>
	<head>
  		<title>A MySQL Error has occured.</title>
	</head>
	<body>
  	' . $error . '
	</body>
	</html>
	';

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Additional headers
        $headers .= 'From: ' . $this->config['error_email'] . "\r\n";

        // Mail it
        mail($to, $subject, $message, $headers);

    }

    /**
     * This function will send an email to the designated
     * email in the config file. The email will contain the
     * current;y run query and the results set returned from
     * the query.
     *
     * @todo Change to plain text emails. Do not use HTML.
     *
     * @author Jeremiah Poisson
     * @param string $q
     * @param string $r
     * @return void
     */
    function email_query($q,$r) {

        $to = $this->config['error_email'];

        // subject
        $subject = "MySQL Query Monitoring SITE: " . $_SERVER['HTTP_HOST'];

        $queryData = '';
        foreach ($q as $k => $v) {
            $queryData .= $k . " = " . $v . "<br />";
        }

        $queryResults = '';
        for ($i = 0; $i < $r['recordCount']; $i++) {
            foreach($r[$i] as $k => $v) {
                $queryResults .= $k . " = " . $v . "<br />";
            }

        }

        // message
        $message = '
	<html>
	<head>
            <title>MySQL Query Monitoring SITE: ' . $_SERVER['HTTP_HOST'] . '</title>
	</head>
	<body>
  	' . $queryData . "<hr />" . $queryResults . '
	</body>
	</html>
	';

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Additional headers
        $headers .= 'From: ' . $this->config['error_email'] . "\r\n";

        // Mail it
        mail($to, $subject, $message, $headers);

    }



    /**
     * Replaces ',", and \ with \', \", and \\  so the sql queries Will not fail
     *
     * @author Jeremiah Poisson
     * @param  string $sString
     * @return string
     */
    function sqlize($sString) {

        foreach ($this->aEscapeThese as $v) {
            $sString = str_replace($v, $this->sDBEscape.$v, $sString);
        }

        return $sString;
    }

    /**
     * Returns an array of all queries run so far. This will
     * be empty if the debug option is turned of.
     *
     * @author Jeremiah Poisson
     * @return array
     */
    function getQueries() { return $this->m_aQueries; }

    /**
     * Returns the total execution time of all queries
     * currently run.
     *
     * @author Jeremiah Poisson
     * @return int
     */
    function getTotalQueryExTime() { return $this->m_TotalQueryExTime; }

    public function getLastInsertID() { return $this->mysqli_conn->insert_id; }

    /**
     * This function frees the memory used by mysql queries...
     *
     * @param resource $rc The resource we are freeing the memory for.
     * @return void
     * @author Jeremiah Poisson
     **/
    function freeResults($rc) { $rc->free(); }

    /**
     * Query the database to return all tables.
     *
     * @author Jeremiah Poisson
     * @return mixed
     */
    function getTables() {
        $sSQL = sprintf("SHOW TABLES IN %s",$this->m_DB);
        return $this->query($sSQL,__FILE__,__LINE__);
    }

    /**
     * Will clear the aEscapeThese array. This is done if
     * PHP's magic quotes are turned on. If you do not do this
     * you will have extra '\'s in the queries which may
     * break the query.
     *
     * @author Jeremiah Poisson
     * @return void
     */
    function setUseMagicQuotes() {
        $this->aEscapeThese = array();
    }


    /**
     * This function will prepare an update statement from an array
     * of data, table and primary key. You can also pass in a list
     * of fields to exclude from the update if you need.
     *
     * @param array  $data          The data array used to build the sql.
     * @param string $table         The table the data is being updated in.
     * @param string $primary_key   The primary key of the row being updated.
     * @param array  $exclude       An array of fields to exclude from teh statement. (optional)
     * @return string
     *
     * @throws Exception            If no primary key is found in the data array.
     */
    public function prepareUpdateSQL($data, $table, $primary_key, $exclude = array()) {

        // Make sure the primary key is in the data array
        if (!isset($data[$primary_key])) {
            throw new Exception('Primary key not defined. Cannot generate insert statement.');
        }

        $fields = '';
        foreach ($data as $k => $v) {
            if ($k != $primary_key && !in_array($k,$exclude)) {
                $fields .= $fields == '' ? '' : ', ';
                $fields .= sprintf("%s = '%s'",$k,$this->sqlize($v));
            }
        }

        $sql = sprintf("UPDATE %s SET %s WHERE %s = %d",$table,$fields,$primary_key,$data[$primary_key]);
        return $sql;

    }

    public function prepareInsertSQL($data, $table, $exclude = array()) {

        $fields = '';
        $values = '';
        foreach ($data as $k => $v) {
            if (!in_array($k,$exclude)) {
                $fields .= $fields == '' ? '' . $k : ', ' . $k;
                $values .= $values == '' ? "'" . $this->sqlize($v) . "'" : ", '" . $this->sqlize($v) . "'";
            }
        }

        $sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $table, $fields, $values);
        return $sql;
    }


}
