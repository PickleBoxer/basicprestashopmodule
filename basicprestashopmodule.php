<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use BitbucketUpdateFetcher\UpdateFetcher;

class BasicPrestaShopModule extends Module
{
    protected $config_form = false;

    /**
     * @var UpdateFetcher
     */
    private $fetcher;

    public function __construct()
    {
        $this->name = 'basicprestashopmodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        // Initialize the update fetcher
        $this->fetcher = new UpdateFetcher(__DIR__ . '/temp', __DIR__ . '/..', 60);
        $this->fetcher->setCurrentVersion($this->version);
        $this->fetcher->setWorkspace('aerdigital');
        $this->fetcher->setRepoSlug('test');
        $this->fetcher->setAccessToken('ATCTT3xFfGN0ewdGjzds35DXEnCf6nGk1EEXIkpQsz4vGkDnHpbZONnFqVBe_nBp0t_YhWD4iU-iogcF8VgJskDpPsEMkZsUq43md003HwYlx4tKXlhRoWnY_aTNpMCsQSGSuKNV8VXSQ7L1bYHrmsGpqKkUKN5sVH3gMUeoD8yu1HNS51LzxDw=DE6C3C40');

        // Custom logger (optional)
        $logger = new \Monolog\Logger('default');
        $logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . '/update.log'));
        $this->fetcher->setLogger($logger);

        parent::__construct();

        $this->displayName = $this->l('Basic PrestaShop Module');
        $this->description = $this->l('Basic PrestaShop Module');

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('BASICPRESTASHOPMODULE_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayBanner');
    }

    public function uninstall()
    {
        Configuration::deleteByName('BASICPRESTASHOPMODULE_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /*
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitBasicPrestaShopModuleModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign([
            'module_dir', $this->_path,
            'controller_link' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name,
            'upgrade_ajax_link' => __PS_BASE_URI__ . 'modules/' . $this->name . '/upgrade-ajax.php',
        ]);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBasicPrestaShopModuleModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'BASICPRESTASHOPMODULE_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'BASICPRESTASHOPMODULE_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ],
                    [
                        'type' => 'password',
                        'name' => 'BASICPRESTASHOPMODULE_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return [
            'BASICPRESTASHOPMODULE_LIVE_MODE' => Configuration::get('BASICPRESTASHOPMODULE_LIVE_MODE'),
            'BASICPRESTASHOPMODULE_ACCOUNT_EMAIL' => Configuration::get('BASICPRESTASHOPMODULE_ACCOUNT_EMAIL'),
            'BASICPRESTASHOPMODULE_ACCOUNT_PASSWORD' => Configuration::get('BASICPRESTASHOPMODULE_ACCOUNT_PASSWORD'),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookDisplayBanner()
    {
        /* Place your code here. */
    }

    public function ajaxProcessFetchUpdates()
    {
        // Check for a new update
        if ($this->fetcher->checkUpdate() === false) {
            $response = [
                'status' => 'success',
                'message' => $this->fetcher->checkUpdate(),
            ];
            exit(json_encode($response));
        }

        if ($this->fetcher->newVersionAvailable()) {
            $response = [
                'status' => 'success',
                'message' => 'Updates available',
                'updates' => $this->fetcher->getLatestVersion(),
            ];
        } else {
            $response = [
                'status' => 'success',
                'message' => 'No updates available',
            ];
        }

        exit(json_encode($response));
    }

    public function ajaxProcessInstallUpdates()
    {
        $result = $this->fetcher->update(false);

        if ($result === true) {
            $response = [
                'status' => 'success',
                'message' => 'Update installed! Now Upgrading module...',
            ];
        } else {
            $response = [
                'status' => 'success',
                'message' => 'Update failed: ' . $result . '!<br>',
            ];
        }

        exit(json_encode($response));
    }
}
