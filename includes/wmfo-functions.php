<?php
/**
 *Global functions related fraud management
 * Function to update the block list details
 */

/**
 * Function to get the customer details
 * Billing Phone, Email and IP address
 */
function wmfo_get_customer_details_of_order($order) {
    if (!$order) {
        return false;
    }
    return array(
        'ip_address'    => $order->get_customer_ip_address(),
        'billing_phone' => $order->get_billing_phone(),
        'billing_email' => $order->get_billing_email(),
    );
}

/**
 *
 * In case woo commerce changes the function name to get IP address,
 *
 */

function wmfo_get_ip_address() {
    if (isset($_SERVER['HTTP_X_REAL_IP'])) { // WPCS: input var ok, CSRF ok.
        return sanitize_text_field(wp_unslash($_SERVER['HTTP_X_REAL_IP'])); // WPCS: input var ok, CSRF ok.
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { // WPCS: input var ok, CSRF ok.
        // Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
        // Make sure we always only send through the first IP in the list which should always be the client IP.
        return (string) rest_is_ip_address(trim(current(preg_split('/[,:]/', sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR'])))))); // WPCS: input var ok, CSRF ok.
    } elseif (isset($_SERVER['REMOTE_ADDR'])) { // @codingStandardsIgnoreLine
        return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])); // @codingStandardsIgnoreLine
    }
    return '';
}