-- ============================================
-- SMS3 LMS - Student Portal Database Setup
-- ============================================

-- ============================================
-- 1. CLASSES/COURSES
-- ============================================
-- Note: Using your existing classes table structure
-- Adjust subject_id, semester_id, and teacher_id to match your data
INSERT INTO classes (subject_id, semester_id, teacher_id, section_name, class_code, schedule_days, schedule_time_start, schedule_time_end, room, max_students, status, created_at, updated_at) VALUES
(1, 1, 1, 'BSIT-4A', 'BDA-2025', 'MWF', '08:00:00', '09:30:00', 'CS-101', 40, 'active', NOW(), NOW()),
(2, 1, 1, 'BSIT-3A', 'CAP1-2025', 'TTH', '10:00:00', '11:30:00', 'CS-202', 40, 'active', NOW(), NOW()),
(3, 1, 2, 'BSCS-2B', 'ITE4-2025', 'MWF', '13:00:00', '14:30:00', 'CS-101', 35, 'active', NOW(), NOW()),
(4, 1, 2, 'BSIT-4A', 'WEB-2025', 'TTH', '15:00:00', '17:00:00', 'CS-301', 30, 'active', NOW(), NOW()),
(5, 1, 3, 'BSCS-1A', 'DBM-2025', 'MWF', '08:00:00', '09:30:00', 'CS-102', 45, 'active', NOW(), NOW());

-- ============================================
-- 2. STUDENT ENROLLMENTS
-- ============================================
-- Assuming student user_id is 4 (update as needed)
INSERT INTO enrollments (user_id, class_id, enrollment_date, status, created_at, updated_at) VALUES
(4, 1, '2024-08-15', 'active', NOW(), NOW()),
(4, 2, '2024-08-15', 'active', NOW(), NOW()),
(4, 3, '2024-08-15', 'active', NOW(), NOW()),
(4, 4, '2024-08-15', 'active', NOW(), NOW()),
(4, 5, '2024-08-15', 'active', NOW(), NOW());

-- ============================================
-- 3. ASSIGNMENTS
-- ============================================
INSERT INTO assignments (class_id, title, description, due_date, total_points, created_by, created_at, updated_at) VALUES
(1, 'Data Mining Project', 'Implement data mining algorithms on a real dataset', '2024-10-15 23:59:00', 100, 1, NOW(), NOW()),
(1, 'Big Data Case Study', 'Analyze a big data case study and present findings', '2024-10-20 23:59:00', 50, 1, NOW(), NOW()),
(2, 'Capstone Proposal', 'Submit your capstone project proposal', '2024-10-25 23:59:00', 100, 1, NOW(), NOW()),
(3, 'Emerging Tech Research', 'Research paper on emerging technologies', '2024-10-18 23:59:00', 75, 2, NOW(), NOW()),
(4, 'E-commerce Website', 'Build a functional e-commerce website', '2024-10-30 23:59:00', 150, 2, NOW(), NOW()),
(5, 'Database Design Project', 'Design and implement a complete database system', '2024-10-22 23:59:00', 100, 3, NOW(), NOW());

-- ============================================
-- 4. ASSIGNMENT SUBMISSIONS
-- ============================================
INSERT INTO assignment_submissions (assignment_id, student_id, submission_date, file_path, status, grade, feedback, graded_at, created_at, updated_at) VALUES
(1, 4, '2024-10-14 20:30:00', '/uploads/assignments/student4_assignment1.pdf', 'graded', 95, 'Excellent work! Great implementation of algorithms.', '2024-10-15 10:00:00', NOW(), NOW()),
(2, 4, '2024-10-19 18:00:00', '/uploads/assignments/student4_assignment2.pdf', 'graded', 88, 'Good analysis, could improve on visualization.', '2024-10-20 14:00:00', NOW(), NOW()),
(3, 4, NULL, NULL, 'pending', NULL, NULL, NULL, NOW(), NOW()),
(4, 4, '2024-10-17 22:00:00', '/uploads/assignments/student4_assignment4.pdf', 'graded', 92, 'Well-researched paper with good citations.', '2024-10-18 09:00:00', NOW(), NOW());

-- ============================================
-- 5. QUIZZES
-- ============================================
INSERT INTO quizzes (class_id, title, description, duration_minutes, total_points, available_from, available_until, created_by, created_at, updated_at) VALUES
(1, 'Data Mining Quiz 1', 'Quiz on data mining fundamentals', 30, 50, '2024-10-01 00:00:00', '2024-10-08 23:59:00', 1, NOW(), NOW()),
(1, 'Big Data Quiz 2', 'Quiz on big data processing', 45, 50, '2024-10-10 00:00:00', '2024-10-17 23:59:00', 1, NOW(), NOW()),
(2, 'Project Management Quiz', 'Quiz on project management concepts', 30, 50, '2024-10-05 00:00:00', '2024-10-12 23:59:00', 1, NOW(), NOW()),
(3, 'Tech Trends Quiz', 'Quiz on emerging technologies', 30, 50, '2024-10-08 00:00:00', '2024-10-15 23:59:00', 2, NOW(), NOW()),
(4, 'Web Dev Quiz 1', 'HTML, CSS, JavaScript basics', 30, 50, '2024-10-06 00:00:00', '2024-10-13 23:59:00', 2, NOW(), NOW()),
(5, 'SQL Quiz', 'Database query and design', 45, 50, '2024-10-07 00:00:00', '2024-10-14 23:59:00', 3, NOW(), NOW());

-- ============================================
-- 6. QUIZ ATTEMPTS
-- ============================================
INSERT INTO quiz_attempts (quiz_id, student_id, attempt_number, started_at, completed_at, score, total_possible, status, created_at, updated_at) VALUES
(1, 4, 1, '2024-10-07 14:00:00', '2024-10-07 14:28:00', 50, 50, 'completed', NOW(), NOW()),
(2, 4, 1, '2024-10-16 15:00:00', '2024-10-16 15:35:00', 38, 50, 'completed', NOW(), NOW()),
(3, 4, 1, '2024-10-11 16:00:00', '2024-10-11 16:25:00', 33, 50, 'completed', NOW(), NOW()),
(4, 4, 1, '2024-10-14 10:00:00', '2024-10-14 10:28:00', 45, 50, 'completed', NOW(), NOW()),
(5, 4, 1, '2024-10-12 11:00:00', '2024-10-12 11:27:00', 42, 50, 'completed', NOW(), NOW());

-- ============================================
-- 7. WEEKLY GRADES (For Grade Summary)
-- ============================================
INSERT INTO weekly_grades (student_id, week_number, academic_year, assignment_score, quiz_score, status, created_at, updated_at) VALUES
(4, 1, '2024-2025', 95.00, 100.00, 'graded', NOW(), NOW()),
(4, 2, '2024-2025', 88.00, 75.00, 'graded', NOW(), NOW()),
(4, 3, '2024-2025', 92.00, 65.00, 'graded', NOW(), NOW()),
(4, 4, '2024-2025', 87.00, 88.00, 'graded', NOW(), NOW()),
(4, 5, '2024-2025', 90.00, 92.00, 'graded', NOW(), NOW()),
(4, 6, '2024-2025', NULL, NULL, 'pending', NOW(), NOW());

-- ============================================
-- 8. LESSON MATERIALS
-- ============================================
INSERT INTO lesson_materials (class_id, title, description, file_type, file_path, uploaded_by, visible_to_students, created_at, updated_at) VALUES
(1, 'Introduction to Data Mining', 'Overview of data mining concepts and techniques', 'pdf', '/uploads/materials/data_mining_intro.pdf', 1, 1, NOW(), NOW()),
(1, 'Big Data Processing with Hadoop', 'Tutorial on Hadoop ecosystem', 'pdf', '/uploads/materials/hadoop_tutorial.pdf', 1, 1, NOW(), NOW()),
(1, 'Data Visualization Techniques', 'Guide to data visualization', 'pptx', '/uploads/materials/data_viz.pptx', 1, 1, NOW(), NOW()),
(2, 'Capstone Project Guidelines', 'Complete guidelines for capstone project', 'pdf', '/uploads/materials/capstone_guidelines.pdf', 1, 1, NOW(), NOW()),
(3, 'AI and Machine Learning Overview', 'Introduction to AI/ML concepts', 'pdf', '/uploads/materials/ai_ml_overview.pdf', 2, 1, NOW(), NOW()),
(4, 'Full Stack Web Development Guide', 'Complete web dev tutorial', 'pdf', '/uploads/materials/web_dev_guide.pdf', 2, 1, NOW(), NOW()),
(5, 'Database Design Best Practices', 'Guide to effective database design', 'pdf', '/uploads/materials/db_design.pdf', 3, 1, NOW(), NOW());

-- ============================================
-- 9. STUDENT PERFORMANCE METRICS
-- ============================================
INSERT INTO student_performance (student_id, class_id, attendance_rate, assignment_avg, quiz_avg, overall_grade, last_updated) VALUES
(4, 1, 95.00, 91.50, 88.00, 89.75, NOW()),
(4, 2, 98.00, 90.00, 85.00, 87.50, NOW()),
(4, 3, 92.00, 88.00, 90.00, 89.00, NOW()),
(4, 4, 96.00, 92.00, 87.00, 89.50, NOW()),
(4, 5, 94.00, 89.00, 91.00, 90.00, NOW());

-- ============================================
-- 10. ANNOUNCEMENTS/NOTIFICATIONS
-- ============================================
INSERT INTO announcements (class_id, title, message, posted_by, posted_at, expires_at, created_at, updated_at) VALUES
(1, 'Midterm Exam Schedule', 'Midterm exams will be held on October 25-27. Please review all materials.', 1, NOW(), '2024-10-27 23:59:00', NOW(), NOW()),
(2, 'Capstone Proposal Deadline Extended', 'Due to popular request, the deadline has been extended to October 30.', 1, NOW(), '2024-10-30 23:59:00', NOW(), NOW()),
(3, 'Guest Speaker Session', 'Industry expert will speak about emerging tech trends on October 20.', 2, NOW(), '2024-10-20 23:59:00', NOW(), NOW()),
(4, 'Web Dev Workshop', 'Extra hands-on workshop this Saturday at 2 PM.', 2, NOW(), '2024-10-15 23:59:00', NOW(), NOW());

-- ============================================
-- 11. CLASS PROGRESS TRACKING
-- ============================================
INSERT INTO class_progress (student_id, class_id, modules_completed, total_modules, progress_percentage, last_accessed, created_at, updated_at) VALUES
(4, 1, 3, 50, 6.00, NOW(), NOW(), NOW()),
(4, 2, 6, 50, 12.00, NOW(), NOW(), NOW()),
(4, 3, 10, 50, 20.00, NOW(), NOW(), NOW()),
(4, 4, 15, 50, 30.00, NOW(), NOW(), NOW()),
(4, 5, 8, 50, 16.00, NOW(), NOW(), NOW());

-- ============================================
-- NOTES:
-- ============================================
-- 1. Update user_id (currently 4) to match your actual student user ID
-- 2. Update teacher_id values to match actual teacher user IDs
-- 3. Adjust dates as needed for your academic calendar
-- 4. File paths are placeholders - update with actual storage paths
-- 5. Run migrations first before executing this SQL
-- 6. This is sample data - customize as needed for your institution
