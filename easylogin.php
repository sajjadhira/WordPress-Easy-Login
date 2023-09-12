<?php
!defined('WPINC') && die;

/*
Plugin Name: Easy Login
Plugin URI: http://pluginoo.com/plugins/easy-login
Description: Easy Login is a simple plugin that allows you to create a login form that you can place anywhere on your WordPress site using a shortcode.
Version: 1.0
Author: Pluginoo.com
Author URI: http://pluginoo.com
License: GPL2
Text Domain: easy-login
*/
class EasyLogin
{
    public $version = '1.0';
    protected $plugin_slug = 'easy-login';
    protected $plugin_prefix = 'easy_login_';
    protected $file = 'boot';
    protected $ext;
    protected static $instance = null;
    public function __construct()
    {
        // get current file extension
        $this->ext = strrchr(__FILE__, '.');
        require_once(plugin_dir_path(__FILE__) . $this->file . $this->ext);
    }
}


new EasyLogin;
