<?php

switch ($action)
{
    case 'logout':
        $user->logOut();
        header('Location: ' . $baseurl . '/site/index');
        exit();
        break;

    case 'whois':
        $pageTitle = 'Whois';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Whois</h3>';
        echo '<div class="col-sm-offset-4"><form method="get" action="' . $baseurl .
            '/site/whois">' .
            '<div class="input-group"><input class="form-control input-sm" type="text" name="domain" value="" ' .
            'placeholder="Example: site.' . $_SERVER['SERVER_NAME'] . '"/>' .
            '<span class="input-group-btn"><button class="btn btn-primary btn-sm" type="submit">WHOIS</button>' .
            '</span></div></form></div><hr/>';
        if (isset($_GET['domain']))
        {
            $domain = strtolower(htmlentities($_GET['domain']));
            if (substr($domain, 0, 4) == "www.")
                $domain = substr($domain, 4);
            $br = Base::db()->prepare("SELECT * FROM `site` WHERE `url` = ?");
            $br->execute(array($domain));
            if ($br->rowCount() != 0)
            {
                $site = $br->fetch();
                $uz = Base::db()->query("SELECT `name` FROM `user` WHERE `user_id` = '" .
                    $site['user_id'] . "'");
                $mass1 = $uz->fetch();
                echo '<div class="well well-sm"><dl class="dl-horizontal">' .
                    '<dt>URL Situs</dt><dd><a href="http://' . $domain .
                    '">http://' . $domain . '</a></dd><dt>Pemilik</dt><dd>' .
                    htmlspecialchars($mass1['name']) .
                    '</dd><dt>Mendaftar</dt><dd>' . Func::displayDate($site['time']) .
                    '</dd></dl></div>';
            }
            else
            {
                echo
                    '<div class="alert alert-danger">Data tidak ditemukan.</div>';
            }
        }
        break;

    case 'domain_check':
        $pageTitle = 'Domain Checker';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="head-title">Domain Checker</h3>';
        echo '<div class="domain-check"><form role="form" action="' . $baseurl .
            '/site/domain_check" method="post">' .
            '<h4 class="text-center">Periksa ketersediaan subdomain</h4>' .
            '<div class="input-group">' .
            '<input class="form-control input-sm" name="subdomain" placeholder="Masukan subdomain"/>' .
            '<span class="input-group-btn">' .
            '<button type="submit" class="btn btn-primary btn-sm">Check</button>' .
            '</span></div><p><div class="row">';
        foreach (unserialize($set['domains']) as $domain)
        {
            echo '<div class="col-xs-6"><div class="checkbox"><label>' .
                '<input type="checkbox" name="domains[]" value="' . $domain .
                '"/> ' . $domain . '</label></div></div>';
        }
        echo '</div></p></form></div>';
        $subdomain = isset($_POST['subdomain']) ? Func::permalink(trim($_POST['subdomain'])) : false;
        if ($subdomain != false && (mb_strlen($subdomain) < 4 || mb_strlen($subdomain) >
            16))
            $subdomain = false;
        $domains = isset($_POST['domains']) ? $_POST['domains'] : false;

        $site_domains = unserialize($set['domains']);

        if ($subdomain && $domains && is_array($domains))
        {
            echo '<div class="row" style="margin-top:20px;">';
            foreach ($domains as $domain)
            {
                if (in_array($domain, $site_domains))
                {
                    echo '<div class="col-sm-6">';
                    $url = $subdomain . '.' . $domain;
                    $req = Base::db()->prepare("SELECT COUNT(*) FROM `site` WHERE `url`= ?");
                    $req->execute(array($url));
                    $total = $req->fetch(PDO::FETCH_NUM);
                    echo
                        '<div style="background-color:#f9f9f9;box-shadow: 0 3px 9px #ccc;padding:10px;">';
                    if ($total[0] == 0)
                    {
                        echo
                            '<div class="alert alert-success" style="margin-bottom:5px"><h4>' .
                            $url . '</h4><p>Domain masih tersedia!</p>' .
                            '</div><p><a class="btn btn-primary btn-block btn-sm" href="' .
                            $baseurl . '/panel/create_site/domain/' . $url .
                            '">Pendaftaran</a></p>';
                    }
                    else
                    {
                        echo
                            '<div class="alert alert-danger" style="margin-bottom:5px"><h4>' .
                            $url . '</h4><p>' . 'Domain tidak tersedia!</p>' .
                            '</div><p><a class="btn btn-primary btn-block btn-sm" href="' .
                            $baseurl . '/site/whois/domain/' . $url .
                            '">WHOIS</a></p>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
            }
            echo '</div>';
        }

        break;

    case 'reset_password':
        $code = isset($_GET['code']) ? $_GET['code'] : '';
        $pageTitle = 'Setel ulang kata sandi';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h4 class="head-title">Setel ulang kata sandi</h4>';
        if (mb_strlen($code) != 20)
        {
            echo Func::displayError('Kode konfirmasi tidak benar!');
            include_once (ROOTPATH . 'iwbx-includes/footer.php');
            exit();
        }
        if (abs(intval(mb_substr($code, 0, 10))) < (time() - 3600))
        {
            echo Func::displayError('Kode konfirmasi sudah tidak berlaku lagi!');
            include_once (ROOTPATH . 'iwbx-includes/footer.php');
            exit();
        }
        $req = Base::db()->prepare("SELECT * FROM `user` WHERE `code` = ?");
        $req->execute(array($code));
        if ($req->rowCount() != 1)
        {
            echo Func::displayError('Kode konfirmasi tidak benar!');
            include_once (ROOTPATH . 'iwbx-includes/footer.php');
            exit();
        }
        $usr = $req->fetch();
        $error = false;
        $pass1 = isset($_POST['pass1']) ? $_POST['pass1'] : '';
        $pass2 = isset($_POST['pass2']) ? $_POST['pass2'] : '';

        if (isset($_POST['submit']))
        {
            if ($pass1 != $pass2)
                $errors['pass2'] = 'Kata sandi tidak sama.';
            if (mb_strlen($pass1) < 4 || mb_strlen($pass1) > 16 || mb_strlen($pass2) <
                4 || mb_strlen($pass2) > 16)
                $errors['pass1'] = 'Kata sandi minimal 4 s/d 16 karakter.';
            if (empty($errors))
            {
                $password_hash = md5(md5($pass1));
                $us = Base::db()->prepare("UPDATE `user` SET `password` = ?, `code` = ? WHERE `user_id` = ?");
                $us->execute(array(
                    $password_hash,
                    '',
                    $usr['user_id'],
                    ));
                $_SESSION['uid'] = $usr['user_id'];
                $_SESSION['upw'] = md5($pass1);

                header('Location: ' . $baseurl . '/account');
                exit();
            }
        }
        if ($error)
            echo '<div class="alert alert-danger">' . $error . '</div>';
        echo '<div class="form"><form action="' . $baseurl .
            '/site/reset_password/code/' . $code . '" method="post">' .
            '<div class="form-group"><label>Kata sandi baru</label>' .
            '<input class="form-control input-sm" type="password" name="pass1" value="' .
            '" required/></div>' .
            '<div class="form-group"><label>Ulangi Kata sandi baru</label>' .
            '<input class="form-control input-sm" type="password" name="pass2" value="' .
            '" required/></div>' .
            '<p><button class="btn btn-primary btn-sm" type="submit" name="submit">Simpan</button>' .
            '</p>' . '</form></div>';
        break;
    case 'forgot_password':
        $error = false;
        $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
        if (isset($_POST['submit']))
        {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                $error = 'Email tidak valid.';
            else
            {
                $req = Base::db()->prepare("SELECT * FROM `user` WHERE `email` = ?");
                $req->execute(array($email));
                if ($req->rowCount() == 0)
                    $error = 'Email tidak terdaftar.';
                else
                {
                    $usr = $req->fetch();
                    if (!empty($usr['code']))
                    {
                        if (($tm = abs(intval(mb_substr($usr['code'], 0, 10)))) >
                            (time() - 600))
                            $error =
                                'Sebelumnya kode konfirmasi telah dikirim pada <strong>' .
                                Func::displayDate($tm) .
                                '</strong>, Untuk mengirim ulang kode konfirmasi silakan tunggu minimal 10 ' .
                                'menit dari permintaan sebelumnya!';
                    }
                }
            }
            if (!$error)
            {
                $code = time() . Func::generatePassword();
                $subject = 'Setel Ulang Kata Sandi';
                $mail = "Hai, {$usr['name']}\r\n" .
                    "Baru-baru ini Anda telah meminta menyetel ulang kata sandi pada situs {$set['url']},\r\n" .
                    "untuk melanjutkan silakan klik link berikut ini\r\n" . "{$set['url']}/index.php/site/" .
                    "reset_password/code/$code\r\n" . "\r\nJika bukan Anda yang mengirimkan permintaan tersebut abaikan pesan ini.";
                $adds = "From: <" . $set['siteemail'] . ">\r\n";
                $adds .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
                if (mail($email, $subject, $mail, $adds))
                {
                    $uss = Base::db()->prepare("UPDATE `user` SET `code` = ? WHERE `user_id` = ?");
                    $uss->execute(array(
                        $code,
                        $usr['user_id'],
                        ));

                    $_SESSION['notice'] =
                        'Kode konfirmasi telah dikirim ke alamat email Kamu.';
                    header('Location: ' . $baseurl . '/site/login');
                    exit();

                }
                else
                {
                    $error = 'Gagal mengirim email';
                }
            }

        }

        $pageTitle = 'Lupa Kata sandi';
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h4 class="head-title">Lupa Kata sandi</h4>';
        if ($error)
            echo '<div class="alert alert-danger">' . $error . '</div>';
        echo '<div class="form"><form role="form" action="' . $baseurl .
            '/site/forgot_password" method="post">' . '<div class="form-group">' .
            '<label>Email</label>' .
            '<input class="form-control input-sm" type="email" name="email" value="" required/>' .
            '</div>' .
            '<p><button class="btn btn-primary btn-sm" type="submit" name="submit">Kirim</button></p>' .
            '</form></div>';
        break;

    case 'register':
        if ($user->id)
        {
            header('Location: ' . $baseurl . '/panel');
            exit();
        }
        $pageTitle = 'Pendaftaran';
        $errors = array();
        $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
        $author = isset($_POST['author']) ? trim($_POST['author']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $repeat_password = isset($_POST['repeat_password']) ? $_POST['repeat_password'] :
            '';

        if (isset($_POST['submit']))
        {
            if (mb_strlen($author) < 3 || mb_strlen($author) > 32)
                $errors['author'] = 'Panjang nama min. 3 s/d 32 karakter.';
            elseif (str_word_count($author) > 3)
                $errors['author'] = 'Nama tidak benar.';
            elseif (!filter_var($author, FILTER_VALIDATE_REGEXP, array('options' =>
                    array('regexp' => '/[a-zA-Z0-9 \-\=\@\!\?\_\(\)\[\]]+$/'))))
                $errors['author'] = 'Nama tidak benar.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                $errors['email'] = 'Email tidak valid.';
            else
            {
                $req = Base::db()->prepare("SELECT * FROM `user` WHERE `email` = ?");
                $req->execute(array($email));
                if ($req->rowCount() > 0)
                    $errors['email'] = 'Email sudah terdaftar.';
            }
            if ($password != $repeat_password)
                $errors['password'] = 'Kata sandi tidak sama.';
            if (mb_strlen($password) < 4 || mb_strlen($password) > 16)
                $errors['password'] = 'Kata sandi minimal 4 s/d 16 karakter.';
            if (empty($errors))
            {
                $password_hash = md5(md5($password));

                $us = Base::db()->prepare("INSERT INTO `user` SET `name` = ?, `email` = ?, `password` = ?, `regtime` = ?");
                $us->execute(array(
                    $author,
                    $email,
                    $password_hash,
                    time(),
                    ));
                $uid = Base::db()->lastInsertId();

                $_SESSION['uid'] = $uid;
                $_SESSION['upw'] = md5($password);
                header('Location: ' . $baseurl . '/panel/');
                exit();

            }
            else
            {
                $errors[] = 'Pendaftaran gagal, silakan hubungi Administrator.';
            }

        }
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h4 class="head-title">Pendaftaran</h4>';
        if ($errors)
            echo '<div class="alert alert-danger">' . implode('<br/>', $errors) .
                '</div>';
        echo '<div class="form"><form role="form" action="' . $baseurl .
            '/site/register" method="post">';
        echo '<div class="form-group">' . '<label>Nama Anda</label>' .
            '<input class="form-control input-sm" type="text" name="author" value="' .
            htmlentities($author) . '" required/>' . '</div>';
        echo '<div class="form-group">' . '<label>Email</label>' .
            '<input class="form-control input-sm" type="email" name="email" value="' .
            htmlentities($email) . '" required/>' . '</div>';
        echo '<div class="form-group"><label>Kata sandi</label>' .
            '<input class="form-control input-sm" type="password" name="password" value="" required/></div>';
        echo '<div class="form-group"><label>Ulangi kata sandi</label>' .
            '<input class="form-control input-sm" type="password" name="repeat_password" value="" required/></div>';
        echo '<p><button class="btn btn-primary btn-sm" type="submit" name="submit">Mendaftar</button></p>' .
            '</form></div>';
        break;

    case 'login':
        if ($user->id)
        {
            header('Location: ' . $baseurl . '/panel');
            exit();
        }
        $pageTitle = 'Masuk';
        $error = false;
        $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
        $redirect = isset($_GET['redirect']) ? filter_var(urldecode($_GET['redirect']),
            FILTER_SANITIZE_URL, FILTER_SANITIZE_ENCODED) : false;
        if ($redirect)
        {
            if (!filter_var($redirect, FILTER_VALIDATE_URL,
                FILTER_FLAG_SCHEME_REQUIRED))
                $redirect = false;
        }
        if (isset($_POST['submit']))
        {
            $password = $_POST['password'];
            if ($email && $password)
            {
                $req = Base::db()->prepare("SELECT * FROM `user` WHERE `email` = ?");
                $req->execute(array($email));
                if ($req->rowCount() == 0)
                    $error = true;
                else
                {
                    $res = $req->fetch();
                    $password_hash = $res['password'];
                    if ($password_hash == md5(md5($password)))
                    {
                        $_SESSION['uid'] = $res['user_id'];
                        $_SESSION['upw'] = md5($password);
                        header('Location: ' . $baseurl . '/panel');
                        exit();
                    }
                    else
                        $error = true;
                }
            }
            else
                $error = true;
        }
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h4 class="head-title">Masuk</h4>';
        if ($error)
            echo '<div class="alert alert-danger">Email atau Kata sandi tidak benar</div>';
        echo Func::getNotice() . '<div class="form"><form role="form" action="' .
            $baseurl . '/site/login' . ($redirect ? '?redirect=' . $redirect :
            '') . '" method="post">' . '<div class="form-group">' .
            '<label>Email</label>' .
            '<input class="form-control input-sm" type="email" name="email" value="' .
            htmlentities($email) . '" required/>' .
            '</div><div class="form-group"><label>Kata sandi</label>' .
            '<input class="form-control input-sm" type="password" name="password" value="" required/></div>' .
            '<p><button class="btn btn-primary btn-sm" type="submit" name="submit">Masuk</button></p>' .
            '<p class="help-block"><a href="' . $baseurl .
            '/site/forgot_password">Lupa kata sandi?</a><br /><a href="' . $baseurl .
            '/site/register">Pendaftaran</a></p></form></div>';
        break;

    case 'index':
        include_once (ROOTPATH . 'iwbx-includes/header.php');
        echo '<h3 class="text-center">Buat wap site gratis</h3>' .
            '<p style="margin-bottom:30px;" class="text-center">' .
            htmlspecialchars($set['sitename']) .
            ' adalah tempat terbaik untuk situs pribadi ataupun situs usaha.</p>' .
            '<div class="domain-check"><form role="form" action="' . $baseurl .
            '/site/domain_check" method="post">' .
            '<h4 class="text-center">Periksa ketersediaan subdomain</h4>' .
            '<div class="input-group">' .
            '<input class="form-control input-sm" name="subdomain" placeholder="Masukan subdomain"/>' .
            '<span class="input-group-btn">' .
            '<button type="submit" class="btn btn-primary btn-sm">Check</button>' .
            '</span></div><p><div class="row">';
        foreach (unserialize($set['domains']) as $domain)
        {
            echo '<div class="col-xs-6"><div class="checkbox"><label>' .
                '<input type="checkbox" name="domains[]" value="' . $domain .
                '"/> ' . $domain . '</label></div></div>';
        }
        echo '</div></p></form></div>';
        echo '<div class="row fiture">';
        echo '<div class="col-sm-6"><div class="bg-red fiture-icon">' .
            '<i class="fa fa-globe"></i></div>' .
            '<h4 class="fiture-title">Multi Site</h4><p class="margin">' .
            'Gak perlu bikin banyak akun kalo cuma mau bikin beberapa situs, ' .
            'karena dalam satu akun Kamu bisa bikin lebih dari satu situs.</p></div>';
        echo '<div class="col-sm-6"><div class="bg-green fiture-icon">' .
            '<i class="fa fa-code"></i></div>' .
            '<h4 class="fiture-title">Template Sistem</h4><p class="margin">Sistem template yang memiliki banyak fungsi dan modul-modul canggih seperti: Blog, Chat, dll.</p></div>';
        echo '</div>';
        break;

    default:
        header('Location: ' . $baseurl . '/error/404');
        exit();
        break;
}
include_once (ROOTPATH . 'iwbx-includes/footer.php');

?>