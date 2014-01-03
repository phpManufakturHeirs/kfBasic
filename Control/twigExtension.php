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

use Twig_Extension;
use Twig_SimpleFunction;
use Silex\Application;

/**
 * The Twig extension class for the kitFramework
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class twigExtension extends Twig_Extension
{

    protected $app = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct (Application $app)
    {
        $this->app = $app;
    }

    /**
     *
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName ()
    {
        return 'kitFramework';
    } // getName()

    /**
     *
     * @see Twig_Extension::getGlobals()
     */
    public function getGlobals ()
    {
        return array(
            'CMS_ADMIN_URL' => CMS_ADMIN_URL,
            'CMS_MEDIA_URL' => CMS_MEDIA_URL,
            'CMS_TYPE' => CMS_TYPE,
            'CMS_URL' => CMS_URL,
            'CMS_VERSION' => CMS_VERSION,
            'FRAMEWORK_MEDIA_URL' => FRAMEWORK_MEDIA_URL,
            'FRAMEWORK_MEDIA_PROTECTED_URL' => FRAMEWORK_MEDIA_PROTECTED_URL,
            'FRAMEWORK_PATH' => FRAMEWORK_PATH,
            'FRAMEWORK_URL' => FRAMEWORK_URL,
            'FRAMEWORK_TEMPLATES' => explode(',', FRAMEWORK_TEMPLATES),
            'LIBRARY_URL' => MANUFAKTUR_URL.'/Basic/Library',
            'MANUFAKTUR_PATH' => MANUFAKTUR_PATH,
            'MANUFAKTUR_URL' => MANUFAKTUR_URL,
            'THIRDPARTY_PATH' => THIRDPARTY_PATH,
            'THIRDPARTY_URL' => THIRDPARTY_URL
        );
    } // getGlobals()


    /**
     *
     * @see Twig_Extension::getFunctions()
     */
    public function getFunctions ()
    {
        return array(
            'isAuthenticated' => new \Twig_Function_Method($this, 'isAuthenticated'),
            'getUserDisplayName' => new \Twig_Function_Method($this, 'getUserDisplayName'),
            'template_file' => new \Twig_Function_Method($this, 'template_file'),
            'getTemplateFile' => new \Twig_Function_Method($this, 'getTemplateFile'),
            'kitCommandParser' => new \Twig_Function_Method($this, 'kitCommandParser'),
            'kitCommand' => new \Twig_Function_Method($this, 'kitCommand'),
            'reCaptcha' => new \Twig_Function_Method($this, 'reCaptcha'),
            'reCaptchaIsActive' => new \Twig_Function_Method($this, 'reCaptchaIsActive'),
            'mailHide' => new \Twig_Function_Method($this, 'mailHide'),
            'mailHideIsActive' => new \Twig_Function_Method($this, 'mailHideIsActive'),
            'fileExists' => new \Twig_Function_Method($this, 'fileExists'),
            'image' => new \Twig_Function_Method($this, 'image')
        );
    }

    /**
     * (non-PHPdoc)
     * @see Twig_Extension::getFilters()
     */
    public function getFilters()
    {
        return array(
            'ellipsis' => new \Twig_Filter_Method($this, 'filterEllipsis')
        );
    }

    /**
     * Check if the user is authenticated
     *
     * @return boolean
     */
    function isAuthenticated ()
    {
        return $this->app['account']->isAuthenticated();
    }

    /**
     * Get the display name of the authenticated user
     *
     * @throws Twig_Error
     * @return string Ambigous string, mixed>
     */
    function getUserDisplayName()
    {
        return $this->app['account']->getDisplayName();
    }

    /**
     * Get the template depending on namespace and the framework settings for the template itself
     *
     * @deprecated template_file() is deprecated since kfBasic 0.33, use getTemplateFile() instead
     */
    function template_file($template_namespace, $template_file, $preferred_template='')
    {
        trigger_error('template_file() is deprecated since kfBasic 0.33, use getTemplateFile() instead', E_USER_DEPRECATED);
        return $this->app['utils']->getTemplateFile($template_namespace, $template_file, $preferred_template);
    }

    /**
     * Get the template depending on namespace and the framework settings for the template itself
     *
     * @param string $template_namespace
     * @param string $template_file
     * @param string $preferred_template
     * @return string
     */
    function getTemplateFile($template_namespace, $template_file, $preferred_template='')
    {
        return $this->app['utils']->getTemplateFile($template_namespace, $template_file, $preferred_template);
    }

    /**
     * Parse the content for kitCommands and execute them
     *
     * @param Application $app
     * @param string $content
     * @return string parsed content
     */
    function kitCommandParser($content)
    {
        return $this->app['utils']->parseKITcommand($content);
    }

    /**
     * Execute a kitCommand with the given parameter
     *
     * @param Application $app
     * @param string $command
     * @param array $parameter
     */
    function kitCommand($command, array $parameter=array())
    {
        return $this->app['utils']->execKITcommand($command, $parameter);
    }

    /**
     * Return a ReCaptcha dialog if the ReCaptcha service is active
     *
     * @param Application $app
     * @param string theme to use (override global settings)
     * @param string widget to use for 'custom' theme
     *
     * @link https://developers.google.com/recaptcha/docs/customization
     */
    function reCaptcha($theme=null, $widget=null)
    {
        return $this->app['recaptcha']->getHTML($theme, $widget);
    }

    /**
     * Check if the ReCaptcha Service is active or not
     *
     * @param Application $app
     * @return boolean
     */
    function reCaptchaIsActive()
    {
        return $this->app['recaptcha']->isActive();
    }

    /**
     * Return a MailHide link if the service is active. Otherwise, if $mailto is true,
     * return a complete mailto link. You can set an optional $class for this mailto link
     *
     * @param string $email
     * @param string $title
     * @param boolean $mailto
     * @param string $class
     */
    public function MailHide($email, $title='', $class='', $mailto=true)
    {
        return $this->app['recaptcha']->MailHideGetHTML($email, $title, $class, $mailto);
    }

    /**
     * Check if the MailHide Service is active or not
     *
     */
    public function MailHideIsActive()
    {
        return $this->app['recaptcha']->MailHideIsActive();
    }

    /**
     * Check if the given file exists
     *
     * @param string $file absolute path
     * @return boolean
     */
    public function fileExists($file)
    {
        return $this->app['filesystem']->exists($file);
    }

    /**
     * Resample a image and save it to a new path
     *
     * @param string $image_path path to the origin image
     * @param integer $image_type IMG_XX constant with the image type
     * @param integer $origin_width origin width
     * @param integer $origin_height origini height
     * @param string $new_image_path the path to the new image to create
     * @param integer $new_width the new width
     * @param integer $new_height the new height
     * @throws \Exception
     */
    protected function resampleImage($image_path, $image_type, $origin_width, $origin_height, $new_image_path, $new_width, $new_height) {

        switch ($image_type) {
            case IMG_GIF:
                $origin_image = imagecreatefromgif($image_path);
                break;
            case IMG_JPEG:
            case IMG_JPG:
                $origin_image = imagecreatefromjpeg($image_path);
                break;
            case IMG_PNG:
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
            case IMG_GIF:
                imagegif($new_image, $new_image_path);
                break;
            case IMG_JPEG:
            case IMG_JPG:
                // static setting for the JPEG Quality
                imagejpeg($new_image, $new_image_path, 90);
                break;
            case IMG_PNG:
                imagepng($new_image, $new_image_path);
                break;
        }

        $this->app['filesystem']->chmod($new_image_path, 0644);
    }

    /**
     * Return a array with the URL source, width and height of the given image.
     * If $max_width or $max_height ar not NULL a new image will be resampled.
     *
     * @param string $relative_image_path relative path to $parent_path
     * @param integer $max_width of the image in pixel
     * @param integer $max_height of the image in pixel
     * @param string $parent_path FRAMEWORK_PATH by default
     * @param string $parent_url FRAMEWORK_URL by default
     * @return array with src, width and height
     */
    public function image($relative_image_path, $max_width=null, $max_height=null, $parent_path=FRAMEWORK_PATH, $parent_url=FRAMEWORK_URL)
    {
        $relative_image_path = $this->app['utils']->sanitizePath($relative_image_path);
        if ($relative_image_path[0] != '/') {
            $relative_image_path = '/'.$relative_image_path;
        }

        $parent_path = $this->app['utils']->sanitizePath($parent_path);

        if ($parent_url[strlen($parent_url)-1] == '/') {
            $parent_url = substr($parent_url, 0, -1);
        }

        if (!$this->app['filesystem']->exists($parent_path.$relative_image_path)) {
            $this->app['monolog']->addDebug("The image $parent_path.$relative_image_path does not exists!",
                array(__METHOD__, __LINE__));
            return array(
                'src' => $parent_url.$relative_image_path,
                'width' => '100%',
                'height' => '100%'
            );
        }

        // get the image information
        list($width, $height, $type) = getimagesize($parent_path.$relative_image_path);

        if ((!is_null($max_width) && ($width > $max_width)) || (!is_null($max_height) && ($height > $max_height))) {
            // optimize the image
            if (!is_null($max_width) && ($width > $max_width)) {
                // set a new image width
                $percent = (int) ($max_width / ($width / 100));
                $new_width = $max_width;
                $new_height = (int) (($height / 100) * $percent);
            }
            else {
                // set a new image height
                $percent = (int) ($max_height / ($height/100));
                $new_height = $max_height;
                $new_width = (int) (($width / 109) * $percent);
            }

            // create a new filename
            $pathinfo = pathinfo($relative_image_path);

            $new_relative_image_path = sprintf('%s/%s_%dx%d.%s', $pathinfo['dirname'],
                $pathinfo['filename'], $new_width, $new_height, $pathinfo['extension']);

            $tweak_path = FRAMEWORK_PATH.'/media/twig';
            $tweak_url = FRAMEWORK_URL.'/media/twig';

            if (!$this->app['filesystem']->exists($tweak_path.$new_relative_image_path)) {
                $this->resampleImage($parent_path.$relative_image_path, $type, $width, $height, $tweak_path.$new_relative_image_path, $new_width, $new_height);
            }

            return array(
                'src' => $tweak_url.$new_relative_image_path,
                'width' => $new_width,
                'height' => $new_height
            );
        }
        else {
            // nothing to do ...
            return array(
                'src' => $parent_url.$relative_image_path,
                'width' => $width,
                'height' => $height
            );
        }
    }

    /**
     * Ellipsis function - shorten the given $text to $length at the nearest
     * space and add three dots at the end ...
     *
     * @param string $text
     * @param number $length
     * @param boolean $striptags remove HTML tags by default
     * @return string
     */
    public function filterEllipsis($text, $length=100, $striptags=true) {
        if ($striptags) {
            $text = strip_tags($text);
        }
        if (empty($text)) {
            return '';
        }
        $start_length = strlen($text);
        $text .= ' ';
        $text = substr($text, 0, $length);
        $text = substr($text, 0, strrpos($text, ' '));
        if ($start_length > strlen($text)) {
            $text .= ' ...';
        }
        return $text;
    }

}

