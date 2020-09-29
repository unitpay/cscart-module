<div class="control-group">
    <label class="control-label" for="unitpay_domain">{__("unitpay_domain")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][unitpay_domain]" id="unitpay_domain" value="{$processor_params.unitpay_domain}" class="input-text" size="60" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="unitpay_public_key">{__("unitpay_public_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][unitpay_public_key]" id="unitpay_public_key" value="{$processor_params.unitpay_public_key}" class="input-text" size="60" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="unitpay_secret_key">{__("unitpay_secret_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][unitpay_secret_key]" id="unitpay_secret_key" value="{$processor_params.unitpay_secret_key}" class="input-text" size="60" />
    </div>
</div>