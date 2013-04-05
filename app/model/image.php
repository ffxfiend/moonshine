<?php
/**
 * Image
 *
 * This is a special object that holds image data
 * associated with a model. It is not a typical model
 * in this framework recognizes as it is not related
 * to any database table and has no inherit CRUD
 * capabilities.
 *
 * All this object does is store necessary image data
 * for a model. Each model can have multiple image
 * objects associated with it that will handle all
 * configurations for each image.
 *
 * You can configure the following:
 *
 * width
 * height
 * thumb_width
 * thumb_height
 * crop_thumb
 * save_location
 *
 * @author Jeremiah Poisson <jpoisson@igzactly.com>
 *
 */
class image {

    private $width;
    private $height;
    private $thumb_width;
    private $thumb_height;
    private $crop_thumb;
    private $save_location;

    // Getters/Setters
    public function getWidth() { return $this->width; }
    public function getHeight() { return $this->height; }
    public function getThumbWidth() { return $this->thumb_width; }
    public function getThumbHeight() { return $this->thumb_height; }
    public function cropThumb() { return $this->crop_thumb ? true : false; }
    public function getSaveLocation() { return $this->save_location; }

    /**
     * The only required fields for the constructor is the image width and
     * and height. It will default to no thumb dimensions (no thumbs will be
     * generated), cropping the thumbnail image and a NULL save location.
     *
     * If the save location is set to NULL it will save the image file in
     * the following location (which is based on the model this image object
     * is a part of):
     *
     * PUBLIC_SITE_PATH . '/images/d/' . strtoupper(CLASS_NAME) . '/'
     *
     * @param $w
     * @param $h
     * @param int $tw
     * @param int $th
     * @param bool $crop
     * @param null $save_location
     */
    public function __construct($w, $h, $tw = 0, $th = 0, $crop = true, $save_location = NULL) {

        $this->width = $w;
        $this->height = $h;
        $this->thumb_width = $tw;
        $this->thumb_height = $th;
        $this->crop_thumb = $crop;
        $this->save_location = $save_location;

    }

    /**
     * This will return a bool indicating if we need to generate a
     * thumbnail for the image when it is uploaded.
     *
     * @author Jeremiah Poisson <jpoisson@igzactly.com>
     *
     * @return bool
     */
    public function shouldCreateThumbnail() {
        return $this->thumb_width > 0 || $this->thumb_height > 0;
    }

    /**
     * This function will generate a string containing
     * the save location for this particular image object.
     * If the save_location member is NULL we will
     * dynamically generate it based on the model passed
     * to the function.
     *
     * @param string $model
     * @return null|string
     */
    public function generateSaveLocation($model) {
        return $this->save_location == NULL ? PUBLIC_SITE_PATH . '/images/d/' . $model . '/' : $this->save_location;
    }

}