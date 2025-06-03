<?php
// views/habits.php - Habits management page

// Include auth controller
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/HabitController.php';

// Check if a notification needs to be marked as read
require_once __DIR__ . '/../utils/notification_handler.php';

// Handle notification read marking - if it returns true, a redirect was performed and we should exit
if(handleNotificationReadMarking()) {
    exit;
}


$authController = new AuthController();

// Redirect if not logged in
if(!$authController->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

// Get logged in user
$user = $authController->getLoggedInUser();

// Initialize habit controller
$habitController = new HabitController();

// Get all habits for the user
$habits = $habitController->getAllHabits($user->id);

// Get habit categories
$categories = $habitController->getAllCategories();

// Group habits by category
$habits_by_category = [];
foreach ($habits as $habit) {
    $category_id = $habit['category_id'] ?? 0;
    $category_name = $habit['category_name'] ?? 'Uncategorized';
    
    if (!isset($habits_by_category[$category_id])) {
        $habits_by_category[$category_id] = [
            'name' => $category_name,
            'color' => $habit['category_color'] ?? '#3498db',
            'habits' => []
        ];
    }
    
    $habits_by_category[$category_id]['habits'][] = $habit;
}

// Include header
include '../views/partials/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../views/partials/sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Habits</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addHabitModal">
                        <i class="bi bi-plus"></i> Add New Habit
                    </button>
                </div>
            </div>
            
            <!-- Alert messages -->
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Modern Habit Statistics with Enhanced Styling -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stat-card will-change-transform">
                        <div class="stat-number"><?php echo count($habits); ?></div>
                        <div class="stat-label">Total Habits</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card will-change-transform">
                        <div class="stat-number"><?php echo array_sum(array_column($habits, 'is_completed_today')); ?></div>
                        <div class="stat-label">Completed Today</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card will-change-transform">
                        <div class="stat-number">
                            <?php 
                            // Consider a habit active if it has no end_date or end_date is in the future
                            $activeHabits = array_filter($habits, function($h) { 
                                return empty($h['end_date']) || strtotime($h['end_date']) >= strtotime(date('Y-m-d')); 
                            });
                            echo count($activeHabits);
                            ?>
                        </div>
                        <div class="stat-label">Active Habits</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card will-change-transform">
                        <div class="stat-number">
                            <?php 
                            // Calculate average completion rate based on days since start
                            $totalRate = 0;
                            $habitCount = count($habits);
                            foreach($habits as $habit) {
                                $daysSinceStart = (strtotime(date('Y-m-d')) - strtotime($habit['start_date'])) / (60 * 60 * 24) + 1;
                                $completionRate = $daysSinceStart > 0 ? round(($habit['completion_count'] / $daysSinceStart) * 100) : 0;
                                $totalRate += min($completionRate, 100); // Cap at 100%
                            }
                            $avgCompletion = $habitCount > 0 ? round($totalRate / $habitCount) : 0;
                            echo $avgCompletion;
                            ?>%
                        </div>
                        <div class="stat-label">Avg Completion</div>
                    </div>
                </div>
            </div>
            
            <!-- Modern Floating Action Button -->
            <button class="fab tooltip-modern" data-tooltip="Add New Habit" data-bs-toggle="modal" data-bs-target="#addHabitModal">
                <i class="bi bi-plus"></i>
            </button>
            
            <!-- Enhanced Habits Grid -->
            <div class="row">
                <?php if(empty($habits)): ?>
                    <div class="col-12">
                        <div class="modern-card text-center py-5 card-animate">
                            <i class="bi bi-plus-circle display-1 text-muted mb-3"></i>
                            <h3 class="text-muted">No habits yet</h3>
                            <p class="text-muted mb-4">Start building healthy habits today!</p>
                            <button class="btn btn-primary btn-glow" data-bs-toggle="modal" data-bs-target="#addHabitModal">
                                <i class="bi bi-plus me-2"></i>
                                Create Your First Habit
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach($habits as $index => $habit): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="habit-card-modern <?= $habit['is_completed_today'] ? 'completed' : '' ?>" 
                                 style="animation-delay: <?= $index * 0.1 ?>s">
                                
                                <!-- Habit Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($habit['title']) ?></h5>
                                    <small class="text-muted">
                                        <i class="bi bi-tag me-1"></i>
                                        <?= htmlspecialchars($habit['category_name'] ?? 'Uncategorized') ?>
                                    </small>
                                </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="editHabit(<?= $habit['id'] ?>)">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteHabit(<?= $habit['id'] ?>)">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <!-- Habit Description -->
                                <?php if(!empty($habit['description'])): ?>
                                    <p class="card-text text-muted small mb-3">
                                        <?= htmlspecialchars($habit['description']) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <!-- Progress Bar -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small text-muted">Progress</span>
                                        <span class="small fw-bold">
                                            <?php 
                                            // Calculate completion rate based on days since start
                                            $daysSinceStart = (strtotime(date('Y-m-d')) - strtotime($habit['start_date'])) / (60 * 60 * 24) + 1;
                                            $completionRate = $daysSinceStart > 0 ? round(($habit['completion_count'] / $daysSinceStart) * 100) : 0;
                                            $completionRate = min($completionRate, 100); // Cap at 100%
                                            echo $completionRate;
                                            ?>%
                                        </span>
                                    </div>
                                    <div class="progress progress-modern">
                                        <div class="progress-bar bg-gradient" 
                                             role="progressbar" 
                                             style="width: <?php 
                                             $daysSinceStart = (strtotime(date('Y-m-d')) - strtotime($habit['start_date'])) / (60 * 60 * 24) + 1;
                                             $completionRate = $daysSinceStart > 0 ? round(($habit['completion_count'] / $daysSinceStart) * 100) : 0;
                                             echo min($completionRate, 100); // Cap at 100%
                                             ?>%"
                                             aria-valuenow="<?php echo min($completionRate, 100); ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Habit Stats -->
                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="small text-muted">Streak</div>
                                        <div class="fw-bold text-warning">
                                            <i class="bi bi-fire"></i>
                                            <?= $habit['streak'] ?? 0 ?>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small text-muted">Best</div>
                                        <div class="fw-bold text-success">
                                            <i class="bi bi-trophy"></i>
                                            <?= $habit['best_streak'] ?? 0 ?>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small text-muted">Total</div>
                                        <div class="fw-bold text-info">
                                            <i class="bi bi-check-circle"></i>
                                            <?= $habit['completion_count'] ?? 0 ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
                                    <?php if(!$habit['is_completed_today']): ?>
                                        <form class="habit-completion-form" action="/controllers/process_habit_completion.php" method="POST">
                                            <input type="hidden" name="habit_id" value="<?= $habit['id'] ?>">
                                            <button type="submit" class="btn btn-success habit-complete-btn btn-glow w-100">
                                                <i class="bi bi-check-circle me-2"></i>
                                                Mark Complete
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-outline-success w-100" disabled>
                                            <i class="bi bi-check-circle-fill me-2"></i>
                                            Completed Today!
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Habit Frequency Info -->
                                <div class="mt-3 pt-3 border-top">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?= ucfirst($habit['frequency_type']) ?> 
                                        <?php if($habit['frequency_type'] === 'weekly'): ?>
                                            (<?= $habit['weekly_days'] ?? 'All days' ?>)
                                        <?php elseif($habit['frequency_type'] === 'custom'): ?>
                                            (Every <?= $habit['custom_frequency'] ?> days)
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

<style>
/* Enhanced Habits Page Styles */
.habit-card-modern {
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    transition: var(--transition-smooth);
    position: relative;
    overflow: hidden;
    animation: slide-up 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.habit-card-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--primary-gradient);
}

.habit-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
}

.habit-card-modern.completed {
    background: linear-gradient(135deg, rgba(75, 172, 254, 0.1) 0%, rgba(0, 242, 254, 0.1) 100%);
    border-color: rgba(75, 172, 254, 0.3);
}

.habit-card-modern.completed::before {
    background: var(--success-gradient);
}

.habit-card-modern.completed::after {
    content: '✓';
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 24px;
    height: 24px;
    background: var(--success-gradient);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
    animation: bounce-in 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Habit statistics styling */
.habit-card-modern .row.text-center > div {
    padding: 0.5rem;
}

.habit-card-modern .row.text-center > div:not(:last-child) {
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}

/* Enhanced button styling */
.habit-complete-btn {
    background: var(--success-gradient);
    border: none;
    color: white;
    font-weight: 600;
    transition: var(--transition-smooth);
    position: relative;
    overflow: hidden;
}

.habit-complete-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(75, 172, 254, 0.3);
    color: white;
}

/* Empty state styling */
.modern-card {
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    transition: var(--transition-smooth);
}

.modern-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-xl);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .habit-card-modern {
        margin-bottom: 1rem;
    }
    
    .stat-card {
        margin-bottom: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced habit card animations
    const habitCards = document.querySelectorAll('.habit-card-modern');
    habitCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        
        // Add hover effect for better interactivity
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
    
    // Enhanced completion animation
    const completionForms = document.querySelectorAll('.habit-completion-form');
    completionForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = this.querySelector('.habit-complete-btn');
            const card = this.closest('.habit-card-modern');
            
            // Animation sequence
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Completing...';
            button.disabled = true;
            
            setTimeout(() => {
                card.classList.add('completed');
                button.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Completed!';
                
                // Create celebration effect
                const celebration = document.createElement('div');
                celebration.innerHTML = '🎉';
                celebration.style.cssText = `
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    font-size: 2rem;
                    pointer-events: none;
                    animation: celebrate 1s ease-out forwards;
                `;
                card.appendChild(celebration);
                
                setTimeout(() => celebration.remove(), 1000);
                
                // Update UI after delay
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }, 1500);
        });
    });
});

// Habit management functions
function editHabit(habitId) {
    // Implementation for edit modal
    console.log('Edit habit:', habitId);
}

function deleteHabit(habitId) {
    if (confirm('Are you sure you want to delete this habit?')) {
        // Implementation for delete
        console.log('Delete habit:', habitId);
    }
}
</script>

<?php
// Include footer
include __DIR__ . '/partials/footer.php';

?>