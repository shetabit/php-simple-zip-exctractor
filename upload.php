<?php

session_start();
ini_set('max_execution_time', '300'); // 5 minutes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}

$config = [
    'username' => 'vue',
    'password' => 'It$gsc^hjLv&@fdkzlldknsjebfmYWVD',
    'allowed_file_extensions' => [
        ''
    ],
    'allow_htaccess' => false // Important: Always disable or it allows code injection!
];

$username = $config['username'];
$password = $config['password'];
$_SESSION['message'] = '';

// logout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST) && isset($_POST['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['REQUEST_URI']);
    die();
}

//security check
if (isset($_POST) && isset($_POST['username']) && isset($_POST['password'])) {
    if (ipChek(getIPAddress())) {
        if ($_POST['username'] == $username && $_POST['password'] == $password) {
            $_SESSION['username'] = $username;
        } else {
            $_SESSION['message'] = 'Username or password is wrong';
        }
    } else {
        $_SESSION['message'] = '** Too many attempts Your IP has been blocked **';
    }
}
// var_dump($_FILES);

if (isset($_POST) && isset($_FILES['zip'])) {
    if (isset($_SESSION['username']) && $_SESSION['username'] != $username) {
        session_destroy();
        $_SESSION['message'] = 'You are not allowed to upload';
    }

    $target_Path = __DIR__ . '/' . basename($_FILES['zip']['name']);
    if (move_uploaded_file($_FILES['zip']['tmp_name'], $target_Path)) {
        $_SESSION['message'] = 'Something wrong happened';
    };

    $zip = new ZipArchive;
    if ($zip->open($target_Path) === true) {
        $home_folder = dirname(__FILE__) . "/";
        //make all the folders
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $OnlyFileName = $zip->getNameIndex($i);
            $FullFileName = $zip->statIndex($i);

            $stack = [];
            $dirs = explode('/', $FullFileName['name']);
            foreach ($dirs as $index => $folder) {
                if (($index + 1) == count($dirs)) {
                    break;
                }
                $stack[] = $folder;
                $currentPath = '/';
                foreach ($stack as $oldPath) {
                    $currentPath .= $oldPath . '/';
                }
                if (!is_dir(__DIR__ . $currentPath)) {
                    @mkdir(__DIR__ . $currentPath, 0755, true);
                }
            }

            if ($FullFileName['name'][strlen($FullFileName['name']) - 1] == "/") {
                @mkdir($home_folder . "/" . $FullFileName['name'], 0755, true);
            }
        }

        //unzip into the folders
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $OnlyFileName = $zip->getNameIndex($i);
            $FullFileName = $zip->statIndex($i);

            if ($FullFileName['name'][strlen($FullFileName['name']) - 1] != "/") {
                if (!preg_match('#\.(php|phtml|php7|php8|pcgi|pcgi3|pcgi4|pcgi5|pchi6|inc)$#i', $OnlyFileName) && $OnlyFileName !== '.htaccess') {
                    copy('zip://' . $target_Path . '#' . $OnlyFileName, $home_folder . "/" . $FullFileName['name']);
                }
            }
        }
        $zip->close();
        $_SESSION['message'] = 'Successfully extracted!';
        unlink($target_Path);
    } else {
        $_SESSION['message'] = 'Unzipped Process failed';
    }
    if (!empty($_SESSION['message'])) {
        echo $_SESSION['message'];
    }
    die();
}

echo "<p class='message' id='message'> ";
if (!empty($_SESSION['message'])) {
    echo $_SESSION['message'];
}
echo "</p>";


?>

<?php if (!isset($_SESSION['username'])) {
?>
    <div class="container">
        <h3>UploadeR</h3>
        <br>
        <form class="form-container" action="" method="post">
            <br>
            <label>
                <input placeholder="username" class="form-control" type="text" name="username">
            </label>
            <label>
                <input placeholder="password" class="form-control" type="password" name="password">
            </label>
            <button class="submit-button" type="submit">login</button>
        </form>
    </div>

<?php
} ?>

<?php if (isset($_SESSION['username']) && $_SESSION['username'] == 'vue') { ?>
    <form method="POST">
        <input type="hidden" name="logout" value="logout">
        <button class="logout">Logout</button>
    </form>
    <div class="container">
        <h3>Zip Uploader</h3>
        <form id="file-upload-form" class="form-container" action="" method="post" enctype="multipart/form-data">

            <div class="custom-file">
                <input id="file-upload" type="file" name="zip">
                <label class="custom-input-file" for="file-upload" id="file-drag">
                    <div id="start">
                        <i class="fa fa-download" aria-hidden="true"></i>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="48" height="48" viewBox="0 0 24 24" fill="#bfbfbf">
                                <path d="M2 12H4V17H20V12H22V17C22 18.11 21.11 19 20 19H4C2.9 19 2 18.11 2 17V12M12 2L6.46 7.46L7.88 8.88L11 5.75V15H13V5.75L16.13 8.88L17.55 7.45L12 2Z" />
                            </svg>
                        </div>
                        <div id="selectFileMesseage">Select a file or drag here</div>
                    </div>
                    <div id="response" class="hidden">
                        <div id="messages"></div>
                        <progress class="progress" id="file-progress" value="0">
                            <span>0</span>%
                        </progress>
                    </div>
                </label>

            </div>

        </form>
        <button id="submit-button" class="submit-button">upload</button>

        <div class="footer">
            <p>Developed in <a target="_blank" href="http://shetabit.com">Shetab</a> group</p>
        </div>
    </div>

<?php }
prCSS();
prScript();
?>



<!-- ips
31.59.51.222, 1
::1, 1
::1, 1
::1, 1
127.0.0.1, 10
93.117.179.94, 2
    end -->



<?php

function ipChek($ipAddress): bool
{
    $maxWrongAttempts = 10;
    $ips = findIps();
    if (!empty($ips)) {
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
        file_put_contents('./upload.php', $content);
        fclose($fh);
    }
    $fh = fopen('./upload.php', 'r+') or die($php_errormsg);
    $content = '';
    $pattern = '/<!-- ip';
    $added = false;
    while (!feof($fh)) {
        $line = fgets($fh, 4096);
        $content .= $line;
        if (!$added && preg_match($pattern . 's/', $line)) {
            $added = true;
            $content .= getIPAddress() . ', ' . $attempts . PHP_EOL;
        }
    }
    file_put_contents('./upload.php', $content);

    return true;
}

function getIPAddress()
{
    //whether ip is from the share internet
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    //whether ip is from the proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    //whether ip is from the remote address
    else {
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

function prCSS()
{
?>
    <style>
        * {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .form-container {
            display: flex;
            margin: auto;
            flex-direction: column;
            width: 350px;
            /* justify-content: center; */
            height: 30%;
        }

        @media only screen and (max-width: 600px) {
            .form-container {
                width: 90%;
            }

            .container>h3 {
                font-size: 50px !important;
            }
        }

        .submit-button {
            background: #2196f3;
            color: #fff;
            padding: 10px 0px;
            border: none;
            outline: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s all ease-in-out;
            font-size: 1.4rem;
            width: 150px;
            margin: 50px auto 0 auto;
        }

        .submit-button:hover {
            background: #3f51b5;
            box-shadow: rgb(0 0 0 / 9%) 0px 3px 12px;
        }

        .form-control {
            margin: 20px 0px 10px 0px;
            border-radius: 5px;
            outline: none;
            width: 100%;
            min-height: 40px;
            border: solid 1px #3f51b5;
            transition: border 150ms;
        }

        .form-control:focus {
            border: solid 2px #3f51b5;
        }

        .container {
            padding-right: 40px;
            padding-left: 40px;
            display: flex;
            flex-direction: column;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .footer p {
            text-align: center;
            background: transparent;
            margin: auto;
            padding-top: 5px;
            padding-bottom: 5px;
            box-shadow: rgb(0 0 0 / 25%) 0px 0.0625em 0.0625em, rgb(0 0 0 / 25%) 0px 0.125em 0.5em, rgb(255 255 255 / 10%) 0px 0px 0px 1px inset;
            font-size: 0.9rem;
        }

        .container>h3 {
            margin-top: 40px;
            text-align: center;
            font-size: 4rem;
            font-weight: 900;
            letter-spacing: -5px;
            background: -webkit-linear-gradient(315deg, #2196f3 25%, #e91e63);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .message {
            position: absolute;
            right: 0;
            left: 0;
            background: #FFFFFF;
            color: #f44336;
            text-align: center;
            box-shadow: rgb(0 0 0 / 9%) 0px 3px 12px;
            font-size: 1.2rem;
            line-height: 2.38rem;
        }

        .logout {
            position: absolute;
            top: 0;
            left: 0;
            background: #ffffff;
            color: #000;
            padding: 10px 10px;
            border: none;
            outline: none;
            border-radius: 0 5px 5px 5px;
            cursor: pointer;
            transition: 0.3s all;
            box-shadow: rgba(0, 0, 0, 0.02) 0px 1px 3px 0px, rgba(27, 31, 35, 0.15) 0px 0px 0px 1px;
        }

        .logout:hover {
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }

        input {
            padding: 0 5px;
        }

        .custom-file label {
            float: left;
            clear: both;
            width: 100%;
            padding: 2rem 1.5rem;
            text-align: center;
            background: #fff;
            border-radius: 7px;
            border: 3px dashed #ddd;
            user-select: none;
            margin-top: 30px;
        }

        .custom-input-file:hover {
            border: 3px dashed #adadad;
            cursor: pointer;
        }

        .custom-file .hover {
            border: 3px dashed #673ab7;
        }

        input[type="file"] {
            display: none;
        }

        progress {
            height: 40px;
            width: 100%;
            margin-top: 20px;
        }

        #selectFileMesseage {
            margin-top: 30px;
        }
    </style>
<?php
}

function prScript()
{ ?>

    <script>
        function ekUpload() {

            var status = "";

            function Init() {

                var fileSelect = document.getElementById('file-upload'),
                    fileDrag = document.getElementById('file-drag'),
                    submitButton = document.getElementById('submit-button');

                fileSelect.addEventListener('change', fileSelectHandler, false);

                // Is XHR2 available?
                var xhr = new XMLHttpRequest();
                if (xhr.upload) {
                    // File Drop
                    fileDrag.addEventListener('dragover', fileDragHover, false);
                    fileDrag.addEventListener('dragleave', fileDragHover, false);
                    fileDrag.addEventListener('drop', fileSelectHandler, false);
                }

                submitButton.addEventListener('click', function() {
                    var m = document.getElementById('message');
                    m.innerHTML = "please import file";
                });

                var pBar = document.getElementById('file-progress');
                pBar.style.opacity = "0";

            }

            function reset() {
                var m = document.getElementById('message');
                m.innerHTML = '';
                var pBar = document.getElementById('file-progress');
                pBar.value = 0;
                pBar.style.opacity = "0";
            }

            function fileDragHover(e) {
                var fileDrag = document.getElementById('file-drag');

                e.stopPropagation();
                e.preventDefault();

                fileDrag.className = (e.type === 'dragover' ? 'hover' : 'class-upload custom-input-file');
            }

            function fileSelectHandler(e) {
                if (status == 'uploading') {
                    return false;
                }


                reset();

                // Fetch FileList object
                var files = e.target.files || e.dataTransfer.files;

                // console.log(files[0]);
                // Cancel event and hover styling
                fileDragHover(e);

                var file = files[0];
                parseFile(file);
                // Process all File objects
                // for (var i = 0, f; f = files[i]; i++) {
                //     
                //     file = f;
                // }
                console.log(file);

                var submitButton = document.getElementById('submit-button');

                submitButton.addEventListener('click', function(b) {
                    if (status == 'uploading') {
                        return false;
                    }
                    var m = document.getElementById('message');
                    m.innerHTML = "";
                    var pBar = document.getElementById('file-progress');
                    pBar.style.opacity = "1";
                    // if

                    uploadFile(file);

                });
            }

            // Output
            function output(msg) {
                // Response
                var m = document.getElementById('selectFileMesseage');
                m.innerHTML = msg;
            }

            function parseFile(file) {

                console.log(file.name);

                output(
                    encodeURI(file.name)
                );

                var fileType = file.type;
                //console.log(fileType);
                var imageName = file.name;

                var isGood = (/\.(?=gif|jpg|png|jpeg)/gi).test(imageName);
                if (isGood) {
                    document.getElementById('start').classList.add("hidden");
                    // document.getElementById('response').classList.remove("hidden");
                    document.getElementById('notimage').classList.add("hidden");
                    // Thumbnail Preview
                    // document.getElementById('file-image').classList.remove("hidden");
                    // document.getElementById('file-image').src = URL.createObjectURL(file);
                } else {
                    // document.getElementById('file-image').classList.add("hidden");
                    // document.getElementById('notimage').classList.remove("hidden");
                    // document.getElementById('start').classList.remove("hidden");
                    // document.getElementById('response').classList.add("hidden");
                    document.getElementById("file-upload-form").reset();
                }
            }

            function setProgressMaxValue(e) {
                var pBar = document.getElementById('file-progress');

                if (e.lengthComputable) {
                    pBar.max = e.total;
                }
                console.log(">>>>" + pBar.max);

            }

            function updateFileProgress(e) {
                var pBar = document.getElementById('file-progress');

                if (e.lengthComputable) {
                    pBar.value = e.loaded;
                }
                console.log(e.loaded);
            }

            function uploadFile(file) {

                var data = new FormData();
                data.append('zip', file, file.name);

                var xhr = new XMLHttpRequest(),
                    fileInput = document.getElementById('class-roster-file'),
                    pBar = document.getElementById('file-progress'),
                    fileSizeLimit = 1024; // In MB
                xhr.withCredentials = true;
                if (xhr.upload) {
                    // Check if file is less than x MB
                    if (file.size <= fileSizeLimit * 1024 * 1024) {
                        var submitButton = document.getElementById('submit-button');

                        // Progress bar
                        pBar.style.display = 'inline';
                        xhr.upload.addEventListener('loadstart', function(e) {
                            var pBar = document.getElementById('file-progress');

                            if (e.lengthComputable) {
                                pBar.max = file.size;
                                status = 'uploading';
                            }
                            console.log(">>>>" + pBar.max);
                        }, false);
                        xhr.upload.addEventListener('progress', updateFileProgress, false);

                        // File received / failed
                        xhr.onreadystatechange = function(e) {
                            if (xhr.readyState == 4) {
                                // Everything is good!
                                console.log(this.responseText);
                                document.getElementById('message').innerHTML = this.responseText;
                                status = 'uploaded';
                                submitButton.innerHTML = "upload";
                                //progress.className = (xhr.status == 200 ? "success" : "failure");
                                // document.location.reload(true);
                            }
                        };


                        submitButton.addEventListener('click', function() {
                            if (status != 'uploading') {
                                return false;
                            }

                            xhr.abort();
                            submitButton.innerHTML = "resend";

                        });
                        // console.log(file);

                        // Start upload
                        xhr.open('POST', document.getElementById('file-upload-form').action, true);
                        xhr.setRequestHeader('X-File-Name', file.name);
                        xhr.setRequestHeader('X-File-Size', file.size);
                        xhr.send(data);
                        submitButton.innerHTML = "cancel";

                    } else {
                        output('Please upload a smaller file (< ' + fileSizeLimit + ' MB).');
                    }
                }
            }

            // Check for the various File API support.
            if (window.File && window.FileList && window.FileReader) {
                Init();
            } else {
                document.getElementById('file-drag').style.display = 'none';
            }
        }
        ekUpload();
    </script>

<?php
}
?>
