<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\i18nEditor;

use Silex\Application;
use Symfony\Component\Form\FormFactoryBuilder;

class i18nEditor extends i18nParser
{
    protected static $script_start = null;
    protected static $script_execution_time = null;
    protected static $usage = null;
    protected static $usage_param = null;
    protected static $info = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\localeEditor\localeParser::initialize()
     */
    protected function initialize(Application $app)
    {
        // get the current timestamp
        self::$script_start = microtime(true);

        parent::initialize($app);

        $usage = $this->app['request']->get('usage');
        self::$usage = is_null($usage) ? 'framework' : $usage;
        self::$usage_param = (self::$usage != 'framework') ? '?usage='.self::$usage : '';

        // set the locale from the CMS locale
        if (self::$usage != 'framework') {
            $app['translator']->setLocale($app['session']->get('CMS_LOCALE', 'en'));
        }

        // gather some information
        $this->gatherInformation();
    }

    /**
     * Gather information about the locales sources, translations ...
     *
     */
    protected function gatherInformation()
    {
        $count = $this->i18nScanFile->selectCount();
        self::$info = array(
            'last_file_modification' => $this->i18nScanFile->getLastModificationDateTime(),
            'count_registered' => $count['count_registered'],
            'count_scanned' => $count['count_scanned'],
            'locale_hits' => $count['locale_hits'],
            'duplicates' => count($this->i18nTranslation->selectDuplicates()),
            'conflicts' => $this->i18nTranslation->countConflicts(),
            'unassigned' => $this->i18nTranslationUnassigned->count()
        );
    }

    /**
     * Create the toolbar for the dialogs
     *
     * @param string $active
     * @return array
     */
    protected function getToolbar($active)
    {
        $toolbar = array();
        $tabs = array_merge(array('overview'), self::$config['translation']['locale'], array('sources', 'problems', 'about'));

        foreach ($tabs as $tab) {
            switch ($tab) {
                case 'about':
                    $toolbar[$tab] = array(
                        'name' => 'about',
                        'text' => $this->app['translator']->trans('About'),
                        'hint' => $this->app['translator']->trans('Information about the i18nEditor'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/about'.self::$usage_param,
                        'active' => ($active === 'about')
                    );
                    break;
                case 'overview':
                    $toolbar[$tab] = array(
                        'name' => 'overview',
                        'text' => $this->app['translator']->trans('Overview'),
                        'hint' => $this->app['translator']->trans('A brief summary of the translation status'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/overview'.self::$usage_param,
                        'active' => ($active === 'overview')
                    );
                    break;
                case 'sources':
                    $toolbar[$tab] = array(
                        'name' => 'sources',
                        'text' => $this->app['translator']->trans('Sources'),
                        'hint' => $this->app['translator']->trans('List of translation sources'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/sources'.self::$usage_param,
                        'active' => ($active === 'sources')
                    );
                    break;
                case 'problems':
                    if (self::$config['developer_mode']) {
                        // add this navigation only in active developer mode!
                        $toolbar[$tab] = array(
                            'name' => 'problems',
                            'text' => $this->app['translator']->trans('Problems'),
                            'hint' => $this->app['translator']->trans('Problems with the translation data'),
                            'link' => FRAMEWORK_URL.'/admin/i18n/editor/problems'.self::$usage_param,
                            'active' => ($active === 'problems')
                        );
                    }
                    break;
                default:
                    if (in_array($tab, self::$config['translation']['locale'])) {
                        $toolbar[$tab] = array(
                            'name' => strtolower($tab),
                            'text' => $tab, // no translation!
                            'hint' => $this->app['translator']->trans('Edit the locale %locale%', array(
                                '%locale%' => $this->app['translator']->trans($tab))),
                            'link' => FRAMEWORK_URL.'/admin/i18n/editor/locale/'.strtolower($tab).self::$usage_param,
                            'active' => ($active === strtolower($tab))
                        );
                    }
                    break;
            }
        }
        return $toolbar;
    }

    /**
     * Get the toolbar for the locale dialogs
     *
     * @param string $active
     * @param string $locale
     * @return multitype:multitype:string boolean NULL
     */
    protected function getToolbarLocale($active, $locale)
    {
        $toolbar = array();
        $tabs = array('summary', 'pending', 'edit');

        foreach ($tabs as $tab) {
            switch ($tab) {
                case 'summary':
                    $toolbar[$tab] = array(
                        'name' => 'summary',
                        'text' => $this->app['translator']->trans('Summary'),
                        'hint' => $this->app['translator']->trans('Summary for the locale %locale%', array(
                            '%locale%' => $this->app['translator']->trans(strtoupper($locale)))),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/locale/'.strtolower($locale).self::$usage_param,
                        'active' => ($active === 'summary')
                    );
                    break;
                case 'pending':
                    $toolbar[$tab] = array(
                        'name' => 'pending',
                        'text' => $this->app['translator']->trans('Waiting'),
                        'hint' => $this->app['translator']->trans('Locales waiting for a translation'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/locale/'.strtolower($locale).'/pending'.self::$usage_param,
                        'active' => ($active === 'pending')
                    );
                    break;
                case 'edit':
                    $toolbar[$tab] = array(
                        'name' => 'edit',
                        'text' => $this->app['translator']->trans('Edit'),
                        'hint' => $this->app['translator']->trans('Create or edit translations'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/translation/edit/id'.self::$usage_param,
                        'active' => ($active === 'edit')
                    );
                    break;
            }
        }
        return $toolbar;
    }

    /**
     * Get the toolbar for the problems dialog
     *
     * @param string $active
     * @return array
     */
    protected function getToolbarProblems($active)
    {
        $toolbar = array();
        $tabs = array('conflicts', 'unassigned', 'duplicates');

        foreach ($tabs as $tab) {
            switch ($tab) {
                case 'conflicts':
                    $toolbar[$tab] = array(
                        'name' => 'conflicts',
                        'text' => $this->app['translator']->trans('Conflicts'),
                        'hint' => $this->app['translator']->trans('Translations which causes a conflict'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/problems/conflicts'.self::$usage_param,
                        'active' => ($active === 'conflicts')
                    );
                    break;
                case 'unassigned':
                    $toolbar[$tab] = array(
                        'name' => 'unassigned',
                        'text' => $this->app['translator']->trans('Unassigned'),
                        'hint' => $this->app['translator']->trans('Translations which are not assigned to any files'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/problems/unassigned'.self::$usage_param,
                        'active' => ($active === 'unassigned')
                    );
                    break;
                case 'duplicates':
                    $toolbar[$tab] = array(
                        'name' => 'duplicates',
                        'text' => $this->app['translator']->trans('Duplicates'),
                        'hint' => $this->app['translator']->trans('Duplicate translations'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/problems/duplicates'.self::$usage_param,
                        'active' => ($active === 'duplicates')
                    );
                    break;
            }
        }
        return $toolbar;
    }


    /**
     * Show the about dialog for flexContent
     *
     * @return string rendered dialog
     */
    public function ControllerAbout(Application $app)
    {
        $this->initialize($app);

        $extension = $this->app['utils']->readJSON(MANUFAKTUR_PATH.'/Basic/extension.i18n.editor.json');

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/about.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('about'),
                'extension' => $extension
            ));
    }

    /**
     * Show the overview dialog for the i18nEditor
     *
     * @return string \Twig_Template
     */
    protected function showOverview()
    {
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/overview.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('overview'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'info' => self::$info
            ));
    }

    /**
     * Scan the complete kitFramework and update all i18n data tables
     *
     * @param Application $app
     * @return string
     */
    public function ControllerScan(Application $app)
    {
        $this->initialize($app);

        // process PHP files
        $this->findPHPfiles();

        if (false !== ($files = $this->i18nScanFile->selectRegistered('PHP'))) {
            foreach ($files as $file) {
                $this->parsePHPfile($file['file_path']);
            }
        }

        // process Twig files
        $this->findTwigFiles();

        if (false !== ($files = $this->i18nScanFile->selectRegistered('TWIG'))) {
            foreach ($files as $file) {
                $this->parseTwigFile($file['file_path']);
            }
        }

        // remove widowed locale sources from the database
        $widowed = $this->i18nSource->selectWidowed();
        if (is_array($widowed)) {
            self::$translation_deleted = array();
            foreach ($widowed as $widow) {
                if (!in_array($widow['locale_source'], self::$config['translation']['system'])) {
                    $this->i18nSource->delete($widow['locale_id']);
                    self::$translation_deleted[] = $widow['locale_id'];
                }
            }
        }

        // update the translation table
        $this->updateTranslationTable();

        // find the locale files
        $this->findLocaleFiles();

        // check if conflicts are solved
        $this->checkConflicts();

        self::$script_execution_time = sprintf('%01.2f', (microtime(true) - self::$script_start));
        $this->setAlert('Executed search run in %seconds% seconds.',
            array('%seconds%' => self::$script_execution_time), self::ALERT_TYPE_SUCCESS);
        $this->gatherInformation();
        return $this->showOverview();
    }

    /**
     * The general controller for the localeEditor
     *
     * @param Application $app
     * @return string
     */
    public function ControllerOverview(Application $app)
    {
        $this->initialize($app);
        return $this->showOverview();
    }

    /**
     * Controller for all Locale start dialogs
     *
     * @param Application $app
     * @param string $locale
     */
    public function ControllerLocale(Application $app, $locale)
    {
        $this->initialize($app);



        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/locale.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar(strtolower($locale)),
                'toolbar_locale' => $this->getToolbarLocale('summary', $locale),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'info' => self::$info
            ));
    }

    public function ControllerLocalePending(Application $app, $locale)
    {
        $this->initialize($app);

        if (false === ($pendings = $this->i18nTranslation->selectPendings($locale))) {
            $this->setAlert('There exists no pending translations for the locale %locale%.',
                array('%locale%' => $this->app['translator']->trans(strtoupper($locale))),
                self::ALERT_TYPE_INFO);
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/locale.pending.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar(strtolower($locale)),
                'toolbar_locale' => $this->getToolbarLocale('pending', $locale),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'info' => self::$info,
                'pendings' => $pendings
            ));
    }

    /**
     * Return the Sources Overview dialog
     *
     */
    protected function showSources()
    {
        if (false === ($list = $this->i18nSource->selectAll(
            self::$config['editor']['sources']['list']['order_by'],
            self::$config['editor']['sources']['list']['order_direction']))) {
            $this->setAlert('No sources available, please <a href="%url%">start a search run</a>!',
                array('%url%' => FRAMEWORK_URL.'/admin/i18n/editor/scan'.self::$usage_param), self::ALERT_TYPE_WARNING);
        }
        $sources = array();

        if (is_array($list)) {
            foreach ($list as $source) {
                $sources[$source['locale_id']] = $source;
                $sources[$source['locale_id']]['references'] =
                $this->i18nReference->countReferencesForLocaleID($source['locale_id']);
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/sources.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('sources'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'sources' => $sources
            ));
    }

    /**
     * Controller to view the locale sources
     *
     * @param Application $app
     */
    public function ControllerSources(Application $app)
    {
        $this->initialize($app);
        return $this->showSources();
    }

    /**
     * Get the form for the Locale Reference
     *
     * @param array $data
     * @return FormFactoryBuilder
     */
    protected function getReferenceForm($data=array())
    {
        return $this->app['form.factory']->createBuilder('form')
            ->add('locale_id', 'hidden', array(
                'data' => isset($data['locale_id']) ? $data['locale_id'] : -1
            ))
            ->add('locale_locale', 'hidden', array(
                'data' => isset($data['locale_locale']) ? $data['locale_locale'] : ''
                ))
            ->add('locale_source', 'hidden', array(
                'data' => isset($data['locale_source']) ? $data['locale_source'] : ''
            ))
            ->add('locale_remark', 'textarea', array(
                'data' => isset($data['locale_remark']) ? $data['locale_remark'] : '',
                'required' => false
            ))
            ->getForm();
    }

    /**
     * Controller to inspect the locale source details and add an optional remark
     *
     * @param Application $app
     * @param integer $locale_id
     */
    public function ControllerSourcesDetail(Application $app, $locale_id)
    {
        $this->initialize($app);

        if (false === ($source = $this->i18nSource->select($locale_id))) {
            $this->setAlert('The record with the ID %id% does not exists!',
                array('%id%' => $locale_id), self::ALERT_TYPE_DANGER);
        }

        $references = array();
        if (is_array($source)) {
            if (false === ($files = $this->i18nReference->selectReferencesForLocaleID($locale_id))) {
                $this->setAlert('There exists no references for the locale source with the id %locale_id%.',
                    array('%locale_id%' => $locale_id), self::ALERT_TYPE_WARNING);
            }
            else {
                foreach ($files as $file) {
                    $references[$file['file_id']] = $file;
                    $references[$file['file_id']]['file_path'] = realpath($file['file_path']);
                    $references[$file['file_id']]['basename'] = basename(realpath($file['file_path']));
                }
            }
        }

        $form = $this->getReferenceForm($source);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/sources.detail.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('sources'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'source' => $source,
                'references' => $references,
                'form' => $form->createView()
            ));
    }

    /**
     * Controller to check the Detail form and update the data record
     *
     * @param Application $app
     */
    public function ControllerSourcesDetailCheck(Application $app)
    {
        $this->initialize($app);

        $form = $this->getReferenceForm();

        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();
            $remark = !is_null($data['locale_remark']) ? $data['locale_remark'] : '';
            $this->i18nSource->update($data['locale_id'], array('locale_remark' => $remark));
            $this->setAlert('The record with the ID %id% was successfull updated.',
                array('%id%' => $data['locale_id']), self::ALERT_TYPE_SUCCESS);
            return $this->showSources();
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(), self::ALERT_TYPE_DANGER);
            return $this->ControllerSourcesDetail($app, -1);
        }
    }

    /**
     * Controller to show the translation conflicts
     *
     * @param Application $app
     */
    public function ControllerProblemsConflicts(Application $app)
    {
        $this->initialize($app);

        $conflict_translations = $this->i18nTranslation->selectConflicts();
        $conflicts = array();
        if (is_array($conflict_translations)) {
            foreach ($conflict_translations as $conflict) {
                $files = $this->i18nTranslationFile->selectByLocaleID($conflict['locale_id'], $conflict['locale_locale']);
                $conflict['conflict_files'] = $files;
                $conflicts[] = $conflict;
            }
        }

        if (empty($conflicts)) {
            $this->setAlert('There exists no conflicts.', array(), self::ALERT_TYPE_INFO);
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/problems.conflicts.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('problems'),
                'toolbar_problems' => $this->getToolbarProblems('conflicts'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'info' => self::$info,
                'conflicts' => $conflicts
            ));
    }

    /**
     * Controller to show the unassigned translations
     *
     * @param Application $app
     */
    public function ControllerProblemsUnassigned(Application $app)
    {
        $this->initialize($app);

        if (false === ($unassigneds = $this->i18nTranslationUnassigned->selectAll())) {
            $this->setAlert('There exists no unassigned translations.', array(), self::ALERT_TYPE_INFO);
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/problems.unassigned.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('problems'),
                'toolbar_problems' => $this->getToolbarProblems('unassigned'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'info' => self::$info,
                'unassigneds' => $unassigneds
            ));
    }

    /**
     * Controller to show duplicate translations
     *
     * @param Application $app
     */
    public function ControllerProblemsDuplicates(Application $app)
    {
        $this->initialize($app);

        if (false === ($duplicate_translations = $this->i18nTranslation->selectDuplicates())) {
            $this->setAlert('There exists no duplicate translations.', array(), self::ALERT_TYPE_INFO);
        }
        $duplicates = array();
        if (is_array($duplicate_translations)) {
            foreach ($duplicate_translations as $duplicate) {
                $files = $this->i18nTranslationFile->selectByLocaleID($duplicate['locale_id'], $duplicate['locale_locale']);
                $duplicate['duplicate_files'] = $files;
                $duplicates[] = $duplicate;
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/problems.duplicates.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('problems'),
                'toolbar_problems' => $this->getToolbarProblems('duplicates'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'info' => self::$info,
                'duplicates' => $duplicates
            ));
    }

    protected function getTranslationForm($data)
    {
        $form = $this->app['form.factory']->createBuilder('form')
            ->add('translation_id', 'hidden', array(
                'data' => isset($data['translation_id']) ? $data['translation_id'] : -1
            ))
            ->add('locale_id', 'hidden', array(
                'data' => isset($data['locale_id']) ? $data['locale_id'] : -1
            ))
            ->add('locale_locale', 'hidden', array(
                'data' => isset($data['locale_locale']) ? $data['locale_locale'] : ''
                ))
            ->add('translation_status', 'hidden', array(
                'data' => isset($data['translation_status']) ? $data['translation_status'] : 'PENDING'
            ))
            ->add('locale_source', 'hidden', array(
                'data' => isset($data['locale_source']) ? $data['locale_source'] : ''
            ))
            ->add('translation_text', 'textarea', array(
                'data' => isset($data['translation_text']) ? $data['translation_text'] : ''
            ))
            ->add('locale_remark', 'textarea', array(
                'data' => isset($data['locale_remark']) ? $data['locale_remark'] : '',
                'required' => false
            ));

        if (self::$config['developer_mode']) {

        }
        else {

        }

        return $form->getForm();
    }

    public function ControllerTranslationEdit(Application $app, $translation_id)
    {
        $this->initialize($app);

        if (false === ($translation = $this->i18nTranslation->select($translation_id))) {
            $this->setAlert('The record with the ID %id% does not exists!',
                array('%id%' => $translation_id), self::ALERT_TYPE_DANGER);
            return $this->promptAlertFramework();
        }

        if (false === ($source = $this->i18nSource->select($translation['locale_id']))) {
            $this->setAlert('The record with the ID %id% does not exists!',
                array('%id%' => $translation['locale_id']), self::ALERT_TYPE_DANGER);
        }

        $references = array();
        if (is_array($source)) {
            if (false === ($files = $this->i18nReference->selectReferencesForLocaleID($translation['locale_id']))) {
                $this->setAlert('There exists no references for the locale source with the id %locale_id%.',
                    array('%locale_id%' => $translation['locale_id']), self::ALERT_TYPE_WARNING);
            }
            else {
                foreach ($files as $file) {
                    $references[$file['file_id']] = $file;
                    $references[$file['file_id']]['file_path'] = realpath($file['file_path']);
                    $references[$file['file_id']]['basename'] = basename(realpath($file['file_path']));
                }
            }
        }

        $form = $this->getTranslationForm($translation);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/translation.edit.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('problems'),
                'toolbar_locales' => $this->getToolbarLocale('edit', $translation['locale_locale']),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'info' => self::$info,
                'form' => $form->createView()
            ));
    }
}
