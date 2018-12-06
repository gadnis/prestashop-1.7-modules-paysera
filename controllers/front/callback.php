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

class PayseraCallbackModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!$this->module->active) {
            exit;
        }

        $projectID         = Configuration::get('PAYSERA_PROJECT_ID');
        $projectPassword   = Configuration::get('PAYSERA_PROJECT_PASSWORD');
        $payseraOrderState = (int) Configuration::get('PAYSERA_ORDER_STATE_ID');
        $paymentAcceptedOrderStateID = (int) Configuration::get('PS_OS_PAYMENT');

        try {
            $response = WebToPay::validateAndParseData($_REQUEST, $projectID, $projectPassword);

            if ($response['status'] == 1) {
                $idOrder = $response['orderid'];
                $responseAmount = (int) $response['payamount'];
                $responseCurrency = $response['paycurrency'];

                $order = new Order($idOrder);
                if (!Validate::isLoadedObject($order) ||
                    $order->getCurrentState() != $payseraOrderState
                ) {
                    exit('OK');
                }

                $orderAmount = (int) $order->getOrdersTotalPaid() * 100;
                $orderCurrency = Currency::getCurrency($order->id_currency);

                if ($responseAmount < $orderAmount) {
                    exit(sprintf('Bad amount: %s', $responseAmount));
                }

                if ($responseCurrency != $orderCurrency['iso_code']) {
                    exit(sprintf('Bad currency: %s', $responseCurrency));
                }

                $history = new OrderHistory();
                $history->id_order = $order->id;
                $history->changeIdOrderState($paymentAcceptedOrderStateID, $order->id);
                $history->addWithemail();

                exit('OK');
            }
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        exit('Not paid');
    }
}
