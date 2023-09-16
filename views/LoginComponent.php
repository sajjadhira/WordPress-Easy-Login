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
        add_action('login_footer', [$this, 'easylogin_fetch_callback']);

        add_action('init', [$this, 'easylogin_login_callback']);
    }

    function easylogin_login_callback()
    {

        // declear header type json

        header('Content-Type: application/json');

        if (isset($_GET[$this->instance->plugin_prefix . 'to_token'])) {
            // get token
            $token = $_GET[$this->instance->plugin_prefix . 'to_token'];
            // sanetize token
            $token = sanitize_text_field($token);



            // get token data from database

            global $wpdb;
            $table_name = $wpdb->prefix . rtrim($this->instance->plugin_prefix, "_");

            $toke_query = $wpdb->get_row("SELECT * FROM $table_name WHERE token = '$token'");

            if ($toke_query) {
                // get user id
                $user_id = $toke_query->wp_user_id;

                // get user data
                $user = get_user_by('id', $user_id);
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
                    // admin url
                    $admin_url = admin_url();
                    echo json_encode(array('status' => 1, 'redirect' => $admin_url));
                    exit;
                } else {
                    echo json_encode(array('status' => 0));
                    exit;
                }
            } else {
                echo json_encode(array('status' => 404));
                exit;
            }
        }
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
            <p><a href="javascript:;" class="<?php echo $this->instance->plugin_slug; ?>" onclick="copy(this)"><?php echo $url; ?></a></p>
            <br />
            <p>Scan this QR with any logged in browser or paste link to logged browser.</p>
            <br />
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
            let urlFetech = '<?php echo $site_url; ?>?<?php echo $this->instance->plugin_prefix ?>to_token=<?php echo $this->token; ?>';

            setInterval(function() {
                fetch(urlFetech)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status == 1) {
                            // get redirect url
                            window.location.href = data.redirect;
                        } else if (data.status == 404) {
                            // reload page
                            window.location.reload();
                        }
                    });
            }, 5000);

            function copy(that) {
                var inp = document.createElement('input');
                document.body.appendChild(inp)
                inp.value = that.textContent
                inp.select();
                document.execCommand('copy', false);
                inp.remove();
            }
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
