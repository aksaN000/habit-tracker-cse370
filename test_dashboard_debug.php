<?php
// Test dashboard XP calculations

require_once 'config/database.php';
require_once 'controllers/AuthController.php';
require_once 'utils/helpers.php';

session_start();

$authController = new AuthController();

if(!$authController->isLoggedIn()) {
    echo "Not logged in!<br>";
    exit;
}

$user = $authController->getLoggedInUser();

echo "<h3>Dashboard Debug Test</h3>";
echo "<p><strong>User:</strong> " . $user->username . "</p>";
echo "<p><strong>Level:</strong> " . $user->level . "</p>";
echo "<p><strong>Current XP:</strong> " . $user->current_xp . "</p>";

// Test level info function
$levelInfo = getLevelInfo($user->level, $GLOBALS['conn']);
echo "<p><strong>Level Info:</strong> " . print_r($levelInfo, true) . "</p>";

// Test next level XP
$nextLevelXP = getNextLevelXP($user->level, $GLOBALS['conn']);
echo "<p><strong>Next Level XP:</strong> " . $nextLevelXP . "</p>";

// Test XP progress calculation
$xpProgressPercentage = calculateXPProgress($user->current_xp, $user->level, $GLOBALS['conn']);
echo "<p><strong>XP Progress Percentage:</strong> " . $xpProgressPercentage . "%</p>";

// Test profile picture URL
$profileUrl = getProfileAvatarUrl($user->profile_picture, $user->username, 100, '../');
echo "<p><strong>Profile Picture URL:</strong> " . $profileUrl . "</p>";

// Test for dashboard-specific calculations
$xpForCurrentLevel = $user->current_xp - $levelInfo['xp_required'];
$xpNeededForNextLevel = $nextLevelXP - $levelInfo['xp_required'];

echo "<p><strong>XP for current level:</strong> " . $xpForCurrentLevel . "</p>";
echo "<p><strong>XP needed for next level:</strong> " . $xpNeededForNextLevel . "</p>";

echo "<h4>Test HTML Elements:</h4>";
echo '<div class="progress xp-progress" style="background-color: rgba(255, 255, 255, 0.3); height: 0.5rem; border-radius: 1rem;">';
echo '<div class="progress-bar bg-success" role="progressbar" style="width: ' . $xpProgressPercentage . '%;" aria-valuenow="' . $xpProgressPercentage . '" aria-valuemin="0" aria-valuemax="100"></div>';
echo '</div>';

echo '<br><img src="' . $profileUrl . '" alt="' . $user->username . '" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #ccc;">';
?>
