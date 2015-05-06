<?php

session_start();

function install_indowapbuilder($pdo, $file = false)
{
    $query = fread(fopen($file, 'r'), filesize($file));
    $query = trim($query);
    $query = preg_replace("/\n\#[^\n]*/", '', "\n" . $query);
    $buffer = array();
    $ret = array();
    $in_string = false;
    for ($i = 0; $i < strlen($query) - 1; $i++)
    {
        if ($query[$i] == ";" && !$in_string)
        {
            $ret[] = substr($query, 0, $i);
            $query = substr($query, $i + 1);
            $i = 0;
        }
        if ($in_string && ($query[$i] == $in_string) && $buffer[1] != "\\")
        {
            $in_string = false;
        }
        elseif (!$in_string && ($query[$i] == '"' || $query[$i] == "'") && (!
            isset($buffer[0]) || $buffer[0] != "\\"))
        {
            $in_string = $query[$i];
        }
        if (isset($buffer[1]))
        {
            $buffer[0] = $buffer[1];
        }
        $buffer[1] = $query[$i];
    }
    if (!empty($query))
    {
        $ret[] = $query;
    }
    for ($i = 0; $i < count($ret); $i++)
    {
        $ret[$i] = trim($ret[$i]);
        if (!empty($ret[$i]) && $ret[$i] != "#")
        {
            $pdo->query($ret[$i]);
        }
    }
}

$db = isset($_POST['db']) ? $_POST['db'] : array();
$data = isset($_POST['data']) ? $_POST['data'] : array();
$err_conn = false;
$conn = false;

if (isset($db['host']) && isset($db['user']) && isset($db['password']) && isset($db['database']))
{
    $dsn = 'mysql:dbname=' . $db['database'] . ';host=' . $db['host'];
    try
    {
        $pdo = new PDO($dsn, $db["user"], $db["password"], array
            (PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $conn = true;
    }
    catch (PDOException $e)
    {
        $err_conn = $e->getMessage();
        $conn = false;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		<meta name="description" content=""/>
		<meta name="author" content=""/>
		<title>
			Installasi IndoWapBuilder
		</title>
		<link href="iwbx-assets/css/bootstrap.min.css" rel="stylesheet"/>
		<link href="iwbx-assets/css/font-awesome.min.css" rel="stylesheet"/>
		<link href="iwbx-assets/css/custom.css" rel="stylesheet"/>
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js">
			</script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js">
			</script>
		<![endif]-->
	</head>
	<body>
		<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<a class="navbar-brand" href="#">IndoWapBuilder</a>
				</div>
			</div>
		</nav>
		<div class="container main" role="main">
			<div class="content">
				<h3 class="head-title">
					Installasi IndoWapBuilder
				</h3>
				<div class="form">
                <?php if (isset($_POST['submit2'])):?>
                    <?php if($conn):?>
                    <?php
                        install_indowapbuilder($pdo,'indowapbuilder.sql');
                        $q = $pdo->prepare("UPDATE `set` SET `val` = ? WHERE `key` = ?");
                        $q->execute(array($data['siteurl'], 'siteurl'));
                        
                        $password_hash = md5(md5($data['admpass']));
                        
                        $user = $pdo->prepare("INSERT INTO `user` SET `name` = ?, `email` = ?, `password` = ?, `rights` = ?, `regtime` = ?");
                        $user->execute(array(
                            $data['admname'],
                            $data['admemail'],
                            $password_hash,
                            '10',
                            time(),
                            ));
                            
                        $uid = $pdo->lastInsertId();
                        $_SESSION['uid'] = $uid;
                        $_SESSION['upw'] = md5($data['admpass']);
                        $dbconfig = "host = \"".addslashes($db['host'])."\"\r\n".
                                    "database = \"".addslashes($db['database'])."\"\r\n".
                                    "user = \"".addslashes($db['user'])."\"\r\n".
                                    "password = \"".addslashes($db['password'])."\";";
                        @file_put_contents('iwbx-includes/db.ini', $dbconfig);
                    ?>
                    <div class="alert alert-success">
                        Installasi berhasil diselesaikan. Silakan <a class="alert-link" href="index.php/admin">Admin Panel</a>
                    </div>
                    <div class="alert alert-danger">
                        Demi keamanan harap hapus file <strong>install.php</strong>
                    </div>
                    <?php else:?>
                    <div class="alert alert-danger">Tidak dapat terhubung ke database</div>
                    <div class="alert alert-info"><?php echo $err_conn;?></div>
                    <p><a class="btn btn-default btn-sm" href="install.php?">Kembali</a></p>
                    <?php endif?>
                <?php elseif (isset($_POST['submit1'])):?>
                    <?php if($conn):?>
                    <form method="post" action="install.php">
						<div class="alert alert-info">
							<div class="form-group">
								<label>
									URL Situs
								</label>
								<input class="form-control input-sm" type="text" name="data[siteurl]" value="http://<?php echo $_SERVER['SERVER_NAME'];?>"/>
								<p class="help-block">
									URL Situs tanpa diakhiri garis miring
								</p>
							</div>
						</div>
						<div class="alert alert-danger">
							<div class="form-group">
								<label>
									Nama Admin
								</label>
								<input class="form-control input-sm" type="text" name="data[admname]" value="admin"/>
								<p class="help-block">
									Jika lebih dari satu pisahkan dengan tanda , (koma)
								</p>
							</div>
							<div class="form-group">
								<label>
									Email Admin
								</label>
								<input class="form-control input-sm" type="text" name="data[admemail]" value="admin@<?php echo $_SERVER['SERVER_NAME'];?>"/>
							</div>
							<div class="form-group">
								<label>
									Kata sandi Admin
								</label>
								<input class="form-control input-sm" type="text" name="data[admpass]" value="admin123"/>
							</div>
						</div>
						<p>
							<button class="btn btn-primary btn-sm" type="submit" name="submit2">
								Install
							</button>
						</p>
                        <input type="hidden" name="db[host]" value="<?php echo htmlentities($db['host']);?>"/>
                        <input type="hidden" name="db[user]" value="<?php echo htmlentities($db['user']);?>"/>
                        <input type="hidden" name="db[password]" value="<?php echo htmlentities($db['password']);?>"/>
                        <input type="hidden" name="db[database]" value="<?php echo htmlentities($db['database']);?>"/>
					</form>
                    <?php else:?>
                    <div class="alert alert-danger">Tidak dapat terhubung ke database</div>
                    <div class="alert alert-info"><?php echo $err_conn;?></div>
                    <p><a class="btn btn-default btn-sm" href="install.php?">Kembali</a></p>
                    <?php endif?>
                <?php else:?>
                    <form method="post" action="install.php">
						<div class="alert alert-warning">
							<div class="form-group">
								<label>
									MySQL Host
								</label>
								<input class="form-control input-sm" type="text" name="db[host]" value="localhost"/>
							</div>
							<div class="form-group">
								<label>
									MySQL User
								</label>
								<input class="form-control input-sm" type="text" name="db[user]" value="root"/>
							</div>
							<div class="form-group">
								<label>
									MySQL Password
								</label>
								<input class="form-control input-sm" type="text" name="db[password]" value=""/>
							</div>
                            <div class="form-group">
								<label>
									MySQL Database
								</label>
								<input class="form-control input-sm" type="text" name="db[database]" value="indowapbuilder"/>
							</div>
						</div>
						<p>
							<button class="btn btn-primary btn-sm" type="submit" name="submit1">
								Lanjutkan
							</button>
						</p>
					</form>
                    <?php endif?>
				</div>
			</div>
			<div id="footer">
				<div class="nav-footer">
					<a href="http://facebook.com/groups/1030305466996373/"><i class="fa fa-facebook"></i> Facebook</a>
					<a href="http://google.com/+AchunkJealousMan"><i class="fa fa-google-plus"></i> Google+</a>
				</div>
				<p>
					&copy; 2014 IndoWapBuilder
				</p>
			</div>
		</div>
        <script src="iwbx-assets/js/jquery-2.1.1.min.js"></script>
        <script src="iwbx-assets/js/bootstrap.min.js"></script>
	</body>

</html>