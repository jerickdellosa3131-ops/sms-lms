<?php

/**
 * Global Helper Functions for SMS3 LMS
 * Migrated from old config.php
 */

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

// User Type Constants
define('USER_TYPE_ADMIN', 'admin');
define('USER_TYPE_TEACHER', 'teacher');
define('USER_TYPE_STUDENT', 'student');

// Date Format Constants
define('DISPLAY_DATE_FORMAT', 'F d, Y');
define('DISPLAY_DATETIME_FORMAT', 'F d, Y h:i A');
define('DB_DATE_FORMAT', 'Y-m-d');
define('DB_DATETIME_FORMAT', 'Y-m-d H:i:s');

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize_input($value);
        }
        return $data;
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return Auth::check();
}

/**
 * Require login (redirect if not authenticated)
 */
function require_login() {
    if (!is_logged_in()) {
        return redirect()->route('login');
    }
}

/**
 * Check user type
 */
function check_user_type($allowed_types) {
    if (!is_logged_in()) {
        return redirect()->route('login');
    }
    
    $user_type = Auth::user()->user_type;
    
    if (!in_array($user_type, (array)$allowed_types)) {
        abort(403, "Access denied. You don't have permission to access this page.");
    }
}

/**
 * Format date
 */
function format_date($date, $format = DISPLAY_DATE_FORMAT) {
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function format_datetime($datetime, $format = DISPLAY_DATETIME_FORMAT) {
    if (!$datetime) return '';
    return date($format, strtotime($datetime));
}

/**
 * Get user initials
 */
function get_user_initials($first_name, $last_name) {
    return strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
}

/**
 * Generate random string
 */
function generate_random_string($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Get file extension
 */
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file type is allowed
 */
function is_allowed_file_type($filename, $allowed_types) {
    $extension = get_file_extension($filename);
    return in_array($extension, $allowed_types);
}

/**
 * Format file size
 */
function format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Time ago function
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    $periods = array("second", "minute", "hour", "day", "week", "month", "year");
    $lengths = array("60","60","24","7","4.35","12");
    
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }
    
    $difference = round($difference);
    
    if($difference != 1) {
        $periods[$j].= "s";
    }
    
    return "$difference $periods[$j] ago";
}

/**
 * Generate breadcrumb
 */
function generate_breadcrumb($items) {
    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    foreach ($items as $key => $value) {
        if ($key === array_key_last($items)) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . $value . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . $key . '">' . $value . '</a></li>';
        }
    }
    
    $html .= '</ol></nav>';
    return $html;
}

/**
 * Calculate GPA from grades
 */
function calculate_gpa($grades) {
    if (empty($grades)) return 0;
    
    $total_points = 0;
    $total_units = 0;
    
    foreach ($grades as $grade) {
        $total_points += $grade['grade_point'] * $grade['units'];
        $total_units += $grade['units'];
    }
    
    return $total_units > 0 ? round($total_points / $total_units, 2) : 0;
}

/**
 * Get grade point from letter grade
 */
function get_grade_point($letter_grade) {
    $grade_points = [
        'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
        'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
        'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
        'D' => 1.0, 'F' => 0.0, 'INC' => 0.0
    ];
    
    return $grade_points[$letter_grade] ?? 0;
}

/**
 * Get status badge HTML
 */
function status_badge($status) {
    $badges = [
        'active' => '<span class="badge bg-success">Active</span>',
        'inactive' => '<span class="badge bg-secondary">Inactive</span>',
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'suspended' => '<span class="badge bg-danger">Suspended</span>',
        'enrolled' => '<span class="badge bg-primary">Enrolled</span>',
        'completed' => '<span class="badge bg-success">Completed</span>',
        'dropped' => '<span class="badge bg-danger">Dropped</span>',
    ];
    
    return $badges[strtolower($status)] ?? '<span class="badge bg-secondary">' . $status . '</span>';
}
