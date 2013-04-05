<?php
/**
 * This file contains common functions for use throughout the moonshine framework
 *
 * @author Jeremiah Poisson
 * @version 1.0
 */


/**
 * This function will return the strings needed to include the styles sheets.
 * If no parameters are passed it will display the current themes default set
 * of stylesheets. If a theme is passed it will use the default set of stylesheets
 * within that theme. If both a theme and a stylesheet is passed it will return
 * the link for that stylesheet.
 *
 * @author Jeremiah Poisson
 * @param string $theme
 * @param string $stylesheet
 * @return string
 */
function ms_styles($theme = '', $stylesheet = '') {

    $theme = $theme == '' ? THEME : $theme;

    if ($stylesheet == '') {
        // return the default set of stylesheets
        $str = '<link href="' . HTTP_TEMPLATE_PATH . '/' . $theme . '/css/default.css" rel="stylesheet" type="text/css" media="screen" />' . "\n";
    } else {
        // return the specified stylesheet
        $str = '<link href="' . HTTP_TEMPLATE_PATH . "/" . $theme . '/css/' . $stylesheet . '" rel="stylesheet" type="text/css" media="screen" />' . "\n";
    }

    return $str;

}

/**
 * This function returns the current theme path.
 * If a theme is passed it will return the path to
 * that theme.
 *
 * @param string $theme
 * @return string
 */
function ms_themePath($theme = '') {
    $theme = $theme == '' ? THEME : $theme;
    return HTTP_TEMPLATE_PATH . "/" . $theme;
}

/**
 * Function to search a multidimenion array to see if a Key exists.
 *
 * @author Jeremiah Poisson
 * @param type $needle_key
 * @param type $array
 * @return mixed
 */
function ms_arraySearchKey( $needle_key, $array ) {

    if (!is_array($array)) { return false; }

    foreach($array as $key=>$value){
        if($key == $needle_key) return $value;
        if(is_array($value)){
            $result = ms_arraySearchKey($needle_key,$value);
            if ($result) { return $result; }
        }
    }
    return false;
}

/**
 * This function will include a template file in the
 * current themes layout directory.
 *
 * @author Jeremiah Poisson
 * @param string $template
 * @return string
 */
function igz_doTemplate($template = '') {

    if ($template == '') { return; } // Exit if no template name is passed.

    $theme_path = ms_themePath(); // Get the current theme directory

    // Include the template only if the file exists.
    if (file_exists(PUBLIC_TEMPLATE_PATH . '/' . THEME . '/layouts/' . $template . ".php")) {
        return PUBLIC_TEMPLATE_PATH . '/' . THEME . '/layouts/' . $template . ".php";
    }

    return;

}

function writeToLog($msg) {

    $logMsg = date('Y-m-d h:i:s - ') . $msg . "\n";

    try {
        $logName = SITE_PATH . '/app/logs/errorLog_' . date('Ymd') . '.txt';
        $logFile = fopen($logName, 'a+');
        $timeString = date('Y-m-d h:i:s');
        fwrite($logFile, $timeString . ': ' . $logMsg);
        fclose($logFile);
    } catch (Exception $e) {
        syslog(LOG_ALERT, "Unable to write to error log");
    }

}

/**
 * This function will dump data to the screen. It will
 * by default terminate the script but can be bypassed
 * by passing false as the second argument.
 *
 * @param mixed $obj
 * @param bool $exit
 */
function dump($obj, $exit = true)
{
    echo "<pre>";
    if (is_array($obj) || is_object($obj)) { print_r($obj); }
    else print($obj);
    echo "</pre>";
    if ($exit === true) exit();
}


function sortMultiDimensionArray($arr,$field) {

    $sort = array();
    foreach ($arr as $key => $row) {
        $sort[$key]  = $row[$field];
    }
    array_multisort($sort, SORT_DESC, $arr);
    return $arr;

}

/**
 * Functions moved from common_functions.php
 */

/**
 * This will take an input string and cut it to a specified
 * length. If the string is shorter then the desired length
 * it will just return the string. The function will also
 * append an ellipse to the end of the string.
 *
 * @author Jeremiah Poisson
 * @param string $str
 * @param int $len
 * @param bool $cut
 * @return string
 */
function short_str( $str, $len, $cut = false ) {
    if ( strlen( $str ) <= $len ) return $str;
    return ( $cut ? substr( $str, 0, $len ) : substr( $str, 0, strrpos( substr( $str, 0, $len ), ' ' ) ) ) . '...';
}

/**
 * Builds a link for the admin portion of the framework.
 *
 * @author Jeremiah Poisson
 * @param string $link
 * @param string $additional_get
 * @return string
 */
function igz_buildCNSLink($link,$additional_get = '') {

    $seo = USE_SEO_URL ? "" : "?rt=";
    $use_subdomain = ADMIN_AT_SUBDOMAIN ? "" : (USE_SEO_URL ? "?cns=1" : "&cns=1");

    if ($additional_get != '' ) {
        $use_subdomain = $use_subdomain == '' ? "?" . $additional_get : $use_subdomain . "&" . $additional_get;
    }

    return HTTP_ROOT . $seo . $link . $use_subdomain;

}

/**
 * Returns a URL friendly version of a string.
 *
 * @author Jeremiah Poisson
 * @param string $string
 * @return string
 */
function url_friendly($string) {
    if ($string == '') { return $string; }

    $string = strtolower(preg_replace('/\s/','-',$string));
    $string = preg_replace('/[^a-z0-9\-]/','',$string);
    $string = preg_replace('/[\-]+/','-',$string);

    return $string;
}

/**
 * This function prints out an array with print_r() surrounded by <pre> tags
 *
 * @author Jeremiah Poisson
 * @param array $aArr
 * @return void
 **/
function print_a($aArr) { echo "<pre>" . print_r($aArr,true) . "</pre>"; }

/**
 * Generates a random ID of letters and numbers as long as is desired.
 *
 * @author Jeremiah Poisson
 * @param int $np
 * @param int $npp
 * @return string
 */
function generate_ID($np,$npp) {
    // The id to create
    $id = "";

    // Create an array of characters
    $aplha = "a b c d e f g h i j k l m n o p q r s t u v w x y z";
    $aplha = explode(" ", $aplha);

    for ($j = 0; $j < $np; $j++) {
        $r = rand(1,2);
        for ($i = 0; $i < $npp; $i++) {
            if ($r == 1) {
                $id .= rand(1,9); // Generate random numbers
            } else {
                // Generate random letters
                $a = rand(0,25);
                $id .= $aplha[$a];
            }
        }
    }
    return $id;
}

/**
 * Validates an email address
 *
 * @author Jeremiah Poisson
 * @param  string $email
 * @return bool
 */
function isValidEmail($email) { return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$", $email); }

/**
 * Validates a phone number
 *
 * @author Jeremiah Poisson
 * @param string $number
 * @return bool
 */
function isValidPhone($number) {

    // List of possible formats: You can add new formats or modify the existing ones
    $formats = array('###-###-####', '####-###-###',
        '(###) ###-###', '####-####-####',
        '##-###-####-####', '####-####', '###-###-###',
        '#####-###-###', '##########');

    $format = trim(ereg_replace("[0-9]", "#", $number));
    return (in_array($format, $formats)) ? true : false;
}

/**
 * Checks the form for any html input.
 *
 * The function takes an array as an argument
 * and parses the array looking for any html
 * content. If it finds any it retunrs false.
 *
 * @author Jeremiah Poisson
 * @param  array $content
 * @return bool
 */
function checkFormForHTML($content) {
    $regex = "/<.*>/";
    foreach ($content as $k => $v) { if(preg_match($regex, $v))return false; }

    $regex = "/http:/";
    foreach ($content as $k => $v) { if(preg_match($regex, $v))return false; }

    return true;
}

/**
 * Takes a string and converts all the new line characters
 * to html <p> tags.
 *
 * @author Jeremiah Poisson
 * @param string $text
 * @return string
 */
function nl2p($text) {

    // Return if there are no line breaks.
    if (!strstr($text, "\n")) { return $text; }

    // put all text into <p> tags
    $text = '<p>' . $text . '</p>';

    // replace all newline characters with paragraph
    // ending and starting tags
    $text = str_replace("\n", "</p>\n<p>", $text);

    // remove empty paragraph tags & any cariage return characters
    $remove = array("\r", "<p></p>");
    $text = str_replace($remove,"", $text);

    return $text;

} // end nl2p

/**
 * Takes a string and converts all html <p> tags
 * to new line characters.
 *
 * @param string $text
 * @return string
 */
function p2nl($text) {

    // replace all <p> and </p> tags with a newline characters
    // ending and starting tags
    $text = str_replace("</p>\n\n<p>", "\n\r", $text);

    // remove <p> tag from begining and </p> tag from end of string
    $remove = array("<p>", "</p>");
    $text = str_replace($remove, "", $text);

    return $text;

} // end p2nl

/**
 * Loops through the cms_document table and files all
 * child documents related to the parent document.
 *
 * @todo Rewrite function to use the new document structure
 *
 * @staticvar int $counter
 * @staticvar int $maxIndent
 * @staticvar array $aItems
 * @param int $dID
 * @param object $oDB
 * @return array
 */
function get_documents_recursivly($dID,&$oDB) {
    static $counter = 0;
    static $maxIndent = 0;
    static $aItems = array();
    $counter++;

    $aAll = array();
    $sSQL = "SELECT * FROM cms_document WHERE par_id = " . $dID;
    $nStmt = $oDB->query($sSQL,__FILE__,__LINE__);

    for ($i = 0; $i < $nStmt['recordCount']; $i++) {
        $aAll[] = $nStmt[$i];
    }

    for ($i = 0; $i < count($aAll); $i++) {
        $maxIndent = $counter > $maxIndent ? $counter : $maxIndent;
        $aItems[] = array( "indent" => $counter, "name" => $aAll[$i]["name"], "path" => $aAll[$i]["path"], "page_id" => $aAll[$i]['page_id'] );
        get_documents_recursivly($aAll[$i]['page_id'],$oDB);
        $aItems[0]["max_indent"] = $maxIndent;
    }
    $counter--;
    return $aItems;
}

/**
 * resize_image()
 *
 * This functions resizes an image to any size
 * specified. It will return an HTML string with
 * the new width/height of the image. It takes in
 * a string that holds the path to the image to
 * resize.
 *
 * @author Jeremiah Poisson
 * @param string $image
 * @param int $target
 * @return string
 */
function resize_image($image,$target) {
    // Get the current image size
    $image = getimagesize($image);

    // Get the percentage to resize the image
    $percent = $image[0] > $image[1] ? $target / $image[0] : $target / $image[1];

    // Get the new width and height
    $w = round($image[0] * $percent);
    $h = round($image[1] * $percent);

    // Return as an html widht/height attribute
    return "width=\"" . $w . "\" height=\"" . $h . "\"";
}