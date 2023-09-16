<?php
class LoginComponent
{
    public $instance;
    public $token;
    public function __construct()
    {
        $this->instance = EasyLogin::get_instance();
        add_action('login_form', [$this, 'login_form_callback']);
        add_action('login_enqueue_scripts', [$this, 'add_login_scripts']);
        add_action('wp_footer', [$this, 'easylogin_ajax_callback']);
    }

    function add_login_scripts()
    {
        wp_enqueue_script($this->instance->plugin_prefix . 'scripts', null, array('jquery'), true);
    }

    public function login_form_callback()
    {




        // generate sha256 hash
        $this->autoDelete();

        $hash =  hash('sha256', time());
        $this->token = $hash;
        $url = site_url('?' . $this->instance->plugin_prefix . 'to=' . $hash);
        $qr = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$url}";

        // add this hash to database

        global $wpdb;
        $table_name = $wpdb->prefix . rtrim($this->instance->plugin_prefix, "_");
        $wpdb->insert(
            $table_name,
            array(
                'token' => $hash,
                'browser' => $_SERVER['HTTP_USER_AGENT'],
                'ip' => $_SERVER['REMOTE_ADDR'],
                'created_at' => current_time('mysql', 1)
            )
        );
?>

        <div style="text-align: center;font-weight:bold;">
            <h4>Login with QR</h4>
            <br />
            <img src="<?php echo $qr; ?>" alt="<?php echo $url; ?>">
            </p>
            <br />
            <p>Scan this QR with any logged in browser or browse link given bellow.</p>
            <p><a href="javascript:;" class="<?php echo $this->instance->plugin_slug; ?>" data-url="<?php echo $url; ?>">Click Here to Copy Link</a></p>
            <br />
        </div>



    <?php
    }

    public function easylogin_ajax_callback()
    {

        // get site url

        $site_url = site_url();
    ?>
        <!-- auto ajax request to specific url each 5 sec for checking user id assinged or not -->


        <script>
            jQuery(document).ready(function($) {
                setInterval(function() {
                    $.ajax({
                        url: '<?php echo $site_url; ?>?<?php echo $this->instance->plugin_prefix ?>_to_token=<?php echo $this->token; ?>',
                        type: 'GET',
                        success: function(data) {
                            if (data) {
                                window.location.href = '<?php echo home_url(); ?>';
                            }
                        }
                    });
                    console.log("ajax request");
                }, 5000);
            });
        </script>
<?php
    }

    function internal_login()
    {
        $this->autoDelete();
        global $wpdb;
        $table_name = $wpdb->prefix . rtrim($this->instance->plugin_prefix, "_");
        $token = $_GET[$this->instance->plugin_prefix . 'to'];
        $row = $wpdb->get_row("SELECT * FROM $table_name WHERE token = '$token'");

        if ($row) {
            $user = get_user_by('id', $row->user_id);
            if ($user) {
                wp_set_current_user($user->ID, $user->user_login);
                wp_set_auth_cookie($user->ID);
                do_action('wp_login', $user->user_login, $user);
                $wpdb->delete(
                    $table_name,
                    array(
                        'token' => $token,
                    )
                );
                wp_redirect(home_url());
                exit;
            }
        }
    }

    public function autoDelete()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . rtrim($this->instance->plugin_prefix, "_");

        # count number of rows

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE wp_user_id IS NULL AND created_at < '" . date('Y-m-d H:i:s', strtotime('-5 minutes')) . "'");

        if ($count < 1) {
            return;
        }
        $deleteData = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name WHERE created_at < %s",
                date('Y-m-d H:i:s', strtotime('-5 minutes'))
            )
        );

        if (!$deleteData) {
            echo "Error: " . $wpdb->last_error . ' ' . $wpdb->last_query;
        }
    }
}
new LoginComponent;
