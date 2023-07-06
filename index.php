<?php
function generateRandomCode($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

function saveShortURL($code, $url, $clickCount = 0) {
    $data = "$code||$url||$clickCount\n";
    file_put_contents('urls.txt', $data, FILE_APPEND);
}

function getOriginalURL($code) {
    $lines = file('urls.txt');
    foreach ($lines as $line) {
        list($shortCode, $url, $clickCount) = explode('||', trim($line));
        if ($shortCode === $code) {
            return array(
                'url' => $url,
                'clickCount' => $clickCount
            );
        }
    }
    return null;
}


function incrementClickCount($code) {
    $lines = file('urls.txt');
    $data = '';
    foreach ($lines as $line) {
        list($shortCode, $url, $clickCount) = explode('||', trim($line));
        if ($shortCode === $code) {
            $clickCount++;
        }
        $data .= "$shortCode||$url||$clickCount\n";
    }
    file_put_contents('urls.txt', $data);
}


function isValidURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function getRecentLinksFromCookie() {
    $recentLinks = array();
    if (isset($_COOKIE['recent_links'])) {
        $recentLinks = unserialize($_COOKIE['recent_links']);
    }
    return $recentLinks;
}

function addLinkToRecentCookie($shortURL, $originalURL) {
    $recentLinks = getRecentLinksFromCookie();
    $link = array(
        'shortURL' => $shortURL,
        'originalURL' => $originalURL
    );
    array_unshift($recentLinks, $link);
    if (count($recentLinks) > 10) {
        array_pop($recentLinks);
    }
    setcookie('recent_links', serialize($recentLinks), time() + (86400 * 30), '/');
}


function isExistingLink($url) {
    $lines = file('urls.txt');
    foreach ($lines as $line) {
        list($shortCode, $existingURL) = explode('||', trim($line));
        if ($existingURL === $url) {
            return $shortCode;
        }
    }
    return false;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url =$_POST['url'];
    if (isValidURL($url)) {
        $existingShortCode = isExistingLink($url);
        if ($existingShortCode) {
            $shortURL = "http://localhost/urlshortner/?u=$existingShortCode";
            $response = 'Short URL already exists for this link.';
        } else {
            $shortCode = generateRandomCode();
            saveShortURL($shortCode, $url);
            $shortURL = "http://localhost/urlshortner/?u=$shortCode";
            $response = 'Short URL created successfully.';
            addLinkToRecentCookie($shortURL, $url);
        }
    } else {
        $error = 'Invalid URL. Please enter a valid URL.';
    }
}

if (isset($_GET['u'])) {
    $shortCode = $_GET['u'];
    $originalURL = getOriginalURL($shortCode);
    if ($originalURL) {
        incrementClickCount($shortCode);
        header("Location: {$originalURL['url']}");
        exit();
    } else {
        $error = 'Invalid short URL.';
        echo "<meta http-equiv='refresh' content='1;url=https://telegram.dog/shorturl2'>";
    }
}
$recentLinks = getRecentLinksFromCookie();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Link Shortener</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans&display=swap');

        body {
            background: rgb(29,42,53);
            color: white;
            font-family: 'DM Sans', sans-serif;
        }

        .container {
            margin-top: 20px;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        .copy-btn {
            margin-left: 10px;
        }
        .table-responsive {
            overflow-x: auto;
            word-break: break-word;
        }

        .btn-primary {
            background-color: #f8b000!important;
            border: none;
            color: black;
        }

        @media (max-width: 576px) {
            .table-responsive td, .table-responsive th {
                display: block;
                font-size: 14px;
                word-break: break-word;
            }
            .table-responsive td::before, .table-responsive th::before {
                content: attr(data-label);
                font-weight: bold;
                display: inline-block;
                width: 100px;
            }
            .table-responsive thead {
                display: none;
            }
        }
        .suar {
            color: white;
            /* background: linear-gradient(97.88deg,#11a97d,#6610f2 150.44%); */
            background: linear-gradient(97.88deg,#060a16,#060a16 150.44%);
        }

        .content {
            font: 14px asap,arial;
            -webkit-box-sizing: border-box;
            max-width: 720px;
            margin: 30px auto 20px;
            padding: 0 0 20px 0;
        }

        td {
        border: none!important;
        }
        .ameer {
            background: rgb(29,42,53)!important;
        }

        .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        }

        .switch input { 
        opacity: 0;
        width: 0;
        height: 0;
        }

        .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
        }

        .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
        }

        input:checked + .slider {
        background-color: #2196F3;
        }

        input:focus + .slider {
        box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
        border-radius: 34px;
        }

        .slider.round:before {
        border-radius: 50%;
        }

    </style>
    <script>
        function copyToClipboard(text) {
    const el = document.createElement('textarea');
    el.value = text;
    el.setAttribute('readonly', '');
    el.style.position = 'absolute';
    el.style.left = '-9999px';
    document.body.appendChild(el);
    const selected =
        document.getSelection().rangeCount > 0 ? document.getSelection().getRangeAt(0) : false;
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    if (selected) {
        document.getSelection().removeAllRanges();
        document.getSelection().addRange(selected);
    }
    alert('Link Copied to clipboard');
}
    </script>
</head>
<body>
    <div class="container">
        <center>
            <h1>Link Shortener</h1>
            <br>
            <?php if (isset($error)) { ?>
                <p class="error"><?php echo $error; ?></p>
            <?php } ?>

            <form action="" method="POST">
                <div class="form-group">
                    <input type="text" name="url" class="form-control" placeholder="Enter URL" required>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Shorten">
                </div>
                <p>ShortURL is a free Fasstest tool to shorten URLs and generate short links
                URL shortener allows to create a shortened link making it easy to share</p>
            </form>

            <?php if (isset($response)) { ?>
                <div class="alert alert-info"><?php echo $response; ?></div>
            <?php } ?>

            <?php if (isset($shortURL)) { ?>
                <div class="form-group">
                    <label>Your short URL:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id='copyinput' value="<?php echo $shortURL; ?>" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-primary copy-btn" onclick="copyToClipboard('<?php echo $shortURL; ?>')">Copy</button>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php if (!empty($recentLinks)) { ?>
                <h3>Show Recent Created Links</h3>

                <label class="switch">
                    <input type="checkbox" id="toggleButton">
                    <span class="slider round"></span>
                </label>

                <div class="table-responsive" id='table-responsive'>
                    <table class="table table-striped">
                        <tbody>
                            <?php foreach ($recentLinks as $link) { ?>
                                <tr class='ameer'>
                                    <td colspan="1"></td>
                                </tr>
                                <tr class='suar'>
                                    <td data-label="Short URL :"><?php echo $link['shortURL']; ?></td>
                                    <td data-label="Original URL :"><?php echo $link['originalURL']; ?></td>
                                    <td data-label="Click Count :">
                                        <?php $shortCode = substr($link['shortURL'], strrpos($link['shortURL'], '=') + 1); ?>
                                        <?php $originalURL = getOriginalURL($shortCode); ?>
                                        <?php echo $originalURL['clickCount']; ?>
                                    </td>
                                </tr>
                                
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
            <hr>
            <section id="content">
            <h2>Simple and fast URL shortener!</h2>
            <p>ShortURL allows to shorten long links from <a href="https://www.instagram.com/" target="_blank">Instagram</a>, <a href="https://www.facebook.com/" target="_blank">Facebook</a>, <a href="https://www.youtube.com/" target="_blank">YouTube</a>, <a href="https://www.twitter.com/" target="_blank">Twitter</a>, <a href="https://www.linkedin.com/" target="_blank">Linked In</a>, <a href="https://www.whatsapp.com/" target="_blank">WhatsApp</a>, <a href="https://www.tiktok.com/" target="_blank">TikTok</a>, blogs and sites. Just paste the long URL and click the Shorten URL button. On the next page, copy the shortened URL and share it on sites, chat and emails. After shortening the URL, check <a href="url-click-counter.php">how many clicks it received</a>.</p>
            <hr>
            <h2>Shorten, share and track</h2>
            <p>Your shortened URLs can be used in publications, documents, advertisements, blogs, forums, instant messages, and other locations. Track statistics for your business and projects by monitoring the number of hits from your URL with our click counter.</p>
            </section>

        </center>
    </div>

    <script>
        var toggleButton = document.getElementById("toggleButton");
        var contentDiv = document.getElementById("table-responsive");
        contentDiv.style.display = "none";
        // Check if the toggle value cookie exists
        var toggleValue = getCookie("toggleValue");
        if (toggleValue === "") {
            // If cookie doesn't exist, set it to "off"
            toggleValue = "off";
            setCookie("toggleValue", toggleValue);
        } else {
            // If cookie exists, set the toggle button state accordingly
            toggleButton.checked = (toggleValue === "on");
            if (toggleButton.checked) {
            contentDiv.style.display = "block";
            }
        }

        // Add event listener to toggle button
        toggleButton.addEventListener("change", function() {
            if (toggleButton.checked) {
            contentDiv.style.display = "block";
            toggleValue = "on";
            } else {
            contentDiv.style.display = "none";
            toggleValue = "off";
            }
            setCookie("toggleValue", toggleValue);
        });

        // Function to set a cookie
        function setCookie(name, value) {
            document.cookie = name + "=" + value + "; path=/";
        }

        // Function to get the value of a cookie
        function getCookie(name) {
            var cookieName = name + "=";
            var cookieArray = document.cookie.split(";");
            for (var i = 0; i < cookieArray.length; i++) {
            var cookie = cookieArray[i].trim();
            if (cookie.indexOf(cookieName) === 0) {
                return cookie.substring(cookieName.length, cookie.length);
            }
            }
            return "";
        };

    </script>
</body>
</html>