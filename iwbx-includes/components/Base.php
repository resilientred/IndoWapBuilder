<?php

/**
 * @package IndoWapBuilder
 * @version VERSION (see attached file)
 * @author Achunk JealousMan
 * @link http://facebook.com/achunks
 * @copyright 2014 - 2015
 * @license LICENSE (see attached file)
 */

class Base
{
    public static $pdo;
    public static $set;
    public static $controller = 'site';
    public static $action = 'index';

    public function __construct()
    {
        $this->getRoutes();
        $this->dbConnect();
        $this->settings();
        session_start();
    }

    public static function getVersion()
    {
        return '1.0.0';
    }
    public static function checkUpdateUrl()
    {
        return 'http://feed.heck.in/files/indowapbuilder.txt';
    }

    public static function db()
    {
        return self::$pdo;
    }

    public static function dbDisconnect()
    {
        self::$pdo = null;
    }

    protected function dbConnect()
    {
        $dbSettings = parse_ini_file(ROOTPATH . "iwbx-includes/db.ini");
        $dsn = 'mysql:dbname=' . $dbSettings["database"] . ';host=' . $dbSettings["host"] .
            '';
        try
        {
            self::$pdo = new PDO($dsn, $dbSettings["user"], $dbSettings["password"], array(PDO::
                    MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        catch (PDOException $e)
        {
            die($e->getMessage());
        }
    }

    protected function settings()
    {
        $set = array();
        $sets = self::$pdo->query("SELECT * FROM `set`");

        foreach ($sets as $st)
        {
            $set[$st['key']] = $st['val'];
        }

        $set['url'] = $set['siteurl'];
        $set['siteurl'] = parse_url($set['siteurl'], PHP_URL_PATH);
        $set['baseurl'] = $set['siteurl'] . '/index.php';

        self::$set = $set;
    }

    protected function getRoutes()
    {
        if (!empty($_SERVER['PATH_INFO']))
        {
            parse_str($_SERVER['QUERY_STRING'], $query_string);
            $handle_requests = array();

            $route = $_SERVER['PATH_INFO'];
            if (mb_substr($route, 0, 1) != '/')
                $route = '/' . $route;
            $r = explode('/', strtr(trim($route), array('//' => '/', '\\' => '/')));
            $c = 0;
            for ($i = 0; $i < count($r); $i++)
            {
                if ($i % 2)
                {
                    if (!isset($handle_requests[$r[$i]]))
                        $handle_requests[$r[$i]] = isset($r[$i + 1]) ? $r[$i + 1] : '';
                }
                if (isset($r[$i]) && $c == 1 && $r[$i] != '/' && !empty($r[$i]))
                    self::$controller = $r[$i];
                if (isset($r[$i]) && $c == 2 && $r[$i] != '/' && !empty($r[$i]))
                    self::$action = $r[$i];
                ++$c;
            }
            $_REQUEST = $_GET = array_merge($query_string, $handle_requests);
        }
    }
}

?>