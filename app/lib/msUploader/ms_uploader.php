<?php
/**
 * This class deals with file uploads. It can be used to upload any number of files
 * and file types.
 *
 * @todo convert to a singleton class.
 *
 * @author Jermeiah Poisson <jpoisson@igzactly.com>
 * @copyright copyright (c) 2010 - 2012 Igzactly Development
 *
 */

class ms_uploader {

    /**
     * Class Variables
     */
    private $filetypes = array();
    private $lastError;

    /**
     * Initializes the class
     */
    function __construct() {
        // Initialize class variables
    }

    /**
     * Sets the file types array.
     *
     * @param array $ft
     * @return void
     */
    function setFileTypes($ft) { $this->filetypes = $ft; }

    /**
     * takes a $_FILE array and uploads it to the
     * designated path. If the path does not exist
     * it will try to create teh directory.
     *
     * The function returns false on error and true
     * otherwise. If an error does occure it is placed
     * in the $lastError class member variable.
     *
     * @param array $file
     * @param string $name
     * @param string $path
     * @param string $FILENAME
     * @param string $sUName
     * @return bool
     */
    function uploadFile($file,$name,$path,&$FILENAME,$sUName = "") {

        if (isset($file[$name]['name']) && basename($file[$name]['name']) != "") {

            // Check what kind of file this is...
            $passed = true;
            $temp = basename($file[$name]['name']);
            $temp = explode(".", $temp);

            // Check to see if we are allowing all file types or just specific ones.
            if ($this->filetypes[0] != '*' && !in_array($temp[1],$this->filetypes)) {
                $passed = false;
            }

            if ($passed) {

                if ($sUName != "") {
                    $fileName =  $sUName . "_" . date("mdy") . "." . $temp[1];
                } else  {
                    $fileName =  str_replace(" ","_",basename($file[$name]['name']));
                }

                $path = $path . "/";

                $bFileExists = true;
                $identifier = 1;
                $tempName = $fileName;
                while ($bFileExists)  {
                    if (file_exists($path . $tempName)) {
                        $tempName = $identifier . "_" . $fileName;
                        $identifier++;
                    } else  {
                        $bFileExists = false;
                    }
                }
                $fileName = $tempName;

                $uploadfile = $path . $fileName;

                if (!file_exists($path)) {
                    if (!mkdir($path)) {
                        $this->setError("FATAL","Could not upload file. (FILE: " . $file[$name]['tmp_name'] . " DESTINATION: " . $uploadfile . "). Could not create the desination directory.",__FILE__,__LINE__);
                        return false;
                    }
                }

                if (!@move_uploaded_file($file[$name]['tmp_name'], $uploadfile)) {
                    $this->setError("FATAL","Could not upload file. (FILE: " . $file[$name]['tmp_name'] . " DESTINATION: " . $uploadfile . "). Please make sure the destination directory has the correct permissions.",__FILE__,__LINE__);
                    return false;
                }

                $FILENAME = $fileName;
                return true;
            } else {
                $this->setError("FATAL","The file being uploaded is not an accepted file type. Accepted file types include: " . implode(", ",$this->filetypes) ,__FILE__,__LINE__);
                return false;
            }
        } else {
            $this->setError("WARNING","No file exists to upload. Aborting function.",__FILE__,__LINE__);
            return false;
        }

    }

    /**
     * This will take an uploaded image and resize it
     * accordingly. If $crop is set to true the image
     * will be cropped to the size passed.
     *
     * @param string $imagePath
     * @param string $imageName
     * @param string $newImageName
     * @param int $maxWidth
     * @param int $maxHeight
     * @param bool $keepAspect
     * @param bool $crop
     * @return bool
     */
    function resize_uploaded_image($imagePath,$imageName,$newImageName,$maxWidth,$maxHeight,$keepAspect = true,$crop = false) {

        // What type of image is it?
        $temp = explode(".",$imageName);
        $imageType = $temp[1];
        $supportedTypes = array(
            'JPG' => array('jpg','JPG','jpeg','JPEG'),
            'GIF' => array('gif','GIF'),
            'PNG' => array('png','PNG')
        );

        $uploadPath = $imagePath . "/";

        // Set the image functions.
        if (in_array($imageType,$supportedTypes['JPG'])) {
            // jpg functions
            $imageCreateFunc = "imagecreatefromjpeg";
            $imageFunc = "imagejpeg";
            $imageQuality = 100;
        } else if (in_array($imageType,$supportedTypes['GIF'])) {
            // gif functions
            $imageCreateFunc = "imagecreatefromgif";
            $imageFunc = "imagegif";
            $imageQuality = 100;
        } else if (in_array($imageType,$supportedTypes['PNG'])) {
            // png functions
            $imageCreateFunc = "imagecreatefrompng";
            $imageFunc = "imagepng";
            $imageQuality = 0;
        } else {
            // Incomapible image type
            $this->setError("FATAL","The file being resized is not an accepted file type. Accepted file types include: jpg, JPG, jpeg, JPEG, gif, GIF, png, PNG",__FILE__,__LINE__);
            return false;
        }

        ## Create a new image from the file so we can resize ##
        $src = $imageCreateFunc($uploadPath . $imageName);

        ## Get the width and height so we can resize the image correctly ##
        list ($cWidth,$cHeight) = getimagesize($uploadPath . $imageName);

        if ($maxHeight == 0)
            $maxHeight = $cHeight;
        if ($maxWidth == 0)
            $maxWidth = $cWidth;

        ## lets calclate the new width/height ##
        $aNewSize = $this->calculate_size($cWidth,$cHeight,$maxWidth,$maxHeight,$keepAspect);

        ## create a new temp image to put the resized image in ##
        $tmp = imagecreatetruecolor($aNewSize[0],$aNewSize[1]);

        // Deal with PNG and GIF transparency so it will resize correctly.
        if (in_array($imageType,$supportedTypes['PNG']) || in_array($imageType,$supportedTypes['GIF'])) {
            $trnprt_indx = imagecolortransparent($src);

            // If we have a specific transparent color
            if ($trnprt_indx >= 0) {

                // Get the original image's transparent color's RGB values
                $trnprt_color    = @imagecolorsforindex($src, $trnprt_indx);

                // Allocate the same color in the new image resource
                $trnprt_indx    = @imagecolorallocate($tmp, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

                // Completely fill the background of the new image with allocated color.
                imagefill($tmp, 0, 0, $trnprt_indx);

                // Set the background color for new image to transparent
                imagecolortransparent($tmp, $trnprt_indx);


            } else if (in_array($imageType,$supportedTypes['PNG'])) {

                // Turn off transparency blending (temporarily)
                imagealphablending($tmp, false);

                // Create a new transparent color for image
                $color = imagecolorallocatealpha($tmp, 0, 0, 0, 127);

                // Completely fill the background of the new image with allocated color.
                imagefill($tmp, 0, 0, $color);

                // Restore transparency blending
                imagesavealpha($tmp, true);
            }
        }

        $src_x = $src_y = 0;
        $c_width = $cWidth;
        $c_height = $cHeight;
        if ($crop) {
            $src_x = ($cWidth / 2) - ($aNewSize[0] / 2);
            $src_y = ($cHeight / 2) - ($aNewSize[1] / 2);
            $c_width = $aNewSize[0];
            $c_height = $aNewSize[1];
        }
        // mail('jpoisson@igzactly.com','CMSA Debug Uploader - Image Croping',"src_x: " . $src_x . "\n\r" . "src_y: " . $src_y . "\n\r" . "c_width: " . $c_width . "\n\r" . "c_height: " . $c_height);

        imagecopyresampled($tmp,$src,0,0,$src_x,$src_y,$aNewSize[0],$aNewSize[1],$c_width,$c_height);


        ## now put the newly resized image on the server ##
        $imageFunc($tmp,$uploadPath . $newImageName,$imageQuality);

        imagedestroy($src);
        imagedestroy($tmp);

        return true;
    }

    /**
     * This will calculate a new size for an image based on the
     * current w/h vs the desired w/h.
     *
     * @param int $nCurrWidth
     * @param int $nCurrHeight
     * @param int $nMaxWidth
     * @param int $nMaxHeight
     * @param bool $keepAspect
     * @return array
     */
    function calculate_size($nCurrWidth, $nCurrHeight, $nMaxWidth, $nMaxHeight, $keepAspect = true) {

        // First lets work on resizing the width
        if ($nCurrWidth > $nMaxWidth) {
            $nPercent = ($nMaxWidth * 100) / $nCurrWidth;
            $nHeight = $keepAspect ? ($nPercent * $nCurrHeight) / 100 : $nCurrHeight;
            $nWidth   = ($nPercent * $nCurrWidth) / 100;
        } else {
            $nHeight = $nCurrHeight;
            $nWidth = $nCurrWidth;
        }

        // Now lets make sure the height is still in range
        if ($nHeight > $nMaxHeight) {
            $nPercent = ($nMaxHeight * 100) / $nHeight;
            $nHeight  = ($nPercent * $nHeight) / 100;
            $nWidth   = $keepAspect ? ($nPercent * $nWidth) / 100 : $nWidth;
        }

        $nWidth = intval($nWidth);
        $nHeight = intval($nHeight);
        return array($nWidth, $nHeight);
    }

    /**
     * Sets the class $lastError variable.
     *
     * @param string $errorType
     * @param string $error
     * @param string $file
     * @param string $line
     * @return void
     */
    function setError($errorType,$error,$file,$line) {
        $this->lastError = "[" . $errorType . "] An error occurred at: FILE: " . $file . " (LINE: " . $line . ")\n\nError: " . $error;
    }

    /**
     * Retrieves teh last error generated by the upload.
     *
     * @param bool $useBR
     * @return string
     */
    function getLastError($useBR = false) { return $useBR ? nl2br($this->lastError) : $this->lastError; }

    /**
     * Emails the last error to the specified email address.
     *
     * @param type $email
     * @param type $subject
     * @return void
     */
    function emailLastError($email,$subject) {
        mail($email,$subject,$this->lastError);
    }


}
