<?php
/**
 * Thaibooklet System Main Entry Point
 */
// Start session
session_start();
// Load configuration
require_once 'config.php';
// Set up error handling
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
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
// Basic routing
$route = isset($_GET['route']) ? $_GET['route'] : 'home';
// Routes that don't require authentication
$publicRoutes = ['login', 'api', 'customer', 'home'];
// Authentication check
if (!in_array($route, $publicRoutes) && !$auth->isLoggedIn()) {
    header('Location: index.php?route=login');
    exit;
}
// Route to appropriate page6
switch ($route) {
    case 'home':
        // Handle login form submission if coming from the modal
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $result = $auth->login($email, $password);
            
            if ($result['success']) {
                header('Location: index.php?route=dashboard');
                exit;
            } else {
                $error = $result['message'];
            }
        }
        
        // Get active editions for public display
        $editions = getEditions($db, ['status' => 'active', 'valid_only' => true]);
        
        // Show public landing page with login modal
        $title = 'Welcome to Thaibooklet';
        include 'views/public_landing.php';
        break;
        
    case 'login':
        // Handle login form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $result = $auth->login($email, $password);
            
            if ($result['success']) {
                header('Location: index.php?route=dashboard');
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
        
        // Display login form - not the public landing page
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
        
        // Get editions for dashboard display
        $featuredEditions = getEditions($db, ['limit' => 4, 'status' => 'active']);
        
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
        
    case 'viewEdition':
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    // Get edition from database
    $edition = getEdition($db, $id);
    if (!$edition) {
        // If edition not found, redirect to editions list
        header('Location: index.php?route=editions');
        exit;
    }
    // Get coupons associated with this edition
    $coupons = getCoupons($db, ['edition_id' => $id]);
    // Show single edition view
    include 'views/editions/view.php';
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
                // Get all editions, companies and categories for dropdown menus
                $editions = getEditions($db);
                $companies = getCompanies($db);
                $categories = getCategories($db);
                
                // Set up filters
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
                
                // Get coupons with filters
                $coupons = getCoupons($db, $filters);
                
                // Show page
                $title = 'Coupons';
                include 'views/coupons/list.php';
                break;
                
            case 'create':
                // Get editions, companies and categories for dropdown menus
                $editions = getEditions($db);
                $companies = getCompanies($db);
                $categories = getCategories($db);
                
                // Handle form submission
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Process image upload
                    $image_path = null;
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        // Define upload directory
                        $uploadDir = __DIR__ . '/uploads/coupons/';
                        
                        // Create directory if it doesn't exist
                        if (!is_dir($uploadDir)) {
                            if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                                error_log("Failed to create upload directory: " . $uploadDir);
                                $error = "A server error occurred during image upload (directory).";
                            }
                        }
                        
                        if (!isset($error)) {
                            // Generate a secure filename
                            $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                            $safeFilename = bin2hex(random_bytes(8)) . '_' . time() . '.' . strtolower($fileExtension);
                            $uploadFile = $uploadDir . $safeFilename;
                            
                            // Validate file type
                            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                            if (!in_array(strtolower($fileExtension), $allowedTypes)) {
                                $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
                            } 
                            // Validate file size (max 2MB)
                            elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                                $error = "File is too large. Max size is 2MB.";
                            } 
                            // Try to move the file
                            elseif (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                                // Save the relative path for web display
                                $image_path = 'uploads/coupons/' . $safeFilename;
                            } else {
                                error_log("Could not move uploaded file to: " . $uploadFile . " (Error code: " . $_FILES['image']['error'] . ")");
                                $error = "An error occurred when saving the image.";
                            }
                        }
                    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                        // If there was an error other than "no file"
                        error_log("Image upload error: Code " . $_FILES['image']['error']);
                        $error = "An error occurred during image upload.";
                    }
                    
                    // If no errors during image upload, continue with coupon creation
                    if (!isset($error)) {
                        // Add image path to POST data
                        $_POST['image_path'] = $image_path;
                        
                        $result = createCoupon($db, $_POST);
                        if ($result['success']) {
                            header('Location: index.php?route=coupons');
                            exit;
                        } else {
                            $error = $result['message'];
                        }
                    }
                }
                
                // Show form
                $title = 'Add New Coupon';
                include 'views/coupons/create.php';
                break;
                
            case 'edit':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                
                // Get editions, companies and categories for dropdown menus
                $editions = getEditions($db);
                $companies = getCompanies($db);
                $categories = getCategories($db);
                
                // Handle form submission
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Fetch the current coupon to get existing image path
                    $currentCoupon = getCoupon($db, $id);
                    $image_path = $currentCoupon['image_path'];
                    
                    // Process image upload if a new image is provided
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        // Define upload directory
                        $uploadDir = __DIR__ . '/uploads/coupons/';
                        
                        // Create directory if it doesn't exist
                        if (!is_dir($uploadDir)) {
                            if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                                error_log("Failed to create upload directory: " . $uploadDir);
                                $error = "A server error occurred during image upload (directory).";
                            }
                        }
                        
                        if (!isset($error)) {
                            // Generate a secure filename
                            $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                            $safeFilename = bin2hex(random_bytes(8)) . '_' . time() . '.' . strtolower($fileExtension);
                            $uploadFile = $uploadDir . $safeFilename;
                            
                            // Validate file type
                            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                            if (!in_array(strtolower($fileExtension), $allowedTypes)) {
                                $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
                            } 
                            // Validate file size (max 2MB)
                            elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                                $error = "File is too large. Max size is 2MB.";
                            } 
                            // Try to move the file
                            elseif (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                                // Save the relative path for web display
                                $image_path = 'uploads/coupons/' . $safeFilename;
                                
                                // Delete old image if it exists
                                if ($currentCoupon['image_path'] && file_exists(__DIR__ . '/' . $currentCoupon['image_path'])) {
                                    @unlink(__DIR__ . '/' . $currentCoupon['image_path']);
                                }
                            } else {
                                error_log("Could not move uploaded file to: " . $uploadFile);
                                $error = "An error occurred when saving the image.";
                            }
                        }
                    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                        // If there was an error other than "no file"
                        error_log("Image upload error: Code " . $_FILES['image']['error']);
                        $error = "An error occurred during image upload.";
                    }
                    
                    // If no errors during image upload, continue with coupon update
                    if (!isset($error)) {
                        // Add image path to POST data
                        $_POST['image_path'] = $image_path;
                        
                        $result = updateCoupon($db, $id, $_POST);
                        if ($result['success']) {
                            header('Location: index.php?route=coupons');
                            exit;
                        } else {
                            $error = $result['message'];
                        }
                    }
                }
                
                // Get coupon
                $coupon = getCoupon($db, $id);
                if (!$coupon) {
                    header('Location: index.php?route=coupons');
                    exit;
                }
                
                // Show form
                $title = 'Edit Coupon';
                include 'views/coupons/edit.php';
                break;
                
            case 'delete':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                
                // Handle deletion
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Fetch the coupon to get image path before deletion
                    $coupon = getCoupon($db, $id);
                    
                    $result = deleteCoupon($db, $id);
                    
                    // If deletion was successful and there's an image, delete it too
                    if ($result['success'] && $coupon && $coupon['image_path'] && file_exists(__DIR__ . '/' . $coupon['image_path'])) {
                        @unlink(__DIR__ . '/' . $coupon['image_path']);
                    }
                    
                    header('Location: index.php?route=coupons');
                    exit;
                }
                
                // Get coupon
                $coupon = getCoupon($db, $id);
                if (!$coupon) {
                    header('Location: index.php?route=coupons');
                    exit;
                }
                
                // Show confirmation
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
            
            // Other case statements for create, edit, delete can be added later
            
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
                $couponCode = isset($_GET['code']) ? $_GET['code'] : '';
                
                if (empty($couponCode)) {
                    echo json_encode(['success' => false, 'message' => 'No coupon code provided']);
                    break;
                }
                
                try {
                    $stmt = $db->prepare("
                        SELECT c.*, e.title as edition_title, co.name as company_name 
                        FROM coupons c
                        JOIN editions e ON c.edition_id = e.id
                        JOIN companies co ON c.company_id = co.id
                        WHERE c.coupon_code = ? AND c.status = 'active'
                        AND (c.valid_until IS NULL OR c.valid_until >= CURDATE())
                    ");
                    $stmt->execute([$couponCode]);
                    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($coupon) {
                        // Check if max uses is reached
                        if ($coupon['max_uses'] && $coupon['current_uses'] >= $coupon['max_uses']) {
                            echo json_encode(['success' => false, 'message' => 'Coupon has reached maximum uses']);
                        } else {
                            echo json_encode(['success' => true, 'coupon' => $coupon]);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Database error']);
                }
                break;
                
            case 'use-coupon':
                // Use coupon logic
                $couponCode = isset($_POST['code']) ? $_POST['code'] : '';
                $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
                
                if (empty($couponCode) || empty($userId)) {
                    echo json_encode(['success' => false, 'message' => 'Missing coupon code or user ID']);
                    break;
                }
                
                try {
                    // Begin transaction
                    $db->beginTransaction();
                    
                    // Get coupon
                    $stmt = $db->prepare("
                        SELECT id, max_uses, current_uses 
                        FROM coupons 
                        WHERE coupon_code = ? AND status = 'active'
                        AND (valid_until IS NULL OR valid_until >= CURDATE())
                    ");
                    $stmt->execute([$couponCode]);
                    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$coupon) {
                        $db->rollBack();
                        echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon']);
                        break;
                    }
                    
                    // Check if max uses is reached
                    if ($coupon['max_uses'] && $coupon['current_uses'] >= $coupon['max_uses']) {
                        $db->rollBack();
                        echo json_encode(['success' => false, 'message' => 'Coupon has reached maximum uses']);
                        break;
                    }
                    
                    // Record coupon use
                    $stmt = $db->prepare("
                        INSERT INTO coupon_uses (coupon_id, user_id, used_at)
                        VALUES (?, ?, NOW())
                    ");
                    $stmt->execute([$coupon['id'], $userId]);
                    
                    // Update coupon use count
                    $stmt = $db->prepare("
                        UPDATE coupons 
                        SET current_uses = current_uses + 1
                        WHERE id = ?
                    ");
                    $stmt->execute([$coupon['id']]);
                    
                    // Commit transaction
                    $db->commit();
                    
                    echo json_encode(['success' => true, 'message' => 'Coupon used successfully']);
                } catch (PDOException $e) {
                    $db->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                }
                break;
                
            default:
                echo json_encode(['error' => 'Invalid endpoint']);
        }
        break;
        
   case 'customer':
        // Get edition ID
        $edition_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Get edition data
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
        
        // Get all coupons for this edition
        $stmt = $db->prepare("
            SELECT c.*, co.name as company, co.name as company_name
            FROM coupons c
            LEFT JOIN companies co ON c.company_id = co.id
            WHERE c.edition_id = ? AND c.status = 'active' 
            AND (c.valid_until IS NULL OR c.valid_until >= CURDATE())
            ORDER BY co.name, c.title
        ");
        $stmt->execute([$edition_id]);
        $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Show demo coupon view
        $title = $edition['title'] . " - Coupons (DEMO)";
        include 'views/customer_view.php';
        break;
        
    default:
        // 404 page
        $title = '404 Not Found';
        include 'views/404.php';
}
// Helper functions
function getEditionsCount($db) {
    $stmt = $db->query('SELECT COUNT(*) FROM editions');
    return $stmt->fetchColumn();
}
function getCompaniesCount($db) {
    $stmt = $db->query('SELECT COUNT(*) FROM companies');
    return $stmt->fetchColumn();
}
function getCouponsCount($db) {
    $stmt = $db->query('SELECT COUNT(*) FROM coupons');
    return $stmt->fetchColumn();
}
function getUsersCount($db) {
    $stmt = $db->query('SELECT COUNT(*) FROM users');
    return $stmt->fetchColumn();
}
function getRecentCouponUses($db) {
    try {
        $stmt = $db->query('
            SELECT cu.id, c.title, co.name as company, u.name as user, cu.used_at
            FROM coupon_uses cu
            JOIN coupons c ON cu.coupon_id = c.id
            JOIN companies co ON c.company_id = co.id
            JOIN users u ON cu.user_id = u.id
            ORDER BY cu.used_at DESC
            LIMIT 5
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Return empty array if no coupon uses yet or tables don't exist
        return [];
    }
}
// Edition functions
function getEditions($db, $options = []) {
    try {
        $query = '
            SELECT e.*, COUNT(c.id) as coupon_count
            FROM editions e
            LEFT JOIN coupons c ON e.id = c.edition_id
            WHERE 1=1
        ';
        
        $params = [];
        
        // Add status filter if provided
        if (isset($options['status'])) {
            if ($options['status'] === 'active') {
                // For public pages, consider both 'active' and 'published' status as 'active'
                $query .= " AND (e.status = 'active' OR e.status = 'published')";
            } else {
                $query .= ' AND e.status = ?';
                $params[] = $options['status'];
            }
        }
        
        // Add validity filter
        if (isset($options['valid_only']) && $options['valid_only']) {
            $query .= ' AND (e.valid_until IS NULL OR e.valid_until >= CURDATE())';
        }
        
        $query .= ' GROUP BY e.id ORDER BY e.created_at DESC';
        
        // Add limit if provided
        if (isset($options['limit']) && is_numeric($options['limit'])) {
            $query .= ' LIMIT ' . (int)$options['limit'];
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Return empty array if error (e.g., table doesn't exist yet)
        return [];
    }
}
function getEdition($db, $id) {
    $stmt = $db->prepare('SELECT * FROM editions WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function createEdition($db, $data) {
    try {
        // Process cover image upload if exists
        $cover_image = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            // Define upload directory
            $uploadDir = __DIR__ . '/uploads/editions/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    error_log("Failed to create upload directory: " . $uploadDir);
                    return ['success' => false, 'message' => "A server error occurred during image upload (directory)."];
                }
            }
            
            // Generate a secure filename
            $fileExtension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $safeFilename = bin2hex(random_bytes(8)) . '_' . time() . '.' . strtolower($fileExtension);
            $uploadFile = $uploadDir . $safeFilename;
            
            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($fileExtension), $allowedTypes)) {
                return ['success' => false, 'message' => "Invalid file type. Only JPG, PNG, and GIF are allowed."];
            } 
            // Validate file size (max 2MB)
            elseif ($_FILES['cover_image']['size'] > 2 * 1024 * 1024) {
                return ['success' => false, 'message' => "File is too large. Max size is 2MB."];
            } 
            // Try to move the file
            elseif (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadFile)) {
                // Save the relative path for web display
                $cover_image = 'uploads/editions/' . $safeFilename;
            } else {
                error_log("Could not move uploaded file to: " . $uploadFile . " (Error code: " . $_FILES['cover_image']['error'] . ")");
                return ['success' => false, 'message' => "An error occurred when saving the image."];
            }
        }
        
        $stmt = $db->prepare('
            INSERT INTO editions (title, description, status, cover_image)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['status'] ?? 'draft',
            $cover_image
        ]);
        
        return ['success' => true, 'id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function updateEdition($db, $id, $data) {
    try {
        // Get current edition to retrieve existing cover image
        $stmt = $db->prepare('SELECT cover_image FROM editions WHERE id = ?');
        $stmt->execute([$id]);
        $currentEdition = $stmt->fetch(PDO::FETCH_ASSOC);
        $cover_image = $currentEdition['cover_image'];
        
        // Process cover image upload if a new image is provided
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            // Define upload directory
            $uploadDir = __DIR__ . '/uploads/editions/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    error_log("Failed to create upload directory: " . $uploadDir);
                    return ['success' => false, 'message' => "A server error occurred during image upload (directory)."];
                }
            }
            
            // Generate a secure filename
            $fileExtension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $safeFilename = bin2hex(random_bytes(8)) . '_' . time() . '.' . strtolower($fileExtension);
            $uploadFile = $uploadDir . $safeFilename;
            
            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($fileExtension), $allowedTypes)) {
                return ['success' => false, 'message' => "Invalid file type. Only JPG, PNG, and GIF are allowed."];
            } 
            // Validate file size (max 2MB)
            elseif ($_FILES['cover_image']['size'] > 2 * 1024 * 1024) {
                return ['success' => false, 'message' => "File is too large. Max size is 2MB."];
            } 
            // Try to move the file
            elseif (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadFile)) {
                // Save the relative path for web display
                $cover_image = 'uploads/editions/' . $safeFilename;
                
                // Delete old image if it exists
                if ($currentEdition['cover_image'] && file_exists(__DIR__ . '/' . $currentEdition['cover_image'])) {
                    @unlink(__DIR__ . '/' . $currentEdition['cover_image']);
                }
            } else {
                error_log("Could not move uploaded file to: " . $uploadFile);
                return ['success' => false, 'message' => "An error occurred when saving the image."];
            }
        }
        
        $stmt = $db->prepare('
            UPDATE editions
            SET title = ?, description = ?, status = ?, cover_image = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['status'],
            $cover_image,
            $id
        ]);
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
function deleteEdition($db, $id) {
    try {
        $stmt = $db->prepare('DELETE FROM editions WHERE id = ?');
        $stmt->execute([$id]);
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
// Company functions
function getCompanies($db) {
    try {
        $stmt = $db->query('SELECT * FROM companies ORDER BY name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Return empty array if error (e.g., table doesn't exist yet)
        return [];
    }
}
function getCompany($db, $id) {
    $stmt = $db->prepare('SELECT * FROM companies WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function createCompany($db, $data) {
    try {
        $stmt = $db->prepare('
            INSERT INTO companies (name, contact_info, logo_url, active)
            VALUES (?, ?, ?, ?)
        ');
        $active = isset($data['active']) ? 1 : 0;
        $stmt->execute([
            $data['name'],
            $data['contact_info'] ?? '',
            $data['logo_url'] ?? '',
            $active
        ]);
        
        return ['success' => true, 'id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
function updateCompany($db, $id, $data) {
    try {
        $stmt = $db->prepare('
            UPDATE companies
            SET name = ?, contact_info = ?, logo_url = ?, active = ?
            WHERE id = ?
        ');
        $active = isset($data['active']) ? 1 : 0;
        $stmt->execute([
            $data['name'],
            $data['contact_info'] ?? '',
            $data['logo_url'] ?? '',
            $active,
            $id
        ]);
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
function deleteCompany($db, $id) {
    try {
        $stmt = $db->prepare('DELETE FROM companies WHERE id = ?');
        $stmt->execute([$id]);
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
// User functions
function getUsers($db) {
    try {
        $stmt = $db->query('SELECT * FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Return empty array if error (e.g., table doesn't exist yet)
        return [];
    }
}
// Coupon functions
function getCoupons($db, $filters = []) {
    try {
        $query = '
            SELECT c.*, e.title as edition_title, co.name as company_name, cat.name as category_name 
            FROM coupons c
            JOIN editions e ON c.edition_id = e.id
            JOIN companies co ON c.company_id = co.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE 1=1
        ';
        
        $params = [];
        
        // Add filters
        if (isset($filters['edition_id']) && $filters['edition_id']) {
            $query .= ' AND c.edition_id = ?';
            $params[] = $filters['edition_id'];
        }
        
        if (isset($filters['company_id']) && $filters['company_id']) {
            $query .= ' AND c.company_id = ?';
            $params[] = $filters['company_id'];
        }
        
        if (isset($filters['category_id']) && $filters['category_id']) {
            $query .= ' AND c.category_id = ?';
            $params[] = $filters['category_id'];
        }
        
        if (isset($filters['status']) && $filters['status']) {
            $query .= ' AND c.status = ?';
            $params[] = $filters['status'];
        }
        
        $query .= ' ORDER BY c.id DESC';
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Return empty array if error
        return [];
    }
}
// Get a specific coupon
function getCoupon($db, $id) {
    $stmt = $db->prepare('
        SELECT c.*, e.title as edition_title, co.name as company_name, cat.name as category_name 
        FROM coupons c
        JOIN editions e ON c.edition_id = e.id
        JOIN companies co ON c.company_id = co.id
        LEFT JOIN categories cat ON c.category_id = cat.id
        WHERE c.id = ?
    ');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
// Function to generate unique coupon codes
function generateUniqueCouponCode($db, $length = 6) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    
    do {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, $charactersLength - 1)];
        }
        
        // Check if code is unique
        $stmt = $db->prepare("SELECT COUNT(*) FROM coupons WHERE coupon_code = ?");
        $stmt->bindParam(1, $code);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
    } while ($count > 0); // Repeat if code already exists
    
    return $code;
}
// Create a new coupon
function createCoupon($db, $data) {
    try {
        // Generate a unique coupon code
        $couponCode = generateUniqueCouponCode($db);
        
        $stmt = $db->prepare('
            INSERT INTO coupons (
                edition_id, company_id, category_id, title, description, 
                value, terms, valid_from, valid_until, max_uses, status, image_path,
                coupon_code, current_uses
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0
            )
        ');
        $stmt->execute([
            $data['edition_id'],
            $data['company_id'],
            $data['category_id'] ?: null,
            $data['title'],
            $data['description'] ?? '',
            $data['value'],
            $data['terms'] ?? '',
            !empty($data['valid_from']) ? $data['valid_from'] : null,
            !empty($data['valid_until']) ? $data['valid_until'] : null,
            !empty($data['max_uses']) ? $data['max_uses'] : null,
            $data['status'] ?? 'active',
            $data['image_path'] ?? null,
            $couponCode
        ]);
        
        return ['success' => true, 'id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
// Update a coupon
function updateCoupon($db, $id, $data) {
    try {
        // Update query with image_path
        $stmt = $db->prepare('
            UPDATE coupons 
            SET edition_id = ?, company_id = ?, category_id = ?, title = ?, 
                description = ?, value = ?, terms = ?, valid_from = ?, 
                valid_until = ?, max_uses = ?, status = ?, image_path = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $data['edition_id'],
            $data['company_id'],
            $data['category_id'] ?: null,
            $data['title'],
            $data['description'] ?? '',
            $data['value'],
            $data['terms'] ?? '',
            !empty($data['valid_from']) ? $data['valid_from'] : null,
            !empty($data['valid_until']) ? $data['valid_until'] : null,
            !empty($data['max_uses']) ? $data['max_uses'] : null,
            $data['status'] ?? 'active',
            $data['image_path'] ?? null,
            $id
        ]);
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
// Delete a coupon
function deleteCoupon($db, $id) {
    try {
        $stmt = $db->prepare('DELETE FROM coupons WHERE id = ?');
        $stmt->execute([$id]);
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
// Category functions
function getCategories($db) {
    try {
        $stmt = $db->query('SELECT * FROM categories ORDER BY name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Return empty array if error
        return [];
    }
}
function getCategory($db, $id) {
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
