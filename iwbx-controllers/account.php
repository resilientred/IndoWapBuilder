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
    $user->redirect(urlencode($set['url'] . '/index.php/account'));
switch ($action)
{
    case 'edit':
        $errors = array();
        $author = isset($_POST['author']) ? trim($_POST['author']) : $user->data['name'];
        $email = isset($_POST['email']) ? trim($_POST['email']) : $user->data['email'];
        $gender = isset($_POST['gender']) ? trim($_POST['gender']) : $user->data['gender'];

        if (isset($_POST['submit']))
        {
            if (mb_strlen($author) < 3 || mb_strlen($author) > 32)
                $errors['author'] = 'Panjang nama min. 3 s/d 32 karakter.';
            elseif (str_word_count($author) > 3)
                $errors['author'] = 'Nama tidak benar.';
            elseif (!filter_var($author, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' =>
                        '/[a-zA-Z0-9 \-\=\@\!\?\_\(\)\[\]]+$/'))))
                $errors['author'] = 'Nama tidak benar.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                $errors['email'] = 'Email tidak valid.';
            else
            {
                $req = Base::db()->prepare("SELECT * FROM `user` WHERE `user_id` != ? AND `email` = ?");
                $req->execute(array($user->id, $email));
                if ($req->rowCount() > 0)
                    $errors['email'] = 'Email sudah terdaftar.';
            }
            if (!in_array($gender, array('male', 'female')))
                $errors['gender'] = 'Jenis kelamin tidak benar!';
            if (empty($errors))
            {
                $us = Base::db()->prepare("UPDATE `user` SET `name` = ?, `email` = ?, `gender` = ? WHERE `user_id` = ?");
                $us->execute(array(
                    $author,
                    $email,
                    $gender,
                    $user->id,
                    ));
                header('Location: ' . $baseurl . '/account');
                exit();
            }
        }

        $pageTitle = 'Akun / Edit Profile';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Edit Profile</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '">' .
            '<i class="fa fa-home"></i> Home</a></li><li><a href="' . $baseurl .
            '/account">' . 'Akun</a></li>' . '<li class="active">Edit Profile</li></ol>';
        if ($errors)
            echo '<div class="alert alert-danger">' . implode('<br/>', $errors) . '</div>';
        echo '<div class="form"><form action="' . $baseurl .
            '/account/edit" method="post">' . '<div class="form-group"><label>Nama</label>' .
            '<input class="form-control input-sm" type="text" name="author" value="' .
            htmlspecialchars($author) . '" required/></div>' .
            '<div class="form-group"><label>Email</label>' .
            '<input class="form-control input-sm" type="email" name="email" value="' .
            htmlspecialchars($email) .
            '" required/><p class="help-block">Harap memasukan alamat email dengan benar,' .
            ' ini digunakan jika Kamu lupa kata sandi</p></div>' .
            '<div class="form-group"><label>Jenis kelamin</label>' .
            '<select class="form-control input-sm" name="gender">' . '<option value="male"' . ($gender ==
            'male' ? ' selected="selected"' : '') . '>Laki-laki</option>' .
            '<option value="female"' . ($gender == 'female' ? ' selected="selected"' : '') .
            '>Perempuan</option></select></div>' .
            '<p><button class="btn btn-primary btn-sm" type="submit" name="submit">Simpan</button>' .
            '&nbsp;<a class="btn btn-default btn-sm" href="' . $baseurl .
            '/account/edit">Reset form</a></p>' . '</form></div>';
        break;

    case 'change_password':
        $errors = array();
        $pass = isset($_POST['pass']) ? $_POST['pass'] : '';
        $pass1 = isset($_POST['pass1']) ? $_POST['pass1'] : '';
        $pass2 = isset($_POST['pass2']) ? $_POST['pass2'] : '';

        if (isset($_POST['submit']))
        {
            if ($user->data['password'] != md5(md5($pass)))
                $errors['pass'] = 'Kata sandi sekarang tidak benar!';
            if ($pass1 != $pass2)
                $errors['pass2'] = 'Kata sandi tidak sama.';
            if (mb_strlen($pass1) < 4 || mb_strlen($pass1) > 16 || mb_strlen($pass2) < 4 ||
                mb_strlen($pass2) > 16)
                $errors['pass1'] = 'Kata sandi minimal 4 s/d 16 karakter.';
            if (empty($errors))
            {
                $password_hash = md5(md5($pass1));
                $us = Base::db()->prepare("UPDATE `user` SET `password` = ? WHERE `user_id` = ?");
                $us->execute(array(
                    $password_hash,
                    $user->id,
                    ));
                $_SESSION['uid'] = $user->id;
                $_SESSION['upw'] = md5($pass1);

                header('Location: ' . $baseurl . '/account');
                exit();
            }
        }

        $pageTitle = 'Akun / Ubah Kata sandi';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Ubah Kata sandi</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '">' .
            '<i class="fa fa-home"></i> Home</a></li><li><a href="' . $baseurl .
            '/account">' . 'Akun</a></li>' . '<li class="active">Ubah Kata sandi</li></ol>';
        if ($errors)
            echo '<div class="alert alert-danger">' . implode('<br/>', $errors) . '</div>';
        echo '<div class="form"><form action="' . $baseurl .
            '/account/change_password" method="post">' .
            '<div class="form-group"><label>Kata sandi sekarang</label>' .
            '<input class="form-control input-sm" type="password" name="pass" value="' .
            '" required/></div>' . '<div class="form-group"><label>Kata sandi baru</label>' .
            '<input class="form-control input-sm" type="password" name="pass1" value="' .
            '" required/></div>' .
            '<div class="form-group"><label>Ulangi Kata sandi baru</label>' .
            '<input class="form-control input-sm" type="password" name="pass2" value="' .
            '" required/></div>' .
            '<p><button class="btn btn-primary btn-sm" type="submit" name="submit">Simpan</button>' .
            '</p>' . '</form></div>';
        break;

    case 'index':
        $pageTitle = 'Akun';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Akun</h3>';
        echo '<ol class="breadcrumb"><li><a href="' . $baseurl . '">' .
            '<i class="fa fa-home"></i> Home</a></li>' . '<li class="active">Akun</li></ol>';
        echo '<div class="row"><div class="col-sm-4"><div class="list-group">' .
            '<a class="list-group-item" href="' . $baseurl .
            '/account/edit"><i class="fa fa-edit"></i> Edit Profile</a>' .
            '<a class="list-group-item" href="' . $baseurl .
            '/account/change_password"><i class="fa fa-lock"></i> Ubah Kata sandi</a>';
        if ($user->data['rights'] == 10)
            echo '<a class="list-group-item" href="' . $baseurl .
                '/admin"><i class="fa fa-shield"></i> Admin Panel</a>';

        echo '</div></div>';
        echo '<div class="col-sm-8"><dl class="dl-horizontal"><dt>ID</dt><dd>' .
            htmlspecialchars($user->id) . '</dd><dt>Nama</dt><dd>' . htmlspecialchars($user->
            getName()) . '</dd><dt>Email</dt><dd>' . htmlspecialchars($user->data['email']) .
            ' (tersembunyi)</dd><dt>Jenis kelamin</dt><dd>' . strtr($user->data['gender'],
            array('male' => 'Laki-laki', 'female' => 'Perempuan')) .
            '</dd><dt>Mendaftar</dt><dd>' . Func::displayDate($user->data['regtime']) .
            '</dd></dl></div></div>';
        break;

    default:
        header('Location: ' . $baseurl . '/error/404');
        exit();
        break;
}
include_once (ROOTPATH . 'iwbx-includes/footer.php');

?>