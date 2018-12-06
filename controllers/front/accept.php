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

class PayseraAcceptModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!$this->module->active) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        $projectID         = Configuration::get('PAYSERA_PROJECT_ID');
        $projectPassword   = Configuration::get('PAYSERA_PROJECT_PASSWORD');

        $response = WebToPay::validateAndParseData($_REQUEST, $projectID, $projectPassword);

        $idOrder = $response['orderid'];
        $order = new Order($idOrder);
        $customer = $this->context->customer;

        if (!Validate::isLoadedObject($customer) ||
            !Validate::isLoadedObject($order)
        ) {
            Tools::redirect($this->context->link->getPageLink('order'));
        }

        $params = [
            'id_cart' => $order->id_cart,
            'id_module' => $this->module->id,
            'id_order' => $order->id,
            'key' => $customer->secure_key,
        ];
        setcookie("Paysera_Order_id", "", time()-3600);
        Tools::redirect($this->context->link->getPageLink('order-confirmation', null, null, $params));
    }
}
