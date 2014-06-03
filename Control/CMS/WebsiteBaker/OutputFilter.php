<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS\WebsiteBaker;


class OutputFilter
{

    // these CSS files will be ignored at file comparison
    protected static $css_ignore = array(
        'editor.css',
        'frontend.css',
        'ie.css',
        'media.css',
        'print.css',
        'screen.css',
        'style.css',
        'template.css',
        'theme.css',
        'view.css'
    );

    /**
     * Get the URL of the submitted PAGE_ID - check for special pages like
     * TOPICS and/or NEWS and return the URL of the TOPIC/NEW page if active
     *
     * @param integer $page_id
     * @return boolean|string
     */
    public static function getURLbyPageID($page_id)
    {
        global $database;
        global $post_id;

        if (defined('TOPIC_ID') && (TOPIC_ID > 0)) {
            // this is a TOPICS page
            $SQL = "SELECT `link` FROM `".TABLE_PREFIX."mod_topics` WHERE `topic_id`='".TOPIC_ID."'";
            $link = $database->get_one($SQL);
            if ($database->is_error()) {
                trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
                return false;
            }
            // include TOPICS settings
            global $topics_directory;
            include WB_PATH . '/modules/topics/module_settings.php';
            return WB_URL . $topics_directory . $link . PAGE_EXTENSION;
        }

        if (!is_null($post_id) || (defined('POST_ID') && (POST_ID > 0))) {
            // this is a NEWS page
            $id = (defined('POST_ID')) ? POST_ID : $post_id;
            $SQL = "SELECT `link` FROM `".TABLE_PREFIX."mod_news_posts` WHERE `post_id`='$id'";
            $link = $database->get_one($SQL);
            if ($database->is_error()) {
                trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
                return false;
            }
            return WB_URL.PAGES_DIRECTORY.$link.PAGE_EXTENSION;
        }

        $SQL = "SELECT `link` FROM `".TABLE_PREFIX."pages` WHERE `page_id`='$page_id'";
        $link = $database->get_one($SQL, MYSQL_ASSOC);
        if ($database->is_error()) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
            return false;
        }
        return WB_URL.PAGES_DIRECTORY.$link.PAGE_EXTENSION;
    }

    /**
     * Get the visibility (public, hidden, private, registered or none) for the
     * given $page_id
     *
     * @param integer $page_id
     */
    public static function getPageVisibility($page_id)
    {
        global $database;

        $SQL = "SELECT `visibility` FROM `".TABLE_PREFIX."pages` WHERE `page_id`=$page_id";
        return $database->get_one($SQL);
    }

    /**
     * Load a CSS file with DOM
     *
     * @param string reference $content
     * @param string $css_url
     * @return boolean
     */
    protected function domLoadCSS(&$content, $css_url)
    {
        $css_file = basename($css_url);

        // create DOM
        $DOM = new \DOMDocument;
        // enable internal error handling for the DOM
        libxml_use_internal_errors(true);
        if (!$DOM->loadHTML($content)) {
            // on error still return false
            return false;
        }
        libxml_clear_errors();

        $load_css = true;

        $links = $DOM->getElementsByTagName('link');
        foreach ($links as $link) {
            if ($link->getAttribute('rel') == 'stylesheet') {
                $link_url = $link->getAttribute('href');
                $basename = basename($link_url);
                if (($link_url == $css_url) ||
                    (!in_array(strtolower($basename), self::$css_ignore) && ($basename == $css_file))) {
                    // this CSS file is already loaded
                    $load_css = false;
                    break;
                }
            }
        }

        if ($load_css) {
            // create a new link tag for the CSS file
            $link = $DOM->createElement('link');
            $link->setAttribute('rel', 'stylesheet');
            $link->setAttribute('type', 'text/css');
            $link->setAttribute('media', 'all');
            $link->setAttribute('href', $css_url);
            $head = $DOM->getElementsByTagName('head')->item(0);
            if (!is_object($head)) {
                // problem initializing - leave here and just return false
                return false;
            }
            $head->appendChild($link);
            $content = $DOM->saveHTML();
            return true;
        }
        return false;
    }

    /**
     * Try to load a CSS file from the specified directory
     *
     * @param string reference $content
     * @param string $directory
     * @param string $css_file
     * @param string $template
     * @return boolean true on success
     */
    protected function load_css_file(&$content, $directory, $css_file, $template)
    {
        // remove leading and trailing slashes and backslashes
        $directory = trim($directory, '/\\');
        $css_file = trim($css_file, '/\\');
        $template = trim($template, '/\\');
        // we will scan the extension path for phpManufaktur and thirdParty
        $scan_paths = array(
            WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur',
            WB_PATH.'/kit2/extension/thirdparty/thirdParty'
        );
        foreach ($scan_paths as $path) {
            if (false === ($scan_files = scandir($path))) {
                return false;
            }
            foreach ($scan_files as $scan_file) {
                if (is_dir($path.'/'.$scan_file) && (strtolower($scan_file) == $directory)) {
                    $css_path = "$path/$scan_file/Template/$template/$css_file";
                    if (file_exists($css_path)) {
                        // build the URL
                        $css_url = WB_URL.substr($css_path, strlen(WB_PATH));
                        return $this->domLoadCSS($content, $css_url);
                    }
                }
            }
        }
        // no CSS file loaded
        return false;
    }

    /**
     * Load a JavaScript or jQuery file with DOM
     *
     * @param string reference $content
     * @param string $js_url
     * @return boolean
     */
    protected function domLoadJS(&$content, $js_url)
    {
        $load_js = true;
        $js_file = basename($js_url);

        // create DOM
        $DOM = new \DOMDocument;
        // enable internal error handling for the DOM
        libxml_use_internal_errors(true);
        if (!$DOM->loadHTML($content)) {
            // on error still return false
            return false;
        }
        libxml_clear_errors();

        $scripts = $DOM->getElementsByTagName('script');
        foreach ($scripts as $script) {
            if ($script->getAttribute('type') == 'text/javascript') {
                $script_url = $script->getAttribute('src');
                $basename = basename($script_url);
                if (($script_url == $js_url) || ($basename == $js_file)) {
                    // this CSS file is already loaded
                    $load_css = false;
                    break;
                }
            }
        }

        if ($load_js) {
            // create a new link tag for the CSS file
            $script = $DOM->createElement('script');
            $script->setAttribute('type', 'text/javascript');
            $script->setAttribute('src', $js_url);
            $head = $DOM->getElementsByTagName('head')->item(0);
            if (!is_object($head)) {
                // problem initializing - leave here and just return false
                return false;
            }
            $head->appendChild($script);
            $content = $DOM->saveHTML();
            return true;
        }
        return false;
    }

    /**
     * Try to load a JavaScript or jQuery file from the specified directory
     *
     * @param string reference $content
     * @param string $directory
     * @param string $js_file
     * @param string $template
     * @return boolean true on success
     */
    protected function load_js_file(&$content, $directory, $js_file, $template)
    {
        // remove leading and trailing slashes and backslashes
        $directory = trim($directory, '/\\');
        $js_file = trim($js_file, '/\\');
        $template = trim($template, '/\\');
        // we will scan the extension path for phpManufaktur and thirdParty
        $scan_paths = array(
            WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur',
            WB_PATH.'/kit2/extension/thirdparty/thirdParty'
        );
        foreach ($scan_paths as $path) {
            if (false === ($scan_files = scandir($path))) {
                return false;
            }
            foreach ($scan_files as $scan_file) {
                if (is_dir($path.'/'.$scan_file) && (strtolower($scan_file) == $directory)) {
                    $js_path = "$path/$scan_file/Template/$template/$js_file";
                    if (file_exists($js_path)) {
                        // ok - the JS file exist, now we load it
                        $js_url = WB_URL.substr($js_path, strlen(WB_PATH));
                        return $this->domLoadJS($content, $js_url);
                    }
                }
            }
        }
        // no JS file loaded
        return false;
    }

    /**
     * Check if a CSS or JS file is to load, check the params, set defaults and
     * call the subroutines to load the files
     *
     * @param string reference $content
     * @param string $command
     * @param string $type i.e. 'css' or 'js'
     * @param string $value
     */
    protected function checkLoadFile(&$content, $command, $type, $value) {
        if ($type == 'css') {
            // we have to load an additional CSS file
            $count = substr_count($value, ',');
            if ($count == 0) {
                if (empty($value)) {
                    // assume that the directory is equal to the command
                    return $this->load_css_file($content, $command, 'screen.css', 'default');
                }
                else {
                    // directory is given, all other values are default
                    return $this->load_css_file($content, strtolower(trim($value)), 'screen.css', 'default');
                }
            }
            elseif ($count == 1) {
                list($directory, $css_file) = explode(',', strtolower($value));
                return $this->load_css_file($content, trim($directory), trim($css_file), 'default');
            }
            elseif ($count == 2) {
                // three parameters
                list($directory, $css_file, $template) = explode(',', strtolower($value));
                return $this->load_css_file($content, trim($directory), trim($css_file), trim($template));
            }
        }
        elseif ($type == 'js') {
            $count = substr_count($value, ',');
            if ($count == 1) {
                // two parameters, split into directory and JS file
                list($directory, $js_file) = explode(',', strtolower($value));
                return $this->load_js_file($content, trim($directory), trim($js_file), 'default');
            }
            elseif ($count == 2) {
                // three parameters, split into directory, JS file and template
                list($directory, $js_file, $template) = explode(',', strtolower($value));
                return $this->load_js_file($content, trim($directory), trim($js_file), trim($template));
            }
        }
    }

    /**
     * Load a specified file from the BASIC library
     *
     * @param string $content
     * @param string $value command value, comma separated relative paths
     */
    protected function load_library_files(&$content, $value)
    {
        $files = array();
        if (strpos($value, ',')) {
            $array = explode(',', $value);
            foreach ($array as $file) {
                $file = trim($file);
                if ($file[0] == '/') {
                    $file = substr($file, 1);
                }
                $files[] = $file;
            }
        }
        else {
            $value = trim($value);
            if ($value[0] == '/') {
                $value = substr($value. 1);
            }
            $files[] = $value;
        }
        // reverse the array to get the correct loading order ...
        $files = array_reverse($files);

        $library_url = WB_URL.'/kit2/extension/phpmanufaktur/phpManufaktur/Library/Library/';

        foreach ($files as $file) {
            if (file_exists(WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Library/Library/'.$file)) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                if ($extension == 'js') {
                    $this->domLoadJS($content, $library_url.$file);
                }
                elseif ($extension == 'css') {
                    $this->domLoadCSS($content, $library_url.$file);
                }
            }
        }
    }

    /**
     * Set the CMS page header with information from the kitCommand
     *
     * @param string $command name of the kitCommand
     * @param integer $id identifier needed by the kitCommand
     * @param string reference $content the CMS page content
     */
    protected function setPageHeader($command, $id, &$content)
    {
        if (false !== ($header_json = @file_get_contents(WB_URL.'/kit2/command/'.$command.'/getheader/id/'.$id))) {
            if (null !== ($header = json_decode($header_json, true))) {
                $doc = new \DOMDocument;
                // no error reporting here!
                @$doc->loadHTML($content);

                $changed = false;

                if (isset($header['title']) && !empty($header['title'])) {
                    $titles = $doc->getElementsByTagName('title');
                    $titles->item(0)->nodeValue = $header['title'];
                    $changed = true;
                }

                $metas = $doc->getElementsByTagName('meta');
                foreach ($metas as $meta) {
                    if ((strtolower($meta->getAttribute('name')) == 'description')  &&
                        (isset($header['description']) && !empty($header['description']))) {
                        $meta->setAttribute('content', $header['description']);
                        $changed = true;
                    }
                    if ((strtolower($meta->getAttribute('name')) == 'keywords') &&
                        (isset($header['keywords']) && !empty($header['keywords']))) {
                        $meta->setAttribute('content', $header['keywords']);
                        $changed = true;
                    }
                }
                if ($changed) {
                    $content = $doc->saveHTML();
                }
            }
        }
    }

    /**
     * Set a canonical link in header of the current page
     *
     * @param string $command kitCommand which need the link
     * @param integer $id identifier to submit to the kitCommand
     * @param string reference $content
     * @return boolean
     */
    protected function setCanonicalLink($command, $id, &$content)
    {
        if (filter_var($id, FILTER_VALIDATE_INT) && (false !== ($header_json = @file_get_contents(WB_URL.'/kit2/command/'.$command.'/canonical/id/'.$id)))) {
            // $id is an integer so we let the kitCommand create the link
            $header = json_decode($header_json, true);
            $canonical_url = $header['canonical_url'];
        }
        elseif (filter_var($id, FILTER_VALIDATE_URL)) {
            // the $id is a URL, so we use this
            $canonical_url = (!parse_url($id, PHP_URL_SCHEME)) ? 'http://'.$id : $id;
        }
        else {
            // no valid $id ...
            return false;
        }

        $DOM = new \DOMDocument;

        // enable internal error handling
        libxml_use_internal_errors(true);
        if (!$DOM->loadHTML($content)) {
            // on error still return false
            return false;
        }
        libxml_clear_errors();

        $changed = false;

        $links = $DOM->getElementsByTagName('link');
        foreach ($links as $link) {
            if (strtolower($link->getAttribute('rel')) == 'canonical') {
                // update the existing link tag
                $link->setAttribute('url', $canonical_url);
                $changed = true;
                break;
            }
        }

        if (!$changed) {
            // create a new link tag
            $link = $DOM->createElement('link');
            $link->setAttribute('rel', 'canonical');
            $link->setAttribute('url', $canonical_url);
            $head = $DOM->getElementsByTagName('head')->item(0);
            if (!is_object($head)) {
                // problem initializing - leave here and just return false
                return false;
            }
            $head->appendChild($link);
        }

        $content = $DOM->saveHTML();
        return true;
    }

    /**
     * Update or create meta tags
     *
     * @param string $meta_name the name of the meta tag
     * @param string $meta_content the content (value) of the meta tag
     * @param string reference $content
     * @return boolean
     */
    protected function setMetaTag($meta_name, $meta_content, &$content)
    {
        $DOM = new \DOMDocument;

        // enable internal error handling
        libxml_use_internal_errors(true);
        if (!$DOM->loadHTML($content)) {
            // on error still return false
            return false;
        }
        libxml_clear_errors();

        $changed = false;

        $metas = $DOM->getElementsByTagName('meta');
        foreach ($metas as $meta) {
            if (strtolower($meta->getAttribute('name')) == $meta_name) {
                // update the existing meta tag
                $meta->setAttribute('content', $meta_content);
                $changed = true;
                break;
            }
        }

        if (!$changed) {
            // create a new meta tag
            $meta = $DOM->createElement('meta');
            $meta->setAttribute('name', $meta_name);
            $meta->setAttribute('content', $meta_content);
            $head = $DOM->getElementsByTagName('head')->item(0);
            if (!is_object($head)) {
                // problem initializing - leave here and just return false
                return false;
            }
            $head->appendChild($meta);
        }

        $content = $DOM->saveHTML();
        return true;
    }

    /**
     * Attach a FUID=FRAMEWORK_UID parameter to all WB_URL links which not point
     * to the PAGES directory, assuming that these are kitFramework permanent links.
     *
     * @param string $content
     * @return boolean|string
     */
    protected function attachFUIDtoPermalinks($content)
    {
        $DOM = new \DOMDocument;
        // enable internal error handling
        libxml_use_internal_errors(true);
        // need a hack to properly handle UTF-8 encoding
        if (!$DOM->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8"))) {
            // on error still return false
            return $content;
        }
        libxml_clear_errors();

        $changed = false;

        $links = $DOM->getElementsByTagName('a');
        foreach ($links as $link) {
            $item = $link->getAttribute('href');
            if ((false !== stripos($item, WB_URL)) && (false === stripos($item, WB_URL.PAGES_DIRECTORY.'/'))) {
                // URL inside the installation but outside of the pages directory - possibly permanent link
                if (false === strpos($item, '?')) {
                    // item has no query
                    $item = $item.'?fuid='.FRAMEWORK_UID;
                    $link->setAttribute('href', $item);
                    $changed = true;
                }
                else {
                    $query_str = parse_url($item, PHP_URL_QUERY);
                    $query_array = strpos($query_str, '&') ? explode('&', $query_str) : array($query_str);
                    $fuid_exists = false;
                    foreach ($query_array as $query_item) {
                        if (strpos($query_item, '=')) {
                            list($key, $value) = explode('=', $query_item);
                            if ($key == 'fuid') {
                                $fuid_exists = true;
                                break;
                            }
                        }
                    }
                    if (!$fuid_exists) {
                        $item = $item.'?fuid='.FRAMEWORK_UID;
                        $link->setAttribute('href', $item);
                        $changed = true;
                    }
                }
            }
        }
        if ($changed) {
            return $DOM->saveHTML();
        }
        return $content;
    }

    /**
     * Execute the content filter for the kitFramework.
     * Extract CMS parameters like type, version, path, url, id of the calling
     * page and other, additional routes all parameters of a kitCommand and all
     * $_REQUESTs to the kitCommand routine of the kitFramework.
     *
     * @param string $content
     * @return mixed
     */
    public function parse($content, $parseCMS=true, &$kit_command=array())
    {
        global $post_id;

        if (defined('LEPTON_VERSION')) {
            $cms_type = 'LEPTON';
            $cms_version = LEPTON_VERSION;
        }
        elseif (defined('CAT_VERSION')) {
            $cms_type = 'BlackCat';
            $cms_version = CAT_VERSION;
        }
        elseif (defined('WB_VERSION')) {
            $cms_type = 'WebsiteBaker';
            $cms_version = WB_VERSION;
            // fix for WB 2.8.4
            if (($cms_version == '2.8.3') && file_exists(WB_PATH.'/setup.ini.php')) {
                $cms_version = '2.8.4';
            }
        }
        else {
            $cms_type = '- unknown -';
            $cms_version = '0.0.0';
        }

        $use_alternate_parameter = false;
        $add_meta_generator = true;

        $config_path = realpath(__DIR__.'/../../../../../../../../kit2/config/cms.json');

        if (file_exists($config_path)) {
            $config = json_decode(file_get_contents($config_path), true);
            if (isset($config['OUTPUT_FILTER']['METHOD']) && ($config['OUTPUT_FILTER']['METHOD'] == 'ALTERNATE')) {
                $use_alternate_parameter = true;
            }
            if (isset($config['OUTPUT_FILTER']['GENERATOR'])) {
                $add_meta_generator = $config['OUTPUT_FILTER']['GENERATOR'];
            }
        }

        $kit_command = array();
        $load_css = array();
        preg_match_all('/(~~)( |&nbsp;)(.){3,512}( |&nbsp;)(~~)/', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if (defined('PAGE_ID') && (PAGE_ID < 1)) {
                // no regular page, probably the search function where we don't
                // want to execute any kitCommand, so we remove it and continue
                $content = str_replace($match[0], '', $content);
                continue;
            }

            $command_expression = str_ireplace("&nbsp;", ' ', $match[0]);
            // get the expression without leading and trailing ~~
            $command_string = trim(str_replace('~~', '', $command_expression));

            if (empty($command_string)) continue;


            // explode the string into an array by spaces
            $command_array = explode(' ', $command_string);
            // the first match is the command!
            $command = strtolower(trim($command_array[0]));
            // delete the command from array
            unset($command_array[0]);
            // get the parameter string
            $parameter_string = implode(' ', $command_array);
            $params = array();
            $css_loaded = false;
            // now we search for the parameters
            preg_match_all('/([a-z,A-Z,0-9,_]{2,32}([ ]){0,2}\[)(.*?)(])/', $parameter_string, $parameter_matches, PREG_SET_ORDER);
            // loop through the parameters
            foreach ($parameter_matches as $parameter_match) {
                // the bracket [ separate key and value
                $parameter_pair = explode('[', $parameter_match[0]);
                // no pair? continue!
                if (count($parameter_pair) != 2) continue;
                // separate the key
                $key = strtolower(trim(strip_tags($parameter_pair[0])));
                // separate the value
                $value = trim(strip_tags(substr($parameter_pair[1], 0, strrpos($parameter_pair[1], ']'))));
                // add to the params array
                $params[$key] = $value;
                if ($parseCMS) {
                    // only css and js within the CMS!
                    if (($key == 'css') || ($key == 'js')) {
                        // we have to load an additional CSS file
                        if ($this->checkLoadFile($content, $command, $key, $value) && ($key == 'css')) {
                            $css_loaded = true;
                        }
                    }
                    elseif ($key == 'library') {
                        // load files from the library
                        $this->load_library_files($content, $value);
                    }
                }
            }

            if (isset($params['simulate'])) {
                // this is a simulated kitCommand - remove "simulate[]" from the command expression and do nothing else
                $simulate_expression = substr($command_expression, stripos($command_expression, 'simulate['));
                $simulate_expression = substr($simulate_expression, 0, strpos($simulate_expression, ']')+2);
                $response = str_replace($simulate_expression, '', $command_expression);
                $response = sprintf('<var class="kitcommand-expression" title="Don\'t copy this expression - '.
                    'it contains HTML tags to prevent it from execution!"><span class="disrupt">~</span>%s</var>',
                    substr($response, 1));
                $content = str_replace($command_expression, $response, $content);
                continue;
            }

            if (isset($params['robots']) && $parseCMS) {
                // create or update the robots meta tag
                $this->setMetaTag('robots', $params['robots'], $content);
            }
            elseif (isset($_GET['robots']) && $parseCMS) {
                // create or update the robots meta tag
                $this->setMetaTag('robots', $_GET['robots'], $content);
            }

            if ($add_meta_generator && $parseCMS && (!isset($params['generator']) || (isset($params['generator']) &&
                (($params['generator'] == 1) || (strtolower($params['generator']) == 'true'))))) {
                // create the generator meta tag
                $this->setMetaTag('generator', 'kitFramework (https://kit2.phpmanufaktur.de)', $content);
            }

            if ($parseCMS) {
                // parse() is executed for the CMS content!
                if (!$css_loaded) {
                    // load the kitCommand default CSS file
                    $this->load_css_file($content, 'basic', '/kitcommand/css/kitcommand.min.css', 'default');
                }
                $cmd_array = array(
                    'cms' => array(
                        'type' => $cms_type,
                        'version' => $cms_version,
                        'locale' => strtolower(LANGUAGE),
                        'page_id' => PAGE_ID,
                        'page_url' => $this->getURLbyPageID(PAGE_ID),
                        'page_visibility' => $this->getPageVisibility(PAGE_ID),
                        'user' => array(
                            'id' => (isset($_SESSION['USER_ID'])) ? $_SESSION['USER_ID'] : -1,
                            'name' => (isset($_SESSION['USERNAME'])) ? $_SESSION['USERNAME'] : '',
                            'email' => (isset($_SESSION['EMAIL'])) ? $_SESSION['EMAIL'] : ''
                        ),
                        'special' => array(
                            'post_id' => (!is_null($post_id) || defined('POST_ID')) ? defined('POST_ID') ? POST_ID : $post_id : null,
                            'topic_id' => defined('TOPIC_ID') ? TOPIC_ID : null
                        )
                    ),
                    'GET' => $_GET,
                    'POST' => $_POST,
                    'parameter' => $params,
                );
                $kit_filter = false;
                if ($use_alternate_parameter) {
                    $command_url = WB_URL.'/kit2/kit_command/'.$command.'/'.base64_encode(json_encode($cmd_array));
                }
                else {
                    $command_url = WB_URL.'/kit2/kit_command/'.$command;
                }
                if ((false !== ($pos = strpos($command, 'filter:'))) && ($pos == 0)) {
                    $kit_filter = true;
                    $command = trim(substr($command, strlen('filter:')));
                    $cmd_array['content'] = $content;
                    $cmd_array['filter_expression'] = $command_expression;
                    $command_url = WB_URL.'/kit2/kit_filter/'.$command;
                    if ($use_alternate_parameter) {
                        $command_url = WB_URL.'/kit2/kit_filter/'.$command.'/'.base64_encode(json_encode($cmd_array));
                    }
                }

                $options = array(
                    CURLOPT_POST => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_URL => $command_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_POSTFIELDS => http_build_query(array('cms_parameter' => $cmd_array), '', '&'),
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                );
                if (!isset($_SESSION['KIT2_COOKIE_FILE']) || !file_exists($_SESSION['KIT2_COOKIE_FILE'])) {
                    $_SESSION['KIT2_COOKIE_FILE'] = WB_PATH.'/kit2/temp/session/'.uniqid('outputfilter_');
                    $options[CURLOPT_COOKIEJAR] = $_SESSION['KIT2_COOKIE_FILE'];
                }
                else {
                    $options[CURLOPT_COOKIEFILE] = $_SESSION['KIT2_COOKIE_FILE'];
                }

                $ch = curl_init();
                curl_setopt_array($ch, $options);

                if (false === ($response = curl_exec($ch))) {
                    trigger_error(curl_error($ch), E_USER_ERROR);
                }
                curl_close($ch);
                if ($kit_filter && !key_exists('help', $params)) {
                    $content = $response;
                }
                else {
                    if (!empty($response) &&
                        ((isset($_GET['fuid']) && ($_GET['fuid'] == FRAMEWORK_UID)) ||
                         (isset($_POST['fuid']) && ($_POST['fuid'] == FRAMEWORK_UID)))) {
                        // attach the fuid parameter to all permanent links!
                        $response = $this->attachFUIDtoPermalinks($response);
                    }
                    // replace the kitCommand
                    $search = str_replace(array('[','|'), array('\[','\|'), $command_expression);
                    if (preg_match('%<[^>\/]+>\s*'.$search.'\s*<\/[^>]+>%si', $content, $hits)) {
                        // also remove the tags around the kitCommand expression!
                        $content = str_replace($hits[0], $response, $content);
                    }
                    else {
                        // only replace the kitCommand
                        $content = str_replace($command_expression, $response, $content);
                    }
                    // set CMS page header?
                    if ((isset($_GET['command']) && (strtolower($_GET['command'] == $command))) &&
                        (isset($_GET['set_header']) && (is_numeric($_GET['set_header']) && ($_GET['set_header'] > 0)))) {
                        $this->setPageHeader($command, $_GET['set_header'], $content);
                    }
                    elseif (isset($params['set_header']) && (is_numeric($params['set_header']) && ($params['set_header'] > 0))) {
                        $this->setPageHeader($command, $params['set_header'], $content);
                    }
                    // set a canonical link?
                    if ((isset($_GET['command']) && (strtolower($_GET['command'] == $command))) &&
                        (isset($_GET['canonical']))) {
                        $this->setCanonicalLink($command, $_GET['canonical'], $content);
                    }
                    elseif (isset($params['canonical'])) {
                        $this->setCanonicalLink($command, $params['canonical'], $content);
                    }

                    // sometimes the filter destroy the brackets of the [wblink123] so we fix it ...
                    $content = str_replace(array('%5B','%5D'), array('[',']'), $content );
                }
            }
            else {
                // parse() is executed within the Framework !!!
                $kit_command[] = array(
                    'cms' => array(
                        'locale' => 'en',
                        'page_id' => '-1',
                        'page_url' => '',
                        'user' => array(
                            'id' => -1,
                            'name' => '',
                            'email' => ''
                        ),
                        'special' => array(
                            'post_id' => null,
                            'topic_id' => null
                        )
                    ),
                    'GET' => array(),
                    'POST' => array(),
                    'command' => $command,
                    'parameter' => $params,
                    'expression' => $command_expression
                );
            }
        }
        return $content;
    }
}
