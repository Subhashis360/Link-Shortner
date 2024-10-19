<!DOCTYPE html>
<html>
<head>
    <title>Click-Count-Check</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans&display=swap');

        body {
            background: rgb(29, 42, 53);
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
    </style>
</head>
<body>
    <div class="container">
        <center>
            <h1>Link Shortener</h1>
            <form method="POST">
                <div class="form-group">
                    <label for="urlInput">Enter URL/Short URL:</label>
                    <input type="text" class="form-control" id="urlInput" name="url" placeholder="Enter Shorted or original Link" required>
                </div>
                <button type="submit" class="btn btn-primary">Fetch Click Count</button>
            </form>
            <br>
            <button onclick="location.href='index.php'" class="btn btn-primary">Create New Link</button>
            <?php
        // Function to search for shorted links and original links
        function searchLinks($url) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $baseURL = "$protocol://$host";
            $currentURL = $_SERVER['REQUEST_URI'];
            $currentURL = str_replace("index.php", "", $currentURL);
            
            // Fetch click count from the urls.txt file
            $file = file("urls.txt", FILE_IGNORE_NEW_LINES);
            
            foreach ($file as $line) {
                $data = explode("||", $line);
                $urlId = $data[0];
                $mainUrl = $data[1];
                $clickCount = $data[2];

                $shorturl = "$currentURL/?u=$urlId";
                
                if ($mainUrl === $url) {
                    return [
                        'shortURL' => $shorturl,
                        'originalURL' => $mainUrl,
                        'clickCount' => $clickCount
                    ];
                }
                
                $shortCode = substr($url, strrpos($url, '=') + 1);
                if ($urlId === $shortCode) {
                    return [
                        'shortURL' => $shorturl,
                        'originalURL' => $mainUrl,
                        'clickCount' => $clickCount
                    ];
                }
            }
            
            return null; // Link not found
        }
        
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $url = $_POST["url"];
            
            $link = searchLinks($url);
            
            echo '<div class="table-responsive mt-3">';
            echo '<table class="table table-bordered">';
            echo '<thead><tr><th>Short URL</th><th>Main URL</th><th>Click Count</th></tr></thead>';
            echo '<tbody>';
            
            if ($link !== null) {
                echo '<tr>';
                echo '<td>' . $link['shortURL'] . '</td>';
                echo '<td>' . $link['originalURL'] . '</td>';
                echo '<td>' . $link['clickCount'] . '</td>';
                echo '</tr>';
            } else {
                echo '<tr><td colspan="3">Link not found.</td></tr>';
            }
            
            echo '</tbody></table></div>';
        }
        ?>



        </center>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
