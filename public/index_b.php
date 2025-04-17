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
$route = isset($_GET['route']) ? $_GET['route'] : 'dashboard';
// Routes that don't require authentication
$publicRoutes = ['login', 'api', 'customer'];
// Authentication check
if (!in_array($route, $publicRoutes) && !$auth->isLoggedIn()) {
    header('Location: index.php?route=login');
    exit;
}
// Route to appropriate page
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
function getEditions($db) {
    try {
        $stmt = $db->query('
            SELECT e.*, COUNT(c.id) as coupon_count
            FROM editions e
            LEFT JOIN coupons c ON e.id = c.edition_id
            GROUP BY e.id
            ORDER BY e.created_at DESC
        ');
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
        $stmt = $db->prepare('
            INSERT INTO editions (title, description, status)
            VALUES (?, ?, ?)
        ');
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['status'] ?? 'draft'
        ]);
        
        return ['success' => true, 'id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
function updateEdition($db, $id, $data) {
    try {
        $stmt = $db->prepare('
            UPDATE editions
            SET title = ?, description = ?, status = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['status'],
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
        
        // Lägg till filter
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
// Hämta en specifik kupong
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
// Skapa en ny kupong
function createCoupon($db, $data) {
    try {
        $stmt = $db->prepare('
            INSERT INTO coupons (
                edition_id, company_id, category_id, title, description, 
                value, terms, valid_from, valid_until, max_uses, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $data['status'] ?? 'active'
        ]);
        
        return ['success' => true, 'id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
// Uppdatera en kupong
function updateCoupon($db, $id, $data) {
    try {
        $stmt = $db->prepare('
            UPDATE coupons 
            SET edition_id = ?, company_id = ?, category_id = ?, title = ?, 
                description = ?, value = ?, terms = ?, valid_from = ?, 
                valid_until = ?, max_uses = ?, status = ?
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
            $id
        ]);
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
// Ta bort en kupong
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