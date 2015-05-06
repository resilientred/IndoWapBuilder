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
$st = isset($_SESSION['st']) ? abs(intval($_SESSION['st'])) : false;
if (!$st)
{
    header('Location: ' . $baseurl . '/panel');
    exit();
}
$req = Base::db()->prepare("SELECT * FROM `site` WHERE `site_id` = ? AND `user_id` = ?");
$req->execute(array($st, $user->id));
if ($req->rowCount() == 0)
{
    unset($_SESSION['st']);
    header('Location: ' . $baseurl . '/panel');
    exit();
}
$site = $req->fetch();
$site_root = ROOTPATH . 'iwbx-sites/' . $site['url'];

function get_dir($exp_limit, $str = false)
{
    if (!$str)
    {
        $str = strtr(strip_tags(trim($_SERVER['PATH_INFO'])), array('\\' => '/', '//' =>
                '/'));
    }
    $path = explode('/', $str, $exp_limit);
    $indexes = explode('/', str_replace('\\', '/', @$path[$exp_limit - 1]));
    $dirs = array();
    foreach ($indexes as $idx)
    {
        $idx = trim($idx);
        if ($idx != '' || $idx != '.' || $idx != '..' || mb_substr($idx, 0, 1) != '.' ||
            mb_substr($idx, -1) != '.')
            $dirs[] = $idx;
    }
    return implode('/', $dirs);
}

$pageTitle = 'Creator';
$ext_text = array(
    'html',
    'txt',
    'css',
    'js',
    );

switch ($action)
{
    case 'upload':
        $dir = get_dir(4);
        $dir = mb_substr($dir, -1) == '/' ? mb_substr($dir, 0, -1) : $dir;
        if (!is_dir($site_root . '/' . $dir))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1');
            exit();
        }
        $errors = array();
        if (!isset($_SESSION['key']))
            $_SESSION['key'] = md5(time());
        $key = $_SESSION['key'];
        if (isset($_POST[$key]) && isset($_FILES['berkas']))
        {
            unset($_SESSION['key']);
            $key = $_SESSION['key'] = md5(time());
            $ffile = $_FILES['berkas']['tmp_name'];
            $fname = strtolower($_FILES['berkas']['name']);
            $fsize = $_FILES['berkas']['size'];
            if ($fsize >= 1024 * $set['filesize'])
                $errors[] = "Ukuran File tidak boleh lebih dari " . $set['filesize'] . " Kb.";
            $ext = Func::getExt($fname);
            $fname = Func::permalink(substr($fname, 0, "-" . (strlen($ext) + 1)));
            $fname = mb_strlen($fname) > 30 ? mb_substr($fname, 0, 30) : $fname;
            $filename = $fname . '.' . $ext;
            if (strlen($ext) > 4 || strlen($ext) < 2)
                $errors[] = "Ekstensi file tidak benar";
            if (empty($fname))
                $errors[] = "Silakan pilih File.";
            if (!$errors)
            {
                if (move_uploaded_file($ffile, $site_root . '/' . $dir . '/' . $filename))
                {
                    header('Location: ' . $baseurl . '/' . $controller . '/file_info/' . $dir . '/' .
                        $filename);
                    exit();
                }
                else
                    $errors[] = 'File gagal diupload';
            }
        }

        $pageTitle = 'Creator / Upload';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Upload</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '/' . $controller .
            '/file_browser/page/1/' . dirname($dir) . '">' .
            '<i class="fa fa-folder-open"></i> File browser</a></li>' .
            '<li class="active">Upload</li></ol>';
        echo '<form role="form" action="' . $baseurl . '/' . $controller . '/upload/' .
            $dir . '" method="post" enctype="multipart/form-data">';
        if ($errors)
            echo Func::displayError($errors);
        echo '<div class="form-group"><label>File</label>' .
            '<input type="file" name="berkas"/>' . '<p class="help-block">Maksimal ukuran ' .
            $set['filesize'] . ' kb</p></div>';
        echo '<p><button class="btn btn-primary btn-sm" type="submit" name="' . $key .
            '">Upload</button>' .
            '&nbsp;<a class="btn btn-default btn-sm" data-dismiss="modal" href="' . $baseurl .
            '/' . $controller . '/file_browser/page/1/' . $dir . '">Batal</a></p>';
        echo '</form>';
        break;

    case 'file_info':
        $dir = get_dir(4);
        $dir = mb_substr($dir, -1) == '/' ? mb_substr($dir, 0, -1) : $dir;
        if ((!is_file($site_root . '/' . $dir) || ($dir == '')))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1');
            exit();
        }
        $name = basename($dir);
        $pageTitle = 'Creator / File Info';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">File Info</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '/' . $controller .
            '/file_browser/page/1/' . dirname($dir) . '">' .
            '<i class="fa fa-folder-open"></i> File browser</a></li>' .
            '<li class="active">' . $name . '</li></ol>';
        $types = include_once (ROOTPATH . 'iwbx-includes/mime_types.php');
        $ext = Func::getExt($name);
        if (in_array($ext, array_keys($types)))
            $type = $types[$ext];
        else
            $type = "application/octet-stream";
        echo '<dl class="dl-horizontal">' . '<dt>Nama</dt><dd>' . $name . '</dd>' .
            '<dt>Tipe</dt><dd>' . $type . '</dd>' . '<dt>Ukuran</dt><dd>' . round(filesize($site_root .
            '/' . $dir) / 1024, 2) . ' kb</dd>' . '<dt>Diupload</dt>' . '<dd>' . Func::
            displayDate(filemtime($site_root . '/' . $dir)) . '</dd>' .
            '<dt>URL</dt><dd><a href="http://' . $site['url'] . '/site/' . $dir .
            '">http://' . $site['url'] . '/' . $dir . '</a></dd></dl>';
        echo '<div class="list-group">';
        echo '<a class="list-group-item" href="' . $baseurl . '/' . $controller .
            '/rename/' . $dir . '">' . '<i class="fa fa-pencil"></i> Ubah nama</a>';
        echo '<a class="list-group-item" href="' . $baseurl . '/' . $controller .
            '/file_browser/page/1/?move=' . $dir . '">' .
            '<i class="fa fa-exchange"></i> Pindah</a>';
        echo '<a class="list-group-item" href="' . $baseurl . '/' . $controller .
            '/delete/' . $dir . '">' . '<i class="fa fa-times"></i> Hapus</a>';
        echo '<a class="list-group-item" href="' . $baseurl . '/' . $controller .
            '/download/' . $dir . '">' . '<i class="fa fa-download"></i> Download</a>';
        if (in_array($ext, $ext_text))
            echo '<a class="list-group-item" href="' . $baseurl . '/' . $controller .
                '/edit_file/' . $dir . '">' . '<i class="fa fa-edit"></i> Edit</a>';
        echo '</div>';
        break;

    case 'edit_file':
        $dir = get_dir(4);
        $dir = mb_substr($dir, -1) == '/' ? mb_substr($dir, 0, -1) : $dir;
        if ((!is_file($site_root . '/' . $dir) || ($dir == '')))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1');
            exit();
        }
        $ext = Func::getExt($dir);
        if (!in_array($ext, $ext_text))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1/' .
                dirname($dir));
            exit();
        }
        $name = basename($dir);
        $code = isset($_POST['code']) ? $_POST['code'] : file_get_contents($site_root .
            '/' . $dir);
        if (isset($_POST['code']))
        {
            if (file_put_contents($site_root . '/' . $dir, $code))
                $result = '<div class="alert alert-success">File berhasil disimpan.</div>';
            else
                $result = Func::displayError('File gagal disimpan!');
        }
        $pageTitle = 'Creator / Hapus';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Edit File</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '/' . $controller .
            '/file_browser/page/1/' . dirname($dir) . '">' .
            '<i class="fa fa-folder-open"></i> File browser</a></li>' .
            '<li class="active">' . $name . '</li></ol>';
        echo '<form role="form" action="' . $baseurl . '/' . $controller . '/edit_file/' .
            $dir . '" method="post">';
        if (isset($result))
            echo $result;
        echo '<div class="form-group"><label class="pull-right"><a class="func" href="//' .
            $site['url'] . '/' . $dir .
            '" target="_new"><i class="fa fa-eye"></i> Preview</a></label><label>Kode</label>' .
            '<textarea class="form-control" name="code" rows="10">' . htmlentities($code) .
            '</textarea>' . '</div>';
        echo '<p><button class="btn btn-primary btn-sm" type="submit">Simpan</button></p>';
        echo '</form>';
        break;

    case 'download':
        $dir = get_dir(4);
        $dir = mb_substr($dir, -1) == '/' ? mb_substr($dir, 0, -1) : $dir;
        if ((!is_file($site_root . '/' . $dir) || ($dir == '')))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1');
            exit();
        }
        $name = basename($dir);
        $mime_types = include_once (ROOTPATH . 'iwbx-includes/mime_types.php');
        $ex = strtolower(substr(strrchr($name, "."), 1));
        if (in_array($ex, array_keys($mime_types)))
            $type = $mime_types[$ex];
        else
            $type = "application/octet-stream";
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $type);
        header('Content-Disposition: attachment; filename=' . $name);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($site_root . '/' . $dir));
        readfile($site_root . '/' . $dir);
        Base::$pdo = null;
        exit();
        break;

    case 'delete':
        $dir = get_dir(4);
        $dir = mb_substr($dir, -1) == '/' ? mb_substr($dir, 0, -1) : $dir;
        if ((!is_dir($site_root . '/' . $dir) && !is_file($site_root . '/' . $dir) || ($dir ==
            '')))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1');
            exit();
        }
        $name = basename($dir);
        if (is_dir($site_root . '/' . $dir))
            $typ = 'Folder';
        else
            $typ = 'File';

        if (isset($_POST['submit']))
        {
            if ($typ == 'File')
            {
                @unlink($site_root . '/' . $dir);
            }
            else
            {
                @Func::deleteDir($site_root . '/' . $dir);
            }
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1/' .
                dirname($dir));
            exit();
        }
        $pageTitle = 'Creator / Hapus';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Hapus</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '/' . $controller .
            '/file_browser/page/1/' . dirname($dir) . '">' .
            '<i class="fa fa-folder-open"></i> File browser</a></li>' .
            '<li class="active">' . $name . '</li></ol>';
        echo '<form role="form" action="' . $baseurl . '/' . $controller . '/delete/' .
            $dir . '" method="post">';
        echo '<div class="alert alert-warning">Apakah Kamu yakin akan menghapus ' . $typ .
            ' ini ?</div>';
        echo '<p><button class="btn btn-danger btn-sm" type="submit" name="submit">Hapus</button>' .
            '&nbsp;<a class="btn btn-default btn-sm" data-dismiss="modal" href="' . $baseurl .
            '/' . $controller . '/file_browser/page/1/' . dirname($dir) . '">Batal</a></p>';
        echo '</form>';
        break;

    case 'move':
        $dir = get_dir(4);
        $dir = mb_substr($dir, -1) == '/' ? mb_substr($dir, 0, -1) : $dir;
        parse_str($_SERVER['QUERY_STRING'], $r2);

        if (!is_dir($site_root . '/' . $dir) || !isset($r2['move']) || (@$r2['move'] ==
            ''))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1');
            exit();
        }
        $move = get_dir(4, '/AChuNk/JealousMan/' . strip_tags(trim($r2['move'])));
        if (!is_dir($site_root . '/' . $move) && !is_file($site_root . '/' . $move))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1/' . $dir);
            exit();
        }
        $name = basename($move);
        if (isset($_POST['submit']))
        {
            if (rename($site_root . '/' . $move, $site_root . '/' . $dir . '/' . $name))
            {
                header("Location: " . $baseurl . "/" . $controller . "/file_browser/page/1/" . $dir .
                    "/" . $name);
                exit();
            }
            else
            {
                $error = Func::displayError("Gagal memindahkan file/folder");
            }
        }

        $pageTitle = 'Creator / Pindah';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Pindah</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '/' . $controller .
            '/file_browser/page/1/' . $dir . '">' .
            '<i class="fa fa-folder-open"></i> File browser</a></li>' .
            '<li class="active">' . $name . '</li></ol>';
        echo '<div class="form"><form role="form" action="' . $baseurl . '/' . $controller .
            '/move/' . $dir . '?move=' . $move . '" method="post">' . (isset($error) ? $error :
            '') . '<div class="alert alert-warning">Apa kamu yakin akan memindahkan <strong class="text-red">' .
            htmlentities($name) . '</strong> ke folder <strong class="text-red">/' .
            htmlentities($dir) .
            '</strong> ?</div><p><button class="btn btn-primary btn-sm" type="submit" name="submit">Ya pindahkan</button>' .
            '&nbsp;<a class="btn btn-default btn-sm" href="' . $baseurl . '/' . $controller .
            '/file_browser/page/1/' . $dir .
            '" data-dismiss="modal">Batal</a></p></form></div>';
        break;

    case 'rename':
        $error = false;
        $dir = get_dir(4);
        $dir = mb_substr($dir, -1) == '/' ? mb_substr($dir, 0, -1) : $dir;
        if ((!is_dir($site_root . '/' . $dir) && !is_file($site_root . '/' . $dir) || ($dir ==
            '')))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1');
            exit();
        }
        $name = basename($dir);
        $value = isset($_POST['value']) ? trim($_POST['value']) : $name;
        $prev_dir = dirname($dir);

        if (is_dir($site_root . '/' . $dir))
            $tipe = 'dir';
        else
            $tipe = 'file';

        if (isset($_POST['value']))
        {
            if (!filter_var($value, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' =>
                        '/^[a-zA-Z0-9\_\-\.]+$/'))))
                $error = 'Nama file salah';
            if (!$error)
            {
                if (file_exists($site_root . '/' . $prev_dir . '/' . $value))
                {
                    $error = 'File sudah ada';
                }
                elseif (is_dir($site_root . '/' . $prev_dir . '/' . $value))
                {
                    $error = 'Folder sudah ada';
                }
                elseif (rename($site_root . '/' . $dir, $site_root . '/' . $prev_dir . '/' . $value))
                {
                    header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1/' . $prev_dir);
                    exit();
                }
                else
                {
                    $error = 'Gagal merubah nama';
                }
            }
        }
        $pageTitle = 'Creator / Ubah nama';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Ubah Nama</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '/' . $controller .
            '/file_browser/page/1/' . $dir . '">' .
            '<i class="fa fa-folder-open"></i> File browser</a></li>' .
            '<li class="active">' . $name . '</li></ol>';
        echo '<div class="form"><form role="form" action="' . $baseurl . '/' . $controller .
            '/rename/' . $dir . '" method="post">';
        if ($error)
            echo Func::displayError($error);
        if ($tipe == 'file')
        {
            echo '<div class="form-group"><label>Nama file</label>';
            echo '<input class="form-control input-sm" type="text" name="value" value="' .
                htmlentities($value) . '"/>';
            echo '</div>';
        }
        else
        {
            echo '<div class="form-group"><label>Nama Folder</label>' .
                '<input class="form-control input-sm" type="text" name="value" value="' .
                htmlentities($value) . '"/></div>';
        }
        echo '<p><button class="btn btn-primary btn-sm" type="submit">Simpan</button>' .
            '&nbsp;<a class="btn btn-default btn-sm" data-dismiss="modal" href="' . $baseurl .
            '/' . $controller . '/file_browser/page/1/' . $prev_dir . '">Batal</a></p>';
        echo '</form></div>';
        break;

    case 'actions':
        $dir = get_dir(4);
        $dir = mb_substr($dir, -1) == '/' ? mb_substr($dir, 0, -1) : $dir;
        if ((!is_dir($site_root . '/' . $dir) && !is_file($site_root . '/' . $dir) || ($dir ==
            '')))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1');
            exit();
        }
        $name = basename($dir);
        if (is_dir($site_root . '/' . $dir))
        {
            $typ = 'folder';
            $textl = 'Folder: ' . $name;
        }
        else
        {
            $typ = 'file';
            $textl = 'File: ' . $name;
        }
        $pageTitle = 'Creator / ' . $textl;
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">' . $textl . '</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '/' . $controller .
            '/file_browser/page/1/' . $dir . '">' .
            '<i class="fa fa-folder-open"></i> File browser</a></li>' .
            '<li class="active">' . $name . '</li></ol>';
        echo '<div class="list-group">';
        echo '<a class="list-group-item" href="' . $baseurl . '/' . $controller .
            '/rename/' . $dir . '">' . '<i class="fa fa-pencil"></i> Ubah nama</a>';
        echo '<a class="list-group-item" href="' . $baseurl . '/' . $controller .
            '/file_browser/page/1/?move=' . $dir . '">' .
            '<i class="fa fa-exchange"></i> Pindah</a>';
        echo '<a class="list-group-item" href="' . $baseurl . '/' . $controller .
            '/delete/' . $dir . '">' . '<i class="fa fa-times"></i> Hapus</a>';
        if ($typ == 'file')
        {
            echo '<a class="list-group-item" href="' . $baseurl . '/' . $controller .
                '/download/' . $dir . '">' . '<i class="fa fa-download"></i> Download</a>';
            $ext = Func::getExt($dir);
            if (in_array($ext, $ext_text))
                echo '<a class="list-group-item" href="' . $baseurl . '/' . $controller .
                    '/edit_file/' . $dir . '">' . '<i class="fa fa-edit"></i> Edit</a>';
        }
        echo '</div>';
        break;

    case 'create_file':
        $dir = get_dir(4);
        $dir = mb_substr($dir, -1) == '/' ? mb_substr($dir, 0, -1) : $dir;
        if (!is_dir($site_root . '/' . $dir))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1');
            exit();
        }
        $errors = array();
        $value = isset($_POST['value']) ? trim($_POST['value']) : '';
        if ($value != '')
        {
            if (mb_strlen($value) < 2 || mb_strlen($value) > 30)
                $errors[] = 'Panjang minimal 2 dan maksimal 30 karakter';
            if (!filter_var($value, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' =>
                        '/^[a-zA-Z0-9\_\-\.]+$/'))))
                $errors[] = 'Nama file salah';
            if (file_exists($site_root . '/' . $dir . '/' . $value) || is_dir($site_root .
                '/' . $dir . '/' . $value))
                $errors[] = 'File/Folder sudah ada';
            if (!$errors)
            {
                if (file_put_contents($site_root . '/' . $dir . '/' . $value, "\n") != false)
                {
                    header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1/' . $dir);
                    exit();
                }
                else
                {
                    $errors[] = 'Gagal membuat file';
                }
            }
        }
        $pageTitle = 'Creaor / Membuat file';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Membuat file</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '/' . $controller .
            '/file_browser/page/1/' . $dir . '">' .
            '<i class="fa fa-folder-open"></i> File browser</a></li>' .
            '<li class="active">Membuat file</li></ol>';
        echo '<div class="form"><form role="form" action="' . $baseurl . '/' . $controller .
            '/create_file/' . $dir . '" method="post">';
        if ($errors)
            echo Func::displayError($errors);
        echo '<div class="form-group"><label>Nama File</label>' .
            '<input class="form-control input-sm" type="text" name="value" value="' .
            htmlentities($value) . '"/>' .
            '<p class="help-block">Karakter yang diijinkan a-z, 0-9 dan simbol _</p></div>';
        echo '<p><button class="btn btn-primary btn-sm" type="submit">Buat</button>' .
            '&nbsp;<a class="btn btn-default btn-sm" data-dismiss="modal" href="' . $baseurl .
            '/' . $controller . '/file_browser/page/1/' . $dir . '">Batal</a></p>';
        echo '</form></div>';
        break;

    case 'create_dir':
        $dir = get_dir(4);
        $dir = mb_substr($dir, -1) == '/' ? mb_substr($dir, 0, -1) : $dir;
        if (!is_dir($site_root . '/' . $dir))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser');
            exit();
        }
        $errors = array();
        $value = isset($_POST['value']) ? trim($_POST['value']) : '';
        if ($value != '')
        {
            if (mb_strlen($value) < 2 || mb_strlen($value) > 30)
                $errors[] = 'Panjang minimal 2 dan maksimal 30 karakter';
            if (!filter_var($value, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' =>
                        '/^[a-zA-Z0-9\_\-\.]+$/'))))
                $errors[] = 'Nama folder salah';
            if (is_dir($site_root . '/' . $dir . '/' . $value) || file_exists($site_root .
                '/' . $dir . '/' . $value))
                $errors[] = 'Folder/File sudah ada';
            if (!$errors)
            {
                if (mkdir($site_root . '/' . $dir . '/' . $value, 0777))
                {
                    header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1/' . $dir .
                        '/' . $value);
                    exit();
                }
                else
                {
                    $errors[] = 'Gagal membuat folder';
                }
            }
        }
        $page_title = 'Creator / Membuat Folder';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Membuat folder</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '/' . $controller .
            '/file_browser/page/1/' . $dir . '">' .
            '<i class="fa fa-folder-open"></i> File browser</a></li>' .
            '<li class="active">Membuat folder</li></ol>';
        echo '<div class="form"><form role="form" action="' . $baseurl . '/' . $controller .
            '/create_dir/' . $dir . '" method="post">';
        if ($errors)
            echo Func::displayError($errors);
        echo '<div class="form-group"><label>Nama Folder</label>' .
            '<input class="form-control input-sm" type="text" name="value" value="' .
            htmlentities($value) . '"/>' .
            '<p class="help-block">Karakter yang diijinkan a-z, A-Z, 0-9, . (titik) dan simbol _</p></div>';
        echo '<p><button class="btn btn-primary btn-sm" type="submit">Buat</button>' .
            '&nbsp;<a class="btn btn-default btn-sm" data-dismiss="modal" href="' . $baseurl .
            '/' . $controller . '/file_browser/page/1/' . $dir . '">Batal</a></p>';
        echo '</form></div>';
        break;

    case 'file_browser':
        $pageTitle = 'Creator / File Browser';
        if (!isset($_GET['page']))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1');
            exit();
        }
        parse_str($_SERVER['QUERY_STRING'], $r2);
        if (isset($r2['move']))
            $move = '?move=' . htmlentities($r2['move']);
        else
            $move = '';

        $dir = get_dir(6);
        $dir = mb_substr($dir, -1) == '/' ? mb_substr($dir, 0, -1) : $dir;
        $dir = mb_substr($dir, 0, 1) == '/' ? mb_substr($dir, 1) : $dir;
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        $site_dir = $site_root . ($dir == '' ? '' : '/' . $dir);
        if (!is_dir($site_dir))
        {
            header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1');
            exit();
        }
        echo '<h3 class="head-title">File Manager</h3>';
        if ($dir == '')
            echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '">' .
                '<i class="fa fa-home"></i> Home</a></li>' .
                '<li class="active">File Manager</li></ol>';
        echo '<p><a class="btn btn-default btn-sm" href="' . $baseurl . '/' . $controller .
            '/create_dir/' . $dir . '">' .
            '<i class="fa fa-folder"></i> Buat Folder</a>&nbsp;<a class="btn btn-default btn-sm" href="' .
            $baseurl . '/' . $controller . '/create_file/' . $dir . '">' .
            '<i class="fa fa-file"></i> Buat File</a>&nbsp;<a class="btn btn-default btn-sm" href="' .
            $baseurl . '/' . $controller . '/upload/' . $dir . '">' .
            '<i class="fa fa-upload"></i> Upload</a></p>';
        if ($dir != '')
        {
            $xdir = '';
            $dirs = preg_split("/\/+/", $dir);
            $total_drx = count($dirs);
            echo '<ol class="breadcrumb">';
            echo '<li><a href="' . $baseurl . '/' . $controller . '/file_browser/page/1/' .
                $move . '">' . '<i class="fa fa-folder-open"></i> File browser</a></li>';
            for ($x = 0; $x < $total_drx; $x++)
            {
                if ($x == ($total_drx - 1))
                {
                    echo '<li class="active">' . $dirs[$total_drx - 1] . '</li>';
                }
                else
                {
                    echo '<li><a href="' . $baseurl . '/' . $controller . '/file_browser/page/1/' .
                        $xdir . $dirs[$x] . $move . '">' . $dirs[$x] . '</a></li>';
                }
                $xdir .= $dirs[$x] . '/';
            }
            echo '</ol>';

        }
        if ($move != '')
        {
            echo '<div class="alert alert-info"><a class="alert-link" href="' . $baseurl .
                '/' . $controller . '/move/' . $dir . $move .
                '">Pindahkan <span class="text-red">' . htmlentities(basename($r2['move'])) .
                '</span> ke sini</a>, atau <a class="alert-link" href="' . $baseurl . '/' . $controller .
                '/file_browser/page/' . $page . '/' . $dir .
                '">batalkan</a> tindakan ini!</div>';
        }
        $files = Func::readDir($site_dir);
        $total = count($files);
        if ($total)
        {
            if (!in_array('index.html', $files) && $move == '')
            {
                echo '<div class="alert alert-warning">' .
                    'Buatlah file dengan nama <strong>index.html</strong> ' .
                    'agar folder bisa dijelajahi secara otomatis</div>';
            }
            $end = $start + $kmess;
            if ($end > $total)
                $end = $total;
            echo '<ul class="list-group">';
            for ($e = $start; $e < $end; $e++)
            {
                echo '<li class="list-group-item">';
                if (is_file($site_dir . '/' . $files[$e]))
                {
                    echo '<a href="' . $baseurl . '/' . $controller . '/file_info/' . ($dir == '' ?
                        '' : $dir . '/') . $files[$e] . $move . '"><i class="fa fa-file"></i> ' . $files[$e] .
                        '</a>';
                }
                else
                {
                    echo '<a href="' . $baseurl . '/' . $controller . '/file_browser/page/' . $page .
                        '/' . ($dir == '' ? '' : $dir . '/') . $files[$e] . $move .
                        '"><i class="fa fa-folder"></i> ' . $files[$e] . '</a>';
                }
                echo '<a class="pull-right" href="' . $baseurl . '/' . $controller . '/actions/' . ($dir ==
                    '' ? '' : $dir . '/') . $files[$e] . '"> <i class="fa fa-cog"></i> </a>';
                echo '</li>';
            }
            echo '</ul>';
            if ($total > $kmess)
                echo Func::displayPagination($baseurl . '/' . $controller . '/file_browser/', $start,
                    $total, $kmess, 'page/%d/' . strtr($dir, array('%' => '%%')) . $move);
        }
        else
        {
            echo '<div class="alert alert-info">Folder kosong</div>';
        }
        break;

    case 'index':
        header('Location: ' . $baseurl . '/' . $controller . '/file_browser/page/1');
        exit();
        break;

    default:
        header('Location: ' . $baseurl . '/error/404');
        exit();
        break;
}

include_once (ROOTPATH . 'iwbx-includes/footer.php');
