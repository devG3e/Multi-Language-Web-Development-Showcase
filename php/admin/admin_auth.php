<?php
// ===== ADMIN AUTHENTICATION SYSTEM =====
// Industry-standard admin authentication with 3-admin constraint

require_once '../config.php';

class AdminAuth {
    private $db;
    private $max_admins = 3;
    private $session_timeout = 3600; // 1 hour
    private $max_login_attempts = 5;
    private $lockout_duration = 900; // 15 minutes
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->initSession();
    }
    
    private function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
    }
    
    public function login($username, $password) {
        // Check for brute force attempts
        if ($this->isAccountLocked($username)) {
            return ['success' => false, 'message' => 'Account temporarily locked due to too many failed attempts.'];
        }
        
        // Validate input
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required.'];
        }
        
        try {
            // Get user with role check
            $sql = "SELECT id, username, email, password_hash, role, is_active, last_login, failed_attempts, locked_until 
                    FROM users 
                    WHERE username = :username AND role = 'admin'";
            
            $user = $this->db->fetch($sql, ['username' => $username]);
            
            if (!$user) {
                $this->recordFailedAttempt($username);
                return ['success' => false, 'message' => 'Invalid credentials or insufficient privileges.'];
            }
            
            // Check if account is active
            if (!$user['is_active']) {
                return ['success' => false, 'message' => 'Account is deactivated. Contact system administrator.'];
            }
            
            // Check if account is locked
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                return ['success' => false, 'message' => 'Account is temporarily locked. Try again later.'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                $this->recordFailedAttempt($username);
                return ['success' => false, 'message' => 'Invalid credentials.'];
            }
            
            // Reset failed attempts on successful login
            $this->resetFailedAttempts($username);
            
            // Create session
            $this->createSession($user);
            
            // Update last login
            $this->updateLastLogin($user['id']);
            
            // Log successful login
            $this->logActivity($user['id'], 'login', 'Successful admin login');
            
            return ['success' => true, 'message' => 'Login successful.', 'user' => $user];
            
        } catch (Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during login.'];
        }
    }
    
    public function logout() {
        if (isset($_SESSION['admin_id'])) {
            $this->logActivity($_SESSION['admin_id'], 'logout', 'Admin logout');
        }
        
        // Clear session
        session_unset();
        session_destroy();
        
        // Clear session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        return ['success' => true, 'message' => 'Logged out successfully.'];
    }
    
    public function isAuthenticated() {
        if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_token'])) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['admin_last_activity']) && 
            (time() - $_SESSION['admin_last_activity']) > $this->session_timeout) {
            $this->logout();
            return false;
        }
        
        // Update last activity
        $_SESSION['admin_last_activity'] = time();
        
        return true;
    }
    
    public function requireAuth() {
        if (!$this->isAuthenticated()) {
            header('Location: login.php');
            exit();
        }
    }
    
    public function getCurrentAdmin() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        $sql = "SELECT id, username, email, role, created_at, last_login 
                FROM users 
                WHERE id = :id AND role = 'admin' AND is_active = 1";
        
        return $this->db->fetch($sql, ['id' => $_SESSION['admin_id']]);
    }
    
    public function createAdmin($username, $email, $password, $full_name) {
        // Check admin count constraint
        $admin_count = $this->getAdminCount();
        if ($admin_count >= $this->max_admins) {
            return ['success' => false, 'message' => "Maximum number of admins ({$this->max_admins}) reached."];
        }
        
        // Validate input
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }
        
        if (!validateEmail($email)) {
            return ['success' => false, 'message' => 'Invalid email format.'];
        }
        
        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
        }
        
        try {
            // Check if username or email already exists
            $sql = "SELECT id FROM users WHERE username = :username OR email = :email";
            $existing = $this->db->fetch($sql, ['username' => $username, 'email' => $email]);
            
            if ($existing) {
                return ['success' => false, 'message' => 'Username or email already exists.'];
            }
            
            // Hash password
            $password_hash = password_hash($password, PASSWORD_ARGON2ID);
            
            // Insert new admin
            $data = [
                'username' => $username,
                'email' => $email,
                'password_hash' => $password_hash,
                'full_name' => $full_name,
                'role' => 'admin',
                'is_active' => 1
            ];
            
            $admin_id = $this->db->insert('users', $data);
            
            if ($admin_id) {
                $this->logActivity($admin_id, 'admin_created', "Admin account created: {$username}");
                return ['success' => true, 'message' => 'Admin account created successfully.', 'admin_id' => $admin_id];
            } else {
                return ['success' => false, 'message' => 'Failed to create admin account.'];
            }
            
        } catch (Exception $e) {
            error_log("Create admin error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while creating admin account.'];
        }
    }
    
    public function updateAdmin($admin_id, $data) {
        if (!$this->isAuthenticated()) {
            return ['success' => false, 'message' => 'Authentication required.'];
        }
        
        try {
            // Validate admin exists and is admin
            $sql = "SELECT id FROM users WHERE id = :id AND role = 'admin'";
            $admin = $this->db->fetch($sql, ['id' => $admin_id]);
            
            if (!$admin) {
                return ['success' => false, 'message' => 'Admin not found.'];
            }
            
            // Update admin data
            $result = $this->db->update('users', $data, 'id = :id', ['id' => $admin_id]);
            
            if ($result) {
                $this->logActivity($admin_id, 'admin_updated', "Admin account updated");
                return ['success' => true, 'message' => 'Admin account updated successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to update admin account.'];
            }
            
        } catch (Exception $e) {
            error_log("Update admin error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating admin account.'];
        }
    }
    
    public function deactivateAdmin($admin_id) {
        if (!$this->isAuthenticated()) {
            return ['success' => false, 'message' => 'Authentication required.'];
        }
        
        // Prevent self-deactivation
        if ($admin_id == $_SESSION['admin_id']) {
            return ['success' => false, 'message' => 'Cannot deactivate your own account.'];
        }
        
        try {
            $result = $this->db->update('users', 
                ['is_active' => 0], 
                'id = :id AND role = "admin"', 
                ['id' => $admin_id]
            );
            
            if ($result) {
                $this->logActivity($admin_id, 'admin_deactivated', "Admin account deactivated");
                return ['success' => true, 'message' => 'Admin account deactivated successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to deactivate admin account.'];
            }
            
        } catch (Exception $e) {
            error_log("Deactivate admin error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deactivating admin account.'];
        }
    }
    
    public function getAllAdmins() {
        try {
            $sql = "SELECT id, username, email, full_name, is_active, created_at, last_login 
                    FROM users 
                    WHERE role = 'admin' 
                    ORDER BY created_at DESC";
            
            return $this->db->fetchAll($sql);
            
        } catch (Exception $e) {
            error_log("Get admins error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAdminCount() {
        try {
            $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND is_active = 1";
            $result = $this->db->fetch($sql);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Get admin count error: " . $e->getMessage());
            return 0;
        }
    }
    
    private function createSession($user) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_token'] = bin2hex(random_bytes(32));
        $_SESSION['admin_last_activity'] = time();
        $_SESSION['admin_ip'] = $_SERVER['REMOTE_ADDR'];
    }
    
    private function updateLastLogin($user_id) {
        $this->db->update('users', 
            ['last_login' => date('Y-m-d H:i:s')], 
            'id = :id', 
            ['id' => $user_id]
        );
    }
    
    private function recordFailedAttempt($username) {
        try {
            $sql = "UPDATE users SET 
                    failed_attempts = COALESCE(failed_attempts, 0) + 1,
                    locked_until = CASE 
                        WHEN COALESCE(failed_attempts, 0) + 1 >= :max_attempts 
                        THEN DATE_ADD(NOW(), INTERVAL :lockout_duration SECOND)
                        ELSE locked_until 
                    END
                    WHERE username = :username";
            
            $this->db->query($sql, [
                'username' => $username,
                'max_attempts' => $this->max_login_attempts,
                'lockout_duration' => $this->lockout_duration
            ]);
        } catch (Exception $e) {
            error_log("Record failed attempt error: " . $e->getMessage());
        }
    }
    
    private function resetFailedAttempts($username) {
        try {
            $this->db->update('users', 
                ['failed_attempts' => 0, 'locked_until' => null], 
                'username = :username', 
                ['username' => $username]
            );
        } catch (Exception $e) {
            error_log("Reset failed attempts error: " . $e->getMessage());
        }
    }
    
    private function isAccountLocked($username) {
        try {
            $sql = "SELECT locked_until FROM users WHERE username = :username";
            $user = $this->db->fetch($sql, ['username' => $username]);
            
            if ($user && $user['locked_until']) {
                return strtotime($user['locked_until']) > time();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Check account lock error: " . $e->getMessage());
            return false;
        }
    }
    
    private function logActivity($user_id, $action, $description) {
        try {
            $data = [
                'user_id' => $user_id,
                'action' => $action,
                'description' => $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('admin_activities', $data);
        } catch (Exception $e) {
            error_log("Log activity error: " . $e->getMessage());
        }
    }
    
    public function getActivityLog($limit = 50) {
        try {
            $sql = "SELECT a.*, u.username 
                    FROM admin_activities a 
                    JOIN users u ON a.user_id = u.id 
                    ORDER BY a.created_at DESC 
                    LIMIT :limit";
            
            return $this->db->fetchAll($sql, ['limit' => $limit]);
        } catch (Exception $e) {
            error_log("Get activity log error: " . $e->getMessage());
            return [];
        }
    }
    
    public function changePassword($admin_id, $current_password, $new_password) {
        if (!$this->isAuthenticated()) {
            return ['success' => false, 'message' => 'Authentication required.'];
        }
        
        // Validate new password
        if (strlen($new_password) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters long.'];
        }
        
        try {
            // Get current password hash
            $sql = "SELECT password_hash FROM users WHERE id = :id AND role = 'admin'";
            $user = $this->db->fetch($sql, ['id' => $admin_id]);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Admin not found.'];
            }
            
            // Verify current password
            if (!password_verify($current_password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect.'];
            }
            
            // Hash new password
            $new_password_hash = password_hash($new_password, PASSWORD_ARGON2ID);
            
            // Update password
            $result = $this->db->update('users', 
                ['password_hash' => $new_password_hash], 
                'id = :id', 
                ['id' => $admin_id]
            );
            
            if ($result) {
                $this->logActivity($admin_id, 'password_changed', 'Password changed successfully');
                return ['success' => true, 'message' => 'Password changed successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to change password.'];
            }
            
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while changing password.'];
        }
    }
}
?> 