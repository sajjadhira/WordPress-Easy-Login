<?php

use chillerlan\QRCode\QRCode;

class LoginController
{
    protected $instance;

    public function __construct()
    {
        $this->instance = EasyLogin::get_instance();
        $views = $this->instance->plugin_dir . $this->instance->viewsDir . DIRECTORY_SEPARATOR;
        // get current file 
        $file = basename(__FILE__);
        $loginFile = str_replace($this->instance->ControllerType, $this->instance->viewType, $file);
        $loginPath = $views . $loginFile;
        if (file_exists($loginPath))
            require_once($loginPath);
    }

    public static function generateQR($id = null, $direction = 'to')
    {
        $instance = EasyLogin::get_instance();
        $qrLib = $instance->plugin_dir . DIRECTORY_SEPARATOR . 'libs/vendor/autoload.php';
        if (file_exists($qrLib)) {
            require_once($qrLib);
        } else {
            wp_die('QR library not found');
        }

        if (is_null($id)) {
            $hashData =  time() . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'];
        } else {
            $hashData = time() . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . $id;
        }
        $hash = hash('sha256', $hashData);
        $url = site_url('?' . $instance->plugin_prefix . $direction . '=' . $hash);
        $qr = (new QRCode)->render($url);
        // add this hash to database

        $dataDB = array(
            'token' => $hash,
            'browser' => $_SERVER['HTTP_USER_AGENT'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'created_at' => current_time('mysql', 1)
        );
        if (!is_null($id)) {
            $dataDB['wp_user_id'] = $id;
        }
        global $wpdb;
        $table_name = $wpdb->prefix . rtrim(EasyLogin::get_instance()->plugin_prefix, "_");
        $dataDB = $wpdb->insert(
            $table_name,
            $dataDB
        );

        if (!$dataDB) {
            wp_die($wpdb->last_error);
        }

        // return $qr and $url;
        return array(
            'qr' => $qr,
            'url' => $url,
            'token' => $hash
        );
    }
}
new LoginController;
