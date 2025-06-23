<?php
/**
 * Knowledge Hub Installation Script
 * This script helps set up the Firebase configuration and dependencies
 */

// Check if Composer is installed
function checkComposer() {
    $output = shell_exec('composer --version 2>&1');
    return strpos($output, 'Composer version') !== false;
}

// Check if required PHP extensions are available
function checkExtensions() {
    $required = ['curl', 'json', 'openssl'];
    $missing = [];
    
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            $missing[] = $ext;
        }
    }
    
    return $missing;
}

// Create necessary directories
function createDirectories() {
    $dirs = ['config', 'api', 'vendor'];
    $created = [];
    
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                $created[] = $dir;
            }
        }
    }
    
    return $created;
}

// Check if Firebase credentials file exists
function checkFirebaseCredentials() {
    return file_exists('config/firebase-credentials.json');
}

// Generate sample .htaccess file
function generateHtaccess() {
    $content = "RewriteEngine On\n";
    $content .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
    $content .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
    $content .= "RewriteRule ^(.*)$ index.php [QSA,L]\n\n";
    $content .= "# Protect sensitive files\n";
    $content .= "<Files \"firebase-credentials.json\">\n";
    $content .= "    Order allow,deny\n";
    $content .= "    Deny from all\n";
    $content .= "</Files>\n";
    
    file_put_contents('.htaccess', $content);
    return true;
}

// Main installation process
$step = $_GET['step'] ?? 1;
$errors = [];
$success = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 2:
            // Install Composer dependencies
            if (checkComposer()) {
                $output = shell_exec('composer install 2>&1');
                if (strpos($output, 'Generating autoload files') !== false) {
                    $success[] = 'Composer dependencies installed successfully';
                } else {
                    $errors[] = 'Failed to install Composer dependencies: ' . $output;
                }
            } else {
                $errors[] = 'Composer is not installed. Please install Composer first.';
            }
            break;
            
        case 3:
            // Create directories
            $created = createDirectories();
            if (!empty($created)) {
                $success[] = 'Created directories: ' . implode(', ', $created);
            }
            
            // Generate .htaccess
            if (generateHtaccess()) {
                $success[] = 'Generated .htaccess file';
            }
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Hub Installation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .step {
            margin-bottom: 2rem;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .step.active {
            border-color: #007bff;
            background: #f8f9fa;
        }
        .step.completed {
            border-color: #28a745;
            background: #d4edda;
        }
        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 0.75rem;
            border-radius: 5px;
            margin: 1rem 0;
        }
        .success {
            color: #155724;
            background: #d4edda;
            padding: 0.75rem;
            border-radius: 5px;
            margin: 1rem 0;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem 0;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .code {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            font-family: monospace;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Knowledge Hub Installation</h1>
        
        <?php foreach ($errors as $error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
        
        <?php foreach ($success as $msg): ?>
            <div class="success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endforeach; ?>
        
        <!-- Step 1: Prerequisites -->
        <div class="step <?php echo $step == 1 ? 'active' : ($step > 1 ? 'completed' : ''); ?>">
            <h3>Step 1: Check Prerequisites</h3>
            
            <h4>PHP Version</h4>
            <p>Current version: <?php echo PHP_VERSION; ?></p>
            <?php if (version_compare(PHP_VERSION, '7.4.0', '>=')): ?>
                <p style="color: green;">✓ PHP version is compatible</p>
            <?php else: ?>
                <p style="color: red;">✗ PHP 7.4 or higher is required</p>
            <?php endif; ?>
            
            <h4>Required Extensions</h4>
            <?php 
            $missing = checkExtensions();
            if (empty($missing)): ?>
                <p style="color: green;">✓ All required extensions are available</p>
            <?php else: ?>
                <p style="color: red;">✗ Missing extensions: <?php echo implode(', ', $missing); ?></p>
            <?php endif; ?>
            
            <h4>Composer</h4>
            <?php if (checkComposer()): ?>
                <p style="color: green;">✓ Composer is installed</p>
            <?php else: ?>
                <p style="color: red;">✗ Composer is not installed</p>
                <p>Please install Composer from <a href="https://getcomposer.org/" target="_blank">https://getcomposer.org/</a></p>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
                <a href="?step=2" class="btn">Continue to Step 2</a>
            <?php endif; ?>
        </div>
        
        <!-- Step 2: Install Dependencies -->
        <div class="step <?php echo $step == 2 ? 'active' : ($step > 2 ? 'completed' : ''); ?>">
            <h3>Step 2: Install Dependencies</h3>
            
            <?php if ($step == 2): ?>
                <p>This step will install the required PHP packages using Composer.</p>
                <form method="POST">
                    <button type="submit" class="btn">Install Dependencies</button>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- Step 3: Create Directories -->
        <div class="step <?php echo $step == 3 ? 'active' : ($step > 3 ? 'completed' : ''); ?>">
            <h3>Step 3: Create Directories and Files</h3>
            
            <?php if ($step == 3): ?>
                <p>This step will create necessary directories and configuration files.</p>
                <form method="POST">
                    <button type="submit" class="btn">Create Directories</button>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- Step 4: Firebase Setup -->
        <div class="step <?php echo $step == 4 ? 'active' : ($step > 4 ? 'completed' : ''); ?>">
            <h3>Step 4: Firebase Configuration</h3>
            
            <h4>Firebase Credentials</h4>
            <?php if (checkFirebaseCredentials()): ?>
                <p style="color: green;">✓ Firebase credentials file found</p>
            <?php else: ?>
                <p style="color: red;">✗ Firebase credentials file not found</p>
                <p>Please follow these steps:</p>
                <ol>
                    <li>Go to <a href="https://console.firebase.google.com/" target="_blank">Firebase Console</a></li>
                    <li>Create a new project or select existing one</li>
                    <li>Go to Project Settings > Service Accounts</li>
                    <li>Click "Generate new private key"</li>
                    <li>Save the JSON file as <code>config/firebase-credentials.json</code></li>
                </ol>
            <?php endif; ?>
            
            <h4>Configuration File</h4>
            <?php if (file_exists('config/firebase-config.php')): ?>
                <p style="color: green;">✓ Firebase configuration file exists</p>
            <?php else: ?>
                <p style="color: red;">✗ Firebase configuration file missing</p>
            <?php endif; ?>
            
            <?php if ($step == 4): ?>
                <a href="?step=5" class="btn">Continue to Step 5</a>
            <?php endif; ?>
        </div>
        
        <!-- Step 5: Final Setup -->
        <div class="step <?php echo $step == 5 ? 'active' : ''; ?>">
            <h3>Step 5: Final Setup</h3>
            
            <h4>Database Rules</h4>
            <p>Set up your Firebase Realtime Database rules:</p>
            <div class="code">
{
  "rules": {
    "users": {
      "$uid": {
        ".read": "$uid === auth.uid",
        ".write": "$uid === auth.uid"
      }
    },
    "posts": {
      ".read": true,
      ".write": "auth != null"
    },
    "post_ratings": {
      "$postId": {
        ".read": true,
        ".write": "auth != null"
      }
    },
    "post_likes": {
      "$postId": {
        ".read": true,
        ".write": "auth != null"
      }
    }
  }
}
            </div>
            
            <h4>Test Installation</h4>
            <p>Once everything is set up, you can test the installation:</p>
            <ol>
                <li>Start your local server (XAMPP)</li>
                <li>Navigate to your website</li>
                <li>Try to register a new user</li>
                <li>Create a post</li>
                <li>Test rating and liking functionality</li>
            </ol>
            
            <a href="index.php" class="btn">Go to Website</a>
        </div>
    </div>
</body>
</html> 