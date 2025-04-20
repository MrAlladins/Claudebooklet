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
// QR-kod funktioner
function verifyQRCoupon($db, $code) {
    // Parsa QR-koden för att få edition, kupong och användare
    if (!preg_match('/^THBOOKLET-E(\d+)-C(\d+)(?:-U(\d+))?$/', $code, $matches)) {
        return ['success' => false, 'message' => 'Ogiltigt kodformat.'];
    }
    
    $editionId = (int) $matches[1];
    $couponId = (int) $matches[2];
    $userId = isset($matches[3]) ? (int) $matches[3] : null;
    
    try {
        // Hämta kupongdetaljer
        $stmt = $db->prepare("
            SELECT c.*, e.title as edition_title, e.valid_until, co.name as company_name
            FROM coupons c
            JOIN editions e ON c.edition_id = e.id
            JOIN companies co ON c.company_id = co.id
            WHERE c.id = ? AND c.edition_id = ?
        ");
        
        $stmt->execute([$couponId, $editionId]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$coupon) {
            return ['success' => false, 'message' => 'Kupongen hittades inte.'];
        }
        
        // Om användare är specificerad, kontrollera om de har tillgång till editionen
        if ($userId) {
            $stmt = $db->prepare("
                SELECT * FROM user_editions 
                WHERE user_id = ? AND edition_id = ?
            ");
            
            $stmt->execute([$userId, $editionId]);
            $userEdition = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userEdition) {
                return ['success' => false, 'message' => 'Användaren har inte tillgång till denna edition.'];
            }
            
            // Hämta användarinformation
            $stmt = $db->prepare("SELECT id, email, name FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Hämta användningshistorik
        $stmt = $db->prepare("
            SELECT * FROM coupon_uses 
            WHERE coupon_id = ? AND user_id = ?
            ORDER BY used_at DESC
        ");
        
        $stmt->execute([
            $couponId,
            $userId ?: 0
        ]);
        
        $redemptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Kontrollera om kupongen fortfarande är giltig
        $isValid = true;
        $invalidReason = null;
        
        // Kontrollera status
        if ($coupon['status'] !== 'active') {
            $isValid = false;
            $invalidReason = "Kupongen är inte aktiv.";
        }
        
        // Kontrollera utgångsdatum
        if ($coupon['valid_until'] && strtotime($coupon['valid_until']) < time()) {
            $isValid = false;
            $invalidReason = "Kupongen har gått ut.";
        }
        
        // Kontrollera användningsgräns
        if ($coupon['max_uses'] && count($redemptions) >= $coupon['max_uses']) {
            $isValid = false;
            $invalidReason = "Kupongen har redan använts maximalt antal gånger.";
        }
        
        // Formatera användningshistorik
        $formattedRedemptions = array_map(function($r) {
            return [
                'id' => $r['id'],
                'date' => date('Y-m-d', strtotime($r['used_at'])),
                'time' => date('H:i', strtotime($r['used_at'])),
                'verification_code' => $r['verification_code'],
                'notes' => $r['notes']
            ];
        }, $redemptions);
        
        // Formatera svaret
        return [
            'success' => true,
            'coupon' => [
                'id' => $coupon['id'],
                'title' => $coupon['title'],
                'description' => $coupon['description'],
                'value' => $coupon['value'],
                'company' => $coupon['company_name'],
                'edition' => $coupon['edition_title'],
                'edition_id' => $coupon['edition_id'],
                'valid_until' => $coupon['valid_until'],
                'max_uses' => $coupon['max_uses'],
                'current_uses' => count($redemptions),
                'status' => $coupon['status'],
                'terms' => $coupon['terms'],
                'valid' => $isValid,
                'invalid_reason' => $invalidReason,
                'full_code' => $code
            ],
            'user' => $userId ? [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ] : null,
            'redemptions' => $formattedRedemptions
        ];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Databasfel vid verifiering.', 'error' => $e->getMessage()];
    }
}

function redeemQRCoupon($db, $code, $staff, $notes = null) {
    // Parsa QR-koden för att få edition, kupong och användare
    if (!preg_match('/^THBOOKLET-E(\d+)-C(\d+)(?:-U(\d+))?$/', $code, $matches)) {
        return ['success' => false, 'message' => 'Ogiltigt kodformat.'];
    }
    
    $editionId = (int) $matches[1];
    $couponId = (int) $matches[2];
    $userId = isset($matches[3]) ? (int) $matches[3] : 0; // 0 om ingen användare angavs
    
    try {
        // Börja en transaktion för att säkerställa att allt går bra
        $db->beginTransaction();
        
        // Hämta kupongdetaljer
        $stmt = $db->prepare("
            SELECT c.*, e.title as edition_title, e.valid_until
            FROM coupons c
            JOIN editions e ON c.edition_id = e.id
            WHERE c.id = ? AND c.edition_id = ?
        ");
        
        $stmt->execute([$couponId, $editionId]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$coupon) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Kupongen hittades inte.'];
        }
        
        // Om användare är specificerad (och inte 0), kontrollera om de har tillgång till editionen
        if ($userId > 0) {
            $stmt = $db->prepare("
                SELECT * FROM user_editions 
                WHERE user_id = ? AND edition_id = ?
            ");
            
            $stmt->execute([$userId, $editionId]);
            $userEdition = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userEdition) {
                $db->rollBack();
                return ['success' => false, 'message' => 'Användaren har inte tillgång till denna edition.'];
            }
        }
        
        // Hämta användningshistorik
        $stmt = $db->prepare("
            SELECT COUNT(*) as use_count FROM coupon_uses 
            WHERE coupon_id = ? AND user_id = ?
        ");
        
        $stmt->execute([$couponId, $userId]);
        $useCount = $stmt->fetch(PDO::FETCH_ASSOC)['use_count'];
        
        // Kontrollera om kupongen fortfarande är giltig
        $isValid = true;
        $invalidReason = null;
        
        // Kontrollera status
        if ($coupon['status'] !== 'active') {
            $isValid = false;
            $invalidReason = "Kupongen är inte aktiv.";
        }
        
        // Kontrollera utgångsdatum
        if ($coupon['valid_until'] && strtotime($coupon['valid_until']) < time()) {
            $isValid = false;
            $invalidReason = "Kupongen har gått ut.";
        }
        
        // Kontrollera användningsgräns
        if ($coupon['max_uses'] && $useCount >= $coupon['max_uses']) {
            $isValid = false;
            $invalidReason = "Kupongen har redan använts maximalt antal gånger.";
        }
        
        if (!$isValid) {
            $db->rollBack();
            return ['success' => false, 'message' => $invalidReason];
        }
        
        // Generera verifieringskod
        $verificationCode = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        
        // Registrera användning
        $stmt = $db->prepare("
            INSERT INTO coupon_uses (coupon_id, user_id, used_at, verification_code, notes)
            VALUES (?, ?, NOW(), ?, ?)
        ");
        
        $stmt->execute([$couponId, $userId, $verificationCode, $notes]);
        $useId = $db->lastInsertId();
        
        // Uppdatera kupong-counts
        $stmt = $db->prepare("
            UPDATE coupons 
            SET current_uses = current_uses + 1 
            WHERE id = ?
        ");
        
        $stmt->execute([$couponId]);
        
        // Slutför transaktionen
        $db->commit();
        
        // Formatera svaret
        return [
            'success' => true,
            'message' => 'Kupongen har lösts in framgångsrikt!',
            'redemption' => [
                'id' => $useId,
                'coupon_id' => $couponId,
                'user_id' => $userId,
                'verification_code' => $verificationCode,
                'timestamp' => date('Y-m-d H:i:s'),
                'notes' => $notes
            ],
            'coupon' => [
                'id' => $coupon['id'],
                'title' => $coupon['title'],
                'description' => $coupon['description'],
                'value' => $coupon['value'],
                'edition_id' => $coupon['edition_id'],
                'edition_title' => $coupon['edition_title'],
                'max_uses' => $coupon['max_uses'],
                'current_uses' => $useCount + 1
            ]
        ];
        
    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return ['success' => false, 'message' => 'Databasfel vid inlösning.', 'error' => $e->getMessage()];
    }
}

function getQRCodesForEdition($db, $editionId, $userId = null) {
    try {
        // Kontrollera om editionen finns
        $stmt = $db->prepare("SELECT * FROM editions WHERE id = ?");
        $stmt->execute([$editionId]);
        $edition = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$edition) {
            return ['success' => false, 'message' => 'Editionen hittades inte.'];
        }
        
        // Hämta alla kuponger för denna edition
        $stmt = $db->prepare("
            SELECT c.*, co.name as company_name 
            FROM coupons c
            JOIN companies co ON c.company_id = co.id
            WHERE c.edition_id = ?
            ORDER BY c.id
        ");
        
        $stmt->execute([$editionId]);
        $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Om en användare är angiven, hämta deras information
        $user = null;
        if ($userId) {
            $stmt = $db->prepare("
                SELECT u.*, ue.purchased_at 
                FROM users u
                JOIN user_editions ue ON u.id = ue.user_id
                WHERE u.id = ? AND ue.edition_id = ?
            ");
            
            $stmt->execute([$userId, $editionId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Användaren har inte tillgång till denna edition.'];
            }
        }
        
        // För varje kupong, skapa QR-kodsinformation
        $qrCodes = [];
        foreach ($coupons as $coupon) {
            // Generera QR-kod-värdet
            $qrValue = "THBOOKLET-E{$editionId}-C{$coupon['id']}";
            if ($userId) {
                $qrValue .= "-U{$userId}";
            }
            
            // Hämta användningshistorik om en användare är angiven
            $redemptions = [];
            if ($userId) {
                $stmt = $db->prepare("
                    SELECT * FROM coupon_uses
                    WHERE coupon_id = ? AND user_id = ?
                    ORDER BY used_at DESC
                ");
                
                $stmt->execute([$coupon['id'], $userId]);
                $redemptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Lägg till QR-kodsinformation i arrayen
            $qrCodes[] = [
                'coupon_id' => $coupon['id'],
                'title' => $coupon['title'],
                'company' => $coupon['company_name'],
                'description' => $coupon['description'],
                'value' => $coupon['value'],
                'max_uses' => $coupon['max_uses'],
                'current_uses' => count($redemptions),
                'qr_value' => $qrValue
            ];
        }
        
        // Formatera svaret
        return [
            'success' => true,
            'edition' => [
                'id' => $edition['id'],
                'title' => $edition['title'],
                'description' => $edition['description']
            ],
            'user' => $user ? [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ] : null,
            'coupons' => $qrCodes
        ];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Databasfel vid generering av QR-koder.', 'error' => $e->getMessage()];
    }
}
