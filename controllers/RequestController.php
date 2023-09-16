<?php

class RequestController
{
    public $instance;
    public function __construct()
    {
        $this->instance = EasyLogin::get_instance();
        add_action('init', array($this, 'internal_login'));
    }

    function internal_login()
    {
        if (!isset($_GET[$this->instance->plugin_prefix . 'to'])) {
            return;
        }

        // check if user is logged in if not then redirect to login page

        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url());
            exit;
        }


        $token = $_GET[$this->instance->plugin_prefix . 'to'];

        $get_current_user = wp_get_current_user();

        // get current user id

        $user_id = $get_current_user->ID;



        global $wpdb;
        $table_name = $wpdb->prefix . rtrim($this->instance->plugin_prefix, "_");
        $token = $_GET[$this->instance->plugin_prefix . 'to'];
        $row = $wpdb->get_row("SELECT * FROM $table_name WHERE token = '$token'");

        if ($row) {
            // wp_die('request found to user id ' . $get_current_user->display_name);
            // set user id to request table
            $updated = $wpdb->update(
                $table_name,
                array(
                    'wp_user_id' => $user_id,
                    'status' => 1
                ),
                array(
                    'token' => $token
                )
            );

            if (!$updated) {
                wp_die($wpdb->last_error);
            }

            // return to home page
            wp_redirect(home_url());
        } else {
            wp_die('token not found');
        }
    }
}

new RequestController;
