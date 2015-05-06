<?php

/**
 * @package IndoWapBuilder
 * @version VERSION (see attached file)
 * @author Achunk JealousMan
 * @link http://facebook.com/achunks
 * @copyright 2014 - 2015
 * @license LICENSE (see attached file)
 */

if (!$user->id)
    $user->redirect(urlencode($set['url'] . '/index.php/panel'));
if ($user->data['rights'] != 10)
    Func::redirect('/account');

switch ($action)
{
    case 'settings':
        $pageTitle = 'Admin Panel / Pengaturan';
        $errors = array();
        if (isset($_POST['submit']))
        {
            $siteurl = trim($_POST['siteurl']);
            $sitename = mb_substr($_POST['sitename'], 0, 20);
            $siteemail = trim($_POST['siteemail']);
            $timezone = trim($_POST['timezone']);
            $pageview = abs(intval($_POST['pageview']));

            $domains = preg_split("/[\s,]+/", strtolower(trim($_POST['domains'])));
            $maxsites = abs(intval($_POST['maxsites']));
            $filesize = abs(intval($_POST['filesize']));

            if (!filter_var($siteurl, FILTER_VALIDATE_URL))
                $errors['siteurl'] = 'URL Situs tidak benar';
            if (!filter_var($siteemail, FILTER_VALIDATE_EMAIL))
                $errors['siteemail'] = 'Email Situs tidak benar';
            if (empty($errors))
            {
                $upd = Base::db()->prepare("UPDATE `set` SET `val` = ? WHERE `key` = ?");
                $upd->execute(array($siteurl, 'siteurl'));
                $upd = Base::db()->prepare("UPDATE `set` SET `val` = ? WHERE `key` = ?");
                $upd->execute(array($sitename, 'sitename'));
                $upd = Base::db()->prepare("UPDATE `set` SET `val` = ? WHERE `key` = ?");
                $upd->execute(array($siteemail, 'siteemail'));
                $upd = Base::db()->prepare("UPDATE `set` SET `val` = ? WHERE `key` = ?");
                $upd->execute(array($timezone, 'timezone'));
                $upd = Base::db()->prepare("UPDATE `set` SET `val` = ? WHERE `key` = ?");
                $upd->execute(array($pageview, 'pageview'));
                $upd = Base::db()->prepare("UPDATE `set` SET `val` = ? WHERE `key` = ?");
                $upd->execute(array(serialize($domains), 'domains'));
                $upd = Base::db()->prepare("UPDATE `set` SET `val` = ? WHERE `key` = ?");
                $upd->execute(array($maxsites, 'maxsites'));
                $upd = Base::db()->prepare("UPDATE `set` SET `val` = ? WHERE `key` = ?");
                $upd->execute(array($filesize, 'filesize'));
                $_SESSION['notice'] = "Pengaturan berhasil disimpan";
                Func::redirect('/admin');
            }
        }
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Pengaturan</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '/admin">' .
            'Admin</a></li>' . '<li class="active">Pengaturan</li></ol>';
        if ($errors)
            echo Func::displayError($errors);
        echo '<div class="form"><form method="post" action="' . $baseurl .
            '/admin/settings">';
        echo '<div class="alert alert-info"><div class="form-group"><label>URL Situs</label>' .
            '<input class="form-control input-sm" type="text" name="siteurl" value="' . $set['url'] .
            '"/><p class="help-block">URL Situs tanpa diakhiri garis miring</p></div>';
        echo '<div class="form-group"><label>Nama Situs</label>' .
            '<input class="form-control input-sm" type="text" name="sitename" value="' .
            htmlentities($set['sitename']) . '"/></div>';
        echo '<div class="form-group"><label>Email Situs</label>' .
            '<input class="form-control input-sm" type="text" name="siteemail" value="' . $set['siteemail'] .
            '"/></div>';
        echo '<div class="form-group"><label>Zona Waktu</label>' .
            '<input class="form-control input-sm" type="text" name="timezone" value="' . $set['timezone'] .
            '"/><p class="help-block">-12 s/d +12</p></div>';
        echo '<div class="form-group"><label>List Per Halaman</label>' .
            '<input class="form-control input-sm" type="text" name="pageview" value="' . $set['pageview'] .
            '"/></div></div>';

        echo '<div class="alert alert-warning"><div class="form-group"><label>Domain Situs</label>' .
            '<input class="form-control input-sm" type="text" name="domains" value="' .
            implode(',', unserialize($set['domains'])) .
            '"/><p class="help-block">Jika lebih dari satu pisahkan dengan tanda , (koma)</p></div>';
        echo '<div class="form-group"><label>Maks Situs</label>' .
            '<input class="form-control input-sm" type="text" name="maxsites" value="' . $set['maxsites'] .
            '"/><p class="help-block">Maksimal jumlah situs per user</p></div>';
        echo '<div class="form-group"><label>Maks Upload</label>' .
            '<input class="form-control input-sm" type="text" name="filesize" value="' . $set['filesize'] .
            '"/><p class="help-block">Besar file maksimal pada yang diupload, (dalam kb).</p></div></div>';


        echo '<p><button class="btn btn-primary btn-sm" type="submit" name="submit">Simpan</button></p>';
        echo '</form></div>';
        break;

    case 'check_update':
        $pageTitle = 'Admin Panel / Periksa Pembaruan';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Periksa Pembaruan</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '/admin">' .
            'Admin</a></li>' . '<li class="active">Periksa Pembaruan</li></ol>';
        if (($ver = file_get_contents(Func::checkUpdateUrl())) != false)
        {
            if (version_compare(Func::getVersion(), $ver, '<'))
                echo '<div class="alert alert-warning">Versi baru telah tersedia, yaitu <strong>IndoWapBuilder v' .
                    $ver . '</strong><p>Untuk info lebih lanjut silakan hubungi ' .
                    '<a class="alert-link" href="http://facebook.com/achunks">Achunk JealousMan</a></div>';
            else
                echo '<div class="alert alert-success">Selamat, Kamu menggunakan IndoWapBuilder v.' .
                    Func::getVersion() . ', ini adalah versi terbaru.</div>';
        }
        else
        {
            echo '<div class="alert alert-danger">' .
                'Tidak dapat memeriksa pembaruan, ini terjadi ketika memanggil URL ' . Func::
                checkUpdateUrl() . '</div>';
        }

        break;

    case 'index':
        $pageTitle = 'Admin Panel';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Admin Panel</h3>';
        echo '<div class="alert alert-info"><strong>IndoWapBuilder v.' . Base::
            getVersion() . '</strong>, <a class="alert-link" href="' . $baseurl .
            '/admin/check_update">' . 'Periksa pembaruan &raquo;</a></div>';
        echo Func::getNotice();
        echo '<div class="list-group"><a class="list-group-item" href="' . $baseurl .
            '/admin/settings"><i class="fa fa-cog"></i> Pengaturan</a></div>';

        break;

    default:
        Func::redirect('/error/404');
        break;

}
include_once (ROOTPATH . 'iwbx-includes/footer.php');

?>