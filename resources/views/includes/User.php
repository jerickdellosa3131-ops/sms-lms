<?php
/**
 * User Data Access Class
 * SMS3 Learning Management System
 */



class User {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, 
                       s.student_id, s.student_number, s.program, s.current_year_level, s.gpa,
                       t.teacher_id, t.employee_number, t.department, t.position,
                       pi.contact_number, pi.address_line1, pi.city
                FROM users u
                LEFT JOIN students s ON u.user_id = s.user_id
                LEFT JOIN teachers t ON u.user_id = t.user_id
                LEFT JOIN personal_info pi ON u.user_id = pi.user_id
                WHERE u.user_id = :user_id
            ");
            
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Get user profile data
     */
    public function getUserProfile($user_id) {
        return $this->getUserById($user_id);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($user_id, $data) {
        try {
            $this->db->beginTransaction();
            
            // Update users table
            $stmt = $this->db->prepare("
                UPDATE users 
                SET first_name = :first_name, 
                    last_name = :last_name, 
                    middle_name = :middle_name,
                    email = :email,
                    profile_picture = :profile_picture
                WHERE user_id = :user_id
            ");
            
            $stmt->execute([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'email' => $data['email'],
                'profile_picture' => $data['profile_picture'] ?? null,
                'user_id' => $user_id
            ]);
            
            // Update or insert personal info
            $stmt = $this->db->prepare("
                INSERT INTO personal_info 
                (user_id, contact_number, address_line1, address_line2, city, state_province, postal_code, country)
                VALUES (:user_id, :contact_number, :address_line1, :address_line2, :city, :state_province, :postal_code, :country)
                ON DUPLICATE KEY UPDATE
                contact_number = :contact_number,
                address_line1 = :address_line1,
                address_line2 = :address_line2,
                city = :city,
                state_province = :state_province,
                postal_code = :postal_code,
                country = :country
            ");
            
            $stmt->execute([
                'user_id' => $user_id,
                'contact_number' => $data['contact_number'] ?? null,
                'address_line1' => $data['address_line1'] ?? null,
                'address_line2' => $data['address_line2'] ?? null,
                'city' => $data['city'] ?? null,
                'state_province' => $data['state_province'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => $data['country'] ?? 'Philippines'
            ]);
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Profile updated successfully'];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error updating profile: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get all users by type
     */
    public function getUsersByType($user_type, $limit = null, $offset = 0) {
        try {
            $sql = "SELECT u.*, 
                           s.student_number, s.program, s.current_year_level,
                           t.employee_number, t.department
                    FROM users u
                    LEFT JOIN students s ON u.user_id = s.user_id
                    LEFT JOIN teachers t ON u.user_id = t.user_id
                    WHERE u.user_type = :user_type AND u.status = 'active'
                    ORDER BY u.last_name, u.first_name";
            
            if ($limit) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_type', $user_type, PDO::PARAM_STR);
            
            if ($limit) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get user count by type
     */
    public function getUserCountByType($user_type) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM users 
                WHERE user_type = :user_type AND status = 'active'
            ");
            
            $stmt->execute(['user_type' => $user_type]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
            
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Search users
     */
    public function searchUsers($search_term, $user_type = null) {
        try {
            $sql = "SELECT u.*, 
                           s.student_number, s.program,
                           t.employee_number, t.department
                    FROM users u
                    LEFT JOIN students s ON u.user_id = s.user_id
                    LEFT JOIN teachers t ON u.user_id = t.user_id
                    WHERE (u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)
                    AND u.status = 'active'";
            
            if ($user_type) {
                $sql .= " AND u.user_type = :user_type";
            }
            
            $sql .= " ORDER BY u.last_name, u.first_name LIMIT 50";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':search', '%' . $search_term . '%', PDO::PARAM_STR);
            
            if ($user_type) {
                $stmt->bindValue(':user_type', $user_type, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Delete/Deactivate user
     */
    public function deactivateUser($user_id) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET status = 'inactive' WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            return ['success' => true, 'message' => 'User deactivated successfully'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deactivating user: ' . $e->getMessage()];
        }
    }
}
?>
