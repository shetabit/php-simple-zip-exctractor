<?php
session_start();

$config = [
    'username' => 'vue',
    'password' => '&E^Wcb7103A+dsa09&@ED12"P)hG7',
    'allowed_file_extensions' => [
        ''
    ],
    'allow_htaccess' => false // Important: Always disable or it allows code injection!
];
$username = $config['vue'];
$password = $config['password'];

$_SESSION['message'] ='';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST) && isset($_POST['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['REQUEST_URI']);
    die();
}

if (isset($_POST) && isset($_POST['username']) && isset($_POST['password']))
{
    if(ipChek(getIPAddress())) {
        if ($_POST['username'] == $username && $_POST['password'] == $password) {
            $_SESSION['username'] = $username;
        } else {
            $_SESSION['message'] = 'Username or password is wrong';

        }
    }else{
        $_SESSION['message'] ='** Too many attempts Your IP has been blocked **';
    }
}

if (isset($_POST) && isset($_FILES['zip']))
{

    if ($_SESSION['username'] != $username){
        session_destroy();
        $_SESSION['message'] ='You are not allowed to upload';
    }

    $target_Path = __DIR__.'/'.basename( $_FILES['zip']['name'] );
    if(move_uploaded_file($_FILES['zip']['tmp_name'], $target_Path )){
        $_SESSION['message'] ='Something wrong happened';
    };

    $zip = new ZipArchive;
    if ($zip->open($target_Path) === true) {
        $home_folder = dirname(__FILE__)."/";
        //make all the folders
        for($i = 0; $i < $zip->numFiles; $i++)
        {
            $OnlyFileName = $zip->getNameIndex($i);
            $FullFileName = $zip->statIndex($i);
            if ($FullFileName['name'][strlen($FullFileName['name'])-1] =="/")
            {
                @mkdir($home_folder."/".$FullFileName['name'],0700,true);
            }
        }

        //unzip into the folders
        for($i = 0; $i < $zip->numFiles; $i++)
        {
            $OnlyFileName = $zip->getNameIndex($i);
            $FullFileName = $zip->statIndex($i);

            if (!($FullFileName['name'][strlen($FullFileName['name'])-1] =="/"))
            {
                if (!preg_match('#\.(php|phtml|gif|png)$#i', $OnlyFileName))
                {
                    copy('zip://'. $target_Path .'#'. $OnlyFileName , $home_folder."/".$FullFileName['name'] );
                }
            }
        }
        $zip->close();
        $zip->extractTo(__DIR__);
        $zip->close();
        $_SESSION['message'] ='Successfully extracted!';
        unlink($target_Path);
    } else {
        $_SESSION['message'] = 'Unzipped Process failed';
    }
}


if (!empty($_SESSION['message'])){
    echo "<p class='message'>".$_SESSION['message']."</p>";
}?>


<?php if(! isset($_SESSION['username'])) {
    ?>
    <div class="container">
        <h3>Login</h3>
        <form class="form-container" action="" method="post">
            <label>
                <input placeholder="username" class="form-control" type="text" name="username">
            </label>
            <label>
                <input placeholder="password" class="form-control" type="password" name="password">
            </label>
            <button class="submit-button" type="submit" >login</button>
        </form>
    </div>

<?php } ?>

<?php if(isset($_SESSION['username']) && $_SESSION['username'] == 'vue') {?>
    <form method="POST">
        <input type="hidden" name="logout" value="logout">
        <button class="logout">Logout</button>
    </form>
    <div class="container">
        <h3>Zip Uploader</h3>
        <form class="form-container" action="" method="post" enctype="multipart/form-data">

            <div class="custom-file">
                <label for="customFile">Choose file ...</label>
                <input type="file" name="zip" class="form-control" id="customFile">
            </div>

            <button type="submit" class="submit-button">upload</button>

        </form>
        <div class="footer">
            <p>Developed in <a target="_blank" href="http://shetabit.com">Shetab</a> group</p>
        </div>
    </div>

<?php } ?>
    <style>
        * {
            font-family: monospace;
        }

        .form-container {
            display: flex;
            margin: auto;
            flex-direction: column;
            width: 50%;
            justify-content: center;
            height: 30%;
        }

        @media only screen and (max-width: 600px) {
            .form-container {
                width: 90%;
            }

            .container > h3 {
                font-size:50px!important;
            }
        }
        .submit-button {
            background: sandybrown;
            color: #000111;
            padding: 10px 0px;
            border: none;
            outline: none;
            box-shadow: 0px 0px 6px -1px #4c1010;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s all;
        }

        .submit-button:hover {
            background: #efac70;
            box-shadow: 0px 0px 6px 0px #561616;
        }

        .form-control {
            margin: 10px 0px;
            border-radius: 5px;
            outline: none;
            width: 100%;
            min-height: 40px;
            border: solid 1px #d7eada;
            transition: border 150ms;
        }

        .form-control:focus {
            border: solid 2px #487051;
        }

        .container {
            padding-right: 40px;
            padding-left: 40px;
        }

        .footer {
            background: #8fd19e;
            position:fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }
        .footer p {
            text-align: center;
        }

        .container > h3 {
            margin-top: 60px;
            text-align: center;
            font-size: 100px;
        }

        .message {
            color: #f10000;
            position: absolute;
            top: 10px;
            left: 0;
            right: 0;
            text-align: center;
        }
        .logout {
            position: absolute;
            top: 0;
            left: 0;
            background: sandybrown;
            color: #000111;
            padding: 10px 10px;
            border: none;
            outline: none;
            box-shadow: 0px 0px 6px -1px #4c1010;
            border-radius: 0px 0px 8px 0px;
            cursor: pointer;
            transition: 0.3s all;
        }

        .logout:hover {
            padding: 13px 13px;
            background: #efa86b;s
        box-shadow: 0px 0px 6px 0px #4c1010;
        }
    </style>


    <!-- ips
127.0.0.1, 1
    end-->



<?php

function ipChek($ipAddress): bool
{
    $maxWrongAttempts = 10;
    $ips = findIps();
    if (! empty($ips)) {
        $found = false;
        foreach ($ips as $ip) {
            $ip = explode(',', $ip);
            $attempts = trim($ip['1']);
            if (trim($ip[0]) == $ipAddress) {
                $found = true;
                if ($attempts >= $maxWrongAttempts) {
                    return false;
                }
                $attempts++;
                ipPush($ip[0], $attempts);
            }
        }
        if (!$found) {
            ipPush();
        }
    } else {
        ipPush();
    }

    return true;
}

function ipPush($ip = null, $attempts = 1): bool
{
    if ($ip && $attempts) {
        $fh = fopen('./upload.php', 'r+') or die($php_errormsg);
        $content = '';
        while (!feof($fh)) {
            $line = fgets($fh, 4096);
            if (preg_match('~' . $ip . '~', $line)) {
                continue;
            }
            $content .= $line;
        }
        file_put_contents('./upload.php' , $content);
        fclose($fh);
    }
    $fh = fopen('./upload.php', 'r+') or die($php_errormsg);
    $content = '';
    $pattern = '/<!-- ip';
    $added = false;
    while (!feof($fh)) {
        $line = fgets($fh, 4096);
        $content .= $line;
        if (!$added && preg_match($pattern.'s/' , $line)){
            $added = true;
            $content .= getIPAddress().', ' . $attempts.PHP_EOL;
        }
    }
    file_put_contents('./upload.php' , $content);

    return true;
}

function getIPAddress() {
    //whether ip is from the share internet
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    //whether ip is from the proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    //whether ip is from the remote address
    else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function findIps(): array
{
    $ips = [];
    $fh = fopen('./upload.php', 'r') or die('$php_errormsg');
    $pattern = '/(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9]))/';
    while (!feof($fh)) {
        $line = fgets($fh, 4096);
        if (preg_match($pattern, $line)) {
            $ips[] = $line;
        }
    }
    fclose($fh);

    return $ips;
}
