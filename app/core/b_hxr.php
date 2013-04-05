<?
$sServerMessage = '';
$sPostMessage   = '';

/**
 * Function to create a warning message. Only used if something went wrong.
 */
function igz_create_haxor_warning_message() {

    global $sServerMessage;

    if ($sServerMessage == '') {
        foreach($_SERVER as $k => $v)  {
            $sServerMessage .= "\$_SERVER[\"" . $k . "\"] = ";
            if (is_array($v)) {
                $sServerMessage .= "Array {" . "\n";
                foreach ($v as $x => $y) {
                    $sServerMessage .= "     [" . $x . "] = " . $y . "\n";
                }
                $sServerMessage .= "}" . "\n\n";
            } else {
                $sServerMessage .= $v . "\n\n";
            }
        }
    }

    return $sServerMessage;

}


// if a URL is passed to the page, email and exit
if (strstr($_SERVER["REQUEST_URI"],"http://")) {
    mail($configuration['email']['hxr'],"illegal value passed to " . $configuration['pj']['url'] . " (first check)", igz_create_haxor_warning_message());
    exit();
}

// if certain values are passed to the page, email exit
$aIllegalTerms = array(
    "'",
    ".ro/",
    ".ru/",
    "intel.com",
    "mosConfig"
);
foreach($aIllegalTerms as $v) {
    if (strstr($_SERVER["REQUEST_URI"],$v)) {
        mail($configuration['email']['hxr'],"illegal value passed to " . $configuration['pj']['url'] . " (second check)", igz_create_haxor_warning_message());
        exit();
    }
    if ($_POST) {
        foreach($_POST as $s) {
            if ($v!="'" && !is_array($s)) {
                if (strstr($s,$v)) {
                    foreach($_POST as $k => $x) { $sPostMessage .= "\$_POST[\"" . $k . "\"] = " . $x . "\n\n"; }
                    mail($configuration['email']['hxr'],"illegal value passed to " . $configuration['pj']['url'] . " (third check)", igz_create_haxor_warning_message() . $sPostMessage);
                    exit();
                }
            }
        }
    }
}
