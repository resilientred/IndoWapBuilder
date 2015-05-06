<?php

/**
 * @package IndoWapBuilder
 * @version VERSION (see attached file)
 * @author Achunk JealousMan
 * @link http://facebook.com/achunks
 * @copyright 2014 - 2015
 * @license LICENSE (see attached file)
 */

class Module extends Base
{
    protected $prefix = 'module_';
    protected $modules = array();

    public function __construct()
    {

    }
    public function getModule($module, $options = null)
    {
        $class = $this->prefix . $module;

        if (isset($this->modules[$module]))
            return $this->modules[$module];

        if (!class_exists($class))
            return $this->modules[$module] = false;

        $loader = new $class;
        return $this->modules[$module] = $loader->load($options);

    }
}

?>