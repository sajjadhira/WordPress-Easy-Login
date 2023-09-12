<?php
!defined('WPINC') && die;


class Init
{

    protected $instance;

    public function __construct()
    {
        $this->instance = EasyLogin::get_instance();
        // load plugin text domain
        add_action('init', array($this, 'load_plugin_textdomain_callback'));
        register_activation_hook($this->instance->file, array($this, 'activate_hook'));
        register_deactivation_hook($this->instance->file, array($this, 'deactivate_hook'));
    }

    public function load_plugin_textdomain_callback()
    {
        load_plugin_textdomain($this->instance->plugin_slug, $this->instance->plugin_dir . '/languages');
    }
    public function activate_hook()
    {
        // create tables
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix;
        $table_name = $prefix . rtrim($this->instance->plugin_prefix, "_");
        $sql = "CREATE TABLE `$table_name` (
            `id` bigint NOT NULL AUTO_INCREMENT,
            `token` varchar(255) NOT NULL,
            `wp_user_id` bigint NULL,
            `status` int DEFAULT 0,
            `created_at` datetime NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function deactivate_hook()
    {
        // drop tables
        global $wpdb;
        $prefix = $wpdb->prefix;
        $table_name = $prefix . rtrim($this->instance->plugin_prefix, "_");
        $sql = "DROP TABLE IF EXISTS `$table_name`;";
        $wpdb->query($sql);
    }
}

new Init;
