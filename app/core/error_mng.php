<?php
/**
 * File: error_mgn.php
 *
 * Custom error function.
 *
 * @author Jeremiah Poisson
 * @version 1.0
 */

/**
 *
 * @param string $e_number
 * @param string $e_message
 * @param string $e_file
 * @param string $e_line
 * @param string $e_vars
 * @return void
 */
function myErrorHandler($e_number,$e_message,$e_file,$e_line,$e_vars) {
    $registry = Registry::getInstance();

    // Build the error message.
    $message = "An error occurred in script '" . $e_file . "' on line " . $e_line . "\n<br />" . $e_message . "\n<br />";
    $message .= "Date/Time: " . date('n-j-Y H:i:s') . "\n<br />";

    if ($registry->configuration['error']['append_e_vars']) {
        // Append $e_vars to the message.
        $message .= "<pre>" . print_r($e_vars,true) . "</pre>\n<br />";
    }

    if ($registry->configuration['bc']['debug']) { // Show the error
        writeToLog($message);
        echo "<p class=\"error\">" . $message . "</p>";
    } else {
        // echo "<p class=\"error\">" . $message . "</p>";
        // Log the error
        // $headers = 'MIME-Version: 1.0' . "\r\n";
        // $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        // error_log($message,1,$registry->errorEmail,$headers); // Send email.
        // Only print an error message if the error isn't a notice or strict.
        if (($e_number != E_NOTICE) && ($e_number < 2048)) {
            echo "<p class\"error\">A system error has occurred. It has been reported and will be fixed ASAP. We apologize for the inconvenience.</p>";
        }
    } // End of debug IF.

} // End of myErrorHandler() definition.

// Use my error handler.
set_error_handler('myErrorHandler');
