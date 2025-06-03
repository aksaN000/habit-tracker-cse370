<?php
// Quick debug for dashboard issues
session_start();
require_once 'config/database.php';
require_once 'controllers/AuthController.php';
require_once 'utils/helpers.php';

$authController = new AuthController();
if(!$authController->isLoggedIn()) {
    echo "Not logged in<br>";
    exit;
}

$user = $authController->getLoggedInUser();
echo "<h3>Dashboard Debug</h3>";
echo "User: " . $user->username . "<br>";
echo "Level: " . $user->level . "<br>";
echo "Current XP: " . $user->current_xp . "<br>";
echo "Profile Picture: " . ($user->profile_picture ? $user->profile_picture : 'NULL') . "<br>";

// Test XP calculations
$levelInfo = getLevelInfo($user->level, $GLOBALS['conn']);
$nextLevelXP = getNextLevelXP($user->level, $GLOBALS['conn']);
$xpProgressPercentage = calculateXPProgress($user->current_xp, $user->level, $GLOBALS['conn']);

echo "<br><strong>XP Calculations:</strong><br>";
echo "Level Info: " . print_r($levelInfo, true) . "<br>";
echo "Next Level XP: " . $nextLevelXP . "<br>";
echo "XP Progress %: " . $xpProgressPercentage . "<br>";

// Test profile avatar URL
$avatarUrl = getProfileAvatarUrl($user->profile_picture, $user->username, 100, '../');
echo "<br><strong>Avatar URL:</strong> " . $avatarUrl . "<br>";

echo "<br><strong>Profile Avatar Test:</strong><br>";
echo '<img src="' . $avatarUrl . '" alt="Avatar" style="width: 100px; height: 100px; border-radius: 50%; border: 2px solid red;">';
?>
