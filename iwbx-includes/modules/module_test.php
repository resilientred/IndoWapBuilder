<?php

class module_test extends Module
{
    protected $options = array();

    protected function load($options = null)
    {
        $this->options = is_null($options) ? array() : json_decode($options);
        return parent::$set['sitename'];
    }
    public static function getName()
    {
        return 'Module Test';
    }
    public function panel()
    {
        global $user;
        echo '<h3 class="head-title">Modul::' . __class__ . '</h3>';
        echo '<div class="alert alert-warning">Ini adalah modul demo' . ($user->data['rights'] ==
            10 ? ', untuk menghapus modul ini s' .
            'ilakan hapus file <strong>iwbx-includes/modules/' . __class__ .
            '.php' : '.') . '</div>';
    }
}

?>