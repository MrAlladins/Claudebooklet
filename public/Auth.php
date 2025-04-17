<?php
/**
 * Authentication class
 */
class Auth {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Check if user is logged in
     * @return boolean
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Login user
     * @param string $email
     * @param string $password
     * @return array
     */
    public function login($email, $password) {
        $stmt = $this->db->prepare('SELECT id, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            
            return ['success' => true, 'user_id' => $user['id'], 'role' => $user['role']];
        }
        
        return ['success' => false, 'message' => 'Ogiltig e-post eller lösenord'];
    }
    
    /**
     * Log out user
     */
    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_role']);
        session_destroy();
    }
    
    /**
     * Create a new user
     * @param string $email
     * @param string $password
     * @param string $name
     * @param string $role
     * @return array
     */
    public function createUser($email, $password, $name, $role = 'customer') {
        // Check if email already exists
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'E-postadressen används redan'];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $this->db->prepare('INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, ?)');
        if ($stmt->execute([$email, $hashedPassword, $name, $role])) {
            return ['success' => true, 'user_id' => $this->db->lastInsertId()];
        }
        
        return ['success' => false, 'message' => 'Kunde inte skapa användare'];
    }
    
    /**
     * Get current user
     * @return array|null
     */
    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        $stmt = $this->db->prepare('SELECT id, email, name, role FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if user has role
     * @param string $role
     * @return boolean
     */
    public function hasRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }
}