<?php
!defined('WPINC') && die;

/*
Plugin Name: Easy Login
Plugin URI: http://pluginoo.com/plugins/easy-login
Description: Easy Login is a simple plugin that allows you to login user via QR Code and URL. No need to share username and password for login.
Version: 1.0
Author: Pluginoo.com
Author URI: http://pluginoo.com
License: GPL2
Text Domain: easy-login
*/
class EasyLogin
{
    public $version = '1.0';
    public $plugin_name = 'Easy Login';
    public $plugin_slug = 'easy-login';
    public $plugin_prefix = 'easy_login_';
    public $boot = 'boot';
    public $ControllerType = 'Controller';
    public $viewType = 'Component';
    public $viewsDir = 'views';
    public $ext;
    public static $instance = null;
    public $file;
    public $plugin_dir;
    public $pluginUrl;
    public $pluginURI = 'http://pluginoo.com/plugins/easy-login';

    public function __construct()
    {
        $this->pluginUrl = plugin_dir_url(__FILE__);
        $this->file = __FILE__;
        $this->plugin_dir = plugin_dir_path($this->file);
        $this->ext = strrchr(__FILE__, '.');
        $file = $this->plugin_dir . $this->boot . $this->ext;
        if (file_exists($file))
            require_once($file);
    }

    public static function get_instance()
    {
        if (null == self::$instance)
            self::$instance = new self;
        return self::$instance;
    }
}


new EasyLogin;
