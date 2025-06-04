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
            
            <!-- Habit Filters -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label for="categoryFilter" class="form-label fw-bold mb-2">Filter by Category:</label>
                                    <select class="form-select" id="categoryFilter">
                                        <option value="all">All Categories</option>
                                        <?php foreach($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label for="statusFilter" class="form-label fw-bold mb-2">Filter by Status:</label>
                                    <select class="form-select" id="statusFilter">
                                        <option value="all">All Habits</option>
                                        <option value="completed">Completed Today</option>
                                        <option value="pending">Pending Today</option>
                                        <option value="active">Active Habits</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="searchHabits" class="form-label fw-bold mb-2">Search Habits:</label>
                                    <input type="text" class="form-control" id="searchHabits" placeholder="Search habit titles...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
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
                                 data-category-id="<?= $habit['category_id'] ?>"
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
                                        <form class="habit-completion-form" data-habit-id="<?= $habit['id'] ?>">
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
// Habit filtering functionality
function filterHabits() {
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchInput = document.getElementById('searchHabits');
    
    if (!categoryFilter || !statusFilter || !searchInput) {
        console.error('Filter elements not found');
        return;
    }
    
    const categoryValue = categoryFilter.value;
    const statusValue = statusFilter.value;
    const searchTerm = searchInput.value.toLowerCase();
    
    const habitCards = document.querySelectorAll('.habit-card-modern');
    let visibleCount = 0;
    
    habitCards.forEach((card) => {
        const cardParent = card.closest('.col-md-6, .col-lg-4');
        if (!cardParent) return;
        
        let showCard = true;
        
        // Get habit title
        const titleElement = card.querySelector('h5, .card-title');
        const title = titleElement ? titleElement.textContent.toLowerCase() : '';
        
        // Get category ID
        const categoryId = card.getAttribute('data-category-id');
        
        // Check if completed
        const isCompleted = card.classList.contains('completed');
        
        // Apply category filter
        if (categoryValue !== 'all' && categoryId !== categoryValue) {
            showCard = false;
        }
        
        // Apply status filter
        if (statusValue !== 'all') {
            switch (statusValue) {
                case 'completed':
                    if (!isCompleted) showCard = false;
                    break;
                case 'pending':
                    if (isCompleted) showCard = false;
                    break;
                case 'active':
                    // Show all active habits (non-archived)
                    break;
            }
        }
        
        // Apply search filter
        if (searchTerm && !title.includes(searchTerm)) {
            showCard = false;
        }
        
        // Show/hide the card
        if (showCard) {
            cardParent.style.display = '';
            visibleCount++;
        } else {
            cardParent.style.display = 'none';
        }
    });
    
    // Update no habits message
    updateNoHabitsMessage();
}

function updateNoHabitsMessage() {
    const visibleCards = document.querySelectorAll('.col-md-6:not([style*="display: none"]), .col-lg-4:not([style*="display: none"])');
    const noHabitsCard = document.querySelector('.modern-card');
    const habitsGrid = document.querySelector('.row:has(.habit-card-modern)');
    
    if (visibleCards.length === 0 && !noHabitsCard) {
        // Create and show "no matching habits" message
        const noMatchMessage = document.createElement('div');
        noMatchMessage.className = 'col-12';
        noMatchMessage.innerHTML = `
            <div class="modern-card text-center py-5 card-animate" id="noMatchMessage">
                <i class="bi bi-search display-1 text-muted mb-3"></i>
                <h3 class="text-muted">No matching habits found</h3>
                <p class="text-muted mb-4">Try adjusting your filters or search terms.</p>
            </div>
        `;
        if (habitsGrid) {
            habitsGrid.appendChild(noMatchMessage);
        }
    } else if (visibleCards.length > 0) {
        // Remove "no matching habits" message if it exists
        const existingMessage = document.getElementById('noMatchMessage');
        if (existingMessage) {
            existingMessage.parentElement.remove();
        }
    }
}

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
    
    // Enhanced completion animation with actual database update
    const completionForms = document.querySelectorAll('.habit-completion-form');
    
    completionForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = this.querySelector('.habit-complete-btn');
            const card = this.closest('.habit-card-modern');
            const habitId = this.dataset.habitId;
              // Animation sequence
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Completing...';
            button.disabled = true;
            
            // Make AJAX call to complete habit using FormData from the entire form
            fetch('../controllers/process_habit_completion.php', {
                method: 'POST',
                body: new FormData(this)  // Use the entire form data instead of manually creating FormData
            })
            .then(response => response.json())            .then(data => {
                if (data.success) {
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
                      setTimeout(() => celebration.remove(), 1000);                        // Update sidebar elements - delay slightly to ensure DOM is ready, then retry
                        setTimeout(() => {
                            updateSidebarElements(data);
                            
                            // Additional delayed retry to catch any timing issues
                            setTimeout(() => {
                                updateSidebarElements(data);
                            }, 300);
                        }, 100);
                    
                    // Show success message without page reload
                    setTimeout(() => {
                        // Show success notification
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show';
                        alert.innerHTML = `
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        document.querySelector('main').insertBefore(alert, document.querySelector('main').firstChild);
                        
                        // Auto-dismiss the alert after 4 seconds
                        setTimeout(() => {
                            if (alert.parentNode) {
                                alert.remove();
                            }
                        }, 4000);
                    }, 1000);
                } else {
                    // Handle error
                    button.innerHTML = '<i class="bi bi-check-circle me-2"></i>Mark Complete';
                    button.disabled = false;
                    
                    // Show error message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-danger alert-dismissible fade show';
                    alert.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.querySelector('main').insertBefore(alert, document.querySelector('main').firstChild);
                }
            })
            .catch(error => {
                console.error('Error completing habit:', error);
                button.innerHTML = '<i class="bi bi-check-circle me-2"></i>Mark Complete';
                button.disabled = false;
                
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show';
                alert.innerHTML = `
                    Error completing habit. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('main').insertBefore(alert, document.querySelector('main').firstChild);
            });
        });
    });
    
    // Add event listener for frequency changes in modals
    const frequencySelect = document.getElementById('habitFrequency');
    if (frequencySelect) {
        frequencySelect.addEventListener('change', handleFrequencyChange);
        // Set initial state
        handleFrequencyChange();
    }
    
    // Add event listeners for habit filters
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchInput = document.getElementById('searchHabits');
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterHabits);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterHabits);
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', filterHabits);
        searchInput.addEventListener('keyup', filterHabits);
    }
});

// Habit management functions
function editHabit(habitId) {
    // Fetch habit data from new API endpoint
    fetch(`../controllers/get_habit.php?id=${habitId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const habit = data.habit;
                
                // Populate edit modal fields
                document.getElementById('editHabitId').value = habit.id;
                document.getElementById('editHabitTitle').value = habit.title;
                document.getElementById('editHabitDescription').value = habit.description || '';
                document.getElementById('editHabitCategory').value = habit.category_id;
                document.getElementById('editHabitXP').value = habit.xp_reward;
                
                // Show edit modal
                const editModal = new bootstrap.Modal(document.getElementById('editHabitModal'));
                editModal.show();
            } else {
                alert('Error loading habit data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading habit data. Please try again.');
        });
}

function deleteHabit(habitId) {
    if (confirm('Are you sure you want to delete this habit? This action cannot be undone.')) {
        // Create form to submit delete request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../controllers/process_delete_habit.php';
        
        const habitIdInput = document.createElement('input');
        habitIdInput.type = 'hidden';
        habitIdInput.name = 'habit_id';
        habitIdInput.value = habitId;
        
        form.appendChild(habitIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Handle frequency type changes for add habit modal
function handleFrequencyChange() {
    const frequencySelect = document.getElementById('habitFrequency');
    const weeklyOptions = document.getElementById('weeklyOptions');
    const monthlyOptions = document.getElementById('monthlyOptions');
    const customOptions = document.getElementById('customOptions');
    
    // Hide all frequency options
    weeklyOptions.style.display = 'none';
    monthlyOptions.style.display = 'none';
    customOptions.style.display = 'none';
    
    // Show relevant options based on selection
    switch (frequencySelect.value) {
        case 'weekly':
            weeklyOptions.style.display = 'block';
            break;
        case 'monthly':
            monthlyOptions.style.display = 'block';
            break;        case 'custom':
            customOptions.style.display = 'block';
            break;
    }
}

// Function to update sidebar elements after habit completion
function updateSidebarElements(data) {
    // Update header notification count if provided
    if (data.notification_count !== undefined) {
        // Multiple retry attempts to ensure badge update works
        let retryCount = 0;
        const maxRetries = 3;
        
        function updateBadges() {
            // Multiple selector strategies to ensure we catch all notification badges
            const selectors = [
                'span.badge.rounded-pill.bg-danger.position-absolute',
                '.position-absolute.top-0.start-100.translate-middle.badge',
                'span.badge.bg-danger[class*="position-absolute"]',
                '.btn.position-relative .badge.bg-danger'
            ];
            
            let badgesUpdated = 0;
            
            selectors.forEach((selector, selectorIndex) => {
                const badges = document.querySelectorAll(selector);
                
                badges.forEach((badge, badgeIndex) => {
                    // Additional verification that this is a notification badge
                    const isNotificationBadge = badge.classList.contains('position-absolute') || 
                                              badge.closest('.btn.position-relative') ||
                                              badge.classList.contains('translate-middle');
                    
                    if (isNotificationBadge) {
                        if (data.notification_count > 0) {
                            // Update text content first
                            const textContent = data.notification_count.toString();
                            badge.textContent = textContent;
                            
                            // Ensure any nested visually-hidden spans are preserved
                            if (!badge.querySelector('.visually-hidden')) {
                                const hiddenSpan = document.createElement('span');
                                hiddenSpan.className = 'visually-hidden';
                                hiddenSpan.textContent = 'unread notifications';
                                badge.appendChild(hiddenSpan);
                            }
                            
                            // Force display and visibility
                            badge.style.display = 'inline-flex !important';
                            badge.style.visibility = 'visible !important';
                            badge.style.opacity = '1 !important';
                            badge.style.pointerEvents = 'auto';
                            
                            // Remove any hidden classes
                            badge.classList.remove('d-none', 'invisible');
                            
                            // Force a reflow to ensure visual update
                            badge.offsetHeight;
                            
                            // Add a subtle animation to draw attention
                            badge.style.transform = 'translate(-50%, -50%) scale(1.1)';
                            setTimeout(() => {
                                badge.style.transform = 'translate(-50%, -50%) scale(1)';
                            }, 200);
                            
                        } else {
                            badge.style.display = 'none !important';
                            badge.style.visibility = 'hidden !important';
                            badge.style.opacity = '0 !important';
                            badge.style.pointerEvents = 'none';
                            badge.classList.add('d-none');
                        }
                        
                        badgesUpdated++;
                    }
                });
            });
            
            // Fallback: if no badges were updated, try a more general approach
            if (badgesUpdated === 0) {
                const allBadges = document.querySelectorAll('.badge');
                allBadges.forEach((badge, index) => {
                    if (badge.classList.contains('bg-danger') && 
                        (badge.textContent.trim().match(/^\d+$/) || badge.textContent.trim() === '')) {
                        
                        if (data.notification_count > 0) {
                            badge.textContent = data.notification_count.toString();
                            badge.style.display = 'inline-flex !important';
                            badge.style.visibility = 'visible !important';
                            badge.style.opacity = '1 !important';
                            badgesUpdated++;
                        } else {
                            badge.style.display = 'none !important';
                        }
                    }
                });
            }
            
            // If still no badges updated and we have retries left, try again
            if (badgesUpdated === 0 && retryCount < maxRetries) {
                retryCount++;
                setTimeout(updateBadges, 150 * retryCount); // Increasing delay for each retry
            }
        }
        
        // Start the badge update process
        updateBadges();
    }
    
    // Update XP and level information if provided
    if (data.new_xp !== undefined && data.current_level !== undefined) {
        // Update level display in progress section
        const levelText = document.querySelector('.progress-section .text-muted');
        if (levelText && levelText.textContent.includes('Level')) {
            levelText.textContent = 'Level ' + data.current_level;
        }
        
        // Update XP progress bar with fresh data
        updateXPProgressBar();
    }
    
    // Show level up message if applicable
    if (data.level_up && data.new_level) {
        setTimeout(() => {
            const levelUpAlert = document.createElement('div');
            levelUpAlert.className = 'alert alert-warning alert-dismissible fade show';
            levelUpAlert.innerHTML = `
                <i class="bi bi-star-fill"></i> 🎉 Level Up! You reached Level ${data.new_level}!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('main').insertBefore(levelUpAlert, document.querySelector('main').firstChild);
            
            // Auto-dismiss after 6 seconds
            setTimeout(() => {
                if (levelUpAlert.parentNode) {
                    levelUpAlert.remove();
                }
            }, 6000);
        }, 500); // Delay to show after habit completion message
    }
}

// Function to update XP progress bar
function updateXPProgressBar() {
    // Make AJAX call to get the exact progress data
    fetch('../controllers/get_user_progress.php')
        .then(response => response.json())
        .then(progressData => {
            if (progressData.success) {
                // Update progress bar
                const progressBar = document.querySelector('.progress-section .progress-bar');
                if (progressBar) {
                    progressBar.style.width = progressData.percentage + '%';
                    progressBar.setAttribute('aria-valuenow', progressData.percentage);
                }
                
                // Update level text
                const levelText = document.querySelector('.progress-section .text-muted');
                if (levelText && levelText.textContent.includes('Level')) {
                    levelText.textContent = 'Level ' + progressData.current_level;
                }
                
                // Update XP text
                const xpText = document.querySelector('.progress-section small:last-child');
                if (xpText) {
                    xpText.innerHTML = `${progressData.current_xp} / ${progressData.next_level_xp} XP to Level ${progressData.next_level}`;
                }
            }
        })
        .catch(error => {
            console.log('Could not update XP progress bar:', error);
        });
}
</script>

<!-- Add Habit Modal -->
<div class="modal fade" id="addHabitModal" tabindex="-1" aria-labelledby="addHabitModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addHabitModalLabel">Add New Habit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../controllers/process_add_habit.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="habitTitle" class="form-label">Habit Title</label>
                        <input type="text" class="form-control" id="habitTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="habitDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="habitDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="habitCategory" class="form-label">Category</label>
                        <select class="form-select" id="habitCategory" name="category_id">
                            <?php
                            foreach($categories as $category) {
                                echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="habitFrequency" class="form-label">Frequency</label>
                        <select class="form-select" id="habitFrequency" name="frequency_type">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="mb-3 frequency-options" id="weeklyOptions" style="display: none;">
                        <label class="form-label">Days of Week</label>
                        <div class="btn-group d-flex flex-wrap" role="group">
                            <?php
                            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                            foreach($days as $index => $day) {
                                echo '<input type="checkbox" class="btn-check" id="day' . $index . '" name="frequency_value[]" value="' . $index . '">';
                                echo '<label class="btn btn-outline-primary m-1" for="day' . $index . '">' . $day . '</label>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="mb-3 frequency-options" id="monthlyOptions" style="display: none;">
                        <label for="monthlyDay" class="form-label">Day of Month</label>
                        <select class="form-select" id="monthlyDay" name="monthly_day">
                            <?php
                            for($i = 1; $i <= 31; $i++) {
                                echo '<option value="' . $i . '">' . $i . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3 frequency-options" id="customOptions" style="display: none;">
                        <label for="customDays" class="form-label">Repeat every X days</label>
                        <input type="number" class="form-control" id="customDays" name="custom_days" min="1" value="1">
                    </div>
                    <div class="mb-3">
                        <label for="habitStartDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="habitStartDate" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="habitEndDate" class="form-label">End Date (Optional)</label>
                        <input type="date" class="form-control" id="habitEndDate" name="end_date">
                    </div>
                    <div class="mb-3">
                        <label for="habitXP" class="form-label">XP Reward</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="habitXP" name="xp_reward" min="1" value="10">
                            <span class="input-group-text">XP</span>
                        </div>
                        <div class="form-text">XP earned when completing this habit</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Habit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Habit Modal -->
<div class="modal fade" id="editHabitModal" tabindex="-1" aria-labelledby="editHabitModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editHabitModalLabel">Edit Habit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../controllers/process_edit_habit.php" method="POST">
                <input type="hidden" id="editHabitId" name="habit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editHabitTitle" class="form-label">Habit Title</label>
                        <input type="text" class="form-control" id="editHabitTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="editHabitDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editHabitDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editHabitCategory" class="form-label">Category</label>
                        <select class="form-select" id="editHabitCategory" name="category_id">
                            <?php
                            foreach($categories as $category) {
                                echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editHabitXP" class="form-label">XP Reward</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="editHabitXP" name="xp_reward" min="1" value="10">
                            <span class="input-group-text">XP</span>
                        </div>
                        <div class="form-text">XP earned when completing this habit</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Habit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
include __DIR__ . '/partials/footer.php';

?>