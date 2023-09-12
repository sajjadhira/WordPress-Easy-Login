<?php
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
        // do something
    }

    public function deactivate_hook()
    {
        // do something
    }
}

new Init;
