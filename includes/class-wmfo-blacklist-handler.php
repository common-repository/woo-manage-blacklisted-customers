<?php
/**
 *
 *Handler class to update the blacklisted settings
 *Show the message in checkout page
 */

if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('WMFO_Blacklist_Handler')) {
    class WMFO_Blacklist_Handler {

        private function get_setting($key, $default = '') {
            return get_option($key) ? get_option($key) : $default;
        }

        private function get_blacklists() {
            return array(
                'prev_black_list_ips'    => self::get_setting('wmfo_black_list_ips'),
                'prev_black_list_phones' => self::get_setting('wmfo_black_list_phones'),
                'prev_black_list_emails' => self::get_setting('wmfo_black_list_emails'),
            );

        }

        private function update_blacklist($key, $pre_values, $to_add) {
            if ($pre_values === false || $pre_values == '') {
                $new_values = $to_add;
            } else {
                $new_values = !substr_count($pre_values, $to_add) ? $pre_values . ', ' . $to_add : $pre_values;
            }
            update_option($key, $new_values);
        }
        public static function init($customer = array(), $order = null) {
            $prev_blacklisted_data = self::get_blacklists();
            if (empty($customer) || !$customer) {
                return false;
            }

            self::update_blacklist('wmfo_black_list_ips', $prev_blacklisted_data['prev_black_list_ips'], $customer['ip_address']);
            self::update_blacklist('wmfo_black_list_phones', $prev_blacklisted_data['prev_black_list_phones'], $customer['billing_phone']);
            self::update_blacklist('wmfo_black_list_emails', $prev_blacklisted_data['prev_black_list_emails'], $customer['billing_email']);

            //handle the cancelation of order
            if (null !== $order) {
                self::cancel_order($order);
            }

            return true;
        }

        private function cancel_order($order) {
            $order_note = apply_filters('wmfo_cancel_order_note', esc_html__('Order details blacklisted for future checkout.', 'woo-manage-fraud-orders'), $order);

            //Set the order status to Canceled
            if (!$order->has_status('cancelled')) {
                $order->update_status('cancelled', $order_note);
            }
        }

        public static function show_blocked_message() {
            $default_notice          = esc_html__('Sorry, You are blocked from checking out.', 'woo-manage-fraud-orders');
            $wmfo_black_list_message = self::get_setting('wmfo_black_list_message', $default_notice);

            //with some reason, get_option with default value not working

            if (!wc_has_notice($wmfo_black_list_message)) {
                wc_add_notice($wmfo_black_list_message, 'error');
            }
        }
    }
}