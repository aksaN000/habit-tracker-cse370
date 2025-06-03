<?php
// Quick dashboard verification
session_start();
require_once '../config/database.php';
require_once '../controllers/AuthController.php';
require_once '../utils/helpers.php';

echo "<h2>Dashboard Verification Test</h2>";

// Test 1: Check if user is logged in
$authController = new AuthController();
if(!$authController->isLoggedIn()) {
    echo "<div style='color: red;'>❌ User not logged in</div>";
    echo "<a href='../auth/login.php'>Login here</a>";
    exit;
}

$user = $authController->getLoggedInUser();
echo "<div style='color: green;'>✅ User logged in: " . $user->username . "</div>";

// Test 2: Check helper functions
echo "<h3>Helper Functions Test</h3>";

try {
    $levelInfo = getLevelInfo($user->level, $GLOBALS['conn']);
    echo "<div style='color: green;'>✅ getLevelInfo() works</div>";
    echo "Level Info: " . print_r($levelInfo, true) . "<br>";
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ getLevelInfo() error: " . $e->getMessage() . "</div>";
}

try {
    $nextLevelXP = getNextLevelXP($user->level, $GLOBALS['conn']);
    echo "<div style='color: green;'>✅ getNextLevelXP() works: " . $nextLevelXP . "</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ getNextLevelXP() error: " . $e->getMessage() . "</div>";
}

try {
    $xpProgressPercentage = calculateXPProgress($user->current_xp, $user->level, $GLOBALS['conn']);
    echo "<div style='color: green;'>✅ calculateXPProgress() works: " . $xpProgressPercentage . "%</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ calculateXPProgress() error: " . $e->getMessage() . "</div>";
}

// Test 3: Profile Avatar
echo "<h3>Profile Avatar Test</h3>";
$avatarUrl = getProfileAvatarUrl($user->profile_picture, $user->username, 100, '../');
echo "<div style='color: green;'>✅ Profile avatar URL: " . $avatarUrl . "</div>";
echo '<img src="' . $avatarUrl . '" style="width: 100px; height: 100px; border-radius: 50%; border: 2px solid blue;">';

// Test 4: XP Progress Bar HTML
echo "<h3>XP Progress Bar Test</h3>";
if(isset($xpProgressPercentage)) {
    $xpForCurrentLevel = $user->current_xp - $levelInfo['xp_required'];
    $xpNeededForNextLevel = $nextLevelXP - $levelInfo['xp_required'];

    echo '<div style="background: #333; padding: 20px; color: white; border-radius: 10px;">';
    if ($nextLevelXP) {
        echo '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;">';
        echo '<span>Level ' . $user->level . '</span>';
        echo '<span>Level ' . ($user->level + 1) . '</span>';
        echo '</div>';
        echo '<div class="progress xp-progress" style="height: 8px; background: rgba(255,255,255,0.3); border-radius: 10px;">';
        echo '<div class="progress-bar bg-success" style="width: ' . $xpProgressPercentage . '%; height: 100%; background: #198754; border-radius: 10px;"></div>';
        echo '</div>';
        echo '<div style="margin-top: 5px; opacity: 0.7;">';
        echo '<small>' . $xpForCurrentLevel . ' / ' . $xpNeededForNextLevel . ' XP to next level</small>';
        echo '</div>';
    } else {
        echo '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;">';
        echo '<span>Level ' . $user->level . '</span>';
        echo '<span>Max Level</span>';
        echo '</div>';
        echo '<div class="progress xp-progress" style="height: 8px; background: rgba(255,255,255,0.3); border-radius: 10px;">';
        echo '<div class="progress-bar bg-success" style="width: 100%; height: 100%; background: #198754; border-radius: 10px;"></div>';
        echo '</div>';
        echo '<div style="margin-top: 5px; opacity: 0.7;">';
        echo '<small>Maximum level reached!</small>';
        echo '</div>';
    }
    echo '</div>';
}

echo "<br><h3>Check Dashboard Now</h3>";
echo '<a href="dashboard.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Dashboard</a>';
?>
