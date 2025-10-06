<?php
/**
 * Authentication System
 * SMS3 Learning Management System
 */



class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Login user
     */
    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, 
                       s.student_id, s.student_number, s.program,
                       t.teacher_id, t.employee_number, t.department
                FROM users u
                LEFT JOIN students s ON u.user_id = s.user_id
                LEFT JOIN teachers t ON u.user_id = t.user_id
                WHERE u.username = :username AND u.status = 'active'
            ");
            
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['profile_picture'] = $user['profile_picture'];
                
                // Set role-specific session data
                if ($user['user_type'] === 'student') {
                    $_SESSION['student_id'] = $user['student_id'];
                    $_SESSION['student_number'] = $user['student_number'];
                    $_SESSION['program'] = $user['program'];
                } elseif ($user['user_type'] === 'teacher') {
                    $_SESSION['teacher_id'] = $user['teacher_id'];
                    $_SESSION['employee_number'] = $user['employee_number'];
                    $_SESSION['department'] = $user['department'];
                }
                
                // Update last login
                $this->updateLastLogin($user['user_id']);
                
                // Log activity
                $this->logActivity($user['user_id'], 'login', 'User logged in');
                
                return [
                    'success' => true,
                    'user_type' => $user['user_type'],
                    'message' => 'Login successful'
                ];
            }
            
            return ['success' => false, 'message' => 'Invalid username or password'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Login error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }
        
        session_destroy();
        
        return true;
    }
    
    /**
     * Register new user
     */
    public function register($data) {
        try {
            $this->db->beginTransaction();
            
            // Check if username or email already exists
            $stmt = $this->db->prepare("SELECT user_id FROM users WHERE username = :username OR email = :email");
            $stmt->execute([
                'username' => $data['username'],
                'email' => $data['email']
            ]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            // Hash password
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password_hash, user_type, first_name, last_name, middle_name, status)
                VALUES (:username, :email, :password_hash, :user_type, :first_name, :last_name, :middle_name, 'active')
            ");
            
            $stmt->execute([
                'username' => $data['username'],
                'email' => $data['email'],
                'password_hash' => $password_hash,
                'user_type' => $data['user_type'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'] ?? null
            ]);
            
            $user_id = $this->db->lastInsertId();
            
            // Create role-specific record
            if ($data['user_type'] === 'student') {
                $stmt = $this->db->prepare("
                    INSERT INTO students (user_id, student_number, enrollment_year, current_year_level, program, section)
                    VALUES (:user_id, :student_number, :enrollment_year, :year_level, :program, :section)
                ");
                
                $stmt->execute([
                    'user_id' => $user_id,
                    'student_number' => $data['student_number'],
                    'enrollment_year' => date('Y'),
                    'year_level' => $data['year_level'] ?? '1',
                    'program' => $data['program'] ?? null,
                    'section' => $data['section'] ?? null
                ]);
            } elseif ($data['user_type'] === 'teacher') {
                $stmt = $this->db->prepare("
                    INSERT INTO teachers (user_id, employee_number, department, position, specialization, hire_date)
                    VALUES (:user_id, :employee_number, :department, :position, :specialization, :hire_date)
                ");
                
                $stmt->execute([
                    'user_id' => $user_id,
                    'employee_number' => $data['employee_number'],
                    'department' => $data['department'] ?? null,
                    'position' => $data['position'] ?? null,
                    'specialization' => $data['specialization'] ?? null,
                    'hire_date' => date('Y-m-d')
                ]);
            }
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $user_id];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Registration error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($user_id, $old_password, $new_password) {
        try {
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($old_password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id");
            $stmt->execute([
                'password_hash' => $new_hash,
                'user_id' => $user_id
            ]);
            
            $this->logActivity($user_id, 'password_change', 'User changed password');
            
            return ['success' => true, 'message' => 'Password changed successfully'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error changing password: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin($user_id) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
        } catch (PDOException $e) {
            // Silently fail
        }
    }
    
    /**
     * Log user activity
     */
    private function logActivity($user_id, $activity_type, $description) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (user_id, activity_type, activity_description, ip_address, user_agent)
                VALUES (:user_id, :activity_type, :description, :ip_address, :user_agent)
            ");
            
            $stmt->execute([
                'user_id' => $user_id,
                'activity_type' => $activity_type,
                'description' => $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (PDOException $e) {
            // Silently fail
        }
    }
    
    /**
     * Check session timeout
     */
    public function checkSessionTimeout() {
        if (isset($_SESSION['last_activity'])) {
            $elapsed_time = time() - $_SESSION['last_activity'];
            
            if ($elapsed_time > SESSION_TIMEOUT) {
                $this->logout();
                return false;
            }
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
}
?>
