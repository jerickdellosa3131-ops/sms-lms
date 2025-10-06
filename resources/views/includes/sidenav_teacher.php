<?php
/**
 * Teacher Sidenav Component
 * SMS3 Learning Management System
 */





use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$user_full_name = $user->first_name . ' ' . $user->last_name;
$user_email = $user->email;
$user_initials = get_user_initials($user->first_name, $user->last_name);
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<div class="app-container">
    <aside class="sidenav">
        <div class="sidenav-logo">
            <img src="<?php echo asset('SMS3logo.jpg'); ?>" alt="SMS 3 Logo">
        </div>
        <nav class="sidenav-nav">
            <ul>
                <li>
                    <a href="<?php echo route('teacher.dashboard'); ?>">
                        <i class="fa-solid fa-house"></i> Dashboard
                    </a>
                </li>

                <li class="nav-title">Teaching</li>
                <li>
                    <a href="<?php echo route('teacher.class-portal'); ?>">
                        <i class="fa-solid fa-book"></i> Class Portal
                    </a>
                </li>
                <li>
                    <a href="<?php echo route('teacher.lesson-materials'); ?>">
                        <i class="fa-solid fa-cloud-upload"></i> Lesson Materials
                    </a>
                </li>
                <li>
                    <a href="<?php echo route('teacher.assignments'); ?>">
                        <i class="fa-solid fa-file-upload"></i> Assignments
                    </a>
                </li>
                <li>
                    <a href="<?php echo route('teacher.quizzes'); ?>">
                        <i class="fa-solid fa-question-circle"></i> Quizzes
                    </a>
                </li>

                <li class="nav-title">Management</li>
                <li>
                    <a href="<?php echo route('teacher.grading'); ?>">
                        <i class="fa-solid fa-file-signature"></i> Grading Integration
                    </a>
                </li>
                <li>
                    <a href="<?php echo route('teacher.module-tracking'); ?>">
                        <i class="fa-solid fa-tasks"></i> Module Tracking
                    </a>
                </li>
                <li>
                    <a href="<?php echo route('teacher.analytics'); ?>">
                        <i class="fa-solid fa-chart-line"></i> Analytics
                    </a>
                </li>
                <li>
                    <a href="<?php echo route('teacher.virtual-classes'); ?>">
                        <i class="fa-solid fa-video"></i> Virtual Classes
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <header class="header">
        <div style="display: flex; align-items: center;">
            <button id="sidenav-toggle" aria-label="Toggle Sidenav">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
        <div class="header-right">
            <nav class="header-nav">
                <ul>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </nav>
            <div class="profile-dropdown">
                <button class="profile-btn" id="profileBtn">
                    <?php echo htmlspecialchars($user_initials); ?>
                </button>
                <div class="dropdown-content" id="profileDropdown">
                    <div class="dropdown-profile-info">
                        <h4><?php echo htmlspecialchars($user_full_name); ?></h4>
                        <p><?php echo htmlspecialchars($user_email); ?></p>
                    </div>
                    <a href="#">Profile</a>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log out</a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Hidden Logout Form -->
    <form id="logout-form" action="<?php echo route('logout'); ?>" method="POST" style="display: none;">
        <?php echo csrf_field(); ?>
    </form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidenavToggle = document.getElementById('sidenav-toggle');
    const appContainer = document.querySelector('.app-container');
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');

    if (sidenavToggle) {
        sidenavToggle.addEventListener('click', () => {
            appContainer.classList.toggle('sidenav-closed');
        });
    }

    if (profileBtn) {
        profileBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            profileDropdown.classList.toggle('show');
        });
    }

    window.addEventListener('click', function(event) {
        if (profileBtn && profileDropdown && 
            !profileBtn.contains(event.target) && 
            !profileDropdown.contains(event.target)) {
            profileDropdown.classList.remove('show');
        }
    });
});
</script>
