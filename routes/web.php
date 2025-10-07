<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes - SMS3 LMS
|--------------------------------------------------------------------------
*/

// Home Page
Route::get('/', function () {
    // Check if user is already logged in
    if (Auth::check()) {
        $user = Auth::user();
        return match($user->user_type) {
            'admin' => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect()->route('login'),
        };
    }
    
    // Redirect to login for now
    // You can create a proper home view later
    return redirect()->route('login');
})->name('home');

// Login Routes
Route::get('/login', function () {
    return view('login.Log_in');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = [
        'username' => $request->username,
        'password' => $request->password,
    ];

    // Custom authentication using password_hash field
    $user = User::where('username', $credentials['username'])->first();
    
    if ($user && password_verify($credentials['password'], $user->password_hash)) {
        Auth::login($user);
        
        // Update last login
        $user->update(['last_login' => now()]);
        
        // Redirect based on user type
        return match($user->user_type) {
            'admin' => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect()->route('home'),
        };
    }
    
    return redirect()->route('login')->with('error', 'Invalid credentials');
})->name('login.post');

// Logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('admin.Dashboard.dashboard');
    })->name('dashboard');
    
    // User Management
    Route::get('/manage-users', function () {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('admin.Dashboard.Manageuser');
    })->name('manage-users');
    
    // Create User (AJAX)
    Route::post('/users/store', function (Request $request) {
        if (!Auth::user()->isAdmin()) abort(403);
        
        try {
            // Validate
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'user_type' => 'required|in:student,teacher,admin',
                'password' => 'required|string|min:6',
                'phone' => 'nullable|string|max:20',
                'user_number' => 'nullable|string|max:50'
            ]);
            
            DB::beginTransaction();
            
            // Generate username from email (before @ symbol)
            $username = explode('@', $request->email)[0];
            
            // Make sure username is unique
            $baseUsername = $username;
            $counter = 1;
            while (DB::table('users')->where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }
            
            // Insert into users table
            $userId = DB::table('users')->insertGetId([
                'username' => $username,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password_hash' => password_hash($request->password, PASSWORD_DEFAULT),
                'user_type' => $request->user_type,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Insert into type-specific table
            if ($request->user_type === 'student') {
                DB::table('students')->insert([
                    'user_id' => $userId,
                    'student_number' => $request->user_number ?? 'S' . date('Y') . str_pad($userId, 3, '0', STR_PAD_LEFT),
                    'enrollment_year' => date('Y'),
                    'current_year_level' => '1',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } elseif ($request->user_type === 'teacher') {
                DB::table('teachers')->insert([
                    'user_id' => $userId,
                    'employee_number' => $request->user_number ?? 'T' . date('Y') . str_pad($userId, 3, '0', STR_PAD_LEFT),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $userId
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    })->name('users.store');
    
    // Create Quiz (AJAX)
    Route::post('/quizzes/store', function (Request $request) {
        if (!Auth::user()->isAdmin()) abort(403);
        
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'section_id' => 'required|integer',
                'duration' => 'nullable|integer',
                'total_points' => 'nullable|integer',
                'deadline' => 'nullable|date'
            ]);
            
            $quizId = DB::table('quizzes')->insertGetId([
                'quiz_title' => $request->title,
                'section_id' => $request->section_id,
                'duration_minutes' => $request->duration ?? 30,
                'total_points' => $request->total_points ?? 100,
                'deadline' => $request->deadline,
                'created_by' => Auth::id(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Quiz created successfully',
                'quiz_id' => $quizId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create quiz: ' . $e->getMessage()
            ], 500);
        }
    })->name('quizzes.store');
    
    // Create Assignment (AJAX)
    Route::post('/assignments/store', function (Request $request) {
        if (!Auth::user()->isAdmin()) abort(403);
        
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'class_id' => 'required|integer',
                'description' => 'nullable|string',
                'due_date' => 'required|date',
                'max_points' => 'nullable|integer'
            ]);
            
            $assignmentId = DB::table('assignments')->insertGetId([
                'title' => $request->title,
                'class_id' => $request->class_id,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'max_points' => $request->max_points ?? 100,
                'created_by' => Auth::id(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Assignment created successfully',
                'assignment_id' => $assignmentId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create assignment: ' . $e->getMessage()
            ], 500);
        }
    })->name('assignments.store');
    
    // Upload Material (AJAX with file)
    Route::post('/materials/upload', function (Request $request) {
        if (!Auth::user()->isAdmin()) abort(403);
        
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'class_id' => 'required|integer',
                'material_type' => 'required|string',
                'file' => 'nullable|file|max:10240' // 10MB max
            ]);
            
            $filePath = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('materials', $fileName, 'public');
            }
            
            $materialId = DB::table('lesson_materials')->insertGetId([
                'material_title' => $request->title,
                'module_id' => $request->class_id,
                'material_type' => $request->material_type,
                'file_path' => $filePath,
                'teacher_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Material uploaded successfully',
                'material_id' => $materialId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload material: ' . $e->getMessage()
            ], 500);
        }
    })->name('materials.upload');
    
    // Class Portal
    Route::get('/class-portal', function () {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('admin.ClassPortal.index');
    })->name('class-portal');
    
    // Create New Class (Form)
    Route::get('/class-portal/create', function () {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('admin.ClassPortal.create');
    })->name('class-portal.create');
    
    // Store New Class (Form Submission)
    Route::post('/class-portal/store', function (Request $request) {
        if (!Auth::user()->isAdmin()) abort(403);
        
        // Validate the form data
        $validated = $request->validate([
            'class_name' => 'required|string|max:255',
            'class_code' => 'required|string|max:50',
            'subject' => 'required|string',
            'school_year' => 'required|string',
        ]);
        
        // For now, just redirect with success message
        // In production, you would save to database here
        return redirect()->route('admin.class-portal')->with('success', 'Class "' . $request->class_name . '" created successfully!');
    })->name('class-portal.store');
    
    // Analytics
    Route::get('/analytics', function () {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('admin.viewanalytics.viewanaly');
    })->name('analytics');
    
    // Assignments
    Route::get('/assignments', function () {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('admin.AssignmentSubmission.assignsubmission');
    })->name('assignments');
    
    // Quizzes
    Route::get('/quizzes', function () {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('admin.OnlineQuizzes.onlinequizzes');
    })->name('quizzes');
    
    // Grading
    Route::get('/grading', function () {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('admin.GradingIntegration.gradinginteg');
    })->name('grading');
    
    // Feedback & Comments
    Route::get('/feedback', function () {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('admin.FeedbackandComments.feedbackandcomments');
    })->name('feedback');
    
    // Module Completion Tracking
    Route::get('/module-completion', function () {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('admin.ModuleCompletionTracking.modulecompletiontracking');
    })->name('module-completion');
    
    // Lesson Material Upload
    Route::get('/lesson-materials', function () {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('admin.LessonMaterialUpload.lessonmaterialupload');
    })->name('lesson-materials');
    
    // Virtual Class Integration
    Route::get('/virtual-class', function () {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('admin.VirtualClassLinkInteg.virtualclasslinkinteg');
    })->name('virtual-class');
    
    // Create Virtual Class - POST route
    Route::post('/virtual-class', function (Request $request) {
        if (!Auth::user()->isAdmin()) abort(403);
        
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'class_id' => 'required|integer',
                'platform' => 'required|string',
                'meeting_link' => 'required|url',
                'scheduled_at' => 'required|date',
                'duration' => 'nullable|integer'
            ]);
            
            // Get teacher_id from users table
            $teacher = DB::table('teachers')->where('user_id', Auth::id())->first();
            
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher record not found'
                ], 400);
            }
            
            $virtualClassId = DB::table('virtual_class_links')->insertGetId([
                'class_id' => $request->class_id,
                'teacher_id' => $teacher->teacher_id,
                'meeting_title' => $request->title,
                'meeting_platform' => strtolower(str_replace(' ', '_', $request->platform)),
                'meeting_link' => $request->meeting_link,
                'scheduled_date' => $request->scheduled_at,
                'duration' => $request->duration ?? 60,
                'status' => 'scheduled',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Virtual class created successfully',
                'virtual_class_id' => $virtualClassId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create virtual class: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Multimedia Support
    Route::get('/multimedia', function () {
        if (!Auth::user()->isAdmin()) abort(403);
        return view('admin.MultiMediaSupport.multimedsupp');
    })->name('multimedia');
    
});

/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
*/
Route::prefix('teacher')->middleware(['auth'])->name('teacher.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        if (!Auth::user()->isTeacher()) abort(403);
        return view('teacher.Dashboard.dashboard');
    })->name('dashboard');
    
    // Create Quiz with Questions (AJAX)
    Route::post('/quizzes/store', function (Request $request) {
        if (!Auth::user()->isTeacher()) abort(403);
        
        try {
            DB::beginTransaction();
            
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'duration' => 'nullable|integer',
                'total_points' => 'nullable|integer',
                'deadline' => 'nullable|date',
                'description' => 'nullable|string',
                'instructions' => 'nullable|string',
                'questions' => 'nullable|array',
                'questions.*.question_text' => 'required|string',
                'questions.*.question_type' => 'required|string',
                'questions.*.points' => 'required|integer',
                'questions.*.options' => 'nullable|array',
                'questions.*.correct_answer' => 'nullable|string'
            ]);
            
            // Get teacher_id from teachers table
            $teacher = DB::table('teachers')->where('user_id', Auth::id())->first();
            
            if (!$teacher) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher record not found'
                ], 400);
            }
            
            // Get teacher's class
            $teacherClass = DB::table('classes')
                ->where('teacher_id', $teacher->teacher_id)
                ->first();
            
            if (!$teacherClass) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No class found for this teacher'
                ], 400);
            }
            
            // Create quiz
            $quizId = DB::table('quizzes')->insertGetId([
                'quiz_title' => $request->title,
                'quiz_description' => $request->description,
                'instructions' => $request->instructions,
                'class_id' => $teacherClass->class_id,
                'teacher_id' => $teacher->teacher_id,
                'time_limit' => $request->duration ?? 30,
                'total_points' => $request->total_points ?? 100,
                'end_date' => $request->deadline,
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Create questions if provided
            if ($request->has('questions') && is_array($request->questions)) {
                foreach ($request->questions as $questionData) {
                    $questionId = DB::table('quiz_questions')->insertGetId([
                        'quiz_id' => $quizId,
                        'question_type' => $questionData['question_type'],
                        'question_text' => $questionData['question_text'],
                        'points' => $questionData['points'],
                        'correct_answer' => $questionData['correct_answer'] ?? null,
                        'created_at' => now()
                    ]);
                    
                    // Create options for multiple choice questions
                    if ($questionData['question_type'] === 'multiple_choice' && isset($questionData['options'])) {
                        foreach ($questionData['options'] as $index => $optionText) {
                            $isCorrect = isset($questionData['correct_answer']) && $questionData['correct_answer'] == $index;
                            DB::table('quiz_question_options')->insert([
                                'question_id' => $questionId,
                                'option_text' => $optionText,
                                'is_correct' => $isCorrect ? 1 : 0
                            ]);
                        }
                    }
                }
            }
            
            // Send notifications to enrolled students
            $enrolledStudents = DB::table('class_enrollments')
                ->join('students', 'class_enrollments.student_id', '=', 'students.student_id')
                ->where('class_enrollments.class_id', $teacherClass->class_id)
                ->where('class_enrollments.status', 'enrolled')
                ->select('students.user_id')
                ->get();
            
            $teacherName = Auth::user()->first_name . ' ' . Auth::user()->last_name;
            foreach ($enrolledStudents as $student) {
                DB::table('notifications')->insert([
                    'user_id' => $student->user_id,
                    'notification_type' => 'quiz',
                    'title' => 'New Quiz Available',
                    'message' => $teacherName . ' posted a new quiz: ' . $request->title . ' (' . ($request->total_points ?? 100) . ' points)',
                    'reference_type' => 'quiz',
                    'reference_id' => $quizId,
                    'is_read' => 0,
                    'created_at' => now()
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Quiz created successfully with ' . count($request->questions ?? []) . ' questions',
                'quiz_id' => $quizId,
                'questions_count' => count($request->questions ?? [])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create quiz: ' . $e->getMessage()
            ], 500);
        }
    })->name('quizzes.store');
    
    // Create Assignment (AJAX)
    Route::post('/assignments/store', function (Request $request) {
        if (!Auth::user()->isTeacher()) abort(403);
        
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'instructions' => 'nullable|string',
                'due_date' => 'required|date',
                'max_points' => 'nullable|integer',
                'file' => 'nullable|file|max:10240', // 10MB max
                'late_submission_allowed' => 'nullable|boolean',
                'late_penalty_percentage' => 'nullable|numeric|min:0|max:100'
            ]);
            
            // Get teacher_id from teachers table
            $teacher = DB::table('teachers')->where('user_id', Auth::id())->first();
            
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher record not found'
                ], 400);
            }
            
            $teacherClass = DB::table('classes')
                ->where('teacher_id', $teacher->teacher_id)
                ->first();
            
            if (!$teacherClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'No class found for this teacher'
                ], 400);
            }
            
            // Handle file upload
            $filePath = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('assignments', $fileName, 'public');
            }
            
            $assignmentId = DB::table('assignments')->insertGetId([
                'title' => $request->title,
                'class_id' => $teacherClass->class_id,
                'teacher_id' => $teacher->teacher_id,
                'description' => $request->description,
                'instructions' => $request->instructions,
                'due_date' => $request->due_date,
                'total_points' => $request->max_points ?? 100,
                'file_attachment' => $filePath,
                'late_submission_allowed' => $request->late_submission_allowed ?? 1,
                'late_penalty_percentage' => $request->late_penalty_percentage ?? 0,
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Assignment created successfully',
                'assignment_id' => $assignmentId,
                'file_uploaded' => $filePath ? true : false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create assignment: ' . $e->getMessage()
            ], 500);
        }
    })->name('assignments.store');
    
    // Grade Assignment Submission
    Route::post('/assignments/grade', function (Request $request) {
        if (!Auth::user()->isTeacher()) abort(403);
        
        try {
            $validated = $request->validate([
                'submission_id' => 'required|integer',
                'score' => 'required|numeric|min:0',
                'feedback' => 'nullable|string'
            ]);
            
            // Update the submission with grade and feedback
            DB::table('assignment_submissions')
                ->where('submission_id', $request->submission_id)
                ->update([
                    'score' => $request->score,
                    'feedback' => $request->feedback,
                    'status' => 'graded',
                    'graded_at' => now()
                ]);
            
            // Get submission details for notification
            $submission = DB::table('assignment_submissions')
                ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.assignment_id')
                ->join('students', 'assignment_submissions.student_id', '=', 'students.student_id')
                ->where('assignment_submissions.submission_id', $request->submission_id)
                ->select(
                    'students.user_id',
                    'assignments.title as assignment_title',
                    'assignments.total_points'
                )
                ->first();
            
            if ($submission) {
                // Send notification to student
                $teacherName = Auth::user()->first_name . ' ' . Auth::user()->last_name;
                $percentage = round(($request->score / $submission->total_points) * 100, 1);
                
                DB::table('notifications')->insert([
                    'user_id' => $submission->user_id,
                    'notification_type' => 'assignment_graded',
                    'title' => 'Assignment Graded',
                    'message' => $teacherName . ' graded your assignment "' . $submission->assignment_title . '". Score: ' . $request->score . '/' . $submission->total_points . ' (' . $percentage . '%)',
                    'reference_type' => 'assignment',
                    'reference_id' => $request->submission_id,
                    'is_read' => 0,
                    'created_at' => now()
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Grade submitted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit grade: ' . $e->getMessage()
            ], 500);
        }
    })->name('assignments.grade');
    
    // View Assignment Submissions
    Route::get('/assignments/submissions', function () {
        if (!Auth::user()->isTeacher()) abort(403);
        return view('teacher.AssignmentSubmission.assignsubmission');
    })->name('assignments.submissions');
    
    // Class Portal
    Route::get('/class-portal', function () {
        if (!Auth::user()->isTeacher()) abort(403);
        return view('teacher.ClassPortal.classportal');
    })->name('class-portal');
    
    // Lesson Materials
    Route::get('/lesson-materials', function () {
        if (!Auth::user()->isTeacher()) abort(403);
        return view('teacher.LessonMaterialUpload.lessonmaterialupload');
    })->name('lesson-materials');
    
    // Upload Material - Alternative route for lesson-materials POST
    Route::post('/lesson-materials', function (Request $request) {
        if (!Auth::user()->isTeacher()) abort(403);
        
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'material_type' => 'required|string',
                'file' => 'nullable|file|max:10240' // 10MB max
            ]);
            
            $filePath = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('materials', $fileName, 'public');
            }
            
            // Get teacher's class
            $teacherClass = DB::table('classes')
                ->where('teacher_id', Auth::id())
                ->first();
            
            if (!$teacherClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'No class found for this teacher'
                ], 400);
            }
            
            $materialId = DB::table('lesson_materials')->insertGetId([
                'material_title' => $request->title,
                'module_id' => $teacherClass->class_id,
                'material_type' => $request->material_type,
                'file_path' => $filePath,
                'teacher_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Material uploaded successfully',
                'material_id' => $materialId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload material: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Upload Material (AJAX with file)
    Route::post('/materials/upload', function (Request $request) {
        if (!Auth::user()->isTeacher()) abort(403);
        
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'material_type' => 'required|string',
                'file' => 'nullable|file|max:10240' // 10MB max
            ]);
            
            DB::beginTransaction();
            
            $filePath = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('materials', $fileName, 'public');
            }
            
            // Get teacher's class
            $teacherClass = DB::table('classes')
                ->where('teacher_id', Auth::id())
                ->first();
            
            if (!$teacherClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'No class found for this teacher'
                ], 400);
            }
            
            $materialId = DB::table('lesson_materials')->insertGetId([
                'material_title' => $request->title,
                'module_id' => $teacherClass->class_id,
                'material_type' => $request->material_type,
                'file_path' => $filePath,
                'teacher_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Get all students enrolled in this class
            $enrolledStudents = DB::table('class_enrollments')
                ->join('students', 'class_enrollments.student_id', '=', 'students.student_id')
                ->where('class_enrollments.class_id', $teacherClass->class_id)
                ->where('class_enrollments.status', 'enrolled')
                ->select('students.user_id')
                ->get();
            
            // Create notifications for all enrolled students
            $teacherName = Auth::user()->first_name . ' ' . Auth::user()->last_name;
            foreach ($enrolledStudents as $student) {
                DB::table('notifications')->insert([
                    'user_id' => $student->user_id,
                    'notification_type' => 'lesson_material',
                    'title' => 'New Lesson Material Uploaded',
                    'message' => $teacherName . ' uploaded a new ' . $request->material_type . ' material: ' . $request->title,
                    'reference_type' => 'lesson_material',
                    'reference_id' => $materialId,
                    'is_read' => 0,
                    'created_at' => now()
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Material uploaded successfully and students notified',
                'material_id' => $materialId,
                'students_notified' => $enrolledStudents->count()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload material: ' . $e->getMessage()
            ], 500);
        }
    })->name('materials.upload');
    
    // Assignments
    Route::get('/assignments', function () {
        if (!Auth::user()->isTeacher()) abort(403);
        return view('teacher.AssignmentSubmission.assignsubmission');
    })->name('assignments');
    
    // Create Assignment - POST route
    Route::post('/assignments', function (Request $request) {
        if (!Auth::user()->isTeacher()) abort(403);
        
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'required|date',
                'max_points' => 'nullable|integer'
            ]);
            
            // Get teacher_id from teachers table
            $teacher = DB::table('teachers')->where('user_id', Auth::id())->first();
            
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher record not found'
                ], 400);
            }
            
            $teacherClass = DB::table('classes')
                ->where('teacher_id', $teacher->teacher_id)
                ->first();
            
            if (!$teacherClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'No class found for this teacher'
                ], 400);
            }
            
            $assignmentId = DB::table('assignments')->insertGetId([
                'title' => $request->title,
                'class_id' => $teacherClass->class_id,
                'teacher_id' => $teacher->teacher_id,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'total_points' => $request->max_points ?? 100,
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Assignment created successfully',
                'assignment_id' => $assignmentId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create assignment: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Quizzes
    Route::get('/quizzes', function () {
        if (!Auth::user()->isTeacher()) abort(403);
        return view('teacher.OnlineQuizzes.onlinequizzes');
    })->name('quizzes');
    
    // Create Quiz - Alternative POST route
    Route::post('/quizzes', function (Request $request) {
        if (!Auth::user()->isTeacher()) abort(403);
        
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'duration' => 'nullable|integer',
                'total_points' => 'nullable|integer',
                'deadline' => 'nullable|date'
            ]);
            
            // Get teacher_id from teachers table
            $teacher = DB::table('teachers')->where('user_id', Auth::id())->first();
            
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher record not found'
                ], 400);
            }
            
            // Get teacher's class
            $teacherClass = DB::table('classes')
                ->where('teacher_id', $teacher->teacher_id)
                ->first();
            
            if (!$teacherClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'No class found for this teacher'
                ], 400);
            }
            
            $quizId = DB::table('quizzes')->insertGetId([
                'quiz_title' => $request->title,
                'class_id' => $teacherClass->class_id,
                'teacher_id' => $teacher->teacher_id,
                'time_limit' => $request->duration ?? 30,
                'total_points' => $request->total_points ?? 100,
                'end_date' => $request->deadline,
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Quiz created successfully',
                'quiz_id' => $quizId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create quiz: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Grading
    Route::get('/grading', function () {
        if (!Auth::user()->isTeacher()) abort(403);
        return view('teacher.GradingIntegration.gradinginteg');
    })->name('grading');
    
    // Grade Submission - POST route
    Route::post('/grading', function (Request $request) {
        if (!Auth::user()->isTeacher()) abort(403);
        
        try {
            $validated = $request->validate([
                'submission_id' => 'required|integer',
                'score' => 'required|numeric|min:0|max:100',
                'feedback' => 'nullable|string'
            ]);
            
            DB::table('assignment_submissions')
                ->where('submission_id', $request->submission_id)
                ->update([
                    'score' => $request->score,
                    'feedback' => $request->feedback,
                    'status' => 'graded',
                    'graded_at' => now(),
                    'graded_by' => Auth::id(),
                    'updated_at' => now()
                ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Assignment graded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to grade assignment: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Analytics
    Route::get('/analytics', function () {
        if (!Auth::user()->isTeacher()) abort(403);
        return view('teacher.LMSAnalytics.lmsanalytics');
    })->name('analytics');
    
    // Virtual Classes
    Route::get('/virtual-classes', function () {
        if (!Auth::user()->isTeacher()) abort(403);
        return view('teacher.VirtualClassLinkInteg.virtualclasslinkinteg');
    })->name('virtual-classes');
    
    // Create Virtual Class - POST route
    Route::post('/virtual-classes', function (Request $request) {
        if (!Auth::user()->isTeacher()) abort(403);
        
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'platform' => 'required|string',
                'meeting_link' => 'required|url',
                'scheduled_at' => 'required|date',
                'duration' => 'nullable|integer'
            ]);
            
            $teacherClass = DB::table('classes')
                ->where('teacher_id', Auth::id())
                ->first();
            
            if (!$teacherClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'No class found for this teacher'
                ], 400);
            }
            
            // Get teacher_id from users table
            $teacher = DB::table('teachers')->where('user_id', Auth::id())->first();
            
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher record not found'
                ], 400);
            }
            
            $virtualClassId = DB::table('virtual_class_links')->insertGetId([
                'class_id' => $teacherClass->class_id,
                'teacher_id' => $teacher->teacher_id,
                'meeting_title' => $request->title,
                'meeting_platform' => strtolower(str_replace(' ', '_', $request->platform)),
                'meeting_link' => $request->meeting_link,
                'scheduled_date' => $request->scheduled_at,
                'duration' => $request->duration ?? 60,
                'status' => 'scheduled',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Virtual class created successfully',
                'virtual_class_id' => $virtualClassId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create virtual class: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Module Tracking
    Route::get('/module-tracking', function () {
        if (!Auth::user()->isTeacher()) abort(403);
        return view('teacher.ModuleCompletionTracking.modulecompletiontracking');
    })->name('module-tracking');
    
});

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/
Route::prefix('student')->middleware(['auth'])->name('student.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        if (!Auth::user()->isStudent()) abort(403);
        return view('student.Dashboard.dashboard');
    })->name('dashboard');
    
    // Mark Notifications as Read
    Route::post('/notifications/mark-read', function (Request $request) {
        if (!Auth::user()->isStudent()) abort(403);
        
        try {
            DB::table('notifications')
                ->where('user_id', Auth::id())
                ->where('is_read', 0)
                ->update([
                    'is_read' => 1,
                    'read_at' => now()
                ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Notifications marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notifications as read'
            ], 500);
        }
    })->name('notifications.mark-read');
    
    // Class Portal
    Route::get('/class-portal', function () {
        if (!Auth::user()->isStudent()) abort(403);
        return view('student.ClassPortal.classportal');
    })->name('class-portal');
    
    // Class Materials - View specific class materials
    Route::get('/class/{class_id}/materials', function ($class_id) {
        if (!Auth::user()->isStudent()) abort(403);
        
        // Verify student is enrolled in this class
        $student = DB::table('students')->where('user_id', Auth::id())->first();
        if (!$student) abort(403);
        
        $enrollment = DB::table('class_enrollments')
            ->where('student_id', $student->student_id)
            ->where('class_id', $class_id)
            ->where('status', 'enrolled')
            ->first();
            
        if (!$enrollment) abort(403, 'You are not enrolled in this class');
        
        return view('student.ClassPortal.class-materials');
    })->name('class-materials');
    
    // Performance
    Route::get('/performance', function () {
        if (!Auth::user()->isStudent()) abort(403);
        return view('student.Performance.Performance');
    })->name('performance');
    
    // Feedback
    Route::get('/feedback', function () {
        if (!Auth::user()->isStudent()) abort(403);
        return view('student.Feedback.feedback');
    })->name('feedback');
    
    // Submit Feedback
    Route::post('/feedback/submit', function (Request $request) {
        if (!Auth::user()->isStudent()) abort(403);
        
        try {
            $validated = $request->validate([
                'class_id' => 'required|integer',
                'feedback_type' => 'required|string',
                'rating' => 'nullable|integer|min:0|max:5',
                'comment' => 'required|string',
                'is_anonymous' => 'required|boolean'
            ]);
            
            $student = DB::table('students')->where('user_id', Auth::id())->first();
            
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student record not found'
                ], 400);
            }
            
            // Check if student_feedback table exists, create if not
            $tableExists = DB::select("SHOW TABLES LIKE 'student_feedback'");
            
            if (empty($tableExists)) {
                // Create the table
                DB::statement("
                    CREATE TABLE `student_feedback` (
                      `feedback_id` int NOT NULL AUTO_INCREMENT,
                      `student_id` int NOT NULL,
                      `class_id` int NOT NULL,
                      `feedback_type` enum('general','teaching','content','facilities','suggestion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
                      `rating` int DEFAULT NULL,
                      `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
                      `is_anonymous` tinyint(1) DEFAULT '0',
                      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`feedback_id`),
                      KEY `student_id` (`student_id`),
                      KEY `class_id` (`class_id`),
                      CONSTRAINT `student_feedback_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
                      CONSTRAINT `student_feedback_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }
            
            // Insert feedback into database
            DB::table('student_feedback')->insert([
                'student_id' => $student->student_id,
                'class_id' => $request->class_id,
                'feedback_type' => $request->feedback_type,
                'rating' => $request->rating > 0 ? $request->rating : null,
                'comment' => $request->comment,
                'is_anonymous' => $request->is_anonymous ? 1 : 0,
                'created_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Feedback submitted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit feedback: ' . $e->getMessage()
            ], 500);
        }
    })->name('feedback.submit');
    
    // Grades
    Route::get('/grades', function () {
        if (!Auth::user()->isStudent()) abort(403);
        return view('student.Grades.grades');
    })->name('grades');
    
    // Assignments
    Route::get('/assignments', function () {
        if (!Auth::user()->isStudent()) abort(403);
        return view('student.Assignmentsubmission.assignsubmission');
    })->name('assignments');
    
    // Submit Assignment
    Route::post('/assignments/submit', function (Request $request) {
        if (!Auth::user()->isStudent()) abort(403);
        
        try {
            $validated = $request->validate([
                'assignment_id' => 'required|integer',
                'submission_text' => 'nullable|string',
                'file' => 'nullable|file|max:10240' // 10MB max
            ]);
            
            $student = DB::table('students')->where('user_id', Auth::id())->first();
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student record not found'
                ], 400);
            }
            
            // Check if already submitted
            $existing = DB::table('assignment_submissions')
                ->where('assignment_id', $request->assignment_id)
                ->where('student_id', $student->student_id)
                ->first();
            
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already submitted this assignment'
                ], 400);
            }
            
            // Handle file upload
            $filePath = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $file->store('assignments/submissions', 'public');
            }
            
            // Insert submission
            DB::table('assignment_submissions')->insert([
                'assignment_id' => $request->assignment_id,
                'student_id' => $student->student_id,
                'submission_text' => $request->submission_text,
                'file_path' => $filePath,
                'status' => 'submitted',
                'submitted_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Assignment submitted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit assignment: ' . $e->getMessage()
            ], 500);
        }
    })->name('assignments.submit');
    
    // View My Submission
    Route::get('/assignments/{assignment_id}/submission', function ($assignment_id) {
        if (!Auth::user()->isStudent()) abort(403);
        
        try {
            $student = DB::table('students')->where('user_id', Auth::id())->first();
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student record not found'
                ], 400);
            }
            
            $submission = DB::table('assignment_submissions')
                ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.assignment_id')
                ->where('assignment_submissions.assignment_id', $assignment_id)
                ->where('assignment_submissions.student_id', $student->student_id)
                ->select(
                    'assignment_submissions.*',
                    'assignments.total_points'
                )
                ->first();
            
            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'submission' => $submission
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load submission: ' . $e->getMessage()
            ], 500);
        }
    })->name('assignments.view-submission');
    
    // Quizzes
    Route::get('/quizzes', function () {
        if (!Auth::user()->isStudent()) abort(403);
        return view('student.OnlineQuizzes.onlinequizzes');
    })->name('quizzes');
    
    // Take Quiz
    Route::get('/quiz/{quiz_id}/take', function ($quiz_id) {
        if (!Auth::user()->isStudent()) abort(403);
        return view('student.OnlineQuizzes.take-quiz');
    })->name('quiz.take');
    
    // Download Assignment File
    Route::get('/assignment/{assignment_id}/download', function ($assignment_id) {
        if (!Auth::user()->isStudent()) abort(403);
        
        $student = DB::table('students')->where('user_id', Auth::id())->first();
        if (!$student) abort(403);
        
        // Get assignment
        $assignment = DB::table('assignments')
            ->join('classes', 'assignments.class_id', '=', 'classes.class_id')
            ->join('class_enrollments', 'classes.class_id', '=', 'class_enrollments.class_id')
            ->where('assignments.assignment_id', $assignment_id)
            ->where('class_enrollments.student_id', $student->student_id)
            ->where('class_enrollments.status', 'enrolled')
            ->select('assignments.*')
            ->first();
        
        if (!$assignment || !$assignment->file_attachment) {
            abort(404, 'File not found');
        }
        
        $filePath = storage_path('app/public/' . $assignment->file_attachment);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found on server');
        }
        
        return response()->download($filePath, basename($assignment->file_attachment));
    })->name('assignment.download');
    
    // Submit Quiz
    Route::post('/quiz/submit', function (Request $request) {
        if (!Auth::user()->isStudent()) abort(403);
        
        try {
            DB::beginTransaction();
            
            $quiz_id = $request->input('quiz_id');
            $student = DB::table('students')->where('user_id', Auth::id())->first();
            
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student record not found'
                ], 400);
            }
            
            // Get quiz details
            $quiz = DB::table('quizzes')->where('quiz_id', $quiz_id)->first();
            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }
            
            // Create quiz attempt
            $attemptId = DB::table('quiz_attempts')->insertGetId([
                'quiz_id' => $quiz_id,
                'student_id' => $student->student_id,
                'attempt_number' => 1,
                'start_time' => $request->input('start_time'),
                'end_time' => now(),
                'time_spent' => $request->input('time_spent', 0),
                'status' => 'submitted',
                'created_at' => now()
            ]);
            
            // Get all questions
            $questions = DB::table('quiz_questions')
                ->where('quiz_id', $quiz_id)
                ->get();
            
            $totalScore = 0;
            $totalPoints = 0;
            
            // Process each question
            foreach ($questions as $question) {
                $totalPoints += $question->points;
                $answerKey = 'question_' . $question->question_id;
                $studentAnswer = $request->input($answerKey);
                
                $isCorrect = 0;
                $pointsEarned = 0;
                
                if ($question->question_type === 'multiple_choice') {
                    // Check if selected option is correct
                    $selectedOption = DB::table('quiz_question_options')
                        ->where('option_id', $studentAnswer)
                        ->first();
                    
                    if ($selectedOption && $selectedOption->is_correct) {
                        $isCorrect = 1;
                        $pointsEarned = $question->points;
                    }
                    
                    // Save answer
                    DB::table('quiz_answers')->insert([
                        'attempt_id' => $attemptId,
                        'question_id' => $question->question_id,
                        'selected_option_id' => $studentAnswer,
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned
                    ]);
                } elseif ($question->question_type === 'true_false') {
                    // Check if answer matches correct answer
                    if (strtolower($studentAnswer) === strtolower($question->correct_answer)) {
                        $isCorrect = 1;
                        $pointsEarned = $question->points;
                    }
                    
                    DB::table('quiz_answers')->insert([
                        'attempt_id' => $attemptId,
                        'question_id' => $question->question_id,
                        'answer_text' => $studentAnswer,
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned
                    ]);
                } elseif ($question->question_type === 'short_answer') {
                    // For short answer, save but don't auto-grade
                    DB::table('quiz_answers')->insert([
                        'attempt_id' => $attemptId,
                        'question_id' => $question->question_id,
                        'answer_text' => $studentAnswer,
                        'is_correct' => null,
                        'points_earned' => null
                    ]);
                }
                
                $totalScore += $pointsEarned;
            }
            
            // Update attempt with score
            DB::table('quiz_attempts')
                ->where('attempt_id', $attemptId)
                ->update([
                    'score' => $totalScore,
                    'status' => 'graded'
                ]);
            
            DB::commit();
            
            $percentage = $totalPoints > 0 ? round(($totalScore / $totalPoints) * 100, 2) : 0;
            
            return response()->json([
                'success' => true,
                'message' => 'Quiz submitted successfully',
                'score' => $totalScore,
                'total_points' => $totalPoints,
                'percentage' => $percentage
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit quiz: ' . $e->getMessage()
            ], 500);
        }
    })->name('quiz.submit');
    
    // My Profile
    Route::get('/profile', function () {
        if (!Auth::user()->isStudent()) abort(403);
        return view('student.My_Profile.My_Profile');
    })->name('profile');
    
});
