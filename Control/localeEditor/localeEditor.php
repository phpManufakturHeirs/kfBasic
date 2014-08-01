<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\localeEditor;

use Silex\Application;
use Symfony\Component\Finder\Finder;
use phpManufaktur\Basic\Control\Pattern\Alert;
use phpManufaktur\Basic\Data\localeScanFile;
use phpManufaktur\Basic\Data\localeReference;
use phpManufaktur\Basic\Data\localeSource;
use phpManufaktur\Basic\Data\localeTranslation;
use phpManufaktur\Basic\Data\localeTranslationFile;

class localeEditor extends Alert
{
    protected static $config = null;
    protected $localeScanFile = null;
    protected $localeSource = null;
    protected $localeReference = null;
    protected $localeTranslation = null;
    protected $localeTranslationFile = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();

        $this->localeScanFile = new localeScanFile($app);
        $this->localeReference = new localeReference($app);
        $this->localeSource = new localeSource($app);
        $this->localeTranslation = new localeTranslation($app);
        $this->localeTranslationFile = new localeTranslationFile($app);
    }

    /**
     * Controller to create the tables for the localeEditor
     *
     * @param Application $app
     * @return \phpManufaktur\Basic\Control\Pattern\rendered
     */
    public function ControllerCreateTable(Application $app)
    {
        $this->initialize($app);

        $this->localeScanFile->createTable();
        $this->localeSource->createTable();
        $this->localeReference->createTable();
        $this->localeTranslation->createTable();
        $this->localeTranslationFile->createTable();

        $this->setAlert('Successful created the tables for the localeEditor.',
            array(), self::ALERT_TYPE_SUCCESS);
        return $this->promptAlertFramework();
    }

    /**
     * Controller to drop the tables for the localeEditor
     *
     * @param Application $app
     * @return \phpManufaktur\Basic\Control\Pattern\rendered
     */
    public function ControllerDropTable(Application $app)
    {
        $this->initialize($app);

        $this->localeScanFile->dropTable();
        $this->localeSource->dropTable();
        $this->localeReference->dropTable();
        $this->localeTranslation->dropTable();
        $this->localeTranslationFile->dropTable();

        $this->setAlert('Dropped the tables for the localeEditor.',
            array(), self::ALERT_TYPE_SUCCESS);
        return $this->promptAlertFramework();
    }

    /**
     * Parse the given PHP file for translation functions and add the detected
     * locale sources to the database
     *
     * @param string $path
     * @throws \Exception
     * @return boolean
     */
    protected function parsePHPfile($path)
    {
        if (!file_exists($path)) {
            $this->setAlert('The file <strong>%file%</strong> does not exists!',
                array('%file%' => basename($path)), self::ALERT_TYPE_DANGER, true,
                array('path' => $path, 'method' => __METHOD__));
            return false;
        }

        if (!is_readable($path)) {
            $this->setAlert('The file <strong>%file%</strong> is not readable!',
                array('%file%' => basename($path)), self::ALERT_TYPE_DANGER, true,
                array('path' => $path, 'method' => __METHOD__));
            return false;
        }

        if (false === ($code = @file_get_contents($path))) {
            $error = error_get_last();
            $this->setAlert('Can not read the file <strong>%file%</strong>!',
                array('%file%' => $path), self::ALERT_TYPE_DANGER, true,
                array('error' => $error['message'], 'path' => $path, 'method' => __METHOD__));
            return false;
        }

        // get all TOKENS from the code
        $tokens = token_get_all($code);

        $expect_locale = false;
        $counter = 0;
        $code_start = 0;
        $start_word = '';
        $current_source = null;

        if (false === ($file_id = $this->localeScanFile->existsMD5(md5(realpath($path))))) {
            throw new \Exception("Fatal: It exists no data record for the file $path!");
        }
        // first step: delete the existing references!
        $this->localeReference->deleteFileReferences($file_id);
        $locale_hits = 0;

        foreach ($tokens as $token) {

            $start_scan = false;
            if (is_array($token) && ((($token[0] === T_STRING) && in_array($token[1], self::$config['parse']['php']['start_word']))) ||
                (($token[0] === T_CONSTANT_ENCAPSED_STRING) && in_array($token[1], self::$config['parse']['php']['property_word']))) {
                if ($expect_locale) {
                    $this->app['monolog']->addDebug("[localeEditor] Can't evaluate the code, started parsing at line $code_start and stopped at {$token[2]}.",
                        array('path' => $path, 'method' => __METHOD__));
                }
                $expect_locale = true;
                $counter = 0;
                $code_start = $token[2];
                $start_word = $token[1];
                $start_scan = true;
            }

            if (!$start_scan && $expect_locale && is_array($token) && ($token[0] === T_CONSTANT_ENCAPSED_STRING)) {
                if (empty(trim($token[1], "\x22\x27"))) {
                    // don't handle empty strings!
                    continue;
                }
                $last_source = $current_source;
                if (in_array($token[1], self::$config['parse']['php']['stop_word'])) {
                    // skip entry and alert to check the program code
                    $this->app['monolog']->addDebug("[localeEditor] STOP parsing, detect stop word {$token[1]} at line {$token[2]}",
                        array('start_word' => $start_word, 'path' => $path, 'method' => __METHOD__));
                }
                else {
                    if ($start_word === 'add') {
                        // remove leading and trailing " or ' and humanize the string
                        $current_source = $this->app['utils']->humanize(trim($token[1], "\x22\x27"));
                    }
                    else {
                        $current_source = trim($token[1], "\x22\x27");
                    }
                    $source_md5 = md5($current_source);
                    if (false === ($locale_id = $this->localeSource->existsMD5($source_md5))) {
                        // create a new locale source entry
                        $data = array(
                            'locale_source' => $current_source,
                            'locale_locale' => 'EN',
                            'locale_md5' => $source_md5,
                            'locale_remark' => ''
                        );
                        $locale_id = $this->localeSource->insert($data);
                    }

                    if (!$this->localeReference->existsReference($locale_id, $file_id, $token[2])) {
                        // create a new reference
                        switch ($start_word) {
                            case 'add':
                                $usage = 'FORM_FIELD'; break;
                            case "'label'":
                                $usage = 'FORM_LABEL'; break;
                            case 'setAlert':
                                $usage = 'ALERT'; break;
                            case 'trans':
                                $usage = 'TRANSLATOR'; break;
                            default:
                                $usage = 'UNKNOWN'; break;
                        }

                        $data = array(
                            'locale_id' => $locale_id,
                            'file_id' => $file_id,
                            'line_number' => $token[2],
                            'locale_usage' => $usage
                        );
                        $this->localeReference->insert($data);
                        $locale_hits++;
                    }
                }

                if ($current_source === $last_source) {
                    $this->app['monolog']->addDebug("[localeEditor] Possibly duplicate definition of '$current_source' at line {$token[2]}!",
                        array('path' => $path, 'method' => __METHOD__));
                }

                $expect_locale = false;
            }

            if ($expect_locale && ($counter > 5)) {
                $expect_locale = false;
                if (isset($token[2])) {
                    $this->app['monolog']->addDebug("[localeEditor] Can't evaluate the code, started parsing at line $code_start and stopped at {$token[2]}.",
                        array('path' => $path, 'method' => __METHOD__));
                }
                else {
                    $this->app['monolog']->addDebug("[localeEditor] Can't evaluate the code, started parsing at line $code_start and stopped at counter > $counter.",
                        array('path' => $path, 'method' => __METHOD__));
                }
            }
            $counter++;
        }

        // update the file information
        $data = array(
            'file_status' => 'SCANNED',
            'locale_hits' => $locale_hits
        );
        $this->localeScanFile->update($file_id, $data);

        return true;
    }

    /**
     * Scan the kitFramework for *.php files and them to the database
     *
     */
    protected function findPHPfiles()
    {
        $phpFiles = new Finder();
        $phpFiles
            ->files()
            ->name('*.php')
            ->in(MANUFAKTUR_PATH);

        // exclude all specified *.php files
        foreach (self::$config['finder']['php']['exclude']['file'] as $file) {
            $phpFiles->notName($file);
        }

        // exclude the specified directories
        $phpFiles->exclude(self::$config['finder']['php']['exclude']['directory']);

        $path_array = array();
        foreach ($phpFiles as $file) {
            $realpath = $file->getRealpath();
            // extract the extension directory from the path
            $extension = substr($realpath, strlen(realpath(MANUFAKTUR_PATH))+1);
            $extension = substr($extension, 0, strpos($extension, DIRECTORY_SEPARATOR));

            if (false === ($file_id = $this->localeScanFile->existsMD5(md5($realpath)))) {
                // insert a new record
                $data = array(
                    'file_type' => 'PHP',
                    'file_path' => $realpath,
                    'file_md5' => md5($realpath),
                    'file_mtime' => date('Y-m-d H:i:s', $file->getMTime()),
                    'file_status' => 'REGISTERED',
                    'extension' => $extension,
                    'template' => 'NONE',
                    'locale_hits' => 0
                );
                $this->localeScanFile->insert($data);
            }
            else {
                $data = $this->localeScanFile->select($file_id);
                if ($data['file_mtime'] !== date('Y-m-d H:i:s', $file->getMTime())) {
                    // the file was changed
                    $update = array(
                        'file_mtime' => date('Y-m-d H:i:s', $file->getMTime()),
                        'file_status' => 'REGISTERED',
                        'locale_hits' => 0
                    );
                    $this->localeScanFile->update($file_id, $update);
                }
            }
            $path_array[] = $realpath;
        }

        // check for deleted files
        $all_files = $this->localeScanFile->selectType('PHP');
        foreach ($all_files as $file) {
            if (!in_array(realpath($file['file_path']), $path_array)) {
                $this->localeScanFile->delete($file['file_id']);
                $this->app['monolog']->addDebug("[localeEditor] The file ".basename($file['file_path'])." does no longer exists, removed all entries for this file.",
                    array('path' => $file['file_path'], 'file_id' => $file['file_id'], 'method' => __METHOD__));
            }
        }
    }

    /**
     * Find TWIG files in the kitFramework installation and add them to the database
     */
    protected function findTwigFiles()
    {
        $twigFiles = new Finder();
        $twigFiles
            ->files()
            ->name('*.twig')
            ->in(MANUFAKTUR_PATH);

        // exclude all specified *.twig files
        foreach (self::$config['finder']['twig']['exclude']['file'] as $file) {
            $twigFiles->notName($file);
        }

        // exclude the specified directories
        $twigFiles->exclude(self::$config['finder']['twig']['exclude']['directory']);

        $path_array = array();
        foreach ($twigFiles as $file) {
            $realpath = $file->getRealpath();

            // extract the extension directory from the path
            $extension = substr($realpath, strlen(realpath(MANUFAKTUR_PATH))+1);
            $extension = substr($extension, 0, strpos($extension, DIRECTORY_SEPARATOR));

            // extract the template name from the path
            if (!in_array($extension, self::$config['finder']['twig']['template']['name']['exclude'])) {
                if (!in_array($extension, self::$config['finder']['twig']['template']['use_subdirectory'])) {
                    $template = substr($realpath, strpos($realpath, DIRECTORY_SEPARATOR.'Template'.DIRECTORY_SEPARATOR)+
                        strlen(DIRECTORY_SEPARATOR.'Template'.DIRECTORY_SEPARATOR));
                    $template = substr($template, 0, strpos($template, DIRECTORY_SEPARATOR));
                }
                else {
                    // extension is using additional subdirectories, i.e. CommandCollection/Template/Comments/default
                    $template = substr($realpath, strpos($realpath, DIRECTORY_SEPARATOR.'Template'.DIRECTORY_SEPARATOR)+
                        strlen(DIRECTORY_SEPARATOR.'Template'.DIRECTORY_SEPARATOR));
                    $template = substr($template, strpos($template, DIRECTORY_SEPARATOR+1));
                    $template = substr($template, strpos($template, DIRECTORY_SEPARATOR)+1);
                    $template = substr($template, 0, strpos($template, DIRECTORY_SEPARATOR));
                }
            }
            else {
                // no template name available
                $template = 'NONE';
            }

            if (false === ($file_id = $this->localeScanFile->existsMD5(md5($file->getRealpath())))) {
                // insert a new record
                $data = array(
                    'file_type' => 'TWIG',
                    'file_path' => $realpath,
                    'file_md5' => md5($realpath),
                    'file_mtime' => date('Y-m-d H:i:s', $file->getMTime()),
                    'file_status' => 'REGISTERED',
                    'extension' => $extension,
                    'template' => $template,
                    'locale_hits' => 0
                );
                $this->localeScanFile->insert($data);
            }
            else {
                $data = $this->localeScanFile->select($file_id);
                if ($data['file_mtime'] !== date('Y-m-d H:i:s', $file->getMTime())) {
                    // the file was changed
                    $update = array(
                        'file_mtime' => date('Y-m-d H:i:s', $file->getMTime()),
                        'file_status' => 'REGISTERED',
                        'locale_hits' => 0
                    );
                    $this->localeScanFile->update($file_id, $update);
                }
            }
            $path_array[] = $realpath;
        }

        // check for deleted files
        $all_files = $this->localeScanFile->selectType('TWIG');
        foreach ($all_files as $file) {
            if (!in_array(realpath($file['file_path']), $path_array)) {
                $this->localeScanFile->delete($file['file_id']);
                $this->app['monolog']->addDebug("[localeEditor] The file ".basename($file['file_path'])." does no longer exists, removed all entries for this file.",
                    array('path' => $file['file_path'], 'file_id' => $file['file_id'], 'method' => __METHOD__));
            }
        }
    }

    /**
     * Parse the given TWIG file for translations, extract the locale sources and
     * add them to the database
     *
     * @param unknown $path
     * @throws \Exception
     * @return boolean
     */
    protected function parseTwigFile($path)
    {
        if (!file_exists($path)) {
            $this->setAlert('The file <strong>%file%</strong> does not exists!',
                array('%file%' => basename($path)), self::ALERT_TYPE_DANGER, true,
                array('path' => $path, 'method' => __METHOD__));
            return false;
        }

        if (!is_readable($path)) {
            $this->setAlert('The file <strong>%file%</strong> is not readable!',
                array('%file%' => basename($path)), self::ALERT_TYPE_DANGER, true,
                array('path' => $path, 'method' => __METHOD__));
            return false;
        }

        if (false === ($content = @file($path))) {
            $error = error_get_last();
            $this->setAlert('Can not read the file <strong>%file%</strong>!',
                array('%file%' => $path), self::ALERT_TYPE_DANGER, true,
                array('error' => $error['message'], 'path' => $path, 'method' => __METHOD__));
            return false;
        }

        if (false === ($file_id = $this->localeScanFile->existsMD5(md5(realpath($path))))) {
            throw new \Exception("Fatal: It exists no data record for the file $path!");
        }
        // first step: delete the existing references!
        $this->localeReference->deleteFileReferences($file_id);
        $locale_hits = 0;

        foreach ($content as $line_number => $line_content) {
            $matches_array = array();
            foreach (self::$config['parse']['twig']['regex'] as $regex) {
                preg_match_all($regex, $line_content, $matches, PREG_SET_ORDER);
                $matches_array = array_merge($matches_array, $matches);
            }
            foreach ($matches_array as $match) {
                $locale = trim($match[1]);
                if (in_array($locale[0], array('"', "'"))) {
                    $locale = trim($locale, "\x22\x27");

                    // check the filters, perhaps we have to perform the locale string!
                    $check = substr($match[0], strpos($match[0], '|')+1);
                    $check = rtrim($check, ' }');
                    if (strpos($check, '|')) {
                        $params = explode('|', $check);
                        switch (strtolower($params[0])) {
                            case 'humanize':
                                $locale = $this->app['utils']->humanize($locale);
                                break;
                            case 'uppercase':
                                $locale = strtoupper($locale);
                                break;
                            case 'lowercase':
                                $locale = strtolower($locale);
                                break;
                            case 'capitalize':
                                $locale = ucfirst($locale);
                                break;
                        }
                    }

                    $locale_md5 = md5($locale);
                    if (false === ($locale_id = $this->localeSource->existsMD5($locale_md5))) {
                        // create a new locale source entry
                        $data = array(
                            'locale_source' => $locale,
                            'locale_locale' => 'EN',
                            'locale_md5' => $locale_md5,
                            'locale_remark' => ''
                        );
                        $locale_id = $this->localeSource->insert($data);
                    }

                    if (!$this->localeReference->existsReference($locale_id, $file_id, $line_number)) {
                        // create a new reference
                        $data = array(
                            'locale_id' => $locale_id,
                            'file_id' => $file_id,
                            'line_number' => $line_number,
                            'locale_usage' => 'TWIG'
                        );
                        $this->localeReference->insert($data);
                        $locale_hits++;
                    }
                }
            }
        }

        // update the file information
        $data = array(
            'file_status' => 'SCANNED',
            'locale_hits' => $locale_hits
        );
        $this->localeScanFile->update($file_id, $data);
    }

    /**
     * Update the translation table
     *
     */
    protected function updateTranslationTable()
    {
        // build the translation tables for all needed locales
        if (false !== ($sources = $this->localeSource->selectAll())) {
            foreach ($sources as $source) {
                foreach (self::$config['translation']['locale'] as $locale) {
                    if (!$this->localeTranslation->existsLocaleID($source['locale_id'], $locale)) {
                        $data = array(
                            'locale_id' => $source['locale_id'],
                            'locale_source' => $source['locale_source'],
                            'locale_md5' => $source['locale_md5'],
                            'locale_locale' => $locale,
                            'translation_text' => '',
                            'translation_md5' => '',
                            'translation_remark' => '',
                            'translation_status' => 'PENDING'
                        );
                        $this->localeTranslation->insert($data);
                    }
                }
            }
        }

        // check for widowed locale translations
        $widowed = $this->localeTranslation->selectWidowed();
        if (is_array($widowed)) {
            foreach ($widowed as $widow) {
                // remove widow translation
                $this->localeTranslation->deleteLocaleID($widow['locale_id']);
                $this->setAlert('Deleted widowed locale translation with the ID %id%.',
                    array('%id%' => $widow['locale_id']));
            }
        }
    }

    protected function findLocaleFiles()
    {
        $localeFiles = new Finder();
        $localeFiles
            ->files()
            ->in(MANUFAKTUR_PATH);

        // add all specified locale files
        foreach (self::$config['translation']['locale'] as $locale) {
            $localeFiles->name(strtolower($locale).'.php');
        }

        // exclude all specified *.php files
        foreach (self::$config['finder']['locale']['exclude']['file'] as $file) {
            $localeFiles->notName($file);
        }

        // exclude the specified directories
        $localeFiles->exclude(self::$config['finder']['locale']['exclude']['directory']);
        $file_count = 0;
        $translation_count = 0;

        foreach ($localeFiles as $file) {
            $realpath = $file->getRealpath();

            // extract the extension directory from the path
            $extension = substr($realpath, strlen(realpath(MANUFAKTUR_PATH))+1);
            $extension = substr($extension, 0, strpos($extension, DIRECTORY_SEPARATOR));

            $locale_path = substr($realpath, strpos($realpath, DIRECTORY_SEPARATOR.'Locale'.DIRECTORY_SEPARATOR)+
                strlen(DIRECTORY_SEPARATOR.'Locale'.DIRECTORY_SEPARATOR));

            // get the locale type DEFAULT, CUSTOM or METRIC
            $locale_type = 'DEFAULT';
            if (strpos($locale_path, DIRECTORY_SEPARATOR)) {
                $locale_type = strtoupper(substr($locale_path, 0, strpos($locale_path, DIRECTORY_SEPARATOR)));
            }
            // get the LOCALE
            $locale = strtoupper(pathinfo($realpath, PATHINFO_FILENAME));

            // get all TOKENS from the code
            $code = file_get_contents($realpath);
            $tokens = token_get_all($code);

            $walking = false;
            $key = null;

            foreach ($tokens as $token) {
                if ($token[0] ===  T_ARRAY) {
                    $walking = true;
                }
                if (!$walking) continue;

                if (is_null($key) && $token[0] === T_CONSTANT_ENCAPSED_STRING) {
                    // this is the first part: the KEY is the locale_source
                    $key = $token[1];
                    continue;
                }
                if ($token[0] === T_CONSTANT_ENCAPSED_STRING) {
                    // this is the second part: the VALUE is the translation_text
                    $locale_source = trim($key, "\x22\x27");
                    // important: set the KEY to NULL and try to get the next pair
                    $key = null;
                    // trim leading and trailing ' and "
                    $translation_text = trim($token[1], "\x22\x27");

                    $locale_md5 = md5($locale_source);
                    $translation_md5 = md5($translation_text);

                    if (false !== ($translation_id = $this->localeTranslation->existsMD5($locale_md5, $locale))) {
                        // translation record exists - get the record
                        $translation = $this->localeTranslation->select($translation_id);
                        if ($translation['translation_status'] === 'PENDING') {
                            // insert the translation
                            $data = array(
                                'translation_text' => $translation_text,
                                'translation_md5' => $translation_md5,
                                'translation_status' => 'TRANSLATED'
                            );
                            $this->localeTranslation->update($translation_id, $data);

                            // add a new translation file information
                            $data = array(
                                'translation_id' => $translation_id,
                                'locale_id' => $translation['locale_id'],
                                'locale_locale' => $locale,
                                'locale_type' => $locale_type,
                                'extension' => $extension,
                                'file_path' => $realpath
                            );
                            $this->localeTranslationFile->insert($data);
                        }
                        elseif ($translation['translation_status'] === 'TRANSLATED') {
                            // there exists already an translation
                            if (false !== ($file = $this->localeTranslationFile->selectByExtension($translation_id, $locale, $extension))) {
                                if (($translation_md5 !== $translation['translation_md5']) && ($file['locale_type'] === 'CUSTOM')) {
                                    // translation is overwritten by a CUSTOM translation - nothing to do ...
                                    $this->app['monolog']->addDebug("[localeEditor] Translation ID {$translation['translation_id']} is overwritten by CUSTOM translation with File ID {$file['file_id']}!",
                                        array('extension' => $extension, 'locale' => $locale, 'locale_source' => $locale_source, 'translation_text' => $translation_text, 'method' => __METHOD__));
                                }
                                elseif ($translation_md5 !== $translation['translation_md5']) {
                                    // the translation has changed
                                    if (false !== ($files = $this->localeTranslationFile->selectByTranslationID($translation_id, $locale))) {
                                        if ((count($files) === 1) && ($files[0]['extension'] === $extension)) {
                                            // update the translation
                                            $data = array(
                                                'translation_text' => $translation_text,
                                                'translation_md5' => $translation_md5,
                                                'translation_status' => 'TRANSLATED'
                                            );
                                            $this->localeTranslation->update($translation_id, $data);
                                            $this->app['monolog']->addDebug("[localeEditor] Updated Translation ID $translation_id.",
                                                array('extension' => $extension, 'locale' => $locale, 'locale_source' => $locale_source, 'translation_text' => $translation_text, 'method' => __METHOD__));
                                            $this->setAlert('Updated Translation ID %id%', array('%id%' => $translation_id), self::ALERT_TYPE_SUCCESS);
                                        }
                                        else {
                                            // CONFLICTING translation
                                            echo "+++++ CONFLICT +++++<br>";
                                            echo "$realpath<br>";
                                            echo "<pre>";
                                            print_r($translation);
                                            print_r($file);
                                            echo "</pre>";
                                        }
                                    }
                                }
                            }
                            else {
                                // Ooops, missing a translation file?
                                $data = array(
                                    'translation_id' => $translation_id,
                                    'locale_id' => $translation['locale_id'],
                                    'locale_locale' => $locale,
                                    'locale_type' => $locale_type,
                                    'extension' => $extension,
                                    'file_path' => $realpath
                                );
                                // add a new translation file information!
                                $this->localeTranslationFile->insert($data);

                                if ($translation['translation_md5'] !== $translation_md5) {
                                    // this translation causes a CONFLICT!
                                    $data = array(
                                        'translation_status' => 'CONFLICT'
                                    );
                                    $this->localeTranslation->update($translation_id, $data);
                                    $this->app['monolog']->addDebug("[localeEditor] There exists CONFLICTING translations for the translation ID $translation_id",
                                        array('extension' => $extension, 'locale' => $locale, 'locale_source' => $locale_source, 'translation_text' => $translation_text, 'method' => __METHOD__));
                                    $this->setAlert('Translation ID %translation_id% is conflicting!', array('%translation_id%' => $translation_id));
                                }
                            }
                        }
                        elseif ($translation['translation_status'] === 'CONFLICT') {
                            // this translation is marked as CONFLICT - check if the conflict is solved ...
                            echo "check conflict .... <br>";
                        }
                        else {
                            // check the translation
                            echo "other action ... <br>";
                            echo "$realpath<br>";
                            echo "<pre>";
                            print_r($translation);
                            print_r($file);
                            echo "</pre>";
                        }
                    }
                    else {
                        // missing locale source !
                        if (in_array($locale_source, self::$config['translation']['system'])) {
                            // these locale is defined by the system, still add to the source!
                            if (false === ($locale_id = $this->localeSource->existsMD5($locale_md5))) {
                                $data = array(
                                    'locale_source' => $locale_source,
                                    'locale_locale' => 'EN',
                                    'locale_md5' => $locale_md5,
                                    'locale_remark' => 'SYSTEM'
                                );
                                $locale_id = $this->localeSource->insert($data);
                            }

                            $data = array(
                                'locale_id' => $locale_id,
                                'locale_source' => $locale_source,
                                'locale_md5' => $locale_md5,
                                'locale_locale' => $locale,
                                'translation_text' => $translation_text,
                                'translation_md5' => $translation_md5,
                                'translation_remark' => 'SYSTEM',
                                'translation_status' => 'TRANSLATED'
                            );
                            $translation_id = $this->localeTranslation->insert($data);

                            // add a new translation file information
                            $data = array(
                                'translation_id' => $translation_id,
                                'locale_id' => $locale_id,
                                'locale_locale' => $locale,
                                'locale_type' => $locale_type,
                                'extension' => $extension,
                                'file_path' => $realpath
                            );
                            $this->localeTranslationFile->insert($data);
                        }
                        else {
                            //echo 'already translated ... <br>';
                            echo "$extension -> $locale_source: MD5, local: $locale_md5 - trans: $translation_md5 - $realpath<br>";
                        }
                    }
                    $translation_count++;
                }
            }
            $file_count++;
        }
        echo "count: $file_count -> $translation_count<br>";
    }


    /**
     * The general controller for the localeEditor
     *
     * @param Application $app
     * @return string
     */
    public function Controller(Application $app)
    {
        $start = microtime(true);

        $this->initialize($app);
        // process PHP files
        $this->findPHPfiles();

        if (false !== ($files = $this->localeScanFile->selectRegistered('PHP'))) {
            foreach ($files as $file) {
                $this->parsePHPfile($file['file_path']);
            }
        }

        // process Twig files
        $this->findTwigFiles();

        if (false !== ($files = $this->localeScanFile->selectRegistered('TWIG'))) {
            foreach ($files as $file) {
                $this->parseTwigFile($file['file_path']);
            }
        }

        // remove widowed locale sources from the database
        $widowed = $this->localeSource->selectWidowed();
        if (is_array($widowed)) {
            foreach ($widowed as $widow) {
                if (!in_array($widow['locale_source'], self::$config['translation']['system'])) {
                    $this->localeSource->delete($widow['locale_id']);
                    $this->setAlert('Deleted widowed locale source with the ID %id%.',
                        array('%id%' => $widow['locale_id']));
                }
            }
        }

        // update the translation table
        $this->updateTranslationTable();

        // find the locale files
        $this->findLocaleFiles();

        $result = $this->localeScanFile->selectCount();
        echo "<pre>";
        print_r($result);
        echo "</pre>";

        echo "Laufzeit: ". sprintf('%01.2f', (microtime(true) - $start))."<br>";

        echo $this->getAlert();
        return __METHOD__;
    }
}
