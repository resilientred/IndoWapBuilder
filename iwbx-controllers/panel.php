<?php

if (!$user->id)
    $user->redirect(urlencode($set['url'] . '/index.php/panel'));
switch ($action)
{
    case 'modules':
        $pageTitle = 'Panel / Module';
        if (isset($_GET['module']))
        {
            $mod = $_GET['module'];
            if (!filter_var($mod, FILTER_VALIDATE_REGEXP, array('options' =>
                    array('regexp' => '/^[a-zA-Z0-9\_]+$/'))))
                Func::redirect('/panel/dashboard/1');
            if (!class_exists($mod) || !file_exists(ROOTPATH .
                '/iwbx-includes/modules/' . $mod . '.php'))
                Func::redirect('/panel/dashboard/2');
            include_once (ROOTPATH . 'iwbx-includes/header.php');
            $module = new $mod;
            $module->panel();
        }
        else
        {
            include_once (ROOTPATH . 'iwbx-includes/header.php');
            echo '<h3 class="head-title">Module</h3>';
            $modules = glob(ROOTPATH . 'iwbx-includes/modules/module_*.php');
            if (count($modules))
            {
                echo
                    '<div class="list-group"><div class="list-group-item list-group-item-info">' .
                    '<strong>Module</strong></div>';
                foreach ($modules as $module_file)
                {
                    $module = basename($module_file, '.php');
                    echo '<a class="list-group-item" href="' . $baseurl . '/' .
                        $controller . '/modules/module/' . $module . '">' . $module::getName() .
                        '</a>';
                }
                echo '</div>';
            }

        }
        break;

    case 'dashboard':
        $stid = isset($_SESSION['st']) ? abs(intval($_SESSION['st'])) : false;
        $q = Base::db()->prepare("SELECT * FROM `site` WHERE `site_id` = ? AND `user_id` = ?");
        $q->execute(array($stid, $user->id));
        if ($q->rowCount() == 0)
            Func::redirect('/panel');
        $site = $q->fetch();

        $pageTitle = 'Panel / Dashboard';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Dashboard</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '">' .
            '<i class="fa fa-home"></i> Home</a></li>' . '<li><a href="' . $baseurl .
            '/panel">' . 'Panel</a></li>' . '<li class="active">' . $site['url'] .
            '</li></ol>';
        echo '<div class="row"><div class="col-sm-6">';
        $modules = glob(ROOTPATH . 'iwbx-includes/modules/module_*.php');
        if (count($modules))
        {
            echo '<div class="list-group"><div class="list-group-item list-group-item-info">' .
                '<strong>Module</strong></div>';
            foreach ($modules as $module_file)
            {
                $module = basename($module_file, '.php');
                echo '<a class="list-group-item" href="' . $baseurl . '/' . $controller .
                    '/modules/module/' . $module . '">' . $module::getName() .
                    '</a>';
            }
            echo '</div>';
        }
        else
            '<div class="alert alert-warning">Tidak ada modul</div>';
        echo '<div class="list-group"><a class="list-group-item" href="' . $baseurl .
            '/file_manager"><i class="fa fa-folder-open"></i> File Manager</a>' .
            '</div></div>';
        echo '<div class="col-sm-6"><div><dl><dt>URL</dt><dd><a href="//' . $site['url'] .
            '">http://' . $site['url'] . '</a></dd><dt>Mendaftar</dt><dd>' .
            Func::displayDate($site['time']) .
            '</dd><dt>Expired</dt><dd>Never</dd></dl></div>';
        echo '</div></div>';
        break;

    case 'delete_site':
        $q = Base::db()->prepare("SELECT * FROM `site` WHERE `site_id` = ? AND `user_id` = ?");
        $q->execute(array($id, $user->id));
        if ($q->rowCount() == 0)
            Func::redirect('/panel');
        $site = $q->fetch();
        if (isset($_POST['submit']))
        {
            Func::deleteSite($site);
            Func::redirect('/panel');
        }
        $pageTitle = 'Panel / Hapus Situs';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Hapus Situs</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '">' .
            '<i class="fa fa-home"></i> Home</a></li>' . '<li><a href="' . $baseurl .
            '/panel">' . 'Panel</a></li>' .
            '<li class="active">Hapus Situs</li></ol>';
        echo '<div class="form"><form method="post" action"' . $baseurl . '/' .
            $controller . '/' . $action .
            '"><div class="alert alert-warning">Kamu yakin akan menghapus situs <strong>' .
            $site['url'] .
            '</strong>?</div><p><button class="btn btn-primary btn-sm" type="submit" name="submit">Ya Hapus</button>' .
            '&nbsp;<a class="btn btn-default btn-sm" href="' . $baseurl . '/' .
            $controller . '">Batal</a></p></form></div>';

        break;

    case 'switch':
        if ($id)
        {
            $_SESSION['st'] = $id;
            header('Location: ' . $baseurl . '/panel/dashboard');
            exit();
        }
        break;

    case 'create_site':
        $pageTitle = 'Panel / Membuat Situs';
        $req = Base::db()->prepare("SELECT * FROM `site` WHERE `user_id` = ?");
        $req->execute(array($user->id));
        if ($req->rowCount() >= $set['maxsites'])
        {
            include_once (ROOTPATH . 'iwbx-includes/header.php');
            echo '<h4 class="head-title">Membuat Situs</h4>';
            echo '<div class="alert alert-danger">Anda sudah tidak diijinkan lagi membuat situs baru,' .
                ' Maksimal jumlah situs per user adalah ' . $set['maxsites'] .
                '.</div>';
            include_once (ROOTPATH . 'iwbx-includes/footer.php');
            exit();
        }
        $errors = array();
        $domains = unserialize($set['domains']);
        $subdomain = isset($_POST['subdomain']) ? strtolower(trim($_POST['subdomain'])) :
            '';
        $domain = isset($_POST['domain']) ? strtolower(trim($_POST['domain'])) :
            '';
        if (isset($_POST['submit']))
        {
            if (mb_strlen($subdomain) < 4 || mb_strlen($subdomain) > 16)
                $errors['subdomain'] =
                    'Panjang subdomain min. 4 s/d 16 karakter.';
            elseif (!filter_var($subdomain, FILTER_VALIDATE_REGEXP, array('options' =>
                    array('regexp' => '/([a-z0-9-]{4,16}+$)/'))))
                $errors['subdomain'] =
                    'Subdomain hanya diperbolehkan karakter a-z, 0-9 dan simbol -.';
            elseif (mb_substr($subdomain, 0, 1) == '-' || mb_substr($subdomain,
                -1) == '-')
                $errors['subdomain'] =
                    'Subdomain tidak boleh diawali atau diakhiri simbol -.';
            elseif (is_dir(ROOTPATH . $subdomain))
                $errors['subdomain'] = 'Subdomain tidak diijinkan';
            elseif (!in_array($domain, $domains))
                $errors['domain'] = 'Domain tidak benar.';
            else
            {
                $req = Base::db()->prepare("SELECT * FROM `site` WHERE `url` = ?");
                $req->execute(array($subdomain . '.' . $domain));
                if ($req->rowCount() > 0)
                    $errors['subdomain'] = 'Alamat situs <strong>' . $subdomain .
                        '.' . $domain . '</strong> sudah terdaftar.';
            }
            if (empty($errors))
            {
                if (mkdir(ROOTPATH . 'iwbx-sites/' . $subdomain . '.' . $domain,
                    0777))
                {
                    $st = Base::db()->prepare("INSERT INTO `site` SET `user_id` = ?, `url` = ?, `time` = ?");
                    $st->execute(array(
                        $user->id,
                        $subdomain . '.' . $domain,
                        time(),
                        ));
                    header('Location: ' . $baseurl . '/panel/');
                    exit();
                }
                else
                {
                    $errors['subdomain'] =
                        'Pembuatan situs gagal. Silakan hubungi Administrator';
                }

            }

        }
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Membuat Situs</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '">' .
            '<i class="fa fa-home"></i> Home</a></li>' . '<li><a href="' . $baseurl .
            '/panel">' . 'Panel</a></li>' .
            '<li class="active">Membuat Situs</li></ol>';
        if ($errors)
            echo '<div class="alert alert-danger">' . implode('<br/>', $errors) .
                '</div>';
        echo '<div class="form"><form role="form" action="' . $baseurl .
            '/panel/create_site" method="post">';
        echo '<div class="form-group">' . '<label>Subdomain</label>' .
            '<input class="form-control input-sm" type="text" name="subdomain" value="' .
            htmlentities($subdomain) . '" required/>' . '</div>';
        echo '<div class="form-group">' . '<label>Domain</label>' .
            '<select class="form-control input-sm" name="domain">';
        foreach ($domains as $dom)
        {
            echo '<option value="' . $dom . '"' . ($domain == $dom ? ' selected' :
                '') . '>' . $dom . '</option>';
        }
        echo '</select></div>';
        echo '<p><button class="btn btn-primary btn-sm" type="submit" name="submit">Membuat</button></p>' .
            '</form></div>';
        break;

    case 'index':
        $pageTitle = 'Panel';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Panel</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '">' .
            '<i class="fa fa-home"></i> Home</a></li>' .
            '<li class="active">Panel</li></ol>';
        echo '<p>';
        echo '<a class="btn btn-default btn-sm" href="' . $baseurl .
            '/panel/create_site"><i class="fa fa-plus"></i> Membuat situs baru</a>';
        echo '</p>';
        $res = Base::db()->prepare("SELECT * FROM `site` WHERE `user_id` = ? ORDER BY `time` DESC");
        $res->execute(array($user->id));
        if ($res->rowCount() > 0)
        {
            echo '<ul class="list-group">';
            foreach ($res->fetchAll() as $site)
            {
                echo
                    '<li class="list-group-item"><h4 class="list-group-item-heading"><a href="//' .
                    $site['url'] .
                    '" target="_new"><strong><i class="fa fa-globe"></i> ' . $site['url'] .
                    '</strong></a></h4><div class="list-group-item-text"><ul><li>Dibuat: ' .
                    Func::displayDate($site['time']) .
                    '</li></ul><div style="margin-top:10px;"><a href="' . $baseurl .
                    '/panel/switch/id/' . $site['site_id'] .
                    '"><i class="fa fa-sign-in"></i> Kelola</a>&nbsp;&nbsp;&nbsp;<a href="' .
                    $baseurl . '/panel/delete_site/id/' . $site['site_id'] .
                    '"><i class="fa fa-times"></i> Hapus</a></div></div></li>';
            }
            echo '</ul>';
        }
        else
        {
            echo '<div class="alert alert-info">Kamu belum memiliki situs</div>';
        }
        break;

    default:
        header('Location: ' . $baseurl . '/error/404');
        exit();
        break;
}
include_once (ROOTPATH . 'iwbx-includes/footer.php');

?>