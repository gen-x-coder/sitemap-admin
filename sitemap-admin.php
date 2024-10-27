<?php
session_start();
ini_set('max_execution_time', 300); // 5 minutes max execution time

// Configuration
$config = [
    'base_url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]",
    'public_html_path' => $_SERVER['DOCUMENT_ROOT'],
    'excluded_extensions' => ['php', 'txt', 'json', 'md', 'git', 'svg', 'jpg', 'jpeg', 'png', 'gif', 'pdf'],
    'sitemap_path' => $_SERVER['DOCUMENT_ROOT'] . '/sitemap.xml',
    'robots_path' => $_SERVER['DOCUMENT_ROOT'] . '/robots.txt',
    'password' => 'admin' // CHANGE THIS !!
];

// Authentication check
function checkAuth() {
    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        return false;
    }
    return true;
}

// Handle login
if (isset($_POST['password'])) {
    if ($_POST['password'] === $config['password']) {
        $_SESSION['admin'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = "Incorrect password";
    }
}

// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// File scanning function
function scanDirectory($dir, &$results = array()) {
    global $config;
    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = realpath($dir . DIRECTORY_SEPARATOR . $file);
        $relativePath = str_replace($config['public_html_path'], '', $path);
        
        if (is_dir($path)) {
            $results[] = [
                'type' => 'directory',
                'path' => $relativePath,
                'included' => isset($_SESSION['included'][$relativePath]) ? true : false
            ];
            scanDirectory($path, $results);
        } else {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), $config['excluded_extensions'])) {
                $results[] = [
                    'type' => 'file',
                    'path' => $relativePath,
                    'included' => isset($_SESSION['included'][$relativePath]) ? true : false
                ];
            }
        }
    }
    return $results;
}

// Generate sitemap XML
function generateSitemap($files) {
    global $config;
    
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;
    
    $urlset = $xml->createElement('urlset');
    $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    $xml->appendChild($urlset);
    
    // Add homepage
    $url = $xml->createElement('url');
    $loc = $xml->createElement('loc', $config['base_url'] . '/');
    $lastmod = $xml->createElement('lastmod', date('Y-m-d'));
    $priority = $xml->createElement('priority', '1.0');
    $url->appendChild($loc);
    $url->appendChild($lastmod);
    $url->appendChild($priority);
    $urlset->appendChild($url);
    
    foreach ($files as $file) {
        // Only include files that are explicitly checked
        if (!isset($_SESSION['included'][$file['path']])) continue;
        
        $url = $xml->createElement('url');
        $loc = $xml->createElement('loc', $config['base_url'] . str_replace(' ', '%20', $file['path']));
        $lastmod = $xml->createElement('lastmod', date('Y-m-d', filemtime($config['public_html_path'] . $file['path'])));
        $priority = $xml->createElement('priority', '0.8');
        
        $url->appendChild($loc);
        $url->appendChild($lastmod);
        $url->appendChild($priority);
        $urlset->appendChild($url);
    }
    
    $xml->save($config['sitemap_path']);
}

// Generate robots.txt
function generateRobotsTxt($files) {
    global $config;
    
    $content = "User-agent: *\n";
    $content .= "Allow: /\n\n";
    
    foreach ($files as $file) {
        // Exclude paths that aren't explicitly included
        if (!isset($_SESSION['included'][$file['path']])) {
            $content .= "Disallow: " . $file['path'] . "\n";
        }
    }
    
    $content .= "\nSitemap: " . $config['base_url'] . "/sitemap.xml\n";
    file_put_contents($config['robots_path'], $content);
}

// Handle form submission for sitemap generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    if (checkAuth()) {
        // Clear previous selection
        $_SESSION['included'] = array();
        
        // Store new selection
        if (isset($_POST['included']) && is_array($_POST['included'])) {
            foreach ($_POST['included'] as $path => $value) {
                $_SESSION['included'][$path] = true;
            }
        }
        
        $files = scanDirectory($config['public_html_path']);
        generateSitemap($files);
        generateRobotsTxt($files);
        $_SESSION['message'] = 'Sitemap and robots.txt generated successfully!';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitemap Generator</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        .file-list { margin: 20px 0; }
        .file-item { margin: 5px 0; padding: 5px; border: 1px solid #ddd; }
        .directory { font-weight: bold; background: #f5f5f5; }
        .controls { position: sticky; top: 0; background: white; padding: 10px 0; border-bottom: 1px solid #ddd; }
        .message { padding: 10px; background: #e6ffe6; border: 1px solid #99ff99; margin: 10px 0; }
        .error { padding: 10px; background: #ffe6e6; border: 1px solid #ff9999; margin: 10px 0; }
        .login-form { max-width: 300px; margin: 100px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .login-form input[type="password"] { width: 100%; padding: 8px; margin: 10px 0; }
        .login-form input[type="submit"] { width: 100%; padding: 8px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        .login-form input[type="submit"]:hover { background: #45a049; }
        .logout { float: right; }
    </style>
</head>
<body>
    <?php if (!checkAuth()): ?>
        <!-- Login Form -->
        <div class="login-form">
            <h2>Login to Sitemap Generator</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="password" name="password" placeholder="Enter password" required>
                <input type="submit" value="Login">
            </form>
        </div>
    <?php else: ?>
        <!-- Sitemap Generator Interface -->
        <h1>Sitemap Generator</h1>
        <a href="?logout" class="logout">Logout</a>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="controls">
                <button type="submit" name="generate">Generate Sitemap & robots.txt</button>
                <button type="button" onclick="toggleAll(true)">Select All</button>
                <button type="button" onclick="toggleAll(false)">Deselect All</button>
            </div>

            <div class="file-list">
                <?php 
                $files = scanDirectory($config['public_html_path']);
                foreach ($files as $file): 
                ?>
                    <div class="file-item <?php echo $file['type'] === 'directory' ? 'directory' : ''; ?>">
                        <label>
                            <input type="checkbox" 
                                   name="included[<?php echo htmlspecialchars($file['path']); ?>]" 
                                   value="1" 
                                   <?php echo isset($_SESSION['included'][$file['path']]) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($file['path']); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>

        <script>
            function toggleAll(state) {
                document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = state;
                });
            }
        </script>
    <?php endif; ?>
</body>
</html>