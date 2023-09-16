<?php
class AdminController
{
    public $instance;
    public function __construct()
    {
        $this->instance = EasyLogin::get_instance();
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    function admin_menu()
    {
        add_menu_page(
            'Easy Login',
            'Easy Login',
            'manage_options',
            'easy-login',
            array($this, 'easy_login_callback'),
            'dashicons-admin-network',
            100
        );
    }

    function easy_login_callback()
    {
        $views = $this->instance->plugin_dir . $this->instance->viewsDir . DIRECTORY_SEPARATOR;
        $file = basename(__FILE__);
        $loginFile = str_replace($this->instance->ControllerType, $this->instance->viewType, $file);
        $loginPath = $views . $loginFile;
        if (file_exists($loginPath))
            require_once($loginPath);
    }
}

new AdminController;
