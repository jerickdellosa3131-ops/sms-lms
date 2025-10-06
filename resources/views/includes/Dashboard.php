<?php
/**
 * Dashboard Data Access Class
 * SMS3 Learning Management System
 */



class Dashboard {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Get Admin Dashboard Data
     */
    public function getAdminDashboard() {
        try {
            $data = [];
            
            // Get total students count
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM students WHERE enrollment_status = 'enrolled'");
            $data['total_students'] = $stmt->fetch()['count'] ?? 0;
            
            // Get new students (enrolled this month)
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM students 
                WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
            ");
            $data['new_students'] = $stmt->fetch()['count'] ?? 0;
            
            // Get total courses/subjects
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM subjects WHERE status = 'active'");
            $data['total_courses'] = $stmt->fetch()['count'] ?? 0;
            
            // Get upcoming deadlines count
            $stmt = $this->db->query("
                SELECT COUNT(*) as count FROM assignments 
                WHERE due_date >= NOW() AND due_date <= DATE_ADD(NOW(), INTERVAL 7 DAY) AND status = 'published'
            ");
            $data['upcoming_deadlines'] = $stmt->fetch()['count'] ?? 0;
            
            // Get recent announcements
            $stmt = $this->db->query("
                SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as posted_by_name
                FROM announcements a
                INNER JOIN users u ON a.posted_by = u.user_id
                WHERE a.status = 'published' AND (a.expires_at IS NULL OR a.expires_at >= NOW())
                ORDER BY a.is_pinned DESC, a.published_at DESC
                LIMIT 5
            ");
            $data['announcements'] = $stmt->fetchAll();
            
            // Get recent student enrollments
            $stmt = $this->db->query("
                SELECT s.student_number, CONCAT(u.first_name, ' ', u.last_name) as student_name,
                       s.program, s.current_year_level, s.created_at
                FROM students s
                INNER JOIN users u ON s.user_id = u.user_id
                ORDER BY s.created_at DESC
                LIMIT 10
            ");
            $data['recent_students'] = $stmt->fetchAll();
            
            // Get recent teacher uploads
            $stmt = $this->db->query("
                SELECT lm.*, CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                       lm.material_title, lm.material_type, lm.created_at
                FROM lesson_materials lm
                INNER JOIN teachers t ON lm.teacher_id = t.teacher_id
                INNER JOIN users u ON t.user_id = u.user_id
                WHERE lm.is_published = TRUE
                ORDER BY lm.created_at DESC
                LIMIT 10
            ");
            $data['recent_uploads'] = $stmt->fetchAll();
            
            // Get performance data for chart (last 6 weeks)
            $stmt = $this->db->query("
                SELECT 
                    WEEK(submitted_at) as week_num,
                    AVG(score / (SELECT total_points FROM assignments WHERE assignment_id = assignment_submissions.assignment_id) * 100) as avg_score
                FROM assignment_submissions
                WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 6 WEEK)
                GROUP BY WEEK(submitted_at)
                ORDER BY week_num
            ");
            $data['performance_data'] = $stmt->fetchAll();
            
            return $data;
            
        } catch (PDOException $e) {
            error_log("Dashboard error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get Student Dashboard Data
     */
    public function getStudentDashboard($student_id) {
        try {
            $data = [];
            
            // Get enrolled classes
            $stmt = $this->db->prepare("
                SELECT c.class_id, c.class_code, c.section_name,
                       s.subject_code, s.subject_name, s.units,
                       CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                       (SELECT COUNT(*) FROM modules m WHERE m.class_id = c.class_id AND m.is_published = TRUE) as total_modules,
                       (SELECT COUNT(*) FROM module_completions mc 
                        INNER JOIN modules m ON mc.module_id = m.module_id 
                        WHERE m.class_id = c.class_id AND mc.student_id = :student_id) as completed_modules
                FROM class_enrollments ce
                INNER JOIN classes c ON ce.class_id = c.class_id
                INNER JOIN subjects s ON c.subject_id = s.subject_id
                INNER JOIN teachers t ON c.teacher_id = t.teacher_id
                INNER JOIN users u ON t.user_id = u.user_id
                WHERE ce.student_id = :student_id AND ce.status = 'enrolled'
                ORDER BY s.subject_name
            ");
            $stmt->execute(['student_id' => $student_id]);
            $data['enrolled_classes'] = $stmt->fetchAll();
            
            // Get upcoming assignments/deadlines
            $stmt = $this->db->prepare("
                SELECT a.assignment_id, a.title, a.due_date, a.total_points,
                       s.subject_code, s.subject_name,
                       (SELECT submission_id FROM assignment_submissions 
                        WHERE assignment_id = a.assignment_id AND student_id = :student_id) as submitted
                FROM assignments a
                INNER JOIN classes c ON a.class_id = c.class_id
                INNER JOIN subjects s ON c.subject_id = s.subject_id
                INNER JOIN class_enrollments ce ON c.class_id = ce.class_id
                WHERE ce.student_id = :student_id AND a.due_date >= NOW() AND a.status = 'published'
                ORDER BY a.due_date ASC
                LIMIT 10
            ");
            $stmt->execute(['student_id' => $student_id]);
            $data['upcoming_assignments'] = $stmt->fetchAll();
            
            // Get recent grades
            $stmt = $this->db->prepare("
                SELECT g.*, gc.component_name, s.subject_code, s.subject_name, g.graded_at
                FROM grades g
                INNER JOIN grade_components gc ON g.component_id = gc.component_id
                INNER JOIN classes c ON g.class_id = c.class_id
                INNER JOIN subjects s ON c.subject_id = s.subject_id
                WHERE g.student_id = :student_id
                ORDER BY g.graded_at DESC
                LIMIT 10
            ");
            $stmt->execute(['student_id' => $student_id]);
            $data['recent_grades'] = $stmt->fetchAll();
            
            // Get class announcements
            $stmt = $this->db->prepare("
                SELECT DISTINCT a.*, CONCAT(u.first_name, ' ', u.last_name) as posted_by_name
                FROM announcements a
                INNER JOIN users u ON a.posted_by = u.user_id
                LEFT JOIN class_enrollments ce ON a.class_id = ce.class_id
                WHERE a.status = 'published' 
                AND (a.expires_at IS NULL OR a.expires_at >= NOW())
                AND (a.target_audience = 'all' OR a.target_audience = 'students' OR ce.student_id = :student_id)
                ORDER BY a.is_pinned DESC, a.published_at DESC
                LIMIT 5
            ");
            $stmt->execute(['student_id' => $student_id]);
            $data['announcements'] = $stmt->fetchAll();
            
            // Get GPA and academic stats
            $stmt = $this->db->prepare("
                SELECT gpa, current_year_level, program 
                FROM students 
                WHERE student_id = :student_id
            ");
            $stmt->execute(['student_id' => $student_id]);
            $data['academic_info'] = $stmt->fetch();
            
            return $data;
            
        } catch (PDOException $e) {
            error_log("Student Dashboard error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get Teacher Dashboard Data
     */
    public function getTeacherDashboard($teacher_id) {
        try {
            $data = [];
            
            // Get total classes
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM classes WHERE teacher_id = :teacher_id AND status = 'active'
            ");
            $stmt->execute(['teacher_id' => $teacher_id]);
            $data['total_classes'] = $stmt->fetch()['count'] ?? 0;
            
            // Get total students across all classes
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT ce.student_id) as count
                FROM classes c
                INNER JOIN class_enrollments ce ON c.class_id = ce.class_id
                WHERE c.teacher_id = :teacher_id AND c.status = 'active' AND ce.status = 'enrolled'
            ");
            $stmt->execute(['teacher_id' => $teacher_id]);
            $data['total_students'] = $stmt->fetch()['count'] ?? 0;
            
            // Get uploaded materials count
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM lesson_materials WHERE teacher_id = :teacher_id
            ");
            $stmt->execute(['teacher_id' => $teacher_id]);
            $data['uploaded_materials'] = $stmt->fetch()['count'] ?? 0;
            
            // Get pending assignments to grade
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM assignment_submissions asub
                INNER JOIN assignments a ON asub.assignment_id = a.assignment_id
                WHERE a.teacher_id = :teacher_id AND asub.status = 'submitted'
            ");
            $stmt->execute(['teacher_id' => $teacher_id]);
            $data['pending_grading'] = $stmt->fetch()['count'] ?? 0;
            
            // Get teacher's classes
            $stmt = $this->db->prepare("
                SELECT c.*, s.subject_code, s.subject_name,
                       (SELECT COUNT(*) FROM class_enrollments WHERE class_id = c.class_id AND status = 'enrolled') as enrolled_students
                FROM classes c
                INNER JOIN subjects s ON c.subject_id = s.subject_id
                WHERE c.teacher_id = :teacher_id AND c.status = 'active'
                ORDER BY s.subject_name
            ");
            $stmt->execute(['teacher_id' => $teacher_id]);
            $data['classes'] = $stmt->fetchAll();
            
            // Get recent activity
            $stmt = $this->db->prepare("
                SELECT 'material' as type, lm.created_at as date, 
                       CONCAT('Uploaded: ', lm.material_title) as activity, s.subject_name
                FROM lesson_materials lm
                INNER JOIN modules m ON lm.module_id = m.module_id
                INNER JOIN classes c ON m.class_id = c.class_id
                INNER JOIN subjects s ON c.subject_id = s.subject_id
                WHERE lm.teacher_id = :teacher_id
                UNION ALL
                SELECT 'assignment' as type, a.created_at as date,
                       CONCAT('Created assignment: ', a.title) as activity, s.subject_name
                FROM assignments a
                INNER JOIN classes c ON a.class_id = c.class_id
                INNER JOIN subjects s ON c.subject_id = s.subject_id
                WHERE a.teacher_id = :teacher_id
                UNION ALL
                SELECT 'quiz' as type, q.created_at as date,
                       CONCAT('Created quiz: ', q.quiz_title) as activity, s.subject_name
                FROM quizzes q
                INNER JOIN classes c ON q.class_id = c.class_id
                INNER JOIN subjects s ON c.subject_id = s.subject_id
                WHERE q.teacher_id = :teacher_id
                ORDER BY date DESC
                LIMIT 10
            ");
            $stmt->execute(['teacher_id' => $teacher_id]);
            $data['recent_activity'] = $stmt->fetchAll();
            
            // Average class performance
            $stmt = $this->db->prepare("
                SELECT AVG(g.percentage) as avg_performance
                FROM grades g
                INNER JOIN classes c ON g.class_id = c.class_id
                WHERE c.teacher_id = :teacher_id
            ");
            $stmt->execute(['teacher_id' => $teacher_id]);
            $result = $stmt->fetch();
            $data['avg_performance'] = $result['avg_performance'] ? round($result['avg_performance'], 2) : 0;
            
            return $data;
            
        } catch (PDOException $e) {
            error_log("Teacher Dashboard error: " . $e->getMessage());
            return [];
        }
    }
}
?>
