<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * *  @license   http://opensource.org/licenses/GPL-3.0  GNU GENERAL PUBLIC LICENSE (GPL-3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Class AdminPayseraConfigurationController
 */
class AdminPayseraConfigurationController extends ModuleAdminController
{
    /**
     * @var bool Use bootstrap in admin page
     */
    public $bootstrap = true;

    /**
     * @var Paysera
     */
    public $module;

    /**
     * Initialize controller with options
     */
    public function init()
    {
        $this->initOptions();

        parent::init();
    }

    /**
     * Add custom content
     */
    public function initContent()
    {
        $moduleCurrencies = Currency::checkPaymentCurrencies($this->module->id);
        if (!count($moduleCurrencies)) {
            $this->warnings[] = $this->l('No currencies configured for this module.');
        }

        $testingMode = (bool) Configuration::get('PAYSERA_TESTING_MODE');
        if ($testingMode) {
            $this->warnings[] = $this->l('Module is in testing mode.');
        }

        parent::initContent();
    }

    /**
     * Define configuration options
     */
    protected function initOptions()
    {
        $this->fields_options = [
            'paysera_configuration' => [
                'title' => $this->l('Paysera configuration'),
                'fields' => [
                    'PAYSERA_PROJECT_ID' => [
                        'title' => $this->l('Paysera Project ID'),
                        'type' => 'text',
                        'validation' => 'isUnsignedInt',
                        'class' => 'fixed-width-xxl',
                    ],
                    'PAYSERA_PROJECT_PASSWORD' => [
                        'title' => $this->l('Paysera Project password'),
                        'type' => 'text',
                        'validation' => 'isString',
                        'class' => 'fixed-width-xxl',
                    ],
                    'PAYSERA_DISPLAY_PAYMENT_METHODS' => [
                        'title' => $this->l('Display payment methods'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                        'cast' => 'intval',
                        'class' => 'fixed-width-xxl',
                    ],
                    'PAYSERA_DEFAULT_COUNTRY' => [
                        'title' => $this->l('Default payment country'),
                        'type' => 'select',
                        'class' => 'fixed-width-xxl',
                        'list' => $this->getCountries(),
                        'identifier' => 'id',
                    ],
                    'PAYSERA_FORCE_LOGIN' => [
                        'title' => $this->l('Force User to Log In'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                        'cast' => 'intval',
                        'class' => 'fixed-width-xxl',
                    ],
                    'PAYSERA_TESTING_MODE' => [
                        'title' => $this->l('Testing mode'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                        'cast' => 'intval',
                        'class' => 'fixed-width-xxl',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    private function getCountries()
    {
        $countries = [];
        $projectID = (string) Configuration::get('PAYSERA_PROJECT_ID');

        if (!$projectID) {
            return $countries;
        }

        $methods = WebToPay::getPaymentMethodList($projectID)
            ->setDefaultLanguage($this->context->language->iso_code)
            ->getCountries();

        foreach ($methods as $method) {
            $countries[] = [
                'id' => $method->getCode(),
                'name' => $method->getTitle(),
            ];
        }

        return $countries;
    }
}
