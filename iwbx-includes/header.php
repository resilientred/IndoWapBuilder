<?php

$pageTitle = isset($pageTitle) ? htmlspecialchars($pageTitle) : htmlspecialchars($set['sitename']);

echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"/>' .
    '<meta http-equiv="X-UA-Compatible" content="IE=edge"/>' .
    '<meta name="viewport" content="width=device-width, initial-scale=1"/>' .
    '<meta name="description" content=""/><meta name="author" content=""/>' .
    '<title>' . $pageTitle . '</title>' . '<link href="' . $set['siteurl'] .
    '/iwbx-assets/css/bootstrap.min.css" rel="stylesheet"/>' . '<link href="' . $set['siteurl'] .
    '/iwbx-assets/css/font-awesome.min.css" rel="stylesheet"/>' . '<link href="' .
    $set['siteurl'] . '/iwbx-assets/css/custom.css" rel="stylesheet"/>' .
    '<!--[if lt IE 9]><script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js">' .
    '</script><script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]--></head><body><nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">' .
    '<div class="container"><div class="navbar-header">' .
    '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"' .
    ' aria-expanded="false" aria-controls="navbar"><span class="sr-only">Menu</span>' .
    '<span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>' .
    '</button><a class="navbar-brand" href="' . $set['baseurl'] . '/">' .
    htmlspecialchars($set['sitename']) . '</a>' .
    '</div><div id="navbar" class="collapse navbar-collapse">' .
    '<ul class="nav navbar-nav navbar-right">';
if ($user->logIn)
{
    echo '<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><strong><i class="fa fa-user"></i> ' .
        htmlspecialchars($user->getName()) .
        ' <span class="caret"></span></strong></a><ul class="dropdown-menu" role="menu">'.
        '<li><a href="'.$baseurl.'/account"><i class="fa fa-user"></i> Akun</a></li>'.
        '<li><a href="'.$baseurl.'/panel"><i class="fa fa-dashboard"></i> Panel</a></li>'.
        '<li class="divider"></li><li><a href="' . $baseurl . '/site/logout"><i class="fa fa-sign-out"></i> Keluar</a></li></ul></li>';
}
else
{
    echo '<li' . ($controller == 'site' && $action == 'login' ?
        ' class="active"' : '') . '><a href="' . $set['baseurl'] .
        '/site/login"><i class="fa fa-sign-in"></i> Masuk</a></li><li' . ($controller == 'site' && $action == 'register' ?
        ' class="active"' : '') . '><a href="' . $set['baseurl'] .
        '/site/register"><i class="fa fa-user"></i> Pendaftaran</a></li>';
}
echo '</ul></div></div></nav><div class="container main" role="main"><div class="content">';

?>