<?php
/**
 * Reusable Add Habit Modal Component
 */

// Ensure we have access to HabitController
if (!isset($habitController)) {
    require_once __DIR__ . '/../../controllers/HabitController.php';
    $habitController = new HabitController();
}
?>

<!-- Add Habit Modal -->
<div class="modal fade" id="addHabitModal" tabindex="-1" aria-labelledby="addHabitModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addHabitModalLabel">Add New Habit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '../controllers/process_add_habit.php' : 'controllers/process_add_habit.php'; ?>" method="POST">
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
                            $categories = $habitController->getAllCategories();
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle frequency options display
    const frequencySelect = document.getElementById('habitFrequency');
    if(frequencySelect) {
        frequencySelect.addEventListener('change', function() {
            const frequencyType = this.value;
            const weeklyOptions = document.getElementById('weeklyOptions');
            const monthlyOptions = document.getElementById('monthlyOptions');
            const customOptions = document.getElementById('customOptions');
            
            // Hide all options first
            weeklyOptions.style.display = 'none';
            monthlyOptions.style.display = 'none';
            customOptions.style.display = 'none';
            
            // Show the selected option
            if(frequencyType === 'weekly') {
                weeklyOptions.style.display = 'block';
            } else if(frequencyType === 'monthly') {
                monthlyOptions.style.display = 'block';
            } else if(frequencyType === 'custom') {
                customOptions.style.display = 'block';
            }
        });
    }
});
</script>
