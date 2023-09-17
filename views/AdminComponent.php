<?php

use chillerlan\QRCode\QRCode;

class AdminComponent
{
    public $instance;
    public $token;

    public function __construct()
    {
        $this->instance = EasyLogin::get_instance();
        if (isset($_GET['page']) && $_GET['page'] == 'easy-login') {
            $this->adminIndex();
            $this->admin_enqueue_scripts_callback();
            add_action('admin_footer', [$this, 'easylogin_fetch_callback']);
        }
    }


    function admin_enqueue_scripts_callback()
    {
        // adding styles tailwindcss CDN
        wp_enqueue_style('easy-login-styles', $this->instance->pluginUrl . 'assets/css/easy-login.css');
        wp_enqueue_script('easy-login-script', $this->instance->pluginUrl . 'assets/js/easy-login.js', array('jquery'), '1.0.0', true);


        // enqueue admin style and script

    }



    function adminIndex()
    {

        $qrLib = $this->instance->plugin_dir . DIRECTORY_SEPARATOR . 'libs/vendor/autoload.php';
        if (file_exists($qrLib)) {
            require_once($qrLib);
        } else {
            wp_die('QR library not found');
        }


        $current_id = get_current_user_id();
        $hash =  hash('sha256', time() . $current_id);
        $this->token = $hash;
        $url = site_url('?easy_login_from=' . $hash);
        $qr = (new QRCode)->render($url);

        global $wpdb;
        $table_name = $wpdb->prefix . rtrim($this->instance->plugin_prefix, "_");
        $wpdb->insert(
            $table_name,
            array(
                'token' => $hash,
                'browser' => $_SERVER['HTTP_USER_AGENT'],
                'ip' => $_SERVER['REMOTE_ADDR'],
                'wp_user_id' => $current_id,
                'created_at' => current_time('mysql', 1)
            )
        );

?>
        <!--  div wrap -->

        <div class="wrap easylogin">

            <div class="card">
                <h2>Easy Login</h2>

                <div class="text-center" id="easyloginAdmin">
                    <h4>Login with QR</h4>
                    <br />
                    <img src="<?php echo $qr; ?>" alt="<?php echo $url; ?>">
                    <br />
                    <p>
                        <span style="display: none;" id="loginUrl"><?php echo $url; ?></span>
                        <a href=" javascript:;" class="<?php echo $this->instance->plugin_slug; ?>" onclick="copyURL()" id="copyUrl"><span class="dashicons dashicons-clipboard"></span> Copy Link</a>
                    </p>
                    <br />
                    <p>Scan this QR with browser where you want to login or paste the url to browser where you want to login.</p>
                    <br />
                </div>
            </div>
        </div>
    <?php
    }


    public function easylogin_fetch_callback()
    {


        // get site url

        $site_url = site_url();
    ?>
        <!-- auto ajax request to specific url each 5 sec for checking user id assinged or not -->


        <script>
            let urlFetech = '<?php echo $site_url; ?>?easy_login_from_token=<?php echo $this->token; ?>';
        </script>
<?php
    }
}

new AdminComponent;
