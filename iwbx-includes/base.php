<?php

//@ini_set('display_errors', false);
//@error_reporting(7);
@ini_set('session.use_trans_sid', 0);
@ini_set('magic_quotes_sybase', 0);
@ini_set('magic_quotes_runtime', 0);
@ini_set('arg_separator.output', '&amp;');
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

define('ROOTPATH', dirname(dirname(__file__)) . DIRECTORY_SEPARATOR);

spl_autoload_register('loadcomponents');
function loadcomponents($name)
{
    if (file_exists($file = ROOTPATH . 'iwbx-includes/components/' . $name .
        '.php'))
        include $file;
    elseif (file_exists($file = ROOTPATH . 'iwbx-includes/modules/' . $name .
        '.php'))
        include $file;
}

new Base();

$pdo = Base::$pdo;
$set = Base::$set;
$baseurl = $set['baseurl'];
$controller = Base::$controller;
$action = Base::$action;
$kmess = $set['pageview'] > 4 && $set['pageview'] < 100 ? $set['pageview'] : 10;
$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : false;
$is_modal = isset($_GET['__modal']) ? true : false;
$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : '';
$mod = isset($_REQUEST['mod']) ? trim($_REQUEST['mod']) : '';
$page = isset($_REQUEST['page']) && (ctype_digit($_REQUEST['page'])) && ($_REQUEST['page'] >
    0) ? intval($_REQUEST['page']) : 1;
$start = isset($_REQUEST['page']) ? $page * $kmess - $kmess : (isset($_GET['start']) ?
    abs(intval($_GET['start'])) : 0);

?>