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
require_once dirname(__FILE__) . '/../../config/config.inc.php';
require_once dirname(__FILE__) . '/../../init.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

$response = [];

// Calling this function will init module data
$module_list = Module::getModulesOnDisk();

foreach ($module_list as $module) {
    if ($module->name != 'basicprestashopmodule') {
        continue;
    }

    if (Module::initUpgradeModule($module)) {
        $instance = Module::getInstanceByName('basicprestashopmodule');
        $instance->runUpgradeModule();

        Module::upgradeModuleVersion('basicprestashopmodule', $module->version);

        $response = [
            'status' => 'success',
            'message' => 'Module upgraded! New version: ' . $instance->version . '!<br>',
        ];

        $errors = $instance->getErrors();
        if (count($errors)) {
            $response = [
                'status' => 'error',
                'message' => 'Errors found!',
                'errors' => $errors,
            ];
        }
    } elseif (Module::getUpgradeStatus('basicprestashopmodule')) {
        $response = [
            'status' => 'error',
            'message' => 'Upgrade failed!',
        ];
    }
}

exit(json_encode($response));
