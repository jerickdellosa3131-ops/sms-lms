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
                    'student_number' => $request->user_number ?? 'STU' . str_pad($userId, 6, '0', STR_PAD_LEFT),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } elseif ($request->user_type === 'teacher') {
                DB::table('teachers')->insert([
                    'user_id' => $userId,
                    'teacher_number' => $request->user_number ?? 'TCH' . str_pad($userId, 6, '0', STR_PAD_LEFT),
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
    
    // Create Quiz (AJAX)
    Route::post('/quizzes/store', function (Request $request) {
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
    })->name('quizzes.store');
    
    // Create Assignment (AJAX)
    Route::post('/assignments/store', function (Request $request) {
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
    })->name('assignments.store');
    
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
    
    // Class Portal
    Route::get('/class-portal', function () {
        if (!Auth::user()->isStudent()) abort(403);
        return view('student.ClassPortal.classportal');
    })->name('class-portal');
    
    // Performance
    Route::get('/performance', function () {
        if (!Auth::user()->isStudent()) abort(403);
        return view('student.Performance.Performance');
    })->name('performance');
    
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
    
    // Quizzes
    Route::get('/quizzes', function () {
        if (!Auth::user()->isStudent()) abort(403);
        return view('student.OnlineQuizzes.onlinequizzes');
    })->name('quizzes');
    
    // My Profile
    Route::get('/profile', function () {
        if (!Auth::user()->isStudent()) abort(403);
        return view('student.My_Profile.My_Profile');
    })->name('profile');
    
});
