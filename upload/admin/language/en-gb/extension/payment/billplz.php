<?php

/**
 * Billplz OpenCart Plugin
 * 
 * @package Payment Gateway
 * @author Wan Zulkarnain <wan@billplz.com>
 * @version 3.1
 */
// Versioning
$_['billplz_ptype'] = "OpenCart";
$_['billplz_pversion'] = "2.3";

// Heading
$_['heading_title'] = 'Billplz. Fair Payment Software';

// Text
$_['text_payment'] = 'Payment';
$_['text_success'] = 'Success: You have modified Billplz Malaysia Online Payment Gateway account details!';
$_['text_edit'] = 'Edit Billplz';
$_['text_billplz'] = '<a onclick="window.open(\'http://www.billplz.com/\');" style="text-decoration:none;"><img src="view/image/payment/billplz-logo.jpg" alt="Billplz" title="Billplz. Fair Payment Software" style="border: 0px solid #EEEEEE;" height=25 width=94/></a>';

// Entry
$_['billplz_api_key'] = 'Billplz API Secret Key';
$_['billplz_collection_id'] = 'Billplz Collection ID';
$_['billplz_x_signature'] = 'Billplz X Signature Key';
$_['entry_minlimit'] = 'Minimum Limit';
$_['entry_delivery'] = 'Delivery Notification';
$_['entry_completed_status'] = 'Completed Status';
$_['entry_pending_status'] = 'Pending Status';
$_['entry_failed_status'] = 'Failed Status';
$_['entry_geo_zone'] = 'Geo Zone';
$_['entry_status'] = 'Status';
$_['entry_sort_order'] = 'Sort Order';

// Help
$_['help_api_key'] = 'Please enter your Billplz API Key. <a href=\'https://www.billplz.com/enterprise/setting\' target=\'_blank\'>Get Your API Key</a>';
$_['help_x_signature'] = 'Please refer to your Billplz X Signature Key. MAKE SURE YOU ENABLE the X Signature Key. <a href=\'https://www.billplz.com/enterprise/setting\' target=\'_blank\'>Get Your X Signature Key</a>';
$_['help_minlimit'] = 'Set total minimum limit to enable Billplz conditionally';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify Billplz Extensions!';
$_['error_api_key'] = '<b>Billplz API Key</b> Required!';
$_['error_x_signature'] = '<b>Billplz X Signature Key</b> Required!';