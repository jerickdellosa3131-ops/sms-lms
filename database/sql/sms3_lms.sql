-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: sms3_lms
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `academic_history`
--

DROP TABLE IF EXISTS `academic_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_history` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `class_id` int NOT NULL,
  `semester_id` int NOT NULL,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `letter_grade` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade_points` decimal(3,2) DEFAULT NULL,
  `remarks` enum('passed','failed','incomplete','withdrawn') COLLATE utf8mb4_unicode_ci DEFAULT 'passed',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`),
  KEY `semester_id` (`semester_id`),
  CONSTRAINT `academic_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `academic_history_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `academic_history_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`semester_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_history`
--

LOCK TABLES `academic_history` WRITE;
/*!40000 ALTER TABLE `academic_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `academic_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `academic_years`
--

DROP TABLE IF EXISTS `academic_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_years` (
  `academic_year_id` int NOT NULL AUTO_INCREMENT,
  `year_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`academic_year_id`),
  UNIQUE KEY `year_code` (`year_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_years`
--

LOCK TABLES `academic_years` WRITE;
/*!40000 ALTER TABLE `academic_years` DISABLE KEYS */;
INSERT INTO `academic_years` VALUES (1,'2024-2025','2024-08-01','2025-05-31',0,'2025-10-05 17:04:03'),(2,'2025-2026','2025-08-01','2026-05-31',1,'2025-10-05 17:04:03');
/*!40000 ALTER TABLE `academic_years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `activity_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activity_description` text COLLATE utf8mb4_unicode_ci,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` int DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_user_activity` (`user_id`,`created_at`),
  KEY `idx_activity_type` (`activity_type`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,6,'login','User logged in',NULL,NULL,'192.168.1.100',NULL,'2025-10-05 17:04:03'),(2,7,'login','User logged in',NULL,NULL,'192.168.1.101',NULL,'2025-10-05 17:04:03'),(3,3,'material_upload','Uploaded lesson material: Data Structures Overview',NULL,NULL,'192.168.1.50',NULL,'2025-10-05 17:04:03'),(4,6,'assignment_submit','Submitted Assignment 1: Array Implementation',NULL,NULL,'192.168.1.100',NULL,'2025-10-05 17:04:03'),(5,1,'login','User logged in',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-10-05 17:20:22');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `announcement_id` int NOT NULL AUTO_INCREMENT,
  `posted_by` int NOT NULL,
  `user_type` enum('admin','teacher') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `target_audience` enum('all','students','teachers','specific_class') COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_id` int DEFAULT NULL,
  `is_pinned` tinyint(1) DEFAULT '0',
  `published_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`announcement_id`),
  KEY `posted_by` (`posted_by`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,1,'admin','Welcome to AY 2025-2026!','We are excited to welcome all students to the new academic year. Please check your schedules and enroll in your classes.','high','all',NULL,1,'2025-08-01 08:00:00',NULL,'published','2025-10-05 17:04:03','2025-10-05 17:04:03'),(2,3,'teacher','IT301 First Assignment Posted','The first assignment for Data Structures and Algorithms has been posted. Due date is October 15, 2025.','medium','specific_class',NULL,0,'2025-10-01 10:00:00',NULL,'published','2025-10-05 17:04:03','2025-10-05 17:04:03'),(3,1,'admin','Midterm Grades Published','Midterm grades have been published. Please check your grade portal.','high','all',NULL,0,'2025-10-01 14:00:00',NULL,'published','2025-10-05 17:04:03','2025-10-05 17:04:03');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignment_submissions`
--

DROP TABLE IF EXISTS `assignment_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assignment_submissions` (
  `submission_id` int NOT NULL AUTO_INCREMENT,
  `assignment_id` int NOT NULL,
  `student_id` int NOT NULL,
  `submission_text` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_late` tinyint(1) DEFAULT '0',
  `status` enum('submitted','graded','returned') COLLATE utf8mb4_unicode_ci DEFAULT 'submitted',
  `score` decimal(5,2) DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `graded_by` int DEFAULT NULL,
  `graded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`submission_id`),
  UNIQUE KEY `unique_submission` (`assignment_id`,`student_id`),
  KEY `student_id` (`student_id`),
  KEY `graded_by` (`graded_by`),
  CONSTRAINT `assignment_submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`assignment_id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_ibfk_3` FOREIGN KEY (`graded_by`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment_submissions`
--

LOCK TABLES `assignment_submissions` WRITE;
/*!40000 ALTER TABLE `assignment_submissions` DISABLE KEYS */;
INSERT INTO `assignment_submissions` VALUES (1,1,1,'Completed array implementation with all required operations.',NULL,NULL,'2025-10-14 12:30:00',0,'graded',95.00,NULL,NULL,NULL),(2,1,2,'Array implementation completed successfully.',NULL,NULL,'2025-10-15 02:15:00',0,'graded',88.00,NULL,NULL,NULL);
/*!40000 ALTER TABLE `assignment_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assignments` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `module_id` int DEFAULT NULL,
  `teacher_id` int NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `total_points` decimal(5,2) NOT NULL DEFAULT '100.00',
  `due_date` datetime NOT NULL,
  `late_submission_allowed` tinyint(1) DEFAULT '1',
  `late_penalty_percentage` decimal(5,2) DEFAULT '0.00',
  `file_attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  KEY `class_id` (`class_id`),
  KEY `module_id` (`module_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE SET NULL,
  CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignments`
--

LOCK TABLES `assignments` WRITE;
/*!40000 ALTER TABLE `assignments` DISABLE KEYS */;
INSERT INTO `assignments` VALUES (1,1,1,1,'Assignment 1: Array Implementation','Implement basic array operations','Create a program that demonstrates array operations including insertion, deletion, and searching.',100.00,'2025-10-15 23:59:59',1,0.00,NULL,'published','2025-10-05 17:04:03','2025-10-05 17:04:03'),(2,1,2,1,'Assignment 2: Linked List','Create a linked list implementation','Implement a singly linked list with basic operations.',100.00,'2025-10-25 23:59:59',1,0.00,NULL,'published','2025-10-05 17:04:03','2025-10-05 17:04:03'),(3,2,4,2,'React Component Assignment','Build React components','Create reusable React components for a todo application.',100.00,'2025-10-20 23:59:59',1,0.00,NULL,'published','2025-10-05 17:04:03','2025-10-05 17:04:03');
/*!40000 ALTER TABLE `assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `attendance_id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `student_id` int NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','late','excused') COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `unique_attendance` (`class_id`,`student_id`,`attendance_date`),
  KEY `student_id` (`student_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,1,1,'2025-10-01','present',NULL,1,'2025-10-05 17:04:03'),(2,1,2,'2025-10-01','present',NULL,1,'2025-10-05 17:04:03'),(3,1,1,'2025-10-03','present',NULL,1,'2025-10-05 17:04:03'),(4,1,2,'2025-10-03','late',NULL,1,'2025-10-05 17:04:03'),(5,2,1,'2025-10-02','present',NULL,2,'2025-10-05 17:04:03'),(6,2,2,'2025-10-02','present',NULL,2,'2025-10-05 17:04:03');
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_enrollments`
--

DROP TABLE IF EXISTS `class_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_enrollments` (
  `enrollment_id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `student_id` int NOT NULL,
  `enrollment_date` date NOT NULL,
  `status` enum('enrolled','dropped','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'enrolled',
  `final_grade` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`enrollment_id`),
  UNIQUE KEY `unique_enrollment` (`class_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `class_enrollments_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `class_enrollments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_enrollments`
--

LOCK TABLES `class_enrollments` WRITE;
/*!40000 ALTER TABLE `class_enrollments` DISABLE KEYS */;
INSERT INTO `class_enrollments` VALUES (1,1,1,'2025-08-15','enrolled',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(2,1,2,'2025-08-15','enrolled',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(3,2,1,'2025-08-15','enrolled',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(4,2,2,'2025-08-15','enrolled',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(5,3,3,'2025-08-15','enrolled',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(6,4,4,'2025-08-15','enrolled',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(7,5,5,'2025-08-15','enrolled',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03');
/*!40000 ALTER TABLE `class_enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classes` (
  `class_id` int NOT NULL AUTO_INCREMENT,
  `subject_id` int NOT NULL,
  `semester_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `section_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `schedule_days` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `schedule_time_start` time DEFAULT NULL,
  `schedule_time_end` time DEFAULT NULL,
  `room` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_students` int DEFAULT '40',
  `status` enum('active','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`class_id`),
  UNIQUE KEY `class_code` (`class_code`),
  KEY `subject_id` (`subject_id`),
  KEY `semester_id` (`semester_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `idx_class_code` (`class_code`),
  CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`semester_id`) ON DELETE CASCADE,
  CONSTRAINT `classes_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,1,3,1,'BSIT-3A','IT301-3A-2025','MWF','08:00:00','09:30:00','CS-201',40,'active','2025-10-05 17:04:03','2025-10-05 17:04:03'),(2,2,3,2,'BSIT-3A','IT302-3A-2025','TTH','10:00:00','11:30:00','CS-202',40,'active','2025-10-05 17:04:03','2025-10-05 17:04:03'),(3,3,3,1,'BSCS-2B','CS201-2B-2025','MWF','13:00:00','14:30:00','CS-101',35,'active','2025-10-05 17:04:03','2025-10-05 17:04:03'),(4,4,3,3,'BSIT-4A','IT401-4A-2025','TTH','15:00:00','17:00:00','CS-301',30,'active','2025-10-05 17:04:03','2025-10-05 17:04:03'),(5,5,3,2,'BSCS-1A','CS101-1A-2025','MWF','08:00:00','09:30:00','CS-102',45,'active','2025-10-05 17:04:03','2025-10-05 17:04:03');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback_comments`
--

DROP TABLE IF EXISTS `feedback_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback_comments` (
  `comment_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `reference_type` enum('assignment','quiz','material','class','general') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` int DEFAULT NULL,
  `comment_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_comment_id` int DEFAULT NULL,
  `is_private` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  KEY `user_id` (`user_id`),
  KEY `parent_comment_id` (`parent_comment_id`),
  CONSTRAINT `feedback_comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_comments_ibfk_2` FOREIGN KEY (`parent_comment_id`) REFERENCES `feedback_comments` (`comment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback_comments`
--

LOCK TABLES `feedback_comments` WRITE;
/*!40000 ALTER TABLE `feedback_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grade_components`
--

DROP TABLE IF EXISTS `grade_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grade_components` (
  `component_id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `component_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `component_type` enum('assignment','quiz','exam','project','participation','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `weight_percentage` decimal(5,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`component_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `grade_components_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grade_components`
--

LOCK TABLES `grade_components` WRITE;
/*!40000 ALTER TABLE `grade_components` DISABLE KEYS */;
INSERT INTO `grade_components` VALUES (1,1,'Assignments','assignment',30.00,'All programming assignments','2025-10-05 17:04:03','2025-10-05 17:04:03'),(2,1,'Quizzes','quiz',20.00,'Regular quizzes','2025-10-05 17:04:03','2025-10-05 17:04:03'),(3,1,'Midterm Exam','exam',25.00,'Midterm examination','2025-10-05 17:04:03','2025-10-05 17:04:03'),(4,1,'Final Exam','exam',25.00,'Final examination','2025-10-05 17:04:03','2025-10-05 17:04:03'),(5,2,'Assignments','assignment',35.00,'All programming assignments','2025-10-05 17:04:03','2025-10-05 17:04:03'),(6,2,'Quizzes','quiz',15.00,'Regular quizzes','2025-10-05 17:04:03','2025-10-05 17:04:03'),(7,2,'Project','project',30.00,'Final project','2025-10-05 17:04:03','2025-10-05 17:04:03'),(8,2,'Final Exam','exam',20.00,'Final examination','2025-10-05 17:04:03','2025-10-05 17:04:03');
/*!40000 ALTER TABLE `grade_components` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grades`
--

DROP TABLE IF EXISTS `grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grades` (
  `grade_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `class_id` int NOT NULL,
  `component_id` int NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `max_score` decimal(5,2) DEFAULT NULL,
  `percentage` decimal(5,2) DEFAULT NULL,
  `weighted_score` decimal(5,2) DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `graded_by` int DEFAULT NULL,
  `graded_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`grade_id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`),
  KEY `component_id` (`component_id`),
  KEY `graded_by` (`graded_by`),
  CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`component_id`) REFERENCES `grade_components` (`component_id`) ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_4` FOREIGN KEY (`graded_by`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grades`
--

LOCK TABLES `grades` WRITE;
/*!40000 ALTER TABLE `grades` DISABLE KEYS */;
INSERT INTO `grades` VALUES (1,1,1,1,95.00,100.00,95.00,28.50,NULL,1,'2025-10-14 21:00:00','2025-10-05 17:04:03','2025-10-05 17:04:03'),(2,2,1,1,88.00,100.00,88.00,26.40,NULL,1,'2025-10-15 11:00:00','2025-10-05 17:04:03','2025-10-05 17:04:03');
/*!40000 ALTER TABLE `grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lesson_materials`
--

DROP TABLE IF EXISTS `lesson_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_materials` (
  `material_id` int NOT NULL AUTO_INCREMENT,
  `module_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `material_title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `material_description` text COLLATE utf8mb4_unicode_ci,
  `material_type` enum('pdf','doc','video','link','image','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint DEFAULT NULL,
  `external_link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `material_order` int DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`material_id`),
  KEY `module_id` (`module_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `lesson_materials_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_materials_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_materials`
--

LOCK TABLES `lesson_materials` WRITE;
/*!40000 ALTER TABLE `lesson_materials` DISABLE KEYS */;
INSERT INTO `lesson_materials` VALUES (1,1,1,'Data Structures Overview','Introduction slides','pdf',NULL,NULL,NULL,1,1,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(2,1,1,'Video Lecture: DS Basics','Video introduction to data structures','video',NULL,NULL,NULL,2,1,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(3,2,1,'Arrays Tutorial','Complete guide to arrays','pdf',NULL,NULL,NULL,1,1,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(4,4,2,'React Getting Started','React setup guide','pdf',NULL,NULL,NULL,1,1,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(5,6,1,'OOP Concepts','Object-oriented programming fundamentals','pdf',NULL,NULL,NULL,1,1,'2025-10-05 17:04:03','2025-10-05 17:04:03');
/*!40000 ALTER TABLE `lesson_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lms_analytics`
--

DROP TABLE IF EXISTS `lms_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lms_analytics` (
  `analytics_id` int NOT NULL AUTO_INCREMENT,
  `class_id` int DEFAULT NULL,
  `student_id` int DEFAULT NULL,
  `metric_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metric_value` decimal(10,2) DEFAULT NULL,
  `metric_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`analytics_id`),
  KEY `student_id` (`student_id`),
  KEY `idx_analytics` (`class_id`,`metric_date`),
  CONSTRAINT `lms_analytics_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `lms_analytics_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lms_analytics`
--

LOCK TABLES `lms_analytics` WRITE;
/*!40000 ALTER TABLE `lms_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `lms_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_completions`
--

DROP TABLE IF EXISTS `module_completions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_completions` (
  `completion_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `module_id` int NOT NULL,
  `material_id` int DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completion_percentage` decimal(5,2) DEFAULT '0.00',
  `time_spent` int DEFAULT NULL COMMENT 'Time spent in minutes',
  PRIMARY KEY (`completion_id`),
  UNIQUE KEY `unique_completion` (`student_id`,`material_id`),
  KEY `module_id` (`module_id`),
  KEY `material_id` (`material_id`),
  CONSTRAINT `module_completions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `module_completions_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE,
  CONSTRAINT `module_completions_ibfk_3` FOREIGN KEY (`material_id`) REFERENCES `lesson_materials` (`material_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_completions`
--

LOCK TABLES `module_completions` WRITE;
/*!40000 ALTER TABLE `module_completions` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_completions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modules` (
  `module_id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `module_title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_description` text COLLATE utf8mb4_unicode_ci,
  `module_order` int NOT NULL,
  `is_published` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`module_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,1,'Introduction to Data Structures','Basic concepts of data structures',1,1,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(2,1,'Arrays and Linked Lists','Linear data structures',2,1,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(3,1,'Stacks and Queues','Stack and queue implementation',3,1,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(4,2,'Introduction to React','Getting started with React framework',1,1,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(5,2,'React Components','Building reusable components',2,1,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(6,3,'OOP Fundamentals','Introduction to object-oriented programming',1,1,'2025-10-05 17:04:03','2025-10-05 17:04:03');
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `multimedia_files`
--

DROP TABLE IF EXISTS `multimedia_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `multimedia_files` (
  `multimedia_id` int NOT NULL AUTO_INCREMENT,
  `material_id` int NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint DEFAULT NULL,
  `duration` int DEFAULT NULL COMMENT 'Duration in seconds for video/audio',
  `thumbnail_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`multimedia_id`),
  KEY `material_id` (`material_id`),
  CONSTRAINT `multimedia_files_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `lesson_materials` (`material_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `multimedia_files`
--

LOCK TABLES `multimedia_files` WRITE;
/*!40000 ALTER TABLE `multimedia_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `multimedia_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `notification_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` int DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `idx_user_notifications` (`user_id`,`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,6,'assignment','New Assignment Posted','A new assignment has been posted in IT301','assignment',1,0,NULL,'2025-10-05 17:04:03'),(2,6,'grade','Assignment Graded','Your assignment has been graded. Score: 95/100','assignment',1,0,NULL,'2025-10-05 17:04:03'),(3,7,'assignment','New Assignment Posted','A new assignment has been posted in IT301','assignment',1,0,NULL,'2025-10-05 17:04:03'),(4,7,'grade','Assignment Graded','Your assignment has been graded. Score: 88/100','assignment',1,0,NULL,'2025-10-05 17:04:03');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_info`
--

DROP TABLE IF EXISTS `personal_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_info` (
  `info_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_province` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Philippines',
  `emergency_contact_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_relationship` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`info_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `personal_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_info`
--

LOCK TABLES `personal_info` WRITE;
/*!40000 ALTER TABLE `personal_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quiz_answers`
--

DROP TABLE IF EXISTS `quiz_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_answers` (
  `answer_id` int NOT NULL AUTO_INCREMENT,
  `attempt_id` int NOT NULL,
  `question_id` int NOT NULL,
  `selected_option_id` int DEFAULT NULL,
  `answer_text` text COLLATE utf8mb4_unicode_ci,
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_earned` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`answer_id`),
  KEY `attempt_id` (`attempt_id`),
  KEY `question_id` (`question_id`),
  KEY `selected_option_id` (`selected_option_id`),
  CONSTRAINT `quiz_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`attempt_id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`question_id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_answers_ibfk_3` FOREIGN KEY (`selected_option_id`) REFERENCES `quiz_question_options` (`option_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quiz_answers`
--

LOCK TABLES `quiz_answers` WRITE;
/*!40000 ALTER TABLE `quiz_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `quiz_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quiz_attempts`
--

DROP TABLE IF EXISTS `quiz_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_attempts` (
  `attempt_id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `student_id` int NOT NULL,
  `attempt_number` int NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `time_spent` int DEFAULT NULL COMMENT 'Time spent in minutes',
  `score` decimal(5,2) DEFAULT NULL,
  `status` enum('in_progress','submitted','graded') COLLATE utf8mb4_unicode_ci DEFAULT 'in_progress',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attempt_id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quiz_attempts`
--

LOCK TABLES `quiz_attempts` WRITE;
/*!40000 ALTER TABLE `quiz_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `quiz_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quiz_question_options`
--

DROP TABLE IF EXISTS `quiz_question_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_question_options` (
  `option_id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `option_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` tinyint(1) DEFAULT '0',
  `option_order` int DEFAULT NULL,
  PRIMARY KEY (`option_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `quiz_question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quiz_question_options`
--

LOCK TABLES `quiz_question_options` WRITE;
/*!40000 ALTER TABLE `quiz_question_options` DISABLE KEYS */;
/*!40000 ALTER TABLE `quiz_question_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quiz_questions`
--

DROP TABLE IF EXISTS `quiz_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_questions` (
  `question_id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer','essay') COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_order` int DEFAULT NULL,
  `points` decimal(5,2) NOT NULL DEFAULT '1.00',
  `correct_answer` text COLLATE utf8mb4_unicode_ci,
  `explanation` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`question_id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quiz_questions`
--

LOCK TABLES `quiz_questions` WRITE;
/*!40000 ALTER TABLE `quiz_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `quiz_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quizzes`
--

DROP TABLE IF EXISTS `quizzes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quizzes` (
  `quiz_id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `module_id` int DEFAULT NULL,
  `teacher_id` int NOT NULL,
  `quiz_title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quiz_description` text COLLATE utf8mb4_unicode_ci,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `total_points` decimal(5,2) NOT NULL DEFAULT '100.00',
  `passing_score` decimal(5,2) DEFAULT '60.00',
  `time_limit` int DEFAULT NULL COMMENT 'Time limit in minutes',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `attempts_allowed` int DEFAULT '1',
  `show_correct_answers` tinyint(1) DEFAULT '0',
  `shuffle_questions` tinyint(1) DEFAULT '0',
  `status` enum('draft','published','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`quiz_id`),
  KEY `class_id` (`class_id`),
  KEY `module_id` (`module_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `quizzes_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE SET NULL,
  CONSTRAINT `quizzes_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quizzes`
--

LOCK TABLES `quizzes` WRITE;
/*!40000 ALTER TABLE `quizzes` DISABLE KEYS */;
INSERT INTO `quizzes` VALUES (1,1,1,1,'Quiz 1: Data Structures Basics','Test your knowledge of basic data structures',NULL,50.00,35.00,30,'2025-10-01 00:00:00','2025-10-08 23:59:59',2,0,0,'published','2025-10-05 17:04:03','2025-10-05 17:04:03'),(2,2,4,2,'React Fundamentals Quiz','Quiz on React basics',NULL,50.00,35.00,45,'2025-10-05 00:00:00','2025-10-12 23:59:59',2,0,0,'published','2025-10-05 17:04:03','2025-10-05 17:04:03');
/*!40000 ALTER TABLE `quizzes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `semesters`
--

DROP TABLE IF EXISTS `semesters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `semesters` (
  `semester_id` int NOT NULL AUTO_INCREMENT,
  `academic_year_id` int NOT NULL,
  `semester_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`semester_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `semesters_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`academic_year_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semesters`
--

LOCK TABLES `semesters` WRITE;
/*!40000 ALTER TABLE `semesters` DISABLE KEYS */;
INSERT INTO `semesters` VALUES (1,1,'First Semester','1ST-2024','2024-08-01','2024-12-31',0,'2025-10-05 17:04:03'),(2,1,'Second Semester','2ND-2024','2025-01-01','2025-05-31',0,'2025-10-05 17:04:03'),(3,2,'First Semester','1ST-2025','2025-08-01','2025-12-31',1,'2025-10-05 17:04:03'),(4,2,'Second Semester','2ND-2025','2026-01-01','2026-05-31',0,'2025-10-05 17:04:03');
/*!40000 ALTER TABLE `semesters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `student_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `student_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enrollment_year` year NOT NULL,
  `current_year_level` enum('1','2','3','4') COLLATE utf8mb4_unicode_ci NOT NULL,
  `program` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enrollment_status` enum('enrolled','graduated','dropped','on_leave') COLLATE utf8mb4_unicode_ci DEFAULT 'enrolled',
  `gpa` decimal(3,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `student_number` (`student_number`),
  KEY `user_id` (`user_id`),
  KEY `idx_student_number` (`student_number`),
  KEY `idx_enrollment_status` (`enrollment_status`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,6,'S2025001',2025,'3','BS Information Technology','BSIT-3A','enrolled',3.25,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(2,7,'S2025002',2025,'3','BS Information Technology','BSIT-3A','enrolled',3.75,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(3,8,'S2025003',2025,'2','BS Computer Science','BSCS-2B','enrolled',3.50,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(4,9,'S2025004',2025,'4','BS Information Technology','BSIT-4A','enrolled',3.90,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(5,10,'S2025005',2025,'1','BS Computer Science','BSCS-1A','enrolled',3.00,'2025-10-05 17:04:03','2025-10-05 17:04:03');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subjects` (
  `subject_id` int NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `units` int NOT NULL DEFAULT '3',
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year_level` enum('1','2','3','4') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prerequisite_subject_id` int DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`subject_id`),
  UNIQUE KEY `subject_code` (`subject_code`),
  KEY `prerequisite_subject_id` (`prerequisite_subject_id`),
  KEY `idx_subject_code` (`subject_code`),
  CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`prerequisite_subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,'IT301','Data Structures and Algorithms','Study of data structures and algorithm design',3,'Computer Science','3',NULL,'active','2025-10-05 17:04:03','2025-10-05 17:04:03'),(2,'IT302','Web Development 2','Advanced web development with modern frameworks',3,'Computer Science','3',NULL,'active','2025-10-05 17:04:03','2025-10-05 17:04:03'),(3,'CS201','Object-Oriented Programming','Introduction to OOP concepts and implementation',3,'Computer Science','2',NULL,'active','2025-10-05 17:04:03','2025-10-05 17:04:03'),(4,'IT401','Capstone Project 1','First part of capstone project',3,'Information Technology','4',NULL,'active','2025-10-05 17:04:03','2025-10-05 17:04:03'),(5,'CS101','Introduction to Programming','Basic programming concepts',3,'Computer Science','1',NULL,'active','2025-10-05 17:04:03','2025-10-05 17:04:03');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'system_name','SMS3 Learning Management System','string','System name','2025-10-05 16:46:39'),(2,'institution_name','School Management System III','string','Institution name','2025-10-05 16:46:39'),(3,'academic_year','2025-2026','string','Current academic year','2025-10-05 16:46:39'),(4,'semester','First Semester','string','Current semester','2025-10-05 16:46:39'),(5,'timezone','Asia/Manila','string','System timezone','2025-10-05 16:46:39'),(6,'date_format','Y-m-d','string','Date format','2025-10-05 16:46:39'),(7,'time_format','H:i:s','string','Time format','2025-10-05 16:46:39'),(8,'max_file_upload_size','10485760','integer','Max file upload size in bytes (10MB)','2025-10-05 16:46:39'),(9,'allowed_file_types','pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,mp4,mp3','string','Allowed file types for upload','2025-10-05 16:46:39'),(10,'late_submission_penalty','10','integer','Late submission penalty percentage','2025-10-05 16:46:39'),(11,'passing_grade','60','integer','Default passing grade percentage','2025-10-05 16:46:39');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teachers` (
  `teacher_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `employee_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialization` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `employment_status` enum('full-time','part-time','contract') COLLATE utf8mb4_unicode_ci DEFAULT 'full-time',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`teacher_id`),
  UNIQUE KEY `employee_number` (`employee_number`),
  KEY `user_id` (`user_id`),
  KEY `idx_employee_number` (`employee_number`),
  CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teachers`
--

LOCK TABLES `teachers` WRITE;
/*!40000 ALTER TABLE `teachers` DISABLE KEYS */;
INSERT INTO `teachers` VALUES (1,3,'T2024001','Computer Science','Associate Professor','Data Science','2024-01-15','full-time','2025-10-05 17:04:03','2025-10-05 17:04:03'),(2,4,'T2024002','Computer Science','Assistant Professor','Web Development','2024-03-20','full-time','2025-10-05 17:04:03','2025-10-05 17:04:03'),(3,5,'T2023001','Information Technology','Professor','Network Security','2023-06-10','full-time','2025-10-05 17:04:03','2025-10-05 17:04:03');
/*!40000 ALTER TABLE `teachers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` enum('admin','teacher','student') COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_user_type` (`user_type`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@sms3.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','System','Administrator',NULL,NULL,'active','2025-10-06 01:20:22','2025-10-05 17:04:03','2025-10-05 17:20:22'),(2,'admin2','admin2@sms3.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','Maria','Santos',NULL,NULL,'active',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(3,'teacher1','jreyes@sms3.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','teacher','Juan','Reyes','Dela Cruz',NULL,'active',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(4,'teacher2','mcruz@sms3.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','teacher','Maria','Cruz','Garcia',NULL,'active',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(5,'teacher3','plopez@sms3.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','teacher','Pedro','Lopez','Santos',NULL,'active',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(6,'student1','s2025001@sms3.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','student','John','Doe','Smith',NULL,'active',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(7,'student2','s2025002@sms3.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','student','Jane','Smith','Anne',NULL,'active',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(8,'student3','s2025003@sms3.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','student','Carlos','Mendoza','Jose',NULL,'active',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(9,'student4','s2025004@sms3.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','student','Ana','Rivera','Marie',NULL,'active',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(10,'student5','s2025005@sms3.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','student','Miguel','Torres','Luis',NULL,'active',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(11,'marie','marie@gmail.com','$2y$10$A/SP.8TnLdv5Mbl09mTWau1OboLNL9faXmVXa5i9ovSv7mXHcTXke','admin','marie','deguzman',NULL,NULL,'active',NULL,'2025-10-06 01:34:20','2025-10-06 01:34:20');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `view_class_overview`
--

DROP TABLE IF EXISTS `view_class_overview`;
/*!50001 DROP VIEW IF EXISTS `view_class_overview`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_class_overview` AS SELECT 
 1 AS `class_id`,
 1 AS `class_code`,
 1 AS `section_name`,
 1 AS `subject_code`,
 1 AS `subject_name`,
 1 AS `teacher_name`,
 1 AS `semester_name`,
 1 AS `enrolled_students`,
 1 AS `total_assignments`,
 1 AS `total_quizzes`,
 1 AS `total_materials`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_student_dashboard`
--

DROP TABLE IF EXISTS `view_student_dashboard`;
/*!50001 DROP VIEW IF EXISTS `view_student_dashboard`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_student_dashboard` AS SELECT 
 1 AS `student_id`,
 1 AS `first_name`,
 1 AS `last_name`,
 1 AS `student_number`,
 1 AS `current_year_level`,
 1 AS `program`,
 1 AS `gpa`,
 1 AS `enrolled_classes`,
 1 AS `average_grade`,
 1 AS `upcoming_assignments`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_teacher_dashboard`
--

DROP TABLE IF EXISTS `view_teacher_dashboard`;
/*!50001 DROP VIEW IF EXISTS `view_teacher_dashboard`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_teacher_dashboard` AS SELECT 
 1 AS `teacher_id`,
 1 AS `first_name`,
 1 AS `last_name`,
 1 AS `employee_number`,
 1 AS `department`,
 1 AS `total_classes`,
 1 AS `total_students`,
 1 AS `total_assignments`,
 1 AS `uploaded_materials`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `virtual_class_attendance`
--

DROP TABLE IF EXISTS `virtual_class_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `virtual_class_attendance` (
  `vc_attendance_id` int NOT NULL AUTO_INCREMENT,
  `virtual_class_id` int NOT NULL,
  `student_id` int NOT NULL,
  `join_time` datetime DEFAULT NULL,
  `leave_time` datetime DEFAULT NULL,
  `duration_minutes` int DEFAULT NULL,
  `status` enum('attended','absent') COLLATE utf8mb4_unicode_ci DEFAULT 'attended',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`vc_attendance_id`),
  KEY `virtual_class_id` (`virtual_class_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `virtual_class_attendance_ibfk_1` FOREIGN KEY (`virtual_class_id`) REFERENCES `virtual_class_links` (`virtual_class_id`) ON DELETE CASCADE,
  CONSTRAINT `virtual_class_attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `virtual_class_attendance`
--

LOCK TABLES `virtual_class_attendance` WRITE;
/*!40000 ALTER TABLE `virtual_class_attendance` DISABLE KEYS */;
/*!40000 ALTER TABLE `virtual_class_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `virtual_class_links`
--

DROP TABLE IF EXISTS `virtual_class_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `virtual_class_links` (
  `virtual_class_id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `meeting_title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meeting_platform` enum('zoom','google_meet','microsoft_teams','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `meeting_link` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meeting_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passcode` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_date` datetime NOT NULL,
  `duration` int DEFAULT NULL COMMENT 'Duration in minutes',
  `status` enum('scheduled','ongoing','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'scheduled',
  `recording_link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`virtual_class_id`),
  KEY `class_id` (`class_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `virtual_class_links_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `virtual_class_links_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `virtual_class_links`
--

LOCK TABLES `virtual_class_links` WRITE;
/*!40000 ALTER TABLE `virtual_class_links` DISABLE KEYS */;
INSERT INTO `virtual_class_links` VALUES (1,1,1,'IT301 Week 5 Lecture','zoom','https://zoom.us/j/123456789','123456789','pass123','2025-10-08 08:00:00',90,'scheduled',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03'),(2,2,2,'IT302 React Workshop','google_meet','https://meet.google.com/abc-defg-hij',NULL,NULL,'2025-10-09 10:00:00',120,'scheduled',NULL,'2025-10-05 17:04:03','2025-10-05 17:04:03');
/*!40000 ALTER TABLE `virtual_class_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `view_class_overview`
--

/*!50001 DROP VIEW IF EXISTS `view_class_overview`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_class_overview` AS select `c`.`class_id` AS `class_id`,`c`.`class_code` AS `class_code`,`c`.`section_name` AS `section_name`,`s`.`subject_code` AS `subject_code`,`s`.`subject_name` AS `subject_name`,concat(`u`.`first_name`,' ',`u`.`last_name`) AS `teacher_name`,`sem`.`semester_name` AS `semester_name`,count(distinct `ce`.`student_id`) AS `enrolled_students`,count(distinct `a`.`assignment_id`) AS `total_assignments`,count(distinct `q`.`quiz_id`) AS `total_quizzes`,count(distinct `lm`.`material_id`) AS `total_materials` from (((((((((`classes` `c` join `subjects` `s` on((`c`.`subject_id` = `s`.`subject_id`))) join `teachers` `t` on((`c`.`teacher_id` = `t`.`teacher_id`))) join `users` `u` on((`t`.`user_id` = `u`.`user_id`))) join `semesters` `sem` on((`c`.`semester_id` = `sem`.`semester_id`))) left join `class_enrollments` `ce` on(((`c`.`class_id` = `ce`.`class_id`) and (`ce`.`status` = 'enrolled')))) left join `assignments` `a` on((`c`.`class_id` = `a`.`class_id`))) left join `quizzes` `q` on((`c`.`class_id` = `q`.`class_id`))) left join `modules` `m` on((`c`.`class_id` = `m`.`class_id`))) left join `lesson_materials` `lm` on((`m`.`module_id` = `lm`.`module_id`))) group by `c`.`class_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_student_dashboard`
--

/*!50001 DROP VIEW IF EXISTS `view_student_dashboard`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_student_dashboard` AS select `s`.`student_id` AS `student_id`,`u`.`first_name` AS `first_name`,`u`.`last_name` AS `last_name`,`s`.`student_number` AS `student_number`,`s`.`current_year_level` AS `current_year_level`,`s`.`program` AS `program`,`s`.`gpa` AS `gpa`,count(distinct `ce`.`class_id`) AS `enrolled_classes`,avg(`ah`.`final_grade`) AS `average_grade`,(select count(0) from ((`assignments` `a` join `classes` `c` on((`a`.`class_id` = `c`.`class_id`))) join `class_enrollments` `ce2` on((`c`.`class_id` = `ce2`.`class_id`))) where ((`ce2`.`student_id` = `s`.`student_id`) and (`a`.`due_date` > now()) and (`a`.`status` = 'published'))) AS `upcoming_assignments` from (((`students` `s` join `users` `u` on((`s`.`user_id` = `u`.`user_id`))) left join `class_enrollments` `ce` on(((`s`.`student_id` = `ce`.`student_id`) and (`ce`.`status` = 'enrolled')))) left join `academic_history` `ah` on((`s`.`student_id` = `ah`.`student_id`))) group by `s`.`student_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_teacher_dashboard`
--

/*!50001 DROP VIEW IF EXISTS `view_teacher_dashboard`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_teacher_dashboard` AS select `t`.`teacher_id` AS `teacher_id`,`u`.`first_name` AS `first_name`,`u`.`last_name` AS `last_name`,`t`.`employee_number` AS `employee_number`,`t`.`department` AS `department`,count(distinct `c`.`class_id`) AS `total_classes`,count(distinct `ce`.`student_id`) AS `total_students`,(select count(0) from `assignments` `a` where ((`a`.`teacher_id` = `t`.`teacher_id`) and (`a`.`status` = 'published'))) AS `total_assignments`,(select count(0) from `lesson_materials` `lm` where (`lm`.`teacher_id` = `t`.`teacher_id`)) AS `uploaded_materials` from (((`teachers` `t` join `users` `u` on((`t`.`user_id` = `u`.`user_id`))) left join `classes` `c` on(((`t`.`teacher_id` = `c`.`teacher_id`) and (`c`.`status` = 'active')))) left join `class_enrollments` `ce` on(((`c`.`class_id` = `ce`.`class_id`) and (`ce`.`status` = 'enrolled')))) group by `t`.`teacher_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-06 18:09:32
