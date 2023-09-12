<?php
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
}
new LoginController;
