<?php
// Connect to database
try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Include Auth class
require_once 'Auth.php';
$auth = new Auth($db);

// Include helper functions
require_once 'functions.php';

// Basic routing
$route = isset($_GET['route']) ? $_GET['route'] : 'dashboard';

// Routes that don't require authentication
$publicRoutes = ['login', 'api', 'customer'];

// Authentication check
if (!in_array($route, $publicRoutes) && !$auth->isLoggedIn()) {
    header('Location: index.php?route=login');
    exit;
}

// Handle routing based on the requested route
switch ($route) {
    case 'login':
        // Handle login form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $result = $auth->login($email, $password);
            
            if ($result['success']) {
                header('Location: index.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
        
        // Check if it's a logout request
        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            $auth->logout();
            header('Location: index.php?route=login');
            exit;
        }
        
        // Display login form
        $title = 'Login';
        include 'views/login.php';
        break;
        
    case 'dashboard':
        // Get current user
        $currentUser = $auth->getCurrentUser();
        
        // Get statistics
        $editionsCount = getEditionsCount($db);
        $companiesCount = getCompaniesCount($db);
        $couponsCount = getCouponsCount($db);
        $usersCount = getUsersCount($db);
        $recentCouponUses = getRecentCouponUses($db);
        
        // Display dashboard
        $title = 'Dashboard';
        include 'views/dashboard.php';
        break;
        
    case 'editions':
        // Get current user
        $currentUser = $auth->getCurrentUser();
        
        // Handle edition actions
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        
        switch ($action) {
            case 'list':
                $editions = getEditions($db);
                $title = 'Editions';
                include 'views/editions/list.php';
                break;
                
            case 'create':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = createEdition($db, $_POST);
                    if ($result['success']) {
                        header('Location: index.php?route=editions');
                        exit;
                    } else {
                        $error = $result['message'];
                    }
                }
                
                $title = 'Create Edition';
                include 'views/editions/create.php';
                break;
                
            case 'edit':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = updateEdition($db, $id, $_POST);
                    if ($result['success']) {
                        header('Location: index.php?route=editions');
                        exit;
                    } else {
                        $error = $result['message'];
                    }
                }
                
                $edition = getEdition($db, $id);
                if (!$edition) {
                    header('Location: index.php?route=editions');
                    exit;
                }
                
                $title = 'Edit Edition';
                include 'views/editions/edit.php';
                break;
                
            case 'delete':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = deleteEdition($db, $id);
                    header('Location: index.php?route=editions');
                    exit;
                }
                
                $edition = getEdition($db, $id);
                if (!$edition) {
                    header('Location: index.php?route=editions');
                    exit;
                }
                
                $title = 'Delete Edition';
                include 'views/editions/delete.php';
                break;
                
            default:
                header('Location: index.php?route=editions');
                exit;
        }
        break;
        
    case 'companies':
        // Get current user
        $currentUser = $auth->getCurrentUser();
        
        // Handle company actions
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        
        switch ($action) {
            case 'list':
                $companies = getCompanies($db);
                $title = 'Companies';
                include 'views/companies/list.php';
                break;
                
            case 'create':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = createCompany($db, $_POST);
                    if ($result['success']) {
                        header('Location: index.php?route=companies');
                        exit;
                    } else {
                        $error = $result['message'];
                    }
                }
                
                $title = 'Add New Company';
                include 'views/companies/create.php';
                break;
                
            case 'edit':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = updateCompany($db, $id, $_POST);
                    if ($result['success']) {
                        header('Location: index.php?route=companies');
                        exit;
                    } else {
                        $error = $result['message'];
                    }
                }
                
                $company = getCompany($db, $id);
                if (!$company) {
                    header('Location: index.php?route=companies');
                    exit;
                }
                
                $title = 'Edit Company';
                include 'views/companies/edit.php';
                break;
                
            case 'delete':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = deleteCompany($db, $id);
                    header('Location: index.php?route=companies');
                    exit;
                }
                
                $company = getCompany($db, $id);
                if (!$company) {
                    header('Location: index.php?route=companies');
                    exit;
                }
                
                $title = 'Delete Company';
                include 'views/companies/delete.php';
                break;
                
            default:
                header('Location: index.php?route=companies');
                exit;
        }
        break;
        
    case 'coupons':
        // Get current user
        $currentUser = $auth->getCurrentUser();
        
        // Handle coupon actions
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        
        switch ($action) {
            case 'list':
                // Hämta alla editions, companies och kategorier för dropdownmenyerna
                $editions = getEditions($db);
                $companies = getCompanies($db);
                $categories = getCategories($db);
                
                // Sätt upp filter
                $filters = [];
                if (isset($_GET['edition_id']) && $_GET['edition_id']) {
                    $filters['edition_id'] = (int)$_GET['edition_id'];
                }
                if (isset($_GET['company_id']) && $_GET['company_id']) {
                    $filters['company_id'] = (int)$_GET['company_id'];
                }
                if (isset($_GET['category_id']) && $_GET['category_id']) {
                    $filters['category_id'] = (int)$_GET['category_id'];
                }
                if (isset($_GET['status']) && $_GET['status']) {
                    $filters['status'] = $_GET['status'];
                }
                
                // Hämta kuponger med filter
                $coupons = getCoupons($db, $filters);
                
                // Visa sidan
                $title = 'Coupons';
                include 'views/coupons/list.php';
                break;
                
            case 'create':
                // Hämta editions, companies och kategorier för dropdownmenyerna
                $editions = getEditions($db);
                $companies = getCompanies($db);
                $categories = getCategories($db);
                
                // Hantera formuläret
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = createCoupon($db, $_POST);
                    if ($result['success']) {
                        header('Location: index.php?route=coupons');
                        exit;
                    } else {
                        $error = $result['message'];
                    }
                }
                
                // Visa formuläret
                $title = 'Add New Coupon';
                include 'views/coupons/create.php';
                break;
                
            case 'edit':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                
                // Hämta editions, companies och kategorier för dropdownmenyerna
                $editions = getEditions($db);
                $companies = getCompanies($db);
                $categories = getCategories($db);
                
                // Hantera formuläret
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = updateCoupon($db, $id, $_POST);
                    if ($result['success']) {
                        header('Location: index.php?route=coupons');
                        exit;
                    } else {
                        $error = $result['message'];
                    }
                }
                
                // Hämta kupong
                $coupon = getCoupon($db, $id);
                if (!$coupon) {
                    header('Location: index.php?route=coupons');
                    exit;
                }
                
                // Visa formuläret
                $title = 'Edit Coupon';
                include 'views/coupons/edit.php';
                break;
                
            case 'delete':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                
                // Hantera borttagning
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = deleteCoupon($db, $id);
                    header('Location: index.php?route=coupons');
                    exit;
                }
                
                // Hämta kupong
                $coupon = getCoupon($db, $id);
                if (!$coupon) {
                    header('Location: index.php?route=coupons');
                    exit;
                }
                
                // Visa bekräftelse
                $title = 'Delete Coupon';
                include 'views/coupons/delete.php';
                break;
                
            default:
                header('Location: index.php?route=coupons');
                exit;
        }
        break;
        
    case 'users':
        // Get current user
        $currentUser = $auth->getCurrentUser();
        
        // Handle user actions
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        
        switch ($action) {
            case 'list':
                $users = getUsers($db);
                $title = 'Users';
                include 'views/users/list.php';
                break;
            
            // Andra case-satser för create, edit, delete kan läggas till senare
            
            default:
                header('Location: index.php?route=users');
                exit;
        }
        break;
        
    case 'api':
        // Handle API requests
        header('Content-Type: application/json');
        
        $endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';
        
        // Handle different endpoints
        switch ($endpoint) {
            case 'verify-coupon':
                // Verify coupon logic
                break;
                
            case 'use-coupon':
                // Use coupon logic
                break;
                
            default:
                echo json_encode(['error' => 'Invalid endpoint']);
        }
        break;
        
    case 'customer':
        // Hämta ID för editionen
        $edition_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Hämta editionsdata
        $stmt = $db->prepare("
            SELECT * FROM editions 
            WHERE id = ? 
            AND (status = 'active' OR status = 'published') 
            AND (valid_until IS NULL OR valid_until >= CURDATE())
        ");
        $stmt->execute([$edition_id]);
        $edition = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$edition) {
            header("Location: index.php");
            exit;
        }
        
        // Hämta alla kategorier för denna edition
        $stmt = $db->prepare("
            SELECT DISTINCT cat.id, cat.name
            FROM categories cat
            JOIN coupons c ON c.category_id = cat.id
            WHERE c.edition_id = ? AND c.status = 'active'
            ORDER BY cat.name
        ");
        $stmt->execute([$edition_id]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        
        case 'view_editions':
    // Hämta aktiva editioner
    $stmt = $db->prepare("
        SELECT * FROM editions 
        WHERE (status = 'active' OR status = 'published') 
        AND (valid_until IS NULL OR valid_until >= CURDATE())
        ORDER BY title ASC
    ");
    $stmt->execute();
    $editions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Visa listan
    $title = 'Visa Editioner för Kund';
    include 'views/editions/view_editions.php';
    break;
        
        // Hämta alla kuponger för denna edition
        $stmt = $db->prepare("
            SELECT c.*, co.name as company_name, cat.name as category_name 
            FROM coupons c
            LEFT JOIN companies co ON c.company_id = co.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.edition_id = ? AND c.status = 'active' 
            AND (c.valid_until IS NULL OR c.valid_until >= CURDATE())
            ORDER BY cat.name, co.name, c.title
        ");
        $stmt->execute([$edition_id]);
        $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Visa kundvyn
        $title = $edition['title'] . " - Kuponger";
        include 'views/customer_view.php';
        break;
        
    default:
        // 404 page
        $title = '404 Not Found';
        include 'views/404.php';
}