<?php
class LoginComponent
{
    public $instance;
    public function __construct()
    {
        $this->instance = EasyLogin::get_instance();
        add_action('login_form', [$this, 'login_form_callback']);
    }

    public function login_form_callback()
    {


        // generate sha256 hash
        $this->autoDelete();

        $hash =  hash('sha256', time());
        $url = site_url('?' . $this->instance->plugin_slug . '=' . $hash);
        $qr = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$url}";

        // add this hash to database

        global $wpdb;
        $table_name = $wpdb->prefix . rtrim($this->instance->plugin_prefix, "_");
        $wpdb->insert(
            $table_name,
            array(
                'token' => $hash,
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
            <br />

    <?php
    }

    public function autoDelete()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . rtrim($this->instance->plugin_prefix, "_");
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
