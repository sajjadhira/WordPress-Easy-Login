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
        add_action('init', [$this, 'easylogin_login_from_callback']);
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
                    // admin url
                    $admin_url = admin_url();
                    echo json_encode(array('status' => 1, 'redirect' => $admin_url));
                    exit;
                } else {
                    echo json_encode(array('status' => 0));
                    exit;
                }
            } else {

                $hashData = LoginController::generateQR();

                $fetechUrl = site_url('?' . $this->instance->plugin_prefix . 'to_token=' . $hashData['token']);

                // new QR Data

                echo json_encode(array('status' => 404, 'qr' => $hashData['qr'], 'url' => $hashData['url'], 'token' => $hashData['token'], 'fetechUrl' => $fetechUrl));
                exit;
            }
        }
    }


    public function easylogin_login_from_callback()
    {


        $this->autoDelete();

        if (isset($_GET['easy_login_from_token'])) {

            // add header type json

            header('Content-Type: application/json');

            $token = $_GET['easy_login_from_token'];
            $token = sanitize_text_field($token);

            global $wpdb;
            $table_name = $wpdb->prefix . rtrim($this->instance->plugin_prefix, "_");

            $toke_query = $wpdb->get_row("SELECT * FROM $table_name WHERE token = '$token'");

            if ($toke_query) {

                // delete token if token found and status is 1

                if ($toke_query->status == 1) {
                    // $wpdb->delete(
                    //     $table_name,
                    //     array(
                    //         'wp_user_id' => $toke_query->wp_user_id,
                    //     )
                    // );
                    echo json_encode(array('status' => 1, 'browser' => $toke_query->browser, 'ip' => $toke_query->ip));
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


        if (isset($_GET['easy_login_from'])) {


            // get token
            $token = $_GET['easy_login_from'];

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
                    // $wpdb->delete(
                    //     $table_name,
                    //     array(
                    //         'wp_user_id' => $user_id,
                    //     )
                    // );

                    // update status to 1

                    $wpdb->update(
                        $table_name,
                        array(
                            'status' => 1
                        ),
                        array(
                            'token' => $token
                        )
                    );
                    // admin url
                    $admin_url = admin_url();
                    wp_redirect($admin_url);
                    exit;
                } else {
                    wp_die('user not found');
                }
            } else {
                wp_die('token not exist');
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

        $hashData = LoginController::generateQR();

        // dump with die
        $qr = $hashData['qr'];
        $url = $hashData['url'];
        $this->token = $hashData['token'];

        $fetechUrl = site_url('?' . $this->instance->plugin_prefix . 'to_token=' . $this->token);
        $brandText = "Powered By <strong><a href='" . $this->instance->pluginURI . "' target='_blank'>" . $this->instance->plugin_name . "</a></strong>";
?>

        <div style="text-align: center;font-weight:bold;">
            <h4><?php echo __("Login with QR Code", $this->instance->plugin_slug); ?></h4>
            <br />
            <img src="<?php echo $qr; ?>" alt="<?php echo $url; ?>" id="imageURL">
            </p>
            <br />
            <p>
                <span style="display: none;" id="loginUrl"><?php echo $url; ?></span>
                <span style="display: none;" id="fetechUrl"><?php echo $fetechUrl; ?></span>
                <a href=" javascript:;" class="<?php echo $this->instance->plugin_slug; ?>" onclick="copyURL()" id="copyToUrl"><span class="dashicons dashicons-clipboard"></span> <?php echo __("Copy Link", $this->instance->plugin_slug); ?></a>
            </p>
            <br />
            <p><?php echo __("Scan this QR with any logged in browser or paste link to logged browser.", $this->instance->plugin_slug); ?></p>
            <p><?php echo __(
                    $brandText,
                    $this->instance->plugin_slug
                ); ?> </p>
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
            let urlFetech = document.getElementById('fetechUrl').innerHTML;
            let urlCopy = document.getElementById('loginUrl').innerHTML;

            setInterval(function() {
                fetch(urlFetech)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status == 1) {
                            // get redirect url
                            window.location.href = data.redirect;
                        } else if (data.status == 404) {
                            // reload page
                            // window.location.reload();
                            let newURL = data.url;
                            let imgURL = data.qr;

                            // change image url

                            document.getElementById('imageURL').src = imgURL;

                            // chnage loginUrl to new url

                            document.getElementById('loginUrl').innerHTML = newURL;
                            urlCopy = newURL;

                            // set new fetech url

                            document.getElementById('fetechUrl').innerHTML = data.fetechUrl;
                            urlFetech = data.fetechUrl;


                        }
                    });
            }, 3000);


            const copyURL = async () => {
                try {
                    await navigator.clipboard.writeText(urlCopy);
                    // console.log('Content copied to clipboard');

                    // change text of copyToUrl id

                    document.getElementById('copyToUrl').innerHTML = '<span class="dashicons dashicons-saved"></span> Link Copied to clipboard';

                    setTimeout(() => {
                        // change text of copyToUrl id

                        document.getElementById('copyToUrl').innerHTML = '<span class="dashicons dashicons-clipboard"></span> Copy Link';
                    }, 5000);
                } catch (err) {
                    console.error('Failed to copy: ', err);
                }
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

    public static function autoDelete()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . rtrim('easy_login_', "_");

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
