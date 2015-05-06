<?php

/**
 * @package IndoWapBuilder
 * @version VERSION (see attached file)
 * @author Achunk JealousMan
 * @link http://facebook.com/achunks
 * @copyright 2014 - 2015
 * @license LICENSE (see attached file)
 */

if (!file_exists('iwbx-includes/db.ini'))
{
    die('Silakan install terlebih dahulu!');
}
require ('iwbx-includes/base.php');
$server_host = strtolower($_SERVER['SERVER_NAME']);
$site_host = parse_url($set['url'], PHP_URL_HOST);
$site_domain = mb_substr($server_host, 0, 4) == 'www.' ? mb_substr($server_host,
    4) : $server_host;
if (is_dir(ROOTPATH . 'iwbx-sites/' . $site_domain))
{
    $route = isset($_GET['route']) ? Func::validateRoute(trim($_GET['route'])) :
        'index.html';
    $ext = strtolower(substr(strrchr($route, "."), 1));
    if (is_file(ROOTPATH . 'iwbx-sites/' . $site_domain . '/' . $route) && $ext !=
        'html')
    {
        $mime_types = include_once (ROOTPATH . 'iwbx-includes/mime_types.php');
        if (in_array($ext, array_keys($mime_types)))
            $type = $mime_types[$ext];
        else
            $type = "application/octet-stream";
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $type);
        header('Content-Disposition: attachment; filename=' . basename(ROOTPATH .
            'iwbx-sites/' . $site_domain . '/' . $route));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize(ROOTPATH . 'iwbx-sites/' . $site_domain .
            '/' . $route));
        ob_clean();
        flush();
        readfile(ROOTPATH . 'iwbx-sites/' . $site_domain . '/' . $route);
        exit();
    }
    if (!is_dir(ROOTPATH . 'iwbx-sites/' . $site_domain . '/' . $route) && !is_file
        (ROOTPATH . 'iwbx-sites/' . $site_domain . '/' . $route))
    {
        $path = ROOTPATH . 'iwbx-sites/' . $site_domain;
        $index = '404.html';
    }
    elseif (is_dir(ROOTPATH . 'iwbx-sites/' . $site_domain . '/' . $route))
    {
        $path = ROOTPATH . 'iwbx-sites/' . $site_domain;
        $index = $route . '/index.html';
    }
    else
    {
        $path = ROOTPATH . 'iwbx-sites/' . $site_domain;
        $index = $route;
    }
    if (!file_exists(ROOTPATH . 'iwbx-sites/' . $site_domain . '/' . $index))
    {
        $path = ROOTPATH . 'iwbx-includes';
        $index = '404.html';
    }
    require_once ROOTPATH . 'iwbx-includes/lib/Template/Autoloader.php';
    Template_Autoloader::register();
    $loader = new Template_Loader_Filesystem($path);
    $tpl = new Template_Environment($loader, array(
        'cache' => false,
        'debug' => true,
        'autoescape' => false,
        ));
    $tpl->addExtension(new Template_Extension_Debug());
    $module = new Module();
    $moduler = new Template_SimpleFunction("module", function ($name, $options = null)
        use ($module)
    {
        return $module->getModule($name, $options); }
    );
    $tpl->addFunction($moduler);
    echo $tpl->render($index);
}
else
{
    if ($server_host != $site_host)
    {
        header('Location: ' . $set['url']);
        exit();
    }
    $user = new User();

    $controllers = array();
    foreach (glob('iwbx-controllers/*.php') as $controller_file)
        $controllers[] = basename($controller_file, '.php');

    if ($controller && ($key = array_search($controller, $controllers)) !== false &&
        file_exists($file = 'iwbx-controllers/' . $controllers[$key] . '.php'))
    {
        include $file;
    }
    else
    {
        include ('iwbx-controllers/error.php');
    }

}
Base::dbDisconnect();

?>