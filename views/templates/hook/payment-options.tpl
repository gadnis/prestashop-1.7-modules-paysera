
<section id="payseraAdditionalInformation">
     {* EDBOX - pridedame galimybe rodyti papildoma info *}
    <p>{l s='Būsite nukreipti į pasirinkto apmokėjimo būdo sistemą.' mod='paysera'}</p>
    <p>
        <strong>{l s='Dėmesio' mod='paysera'} </strong>
        {l s='Lizingai teikiami 7:00 - 22:00' mod='paysera'}
    </p>
    {* / EDBOX *}
    <div class="form-group row">
        <div class="col-sm-12 form-control-label clearfix">
            <label class="float-left">
                {l s='Select payment country' mod='paysera'}
            </label>
        </div>
        <div class="col-sm-6">
            <select class="form-control form-control-select js-paysera-payment-country" title="{l s='Payment country' mod='paysera'}">
                {foreach $payMethods as $country}
                    <option value="{$country->getCode()}"
                            {if $country->getCode() == $defaultCountry} selected="selected" {/if}
                    >
                        {$country->getTitle()}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>

    <hr>

    {foreach $payMethods as $country}
        <fieldset id="payseraPaymentMethods_{$country->getCode()}" class="form-group row js-paysera-payment-methods" {if $country->getCode() != $defaultCountry}style="display:none"{/if}>
            {foreach $country->getGroups() as $group}
                <legend class="col-form-legend col-sm-12">{$group->getTitle()}</legend>
                <div class="col-sm-12">
                {foreach $group->getPaymentMethods() as $paymentMethod}
                    <div class="form-check">
                        <label class="form-check-label">
                            <input class="form-check-input js-paysera-payment-method"
                                   type="radio"
                                   name="paysera_payment_method_input"
                                   value="{$paymentMethod->getKey()}"
                                   style="height: 80%;"
                            >
                            <img src="{$paymentMethod->getLogoUrl()}"
                                 title="{$paymentMethod->getTitle()}"
                                 alt="{$paymentMethod->getTitle()}"
                            >
                        </label>
                    </div>
                {/foreach}
                </div>
            {/foreach}
        </fieldset>
    {/foreach}
</section>
