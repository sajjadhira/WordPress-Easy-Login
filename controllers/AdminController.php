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
            __(ucfirst($this->instance->plugin_name), $this->instance->plugin_slug),
            __(ucfirst($this->instance->plugin_name), $this->instance->plugin_slug),
            'manage_options',
            $this->instance->plugin_slug,
            array($this, 'easy_login_callback'),
            'dashicons-admin-network',
            100
        );

        $submenus = ['settings' => 'Settings'];
        foreach ($submenus as $key => $value) {
            add_submenu_page(
                $this->instance->plugin_name,
                __($value, $this->instance->plugin_name),
                __($value, $this->instance->plugin_name),
                'manage_options',
                $this->instance->plugin_prefix .  $key,
                $this->instance->plugin_prefix . $key
            );
        }
        // add sub menu page

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
