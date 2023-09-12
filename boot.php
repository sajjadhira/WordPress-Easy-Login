<?php
class Boot
{
    protected $class = 'class';
    public function __construct()
    {
        $files = glob(plugin_dir_path(__FILE__) . $this->class . '/*.php');

        foreach ($files as $file) {
            require_once($file);
        }
    }

    function includeFile($file)
    {
        require_once(plugin_dir_path(__FILE__) . $file);
    }
}

new Boot;
