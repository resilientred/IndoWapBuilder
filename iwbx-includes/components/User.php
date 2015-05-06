<?php

/**
 * @package IndoWapBuilder
 * @version VERSION (see attached file)
 * @author Achunk JealousMan
 * @link http://facebook.com/achunks
 * @copyright 2014 - 2015
 * @license LICENSE (see attached file)
 */

class User extends Base
{
    public $logIn = false;
    public $id = 0;
    public $data = array();
    protected $auth = false;

    public function __construct($auth = true)
    {
        if ($auth)
            $this->auth();
    }

    protected function auth()
    {
        $this->auth = true;

        $id = isset($_SESSION['uid']) ? abs(intval($_SESSION['uid'])) : false;
        $pass = isset($_SESSION['upw']) ? $_SESSION['upw'] : false;
        if (!$id || !$pass)
            return $this->setUser();

        $q = parent::$pdo->prepare("SELECT * FROM `user` WHERE `user_id` = ?");
        $q->execute(array($id));
        if ($q->rowCount() == 0)
            return $this->setUser();
        $user = $q->fetch();
        if ($user['password'] == md5($pass))
        {
            $this->logIn = true;
            $this->id = $user['user_id'];
            $this->data = $user;
            return;
        }
        return $this->setUser();
    }

    public function redirect($redir = '')
    {
        header('Location: ' . parent::$set['baseurl'] . '/site/login?redirect=' . $redir);
        exit();
    }

    public function logOut()
    {
        if (isset($_SESSION['uid']))
            unset($_SESSION['uid']);
        if (isset($_SESSION['upw']))
            unset($_SESSION['upw']);
        session_destroy();
        $this->logged = false;
        $this->setUser();
    }

    protected function setUser()
    {
        $this->data['user_id'] = 0;
        $this->data['name'] = 'Tamu';
    }

    public function getId()
    {
        if (!$this->auth)
            return false;

        return $this->data['user_id'];
    }

    public function getName()
    {
        if (!$this->auth)
            return false;

        return $this->data['name'];
    }

    public function getUser($id)
    {
        $q = parent::$pdo->prepare("SELECT * FROM `user` WHERE `user_id` = ?");
        $q->execute(array($id));
        if ($q->rowCount() == 0)
            return false;
        return $q->fetch();
    }
}

?>