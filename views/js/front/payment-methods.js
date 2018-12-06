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

$(document).ready(function() {
    $('.js-paysera-payment-country').on('change', togglePaymentMethods);
    $('.js-paysera-payment-method').on('change', togglePaymentMethod);

    /**
     * Toggle payment methods when country is changed
     *
     * @param event
     */
    function togglePaymentMethods(event)
    {
        var value = event.target.value;

        $('.js-paysera-payment-methods').hide();
        $('#payseraPaymentMethods_' + value).show();
    }

    /**
     * Toggle payment method value
     *
     * @param event
     */
    function togglePaymentMethod(event)
    {
        $('input[name="paysera_payment_method"]').val(event.target.value);
    }
});
