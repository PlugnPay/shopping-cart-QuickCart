<?php
/**
 * PlugnPay Smart Screens v2 settings.
 *
 * Copy this package into the Quick.Cart root, then edit these values.
 */
$config['plugnpay_ss2_gateway_account'] = 'YOUR_GATEWAY_ACCOUNT';
$config['plugnpay_ss2_currency'] = 'USD';

/*
 * Quick.Cart payment-method ID that should launch Smart Screens v2.
 * The stock English database uses ID 3 for "On-line payment".
 */
$config['plugnpay_ss2_payment_id'] = 3;

/*
 * Required public HTTPS URL of the Quick.Cart installation, with no path
 * beyond the store root. Example: https://shop.example.com/
 */
$config['plugnpay_ss2_store_url'] = 'https://shop.example.com/';
?>
