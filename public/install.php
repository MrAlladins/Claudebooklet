<?php
/**
 * Thaibooklet System Installation Script
 *
 * This script will:
 * 1. Check server requirements
 * 2. Create necessary database tables
 * 3. Set up basic configuration file
 * 4. Create folder structure
 * 5. Set up basic authentication
 */

// Error reporting for installation
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Installation steps
$steps = [
    1 => 'Välkommen',
    2 => 'Systemkrav',
    3 => 'Databasinställningar',
    4 => 'Skapa tabeller',
    5 => 'Administratörskonto',
    6 => 'Klart'
];

// Get current step
$currentStep = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// HTML header
function displayHeader($title) {
    ?>
    <!DOCTYPE html>
    <html lang="sv">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title; ?> - Installation</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 20px;
                background: #f7f7f7;
                color: #333;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: #fff;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 {
                margin-top: 0;
                color: #4a6fa5;
            }
            .steps {
                display: flex;
                justify-content: space-between;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 1px solid #eee;
            }
            .step {
                padding: 5px 10px;
                background: #eee;
                border-radius: 3px;
            }
            .step.active {
                background: #4a6fa5;
                color: #fff;
            }
            form {
                margin-top: 20px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            input[type="text"],
            input[type="password"],
            input[type="email"] {
                width: 100%;
                padding: 10px;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 3px;
            }
            button {
                background: #4a6fa5;
                color: #fff;
                border: none;
                padding: 10px 15px;
                border-radius: 3px;
                cursor: pointer;
            }
            button:hover {
                background: #3a5f95;
            }
            .success {
                color: #4CAF50;
                background: #e8f5e9;
                padding: 10px;
                border-radius: 3px;
                margin-bottom: 15px;
            }
            .error {
                color: #F44336;
                background: #ffebee;
                padding: 10px;
                border-radius: 3px;
                margin-bottom: 15px;
            }
            .requirements {
                margin-bottom: 20px;
            }
            .requirement {
                display: flex;
                justify-content: space-between;
                padding: 10px;
                border-bottom: 1px solid #eee;
            }
            .requirement:last-child {
                border-bottom: none;
            }
            .status {
                font-weight: bold;
            }
            .status.ok {
                color: #4CAF50;
            }
            .status.fail {
                color: #F44336;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Thaibooklet System - Installation</h1>
            <div class="steps">
                <?php foreach($steps as $num => $name): ?>
                    <div class="step <?php echo $num == $currentStep ? 'active' : ''; ?>">
                        <?php echo $num . '. ' . $name; ?>
                    </div>
                <?php endforeach; ?>
            </div>
    <?php
}

// HTML footer
function displayFooter() {
    ?>
        </div>
    </body>
    </html>
    <?php
}

// Check system requirements
function checkRequirements() {
    $requirements = [
        'PHP Version' => [
            'required' => '7.4.0',
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '7.4.0', '>=')
        ],
        'PDO Extension' => [
            'required' => 'Installed',
            'current' => extension_loaded('pdo') ? 'Installed' : 'Not Installed',
            'status' => extension_loaded('pdo')
        ],
        'MySQL Extension' => [
            'required' => 'Installed',
            'current' => extension_loaded('pdo_mysql') ? 'Installed' : 'Not Installed',
            'status' => extension_loaded('pdo_mysql')
        ],
        'GD Extension' => [
            'required' => 'Installed',
            'current' => extension_loaded('gd') ? 'Installed' : 'Not Installed',
            'status' => extension_loaded('gd')
        ],
        'Writable Directory' => [
            'required' => 'Writable',
            'current' => is_writable('./') ? 'Writable' : 'Not Writable',
            'status' => is_writable('./')
        ]
    ];
    
    $allPass = true;
    foreach ($requirements as $requirement) {
        if (!$requirement['status']) {
            $allPass = false;
            break;
        }
    }
    
    return [
        'requirements' => $requirements,
        'pass' => $allPass
    ];
}

// Create database tables
function createTables($conn) {
    $tables = [
        "CREATE TABLE `editions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `description` text,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `published_at` datetime DEFAULT NULL,
            `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
            `woocommerce_product_id` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE `companies` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `contact_info` text,
            `logo_url` varchar(255) DEFAULT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE `coupons` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `edition_id` int(11) NOT NULL,
            `company_id` int(11) NOT NULL,
            `title` varchar(255) NOT NULL,
            `description` text,
            `value` varchar(255) NOT NULL,
            `terms` text,
            `valid_from` date DEFAULT NULL,
            `valid_until` date DEFAULT NULL,
            `max_uses` int(11) DEFAULT NULL,
            `current_uses` int(11) NOT NULL DEFAULT '0',
            `status` enum('active','inactive','expired') NOT NULL DEFAULT 'active',
            PRIMARY KEY (`id`),
            KEY `edition_id` (`edition_id`),
            KEY `company_id` (`company_id`),
            CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE,
            CONSTRAINT `coupons_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `password` varchar(255) NOT NULL,
            `name` varchar(255) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `role` enum('admin','manager','customer') NOT NULL DEFAULT 'customer',
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE `coupon_uses` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `coupon_id` int(11) NOT NULL,
            `user_id` int(11) NOT NULL,
            `used_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `verification_code` varchar(255) DEFAULT NULL,
            `notes` text,
            PRIMARY KEY (`id`),
            KEY `coupon_id` (`coupon_id`),
            KEY `user_id` (`user_id`),
            CONSTRAINT `coupon_uses_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
            CONSTRAINT `coupon_uses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE `user_editions` (
            `user_id` int(11) NOT NULL,
            `edition_id` int(11) NOT NULL,
            `purchased_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `woocommerce_order_id` int(11) DEFAULT NULL,
            PRIMARY KEY (`user_id`,`edition_id`),
            KEY `edition_id` (`edition_id`),
            CONSTRAINT `user_editions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            CONSTRAINT `user_editions_ibfk_2` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE `settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `setting_key` varchar(255) NOT NULL,
            `setting_value` text NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];
    
    try {
        foreach ($tables as $table) {
            $conn->exec($table);
        }
        return ['success' => true, 'message' => 'Databastabeller har skapats.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Fel vid skapande av databastabeller: ' . $e->getMessage()];
    }
}

// Create folder structure
function createFolderStructure() {
    $folders = [
        'config',
        'controllers',
        'models',
        'views',
        'views/editions',
        'views/companies',
        'views/coupons',
        'views/users',
        'views/layout',
        'public',
        'public/css',
        'public/js',
        'public/img',
        'public/uploads',
        'lib',
        'api'
    ];
    
    $result = ['success' => true, 'messages' => []];
    
    foreach ($folders as $folder) {
        if (!file_exists($folder)) {
            if (mkdir($folder, 0755, true)) {
                $result['messages'][] = "Mapp '$folder' har skapats.";
            } else {
                $result['success'] = false;
                $result['messages'][] = "Kunde inte skapa mappen '$folder'.";
            }
        } else {
            $result['messages'][] = "Mapp '$folder' finns redan.";
        }
    }
    
    return $result;
}

// Create configuration file
function createConfigFile($host, $dbname, $username, $password) {
    $configContent = "<?php
/**
 * Thaibooklet System Configuration
 */

// Database settings
define('DB_HOST', '$host');
define('DB_NAME', '$dbname');
define('DB_USER', '$username');
define('DB_PASS', '$password');

// Site settings
define('SITE_URL', '{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}' . dirname($_SERVER['PHP_SELF']));
define('APP_NAME', 'Thaibooklet System');
define('APP_VERSION', '1.0.0');

// WooCommerce API settings
define('WC_STORE_URL', ''); // Your WordPress site URL
define('WC_CONSUMER_KEY', '');
define('WC_CONSUMER_SECRET', '');

// Security settings
define('HASH_SALT', '" . bin2hex(random_bytes(16)) . "');
define('SESSION_LIFETIME', 86400); // 24 hours

// Debug mode
define('DEBUG_MODE', true);
";

    if (file_put_contents('config/config.php', $configContent)) {
        return ['success' => true, 'message' => 'Konfigurationsfil har skapats.'];
    } else {
        return ['success' => false, 'message' => 'Kunde inte skapa konfigurationsfil.'];
    }
}

// Create index.php (router)
function createIndexFile() {
    $indexContent = "<?php
/**
 * Thaibooklet System Main Entry Point
 */

// Start session
session_start();

// Load configuration
require_once 'config/config.php';

// Set up error handling
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Autoloader function
function autoloader(\$class) {
    // Convert namespace to file path
    \$file = str_replace('\\\\', DIRECTORY_SEPARATOR, \$class) . '.php';
    
    // Check if the file exists in the models directory
    if (file_exists('models/' . \$file)) {
        require 'models/' . \$file;
        return;
    }
    
    // Check if the file exists in the controllers directory
    if (file_exists('controllers/' . \$file)) {
        require 'controllers/' . \$file;
        return;
    }
    
    // Check if the file exists in the lib directory
    if (file_exists('lib/' . \$file)) {
        require 'lib/' . \$file;
        return;
    }
}

// Register autoloader
spl_autoload_register('autoloader');

// Connect to database
try {
    \$db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException \$e) {
    die('Database connection failed: ' . \$e->getMessage());
}

// Basic routing
\$route = isset(\$_GET['route']) ? \$_GET['route'] : 'dashboard';

// Check authentication
require_once 'lib/Auth.php';
\$auth = new Auth(\$db);

// Routes that don't require authentication
\$publicRoutes = ['login', 'api'];

// Authentication check
if (!in_array(\$route, \$publicRoutes) && !\$auth->isLoggedIn()) {
    header('Location: index.php?route=login');
    exit;
}

// Route to controller
switch (\$route) {
    case 'login':
        require 'controllers/LoginController.php';
        \$controller = new LoginController(\$db);
        \$controller->index();
        break;
    
    case 'dashboard':
        require 'controllers/DashboardController.php';
        \$controller = new DashboardController(\$db);
        \$controller->index();
        break;
    
    case 'editions':
        require 'controllers/EditionsController.php';
        \$controller = new EditionsController(\$db);
        
        if (isset(\$_GET['action'])) {
            switch (\$_GET['action']) {
                case 'create':
                    \$controller->create();
                    break;
                case 'edit':
                    \$controller->edit(\$_GET['id']);
                    break;
                case 'delete':
                    \$controller->delete(\$_GET['id']);
                    break;
                default:
                    \$controller->index();
            }
        } else {
            \$controller->index();
        }
        break;
    
    case 'companies':
        require 'controllers/CompaniesController.php';
        \$controller = new CompaniesController(\$db);
        
        if (isset(\$_GET['action'])) {
            switch (\$_GET['action']) {
                case 'create':
                    \$controller->create();
                    break;
                case 'edit':
                    \$controller->edit(\$_GET['id']);
                    break;
                case 'delete':
                    \$controller->delete(\$_GET['id']);
                    break;
                default:
                    \$controller->index();
            }
        } else {
            \$controller->index();
        }
        break;
    
    case 'coupons':
        require 'controllers/CouponsController.php';
        \$controller = new CouponsController(\$db);
        
        if (isset(\$_GET['action'])) {
            switch (\$_GET['action']) {
                case 'create':
                    \$controller->create();
                    break;
                case 'edit':
                    \$controller->edit(\$_GET['id']);
                    break;
                case 'delete':
                    \$controller->delete(\$_GET['id']);
                    break;
                default:
                    \$controller->index();
            }
        } else {
            \$controller->index();
        }
        break;
        
    case 'users':
        require 'controllers/UsersController.php';
        \$controller = new UsersController(\$db);
        
        if (isset(\$_GET['action'])) {
            switch (\$_GET['action']) {
                case 'create':
                    \$controller->create();
                    break;
                case 'edit':
                    \$controller->edit(\$_GET['id']);
                    break;
                case 'delete':
                    \$controller->delete(\$_GET['id']);
                    break;
                default:
                    \$controller->index();
            }
        } else {
            \$controller->index();
        }
        break;
        
    case 'api':
        require 'controllers/ApiController.php';
        \$controller = new ApiController(\$db);
        \$controller->handleRequest();
        break;
        
    default:
        // 404 page
        http_response_code(404);
        require 'views/layout/header.php';
        echo '<h1>404 - Sidan kunde inte hittas</h1>';
        require 'views/layout/footer.php';
}
";

    if (file_put_contents('index.php', $indexContent)) {
        return ['success' => true, 'message' => 'Index.php har skapats.'];
    } else {
        return ['success' => false, 'message' => 'Kunde inte skapa index.php.'];
    }
}

// Create htaccess file
function createHtaccessFile() {
    $htaccessContent = "RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?route=$1 [L,QSA]

# Protect config directory
<FilesMatch \"^config\">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Protect .htaccess file
<Files .htaccess>
    Order Allow,Deny
    Deny from all
</Files>

# Disable directory listing
Options -Indexes
";

    if (file_put_contents('.htaccess', $htaccessContent)) {
        return ['success' => true, 'message' => '.htaccess-fil har skapats.'];
    } else {
        return ['success' => false, 'message' => 'Kunde inte skapa .htaccess-fil.'];
    }
}

// Create basic Auth class
function createAuthClass() {
    $authContent = "<?php
/**
 * Authentication class
 */
class Auth {
    private \$db;
    
    public function __construct(\$db) {
        \$this->db = \$db;
    }
    
    /**
     * Check if user is logged in
     * @return boolean
     */
    public function isLoggedIn() {
        return isset(\$_SESSION['user_id']);
    }
    
    /**
     * Login user
     * @param string \$email
     * @param string \$password
     * @return array
     */
    public function login(\$email, \$password) {
        \$stmt = \$this->db->prepare('SELECT id, password, role FROM users WHERE email = ?');
        \$stmt->execute([\$email]);
        \$user = \$stmt->fetch(PDO::FETCH_ASSOC);
        
        if (\$user && password_verify(\$password, \$user['password'])) {
            // Set session
            \$_SESSION['user_id'] = \$user['id'];
            \$_SESSION['user_role'] = \$user['role'];
            
            return ['success' => true, 'user_id' => \$user['id'], 'role' => \$user['role']];
        }
        
        return ['success' => false, 'message' => 'Ogiltig e-post eller lösenord'];
    }
    
    /**
     * Log out user
     */
    public function logout() {
        unset(\$_SESSION['user_id']);
        unset(\$_SESSION['user_role']);
        session_destroy();
    }
    
    /**
     * Create a new user
     * @param string \$email
     * @param string \$password
     * @param string \$name
     * @param string \$role
     * @return array
     */
    public function createUser(\$email, \$password, \$name, \$role = 'customer') {
        // Check if email already exists
        \$stmt = \$this->db->prepare('SELECT id FROM users WHERE email = ?');
        \$stmt->execute([\$email]);
        if (\$stmt->fetch()) {
            return ['success' => false, 'message' => 'E-postadressen används redan'];
        }
        
        // Hash password
        \$hashedPassword = password_hash(\$password, PASSWORD_DEFAULT);
        
        // Insert user
        \$stmt = \$this->db->prepare('INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, ?)');
        if (\$stmt->execute([\$email, \$hashedPassword, \$name, \$role])) {
            return ['success' => true, 'user_id' => \$this->db->lastInsertId()];
        }
        
        return ['success' => false, 'message' => 'Kunde inte skapa användare'];
    }
    
    /**
     * Get current user
     * @return array|null
     */
    public function getCurrentUser() {
        if (!isset(\$_SESSION['user_id'])) {
            return null;
        }
        
        \$stmt = \$this->db->prepare('SELECT id, email, name, role FROM users WHERE id = ?');
        \$stmt->execute([\$_SESSION['user_id']]);
        return \$stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if user has role
     * @param string \$role
     * @return boolean
     */
    public function hasRole(\$role) {
        return isset(\$_SESSION['user_role']) && \$_SESSION['user_role'] === \$role;
    }
}
";

    if (!file_exists('lib')) {
        mkdir('lib', 0755, true);
    }
    
    if (file_put_contents('lib/Auth.php', $authContent)) {
        return ['success' => true, 'message' => 'Auth.php har skapats.'];
    } else {
        return ['success' => false, 'message' => 'Kunde inte skapa Auth.php.'];
    }
}

// Create basic header and footer templates
function createLayoutTemplates() {
    if (!file_exists('views/layout')) {
        mkdir('views/layout', 0755, true);
    }
    
    $headerContent = "<!DOCTYPE html>
<html lang=\"sv\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title><?php echo isset(\$title) ? \$title . ' - ' : ''; ?>Thaibooklet System</title>
    <link rel=\"stylesheet\" href=\"<?php echo SITE_URL; ?>/public/css/style.css\">
</head>
<body>
    <header>
        <div class=\"container\">
            <h1>Thaibooklet System</h1>
            <?php if (isset(\$_SESSION['user_id'])): ?>
            <nav>
                <ul>
                    <li><a href=\"<?php echo SITE_URL; ?>\">Dashboard</a></li>
                    <li><a href=\"<?php echo SITE_URL; ?>?route=editions\">Editions</a></li>
                    <li><a href=\"<?php echo SITE_URL; ?>?route=companies\">Companies</a></li>
                    <li><a href=\"<?php echo SITE_URL; ?>?route=coupons\">Coupons</a></li>
                    <li><a href=\"<?php echo SITE_URL; ?>?route=users\">Users</a></li>
                    <li><a href=\"<?php echo SITE_URL; ?>?route=login&action=logout\">Logout</a></li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <div class=\"container\">
";

    $footerContent = "        </div>
    </main>
    <footer>
        <div class=\"container\">
            <p>&copy; <?php echo date('Y'); ?> Thaibooklet System</p>
        </div>
    </footer>
    <script src=\"<?php echo SITE_URL; ?>/public/js/script.js\"></script>
</body>
</html>";

    $headerResult = file_put_contents('views/layout/header.php', $headerContent);
    $footerResult = file_put_contents('views/layout/footer.php', $footerContent);
    
    if ($headerResult && $footerResult) {
        return ['success' => true, 'message' => 'Layout-mallar har skapats.'];
    } else {
        return ['success' => false, 'message' => 'Kunde inte skapa layout-mallar.'];
    }
}

// Create basic CSS file
function createCssFile() {
    if (!file_exists('public/css')) {
        mkdir('public/css', 0755, true);
    }
    
    $cssContent = "/* Thaibooklet System CSS */

/* Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Base styles */
body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    background: #f7f7f7;
    color: #333;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

/* Header */
header {
    background: #4a6fa5;
    color: #fff;
    padding: 1rem 0;
}

header h1 {
    margin-bottom: 1rem;
}

nav ul {
    display: flex;
    list-style: none;
}

nav ul li {
    margin-right: 1rem;
}

nav ul li a {
    color: #fff;
    text-decoration: none;
}

nav ul li a:hover {
    text-decoration: underline;
}

/* Main content */
main {
    padding: 2rem 0;
}

h2 {
    margin-bottom: 1.5rem;
    color: #4a6fa5;
}

/* Forms */
form {
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

input[type=\"text\"],
input[type=\"email\"],
input[type=\"password\"],
select,
textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 1rem;
    border: 1px solid #ddd;
    border-radius: 3px;
}

button, .button {
    background: #4a6fa5;
    color: #fff;
    border: none;
    padding: 10px 15px;
    border-radius: 3px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

button:hover, .button:hover {
    background: #3a5f95;
}

.button-secondary {
    background: #6c757d;
}

.button-secondary:hover {
    background: #5a6268;
}

.button-danger {
    background: #dc3545;
}

.button-danger:hover {
    background: #c82333;
}

/* Tables */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
    background: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

th, td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background: #4a6fa5;
    color: #fff;
}

tr:nth-child(even) {
    background: #f2f2f2;
}

tr:hover {
    background: #e9e9e9;
}

/* Alerts */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 3px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

/* Footer */
footer {
    background: #4a6fa5;
    color: #fff;
    padding: 1rem 0;
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    nav ul {
        flex-direction: column;
    }
    
    nav ul li {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
}
";

    if (file_put_contents('public/css/style.css', $cssContent)) {
        return ['success' => true, 'message' => 'CSS-fil har skapats.'];
    } else {
        return ['success' => false, 'message' => 'Kunde inte skapa CSS-fil.'];
    }
}

// Create basic JavaScript file
function createJsFile() {
    if (!file_exists('public/js')) {
        mkdir('public/js', 0755, true);
    }
    
    $jsContent = "/* Thaibooklet System JavaScript */

document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-confirm');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Är du säker på att du vill ta bort detta?')) {
                e.preventDefault();
            }
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('invalid');
                    
                    // Add error message if it doesn't exist
                    const errorMsg = field.parentNode.querySelector('.error-message');
                    if (!errorMsg) {
                        const msg = document.createElement('div');
                        msg.className = 'error-message';
                        msg.textContent = 'Detta fält är obligatoriskt';
                        field.parentNode.appendChild(msg);
                    }
                } else {
                    field.classList.remove('invalid');
                    const errorMsg = field.parentNode.querySelector('.error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!valid) {
                e.preventDefault();
            }
        });
    });
});
";

    if (file_put_contents('public/js/script.js', $jsContent)) {
        return ['success' => true, 'message' => 'JavaScript-fil har skapats.'];
    } else {
        return ['success' => false, 'message' => 'Kunde inte skapa JavaScript-fil.'];
    }
}

// Create basic login controller
function createLoginController() {
    if (!file_exists('controllers')) {
        mkdir('controllers', 0755, true);
    }
    
    $controllerContent = "<?php
/**
 * Login Controller
 */
class LoginController {
    private \$db;
    
    public function __construct(\$db) {
        \$this->db = \$db;
    }
    
    public function index() {
        // Check if it's a logout request
        if (isset(\$_GET['action']) && \$_GET['action'] === 'logout') {
            \$auth = new Auth(\$this->db);
            \$auth->logout();
            header('Location: index.php?route=login');
            exit;
        }
        
        // Handle login form submission
        if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
            \$email = \$_POST['email'] ?? '';
            \$password = \$_POST['password'] ?? '';
            
            \$auth = new Auth(\$this->db);
            \$result = \$auth->login(\$email, \$password);
            
            if (\$result['success']) {
                header('Location: index.php');
                exit;
            } else {
                \$error = \$result['message'];
            }
        }
        
        // Display login form
        \$title = 'Login';
        require 'views/login.php';
    }
}
";

    if (file_put_contents('controllers/LoginController.php', $controllerContent)) {
        return ['success' => true, 'message' => 'LoginController.php har skapats.'];
    } else {
        return ['success' => false, 'message' => 'Kunde inte skapa LoginController.php.'];
    }
}

// Create basic login view
function createLoginView() {
    if (!file_exists('views')) {
        mkdir('views', 0755, true);
    }
    
    $viewContent = "<!DOCTYPE html>
<html lang=\"sv\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Login - Thaibooklet System</title>
    <link rel=\"stylesheet\" href=\"<?php echo SITE_URL; ?>/public/css/style.css\">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f7f7f7;
        }
        
        .login-container {
            width: 400px;
            background: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .login-container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #4a6fa5;
        }
        
        .login-form button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            margin-top: 10px;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class=\"login-container\">
        <div class=\"login-logo\">
            <h1>Thaibooklet System</h1>
        </div>
        
        <?php if (isset(\$error)): ?>
        <div class=\"alert alert-danger\">
            <?php echo \$error; ?>
        </div>
        <?php endif; ?>
        
        <form class=\"login-form\" method=\"post\" data-validate>
            <div>
                <label for=\"email\">E-post</label>
                <input type=\"email\" id=\"email\" name=\"email\" required>
            </div>
            
            <div>
                <label for=\"password\">Lösenord</label>
                <input type=\"password\" id=\"password\" name=\"password\" required>
            </div>
            
            <button type=\"submit\">Logga in</button>
        </form>
    </div>
    
    <script src=\"<?php echo SITE_URL; ?>/public/js/script.js\"></script>
</body>
</html>
";

    if (file_put_contents('views/login.php', $viewContent)) {
        return ['success' => true, 'message' => 'login.php-vy har skapats.'];
    } else {
        return ['success' => false, 'message' => 'Kunde inte skapa login.php-vy.'];
    }
}

// Create basic dashboard controller
function createDashboardController() {
    if (!file_exists('controllers')) {
        mkdir('controllers', 0755, true);
    }
    
    $controllerContent = "<?php
/**
 * Dashboard Controller
 */
class DashboardController {
    private \$db;
    
    public function __construct(\$db) {
        \$this->db = \$db;
    }
    
    public function index() {
        // Get auth instance
        \$auth = new Auth(\$this->db);
        \$currentUser = \$auth->getCurrentUser();
        
        // Get statistics
        \$editionsCount = \$this->getEditionsCount();
        \$companiesCount = \$this->getCompaniesCount();
        \$couponsCount = \$this->getCouponsCount();
        \$usersCount = \$this->getUsersCount();
        \$recentCouponUses = \$this->getRecentCouponUses();
        
        // Display dashboard
        \$title = 'Dashboard';
        require 'views/dashboard.php';
    }
    
    private function getEditionsCount() {
        \$stmt = \$this->db->query('SELECT COUNT(*) FROM editions');
        return \$stmt->fetchColumn();
    }
    
    private function getCompaniesCount() {
        \$stmt = \$this->db->query('SELECT COUNT(*) FROM companies');
        return \$stmt->fetchColumn();
    }
    
    private function getCouponsCount() {
        \$stmt = \$this->db->query('SELECT COUNT(*) FROM coupons');
        return \$stmt->fetchColumn();
    }
    
    private function getUsersCount() {
        \$stmt = \$this->db->query('SELECT COUNT(*) FROM users');
        return \$stmt->fetchColumn();
    }
    
    private function getRecentCouponUses() {
        \$stmt = \$this->db->query('
            SELECT cu.id, c.title, co.name as company, u.name as user, cu.used_at
            FROM coupon_uses cu
            JOIN coupons c ON cu.coupon_id = c.id
            JOIN companies co ON c.company_id = co.id
            JOIN users u ON cu.user_id = u.id
            ORDER BY cu.used_at DESC
            LIMIT 5
        ');
        return \$stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
";

    if (file_put_contents('controllers/DashboardController.php', $controllerContent)) {
        return ['success' => true, 'message' => 'DashboardController.php har skapats.'];
    } else {
        return ['success' => false, 'message' => 'Kunde inte skapa DashboardController.php.'];
    }
}

// Create basic dashboard view
function createDashboardView() {
    if (!file_exists('views')) {
        mkdir('views', 0755, true);
    }
    
    $viewContent = "<?php require 'views/layout/header.php'; ?>

<h2>Dashboard</h2>

<div class=\"alert alert-info\">
    Välkommen <?php echo \$currentUser['name']; ?>! Du är inloggad som <?php echo \$currentUser['role']; ?>.
</div>

<div class=\"dashboard-stats\">
    <div class=\"stat-box\">
        <h3>Editions</h3>
        <p class=\"stat-number\"><?php echo \$editionsCount; ?></p>
        <a href=\"<?php echo SITE_URL; ?>?route=editions\" class=\"button\">Hantera editions</a>
    </div>
    
    <div class=\"stat-box\">
        <h3>Companies</h3>
        <p class=\"stat-number\"><?php echo \$companiesCount; ?></p>
        <a href=\"<?php echo SITE_URL; ?>?route=companies\" class=\"button\">Hantera companies</a>
    </div>
    
    <div class=\"stat-box\">
        <h3>Coupons</h3>
        <p class=\"stat-number\"><?php echo \$couponsCount; ?></p>
        <a href=\"<?php echo SITE_URL; ?>?route=coupons\" class=\"button\">Hantera coupons</a>
    </div>
    
    <div class=\"stat-box\">
        <h3>Users</h3>
        <p class=\"stat-number\"><?php echo \$usersCount; ?></p>
        <a href=\"<?php echo SITE_URL; ?>?route=users\" class=\"button\">Hantera users</a>
    </div>
</div>

<h3>Recent Coupon Uses</h3>

<?php if (count(\$recentCouponUses) > 0): ?>
<table>
    <thead>
        <tr>
            <th>Coupon</th>
            <th>Company</th>
            <th>User</th>
            <th>Used At</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (\$recentCouponUses as \$use): ?>
        <tr>
            <td><?php echo htmlspecialchars(\$use['title']); ?></td>
            <td><?php echo htmlspecialchars(\$use['company']); ?></td>
            <td><?php echo htmlspecialchars(\$use['user']); ?></td>
            <td><?php echo htmlspecialchars(\$use['used_at']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p>Inga kuponger har använts ännu.</p>
<?php endif; ?>

<style>
    .dashboard-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-box {
        flex: 1;
        min-width: 200px;
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .stat-number {
        font-size: 36px;
        font-weight: bold;
        margin: 15px 0;
        color: #4a6fa5;
    }
    
    @media (max-width: 768px) {
        .dashboard-stats {
            flex-direction: column;
        }
    }
</style>

<?php require 'views/layout/footer.php'; ?>
";

    if (file_put_contents('views/dashboard.php', $viewContent)) {
        return ['success' => true, 'message' => 'dashboard.php-vy har skapats.'];
    } else {
        return ['success' => false, 'message' => 'Kunde inte skapa dashboard.php-vy.'];
    }
}

// Create basic API controller
function createApiController() {
    if (!file_exists('controllers')) {
        mkdir('controllers', 0755, true);
    }
    
    $controllerContent = "<?php
/**
 * API Controller
 */
class ApiController {
    private \$db;
    
    public function __construct(\$db) {
        \$this->db = \$db;
    }
    
    public function handleRequest() {
        // Set content type to JSON
        header('Content-Type: application/json');
        
        // Get request method
        \$method = \$_SERVER['REQUEST_METHOD'];
        
        // Get the endpoint from the URL
        \$endpoint = isset(\$_GET['endpoint']) ? \$_GET['endpoint'] : '';
        
        // Check API key for non-public endpoints
        if (!\$this->isPublicEndpoint(\$endpoint)) {
            if (!\$this->validateApiKey()) {
                \$this->sendResponse(401, ['error' => 'Unauthorized']);
                return;
            }
        }
        
        // Route to appropriate handler
        switch (\$endpoint) {
            case 'verify-coupon':
                \$this->handleVerifyCoupon(\$method);
                break;
                
            case 'use-coupon':
                \$this->handleUseCoupon(\$method);
                break;
                
            case 'coupon-stats':
                \$this->handleCouponStats(\$method);
                break;
                
            case 'edition-info':
                \$this->handleEditionInfo(\$method);
                break;
                
            default:
                \$this->sendResponse(404, ['error' => 'Endpoint not found']);
        }
    }
    
    private function isPublicEndpoint(\$endpoint) {
        \$publicEndpoints = ['edition-info'];
        return in_array(\$endpoint, \$publicEndpoints);
    }
    
    private function validateApiKey() {
        // Get API key from header
        \$headers = getallheaders();
        \$apiKey = \$headers['X-API-Key'] ?? '';
        
        // Check if API key is valid
        // In a real application, you would check against a stored value
        // For now, we'll use a simple check
        return \$apiKey === 'your-api-key';
    }
    
    private function handleVerifyCoupon(\$method) {
        if (\$method !== 'POST') {
            \$this->sendResponse(405, ['error' => 'Method not allowed']);
            return;
        }
        
        // Get request data
        \$data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset(\$data['coupon_id'])) {
            \$this->sendResponse(400, ['error' => 'Missing coupon_id']);
            return;
        }
        
        // Verify coupon
        \$stmt = \$this->db->prepare('
            SELECT c.*, e.title as edition_title, co.name as company_name 
            FROM coupons c
            JOIN editions e ON c.edition_id = e.id
            JOIN companies co ON c.company_id = co.id
            WHERE c.id = ?
        ');
        \$stmt->execute([\$data['coupon_id']]);
        \$coupon = \$stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!\$coupon) {
            \$this->sendResponse(404, ['error' => 'Coupon not found']);
            return;
        }
        
        // Check if coupon is valid
        \$valid = true;
        \$message = '';
        
        // Check if coupon is active
        if (\$coupon['status'] !== 'active') {
            \$valid = false;
            \$message = 'Coupon is not active';
        }
        
        // Check if coupon is expired
        if (\$valid && \$coupon['valid_until'] && \$coupon['valid_until'] < date('Y-m-d')) {
            \$valid = false;
            \$message = 'Coupon has expired';
        }
        
        // Check if coupon has reached max uses
        if (\$valid && \$coupon['max_uses'] && \$coupon['current_uses'] >= \$coupon['max_uses']) {
            \$valid = false;
            \$message = 'Coupon has reached maximum uses';
        }
        
        \$this->sendResponse(200, [
            'valid' => \$valid,
            'message' => \$message,
            'coupon' => [
                'id' => \$coupon['id'],
                'title' => \$coupon['title'],
                'description' => \$coupon['description'],
                'value' => \$coupon['value'],
                'terms' => \$coupon['terms'],
                'edition_title' => \$coupon['edition_title'],
                'company_name' => \$coupon['company_name'],
                'valid_from' => \$coupon['valid_from'],
                'valid_until' => \$coupon['valid_until'],
                'current_uses' => \$coupon['current_uses'],
                'max_uses' => \$coupon['max_uses']
            ]
        ]);
    }
    
    private function handleUseCoupon(\$method) {
        if (\$method !== 'POST') {
            \$this->sendResponse(405, ['error' => 'Method not allowed']);
            return;
        }
        
        // Get request data
        \$data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset(\$data['coupon_id']) || !isset(\$data['user_id'])) {
            \$this->sendResponse(400, ['error' => 'Missing coupon_id or user_id']);
            return;
        }
        
        // Verify coupon
        \$stmt = \$this->db->prepare('SELECT * FROM coupons WHERE id = ?');
        \$stmt->execute([\$data['coupon_id']]);
        \$coupon = \$stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!\$coupon) {
            \$this->sendResponse(404, ['error' => 'Coupon not found']);
            return;
        }
        
        // Check if coupon is valid
        if (\$coupon['status'] !== 'active') {
            \$this->sendResponse(400, ['error' => 'Coupon is not active']);
            return;
        }
        
        // Check if coupon is expired
        if (\$coupon['valid_until'] && \$coupon['valid_until'] < date('Y-m-d')) {
            \$this->sendResponse(400, ['error' => 'Coupon has expired']);
            return;
        }
        
        // Check if coupon has reached max uses
        if (\$coupon['max_uses'] && \$coupon['current_uses'] >= \$coupon['max_uses']) {
            \$this->sendResponse(400, ['error' => 'Coupon has reached maximum uses']);
            return;
        }
        
        // Check if user exists
        \$stmt = \$this->db->prepare('SELECT * FROM users WHERE id = ?');
        \$stmt->execute([\$data['user_id']]);
        \$user = \$stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!\$user) {
            \$this->sendResponse(404, ['error' => 'User not found']);
            return;
        }
        
        // Generate verification code
        \$verificationCode = bin2hex(random_bytes(16));
        
        // Record coupon use
        \$stmt = \$this->db->prepare('
            INSERT INTO coupon_uses (coupon_id, user_id, verification_code, notes)
            VALUES (?, ?, ?, ?)
        ');
        \$notes = isset(\$data['notes']) ? \$data['notes'] : '';
        \$result = \$stmt->execute([\$data['coupon_id'], \$data['user_id'], \$verificationCode, \$notes]);
        
        if (!\$result) {
            \$this->sendResponse(500, ['error' => 'Failed to record coupon use']);
            return;
        }
        
        // Update coupon current_uses
        \$stmt = \$this->db->prepare('
            UPDATE coupons
            SET current_uses = current_uses + 1
            WHERE id = ?
        ');
        \$stmt->execute([\$data['coupon_id']]);
        
        \$this->sendResponse(200, [
            'success' => true,
            'message' => 'Coupon used successfully',
            'verification_code' => \$verificationCode
        ]);
    }
    
    private function handleCouponStats(\$method) {
        if (\$method !== 'GET') {
            \$this->sendResponse(405, ['error' => 'Method not allowed']);
            return;
        }
        
        // Get request params
        \$editionId = isset(\$_GET['edition_id']) ? (int)\$_GET['edition_id'] : null;
        \$companyId = isset(\$_GET['company_id']) ? (int)\$_GET['company_id'] : null;
        
        // Build query
        \$query = '
            SELECT c.id, c.title, c.current_uses, c.max_uses, co.name as company_name, e.title as edition_title
            FROM coupons c
            JOIN companies co ON c.company_id = co.id
            JOIN editions e ON c.edition_id = e.id
            WHERE 1=1
        ';
        \$params = [];
        
        if (\$editionId) {
            \$query .= ' AND c.edition_id = ?';
            \$params[] = \$editionId;
        }
        
        if (\$companyId) {
            \$query .= ' AND c.company_id = ?';
            \$params[] = \$companyId;
        }
        
        \$stmt = \$this->db->prepare(\$query);
        \$stmt->execute(\$params);
        \$coupons = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        
        \$this->sendResponse(200, [
            'coupons' => \$coupons
        ]);
    }
    
    private function handleEditionInfo(\$method) {
        if (\$method !== 'GET') {
            \$this->sendResponse(405, ['error' => 'Method not allowed']);
            return;
        }
        
        // Get request params
        \$editionId = isset(\$_GET['edition_id']) ? (int)\$_GET['edition_id'] : null;
        
        if (!\$editionId) {
            \$this->sendResponse(400, ['error' => 'Missing edition_id']);
            return;
        }
        
        // Get edition info
        \$stmt = \$this->db->prepare('
            SELECT * FROM editions WHERE id = ?
        ');
        \$stmt->execute([\$editionId]);
        \$edition = \$stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!\$edition) {
            \$this->sendResponse(404, ['error' => 'Edition not found']);
            return;
        }
        
        // Get coupons for this edition
        \$stmt = \$this->db->prepare('
            SELECT c.*, co.name as company_name
            FROM coupons c
            JOIN companies co ON c.company_id = co.id
            WHERE c.edition_id = ?
        ');
        \$stmt->execute([\$editionId]);
        \$coupons = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        
        \$this->sendResponse(200, [
            'edition' => \$edition,
            'coupons' => \$coupons
        ]);
    }
    
    private function sendResponse(\$statusCode, \$data) {
        http_response_code(\$statusCode);
        echo json_encode(\$data);
        exit;
    }
}
";

    if (file_put_contents('controllers/ApiController.php', $controllerContent)) {
        return ['success' => true, 'message' => 'ApiController.php har skapats.'];
    } else {
        return ['success' => false, 'message' => 'Kunde inte skapa ApiController.php.'];
    }
}

// Handle installation steps
switch ($currentStep) {
    case 1:
        // Welcome page
        displayHeader('Välkommen till installationen');
        ?>
        <h2>Välkommen till installationen av Thaibooklet System</h2>
        <p>Detta installationsprogram kommer att hjälpa dig att konfigurera systemet. Processen består av följande steg:</p>
        
        <ol>
            <li>Kontrollera systemkrav</li>
            <li>Konfigurera databasanslutning</li>
            <li>Skapa databastabeller</li>
            <li>Konfigurera administratörskonto</li>
            <li>Slutföra installationen</li>
        </ol>
        
        <p>Klicka på "Fortsätt" för att börja installationen.</p>
        
        <form action="install.php?step=2" method="post">
            <button type="submit">Fortsätt</button>
        </form>
        <?php
        displayFooter();
        break;
    
    case 2:
        // Check system requirements
        $requirementsCheck = checkRequirements();
        
        displayHeader('Systemkrav');
        ?>
        <h2>Kontrollera systemkrav</h2>
        <p>Följande krav måste uppfyllas för att kunna installera systemet:</p>
        
        <div class="requirements">
            <?php foreach ($requirementsCheck['requirements'] as $name => $requirement): ?>
            <div class="requirement">
                <div><?php echo $name; ?></div>
                <div>
                    Krav: <?php echo $requirement['required']; ?><br>
                    Nuvarande: <?php echo $requirement['current']; ?><br>
                    <span class="status <?php echo $requirement['status'] ? 'ok' : 'fail'; ?>">
                        <?php echo $requirement['status'] ? 'OK' : 'Fel'; ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($requirementsCheck['pass']): ?>
        <p class="success">Alla systemkrav är uppfyllda!</p>
        <form action="install.php?step=3" method="post">
            <button type="submit">Fortsätt</button>
        </form>
        <?php else: ?>
        <p class="error">Några systemkrav är inte uppfyllda. Åtgärda problemen och försök igen.</p>
        <form action="install.php?step=2" method="post">
            <button type="submit">Kontrollera igen</button>
        </form>
        <?php endif; ?>
        <?php
        displayFooter();
        break;
    
    case 3:
        // Database configuration
        displayHeader('Databasinställningar');
        ?>
        <h2>Konfigurera databasanslutning</h2>
        <p>Ange inställningarna för din databas:</p>
        
        <form action="install.php?step=4" method="post">
            <div>
                <label for="db_host">Databasvärd</label>
                <input type="text" id="db_host" name="db_host" value="localhost" required>
            </div>
            
            <div>
                <label for="db_name">Databasnamn</label>
                <input type="text