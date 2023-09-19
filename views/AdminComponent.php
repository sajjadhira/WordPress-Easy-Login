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
        wp_enqueue_script('easy-login-script', $this->instance->pluginUrl . 'assets/js/easy-login-admin.js', array('jquery'), '1.0.0', true);


        // enqueue admin style and script

    }



    function adminIndex()
    {

        $site_url = site_url();
        $qrLib = $this->instance->plugin_dir . DIRECTORY_SEPARATOR . 'libs/vendor/autoload.php';
        if (file_exists($qrLib)) {
            require_once($qrLib);
        } else {
            wp_die('QR library not found');
        }


        $current_id = get_current_user_id();


        $hashData = LoginController::generateQR($current_id, 'from');
        // dump with die
        $qr = $hashData['qr'];
        $url = $hashData['url'];
        $this->token = $hashData['token'];

?>
        <!--  div wrap -->

        <div class="wrap easylogin">

            <div class="card">
                <h2><?php echo __($this->instance->plugin_name, $this->instance->plugin_slug); ?></h2>

                <div class="text-center" id="easyloginAdmin">
                    <img src="<?php echo $qr; ?>" alt="QR" id="imageURL">
                    <p>
                        <span class="d-none" id="loginUrl"><?php echo $url; ?></span>
                        <span class="d-none" id="fetechUrl"><?php echo $site_url; ?>?easy_login_from_token=<?php echo $this->token; ?></span>
                        <a href=" javascript:;" class="<?php echo $this->instance->plugin_slug; ?>" id="copyUrl"><span class="dashicons dashicons-clipboard"></span> <?php echo __("Copy Link", $this->instance->plugin_slug); ?></a>
                    </p>
                    <hr>
                    <p><?php echo __("Scan this QR with browser where you want to login or paste the url to browser where you want to login.", $this->instance->plugin_slug); ?></p>
                    <hr>
                    <p><small><?php echo __("Note: Each token lifetime is 5 minutes, so you can't use 5 minute older token or link for login.") ?></small></p>
                </div>
            </div>
        </div>
    <?php
    }


    public function easylogin_fetch_callback()
    {
    ?>
        <!-- auto ajax request to specific url each 5 sec for checking user id assinged or not -->

        <script>
            let copyText = '<span class="dashicons dashicons-clipboard"></span> <?php echo __("Copy Link", $this->instance->plugin_slug); ?>';
            let copiedText = '<span class="dashicons dashicons-saved"></span> <?php echo __("Link Copied to Clipboard", $this->instance->plugin_slug); ?>';
            let loginTitle = '<?php echo __('Login Success', $this->instance->plugin_slug); ?>';
            let loginMessage = '<?php echo __('You are successfully logged in.', $this->instance->plugin_slug); ?>';
        </script>
<?php
    }
}

new AdminComponent;
