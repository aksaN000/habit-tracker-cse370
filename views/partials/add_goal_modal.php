<?php
/**
 * Reusable Add Goal Modal Component
 */
?>

<!-- Add Goal Modal -->
<div class="modal fade" id="addGoalModal" tabindex="-1" aria-labelledby="addGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="addGoalModalLabel">Add New Goal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '../controllers/process_add_goal.php' : 'controllers/process_add_goal.php'; ?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="goalTitle" class="form-label">Goal Title</label>
                        <input type="text" class="form-control" id="goalTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="goalDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="goalDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="goalTarget" class="form-label">Target Value</label>
                        <input type="number" class="form-control" id="goalTarget" name="target_value" min="1" value="1" required>
                        <div class="form-text">Set a numeric target to measure your progress.</div>
                    </div>
                    <div class="mb-3">
                        <label for="goalStartDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="goalStartDate" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="goalEndDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="goalEndDate" name="end_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="goalXP" class="form-label">XP Reward</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="goalXP" name="xp_reward" min="1" value="50">
                            <span class="input-group-text">XP</span>
                        </div>
                        <div class="form-text">XP earned when completing this goal</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Add Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>
