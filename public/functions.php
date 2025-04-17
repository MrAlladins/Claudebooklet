<?php
// Helper functions

// Statistics functions
// Hämta aktiva editioner för kundvy-knappen
public function view() {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // Hämta edition
    $sql = "SELECT * FROM editions WHERE id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Hantera fel, ingen edition hittades
        $this->setError("Edition hittades inte");
        $this->redirect("index.php?route=editions");
        return;
    }
    
    $edition = $result->fetch_assoc();
    
    // Hämta kopplade kuponger
    $sql = "SELECT * FROM coupons WHERE edition_id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $coupons = [];
    while ($row = $result->fetch_assoc()) {
        $coupons[] = $row;
    }
    
    // Visa view
    $this->view->render('editions/view', [
        'edition' => $edition,
        'coupons' => $coupons,
        'title' => $edition['title']
    ]);
}

function getActiveEditions($db) {
    try {
        $stmt = $db->prepare("
            SELECT * FROM editions 
            WHERE (status = 'active' OR status = 'published') 
            AND (valid_until IS NULL OR valid_until >= CURDATE())
            ORDER BY title ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}


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
            INSERT INTO editions (title, description, status, valid_until, image_path)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['status'] ?? 'draft',
            !empty($data['valid_until']) ? $data['valid_until'] : null,
            $data['image_path'] ?? null
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
            SET title = ?, description = ?, status = ?, valid_until = ?, image_path = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['status'],
            !empty($data['valid_until']) ? $data['valid_until'] : null,
            $data['image_path'] ?? null,
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
                value, terms, valid_from, valid_until, max_uses, status, image_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $data['image_path'] ?? null
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