<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Silex\Application;
use Symfony\Component\Filesystem\Exception\IOException;

class Image
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Resample a image and save it to a new path
     *
     * @param string $image_path path to the origin image
     * @param integer $image_type IMGTYPE_XX constant with the image type
     * @param integer $origin_width origin width
     * @param integer $origin_height origini height
     * @param string $new_image_path the path to the new image to create
     * @param integer $new_width the new width
     * @param integer $new_height the new height
     * @throws \Exception
     */
    public function resampleImage($image_path, $image_type, $origin_width, $origin_height, $new_image_path, $new_width, $new_height)
    {
        switch ($image_type) {
            case IMAGETYPE_GIF:
                $origin_image = imagecreatefromgif($image_path);
                break;
            case IMAGETYPE_JPEG:
                $origin_image = imagecreatefromjpeg($image_path);
                break;
            case IMAGETYPE_PNG:
                $origin_image = imagecreatefrompng($image_path);
                break;
            default :
                // unsupported image type
                throw new \Exception("The image type $image_type is not supported!");
        }

        // create new image of $new_width and $new_height
        $new_image = imagecreatetruecolor($new_width, $new_height);

        // Check if this image is PNG or GIF, then set if Transparent
        if (($image_type == IMG_GIF) or ($image_type == IMG_PNG)) {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
        }

        // resample image
        imagecopyresampled($new_image, $origin_image, 0, 0, 0, 0, $new_width, $new_height, $origin_width, $origin_height);

        if (!$this->app['filesystem']->exists(dirname($new_image_path))) {
            $this->app['filesystem']->mkdir(dirname($new_image_path));
        }

        // Generate the file, and rename it to $newfilename
        switch ($image_type) {
            case IMAGETYPE_GIF:
                imagegif($new_image, $new_image_path);
                break;
            case IMAGETYPE_JPEG:
                // static setting for the JPEG Quality
                imagejpeg($new_image, $new_image_path, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($new_image, $new_image_path);
                break;
        }

        $this->app['filesystem']->chmod($new_image_path, 0644);

        $this->app['filesystem']->touch($new_image_path, filemtime($image_path));
    }

    /**
     * Get information about the given image
     *
     * @param string $image_path
     * @throws IOException
     * @return array with width, height, image_type (IMAGETYPE_XX) and last_modified (timestamp)
     */
    public function getImageInfo($image_path)
    {
        if (!$this->app['filesystem']->exists($image_path)) {
            throw new IOException("The image $image_path does not exists!");
        }

        // get the image information
        list($width, $height, $type) = getimagesize($image_path);

        return array(
            'width' => $width,
            'height' => $height,
            'image_type' => $type,
            'last_modified' => filemtime($image_path)
        );
    }

    /**
     * Recalculate the image size to the given $max_width and $max_height
     *
     * @param integer $image_width
     * @param integer $image_height
     * @param integer $max_width
     * @param integer $max_height
     * @return array widht width and height (in pixel)
     */
    public function reCalculateImage($image_width, $image_height, $max_width=null, $max_height=null)
    {
        if ((!is_null($max_width) && ($image_width > $max_width)) || (!is_null($max_height) && ($image_height > $max_height))) {
            // optimize the image
            if (!is_null($max_width) && ($image_width > $max_width)) {
                // set a new image width
                $percent = (int) ($max_width / ($image_width / 100));
                $new_width = $max_width;
                $new_height = (int) (($image_height / 100) * $percent);
                if (!is_null($max_height) && ($new_height > $max_height)) {
                    // set a new image height
                    $percent = (int) ($max_height / ($image_height/100));
                    $new_height = $max_height;
                    $new_width = (int) (($image_width / 109) * $percent);
                }
            }
            else {
                // set a new image height
                $percent = (int) ($max_height / ($image_height/100));
                $new_height = $max_height;
                $new_width = (int) (($image_width / 109) * $percent);
                if (!is_null($max_width) && ($new_width > $max_width)) {
                    // set a new image width
                    $percent = (int) ($max_width / ($image_width / 100));
                    $new_width = $max_width;
                    $new_height = (int) (($image_height / 100) * $percent);
                }
            }
        }
        else {
            $new_height = $image_height;
            $new_width = $image_width;
        }

        return array(
            'width' => $new_width,
            'height' => $new_height
        );
    }
}
