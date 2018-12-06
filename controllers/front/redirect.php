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
define('_PS_PAYSERA_FORCE_LOGIN_', (bool) Configuration::get('PAYSERA_FORCE_LOGIN'));

class PayseraRedirectModuleFrontController extends ModuleFrontController
{
    public $auth = _PS_PAYSERA_FORCE_LOGIN_;

    public $ssl = true;

    /**
     * @var Paysera
     */
    public $module;

    /**
     * Process redirection to Paysera system
     */
    public function postProcess()
    {
        $this->processValidations();

        $cart = $this->context->cart;

        $this->module->validateOrder(
            $cart->id,
            (int) Configuration::get('PAYSERA_ORDER_STATE_ID'),
            $cart->getOrderTotal(),
            $this->module->displayName,
            null,
            [],
            $cart->id_currency,
            false,
            $this->context->customer->secure_key
        );


        $paymentData = $this->collectPaymentData();

        if (null === $paymentData) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        $request = WebToPay::buildRequest($paymentData);
        $paymentUrl = WebToPay::getPaymentUrl().'?'.http_build_query($request);

        Tools::redirect($paymentUrl);
    }

    /**
     * Collect payment information from order
     *
     * @return array|null
     */
    protected function collectPaymentData()
    {
        $projectID       = Configuration::get('PAYSERA_PROJECT_ID');
        $projectPassword = Configuration::get('PAYSERA_PROJECT_PASSWORD');
        $testingMode     = Configuration::get('PAYSERA_TESTING_MODE');

        $cart     = $this->context->cart;
        $order    = Order::getByCartId($cart->id);
        $currency = new Currency($order->id_currency);
        $address  = new Address($order->id_address_invoice);
        $country  = new Country($address->id_country);
        $state    = new State($address->id_state);
        $customer = $this->context->customer;
        setcookie("Paysera_Order_id",  $order->id, time()+3600);
        $data = [
            'projectid'     => $projectID,
            'sign_password' => $projectPassword,
            'orderid'       => $order->id,
            'amount'        => $cart->getOrderTotal() * 100,
            'currency'      => $currency->iso_code,
            'country'       => strtoupper($country->iso_code),
            'accepturl'     => $this->context->link->getModuleLink($this->module->name, 'accept'),
            'cancelurl'     => $this->context->link->getModuleLink($this->module->name, 'cancel'),
            'callbackurl'   => $this->context->link->getModuleLink($this->module->name, 'callback'),
            'test'          => (int) $testingMode,
            'payment'       => Tools::getValue('paysera_payment_method'),
            'p_firstname'   => $customer->firstname,
            'p_lastname'    => $customer->lastname,
            'p_email'       => $customer->email,
            'p_street'      => $address->address1,
            'p_city'        => $address->city,
            'p_state'       => $state->iso_code,
            'p_zip'         => $address->postcode,
            'p_countrycode' => $country->iso_code,
            'lang'          => $this->getPayseraLangCode(),
        ];

        return $data;
    }

    /**
     * Get language code which will be sent to paysera
     */
    protected function getPayseraLangCode()
    {
        $langISO = $this->context->language->iso_code;

        switch ($langISO) {
            case 'lt':
                return 'LIT';
            case 'lv':
                return 'LAV';
            case 'ee':
                return 'EST';
            case 'ru':
                return 'RUS';
            case 'de':
                return 'GER';
            case 'pl':
                return 'POL';
            default:
            case 'en':
                return 'ENG';
        }
    }

    /**
     * Process validations (cart, module, currencies and etc.)
     */
    protected function processValidations()
    {
        $cart = $this->context->cart;

        if ($cart->id_customer == 0 ||
            $cart->id_address_delivery == 0 ||
            $cart->id_address_invoice == 0
        ) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        if (!$this->module->active ||
            !$this->module->checkCurrency()
        ) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        $authorized = false;
        $paymentModules = Module::getPaymentModules();

        foreach ($paymentModules as $module) {
            if ($module['name'] == $this->module->name) {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            $this->errors[] = $this->module->l('This payment method is not available.', 'redirect');
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }
    }
}
