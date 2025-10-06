-- ============================================
-- SMS3 LMS - Student Portal Sample Data
-- Matching Your Existing Database Structure
-- ============================================

-- ============================================
-- 1. CLASS ENROLLMENTS
-- ============================================
-- Note: Update student_id (currently 4) to match your actual student user ID
-- Update class_id values to match existing class IDs in your classes table

INSERT INTO class_enrollments (class_id, student_id, enrollment_date, status, final_grade, created_at, updated_at) VALUES
(1, 4, '2024-08-15', 'enrolled', NULL, NOW(), NOW()),
(2, 4, '2024-08-15', 'enrolled', NULL, NOW(), NOW()),
(3, 4, '2024-08-15', 'enrolled', NULL, NOW(), NOW()),
(4, 4, '2024-08-15', 'enrolled', NULL, NOW(), NOW()),
(5, 4, '2024-08-15', 'enrolled', NULL, NOW(), NOW());

-- ============================================
-- 2. ASSIGNMENTS
-- ============================================
-- Note: Update class_id, module_id, and teacher_id to match your data

INSERT INTO assignments (class_id, module_id, teacher_id, title, description, instructions, total_points, due_date, late_submission_allowed, late_penalty_percentage, file_attachment, status, created_at, updated_at) VALUES
(1, 1, 1, 'Data Mining Project', 'Implement data mining algorithms on a real dataset', 'Submit a complete Python/R implementation with documentation', 100, '2024-10-15 23:59:00', 1, 10.00, NULL, 'published', NOW(), NOW()),
(1, 2, 1, 'Big Data Case Study', 'Analyze a big data case study and present findings', 'Prepare a presentation and written report', 50, '2024-10-20 23:59:00', 1, 5.00, NULL, 'published', NOW(), NOW()),
(2, 1, 1, 'Capstone Proposal', 'Submit your capstone project proposal', 'Follow the proposal template provided', 100, '2024-10-25 23:59:00', 0, 0.00, NULL, 'published', NOW(), NOW()),
(3, 1, 2, 'Emerging Tech Research', 'Research paper on emerging technologies', 'Minimum 10 pages, APA format', 75, '2024-10-18 23:59:00', 1, 15.00, NULL, 'published', NOW(), NOW()),
(4, 1, 2, 'E-commerce Website', 'Build a functional e-commerce website', 'Must include cart, checkout, and payment integration', 150, '2024-10-30 23:59:00', 1, 10.00, NULL, 'published', NOW(), NOW()),
(5, 1, 3, 'Database Design Project', 'Design and implement a complete database system', 'ERD, normalization, and SQL scripts required', 100, '2024-10-22 23:59:00', 1, 5.00, NULL, 'published', NOW(), NOW());

-- ============================================
-- 3. ASSIGNMENT SUBMISSIONS
-- ============================================
-- Note: Update assignment_id and student_id to match your data

INSERT INTO assignment_submissions (assignment_id, student_id, submission_date, file_path, status, grade, feedback, graded_at, created_at, updated_at) VALUES
(1, 4, '2024-10-14 20:30:00', '/uploads/assignments/student4_assignment1.pdf', 'graded', 95, 'Excellent work! Great implementation of algorithms.', '2024-10-15 10:00:00', NOW(), NOW()),
(2, 4, '2024-10-19 18:00:00', '/uploads/assignments/student4_assignment2.pdf', 'graded', 88, 'Good analysis, could improve on visualization.', '2024-10-20 14:00:00', NOW(), NOW()),
(3, 4, NULL, NULL, 'pending', NULL, NULL, NULL, NOW(), NOW()),
(4, 4, '2024-10-17 22:00:00', '/uploads/assignments/student4_assignment4.pdf', 'graded', 92, 'Well-researched paper with good citations.', '2024-10-18 09:00:00', NOW(), NOW());

-- ============================================
-- 4. GRADES
-- ============================================
-- Note: component_id refers to grade_components table (quizzes, assignments, exams, etc.)
-- Update student_id, class_id, and component_id to match your data

INSERT INTO grades (student_id, class_id, component_id, score, max_score, percentage, weighted_score, remarks, graded_by, graded_at, created_at, updated_at) VALUES
-- Class 1 - Big Data Analysis
(4, 1, 1, 95.00, 100.00, 95.00, 38.00, 'Excellent performance', 1, '2024-10-15 10:00:00', NOW(), NOW()),
(4, 1, 2, 88.00, 50.00, 88.00, 17.60, 'Good work', 1, '2024-10-20 14:00:00', NOW(), NOW()),
(4, 1, 3, 92.00, 100.00, 92.00, 27.60, 'Very good', 1, '2024-10-10 15:00:00', NOW(), NOW()),

-- Class 2 - Capstone Project
(4, 2, 1, 90.00, 100.00, 90.00, 36.00, 'Strong proposal', 1, '2024-10-26 10:00:00', NOW(), NOW()),
(4, 2, 3, 85.00, 100.00, 85.00, 25.50, 'Good progress', 1, '2024-10-12 14:00:00', NOW(), NOW()),

-- Class 3 - IT Elective 4
(4, 3, 1, 92.00, 75.00, 92.00, 36.80, 'Outstanding research', 2, '2024-10-19 11:00:00', NOW(), NOW()),
(4, 3, 3, 88.00, 100.00, 88.00, 26.40, 'Very good', 2, '2024-10-14 16:00:00', NOW(), NOW()),

-- Class 4 - Web Development
(4, 4, 1, 145.00, 150.00, 96.67, 58.00, 'Excellent implementation', 2, '2024-10-31 09:00:00', NOW(), NOW()),
(4, 4, 3, 87.00, 100.00, 87.00, 26.10, 'Good work', 2, '2024-10-13 17:00:00', NOW(), NOW()),

-- Class 5 - Database Management
(4, 5, 1, 98.00, 100.00, 98.00, 39.20, 'Perfect ERD design', 3, '2024-10-23 10:00:00', NOW(), NOW()),
(4, 5, 3, 91.00, 100.00, 91.00, 27.30, 'Excellent', 3, '2024-10-14 18:00:00', NOW(), NOW());

-- ============================================
-- 5. QUIZZES (if your structure is different, share it)
-- ============================================
-- Placeholder - please share your quizzes table structure

-- ============================================
-- 6. QUIZ ATTEMPTS (if your structure is different, share it)
-- ============================================
-- Placeholder - please share your quiz_attempts table structure

-- ============================================
-- 7. LESSON MATERIALS (if your structure is different, share it)
-- ============================================
-- Placeholder - please share your lesson_materials table structure

-- ============================================
-- 8. ANNOUNCEMENTS (if exists)
-- ============================================
-- Placeholder - please share your announcements table structure if it exists

-- ============================================
-- NOTES:
-- ============================================
-- 1. Update all student_id values (currently 4) to match your actual student
-- 2. Update class_id values to match IDs from your classes table
-- 3. Update teacher_id values to match teacher user IDs
-- 4. Update module_id values to match your modules table
-- 5. Update component_id values to match your grade_components table
-- 6. Adjust dates as needed for your academic calendar
-- 7. File paths are placeholders - update with actual storage paths
-- 8. Share remaining table structures for complete data:
--    - quizzes
--    - quiz_attempts
--    - lesson_materials
--    - modules
--    - grade_components
