<?php
// controllers/get_user_progress.php - Get current user progress data

session_start();
header('Content-Type: application/json');

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get current user data
    $query = "SELECT current_xp, level FROM users WHERE id = :user_id LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    $current_xp = $user['current_xp'];
    $current_level = $user['level'];
    
    // Get current and next level information
    $level_info = getLevelInfo($current_level, $conn);
    $next_level_xp = getNextLevelXP($current_level, $conn);
    
    // Calculate progress
    if ($next_level_xp) {
        $current_level_xp = $level_info['xp_required'];
        $xp_for_current_level = $current_xp - $current_level_xp;
        $xp_needed_for_next_level = $next_level_xp - $current_level_xp;
        $progress_percentage = min(100, max(0, ($xp_for_current_level / $xp_needed_for_next_level) * 100));
        $next_level = $current_level + 1;
    } else {
        // Max level reached
        $progress_percentage = 100;
        $xp_for_current_level = $current_xp;
        $xp_needed_for_next_level = $current_xp;
        $next_level = $current_level;
        $next_level_xp = $current_xp;
    }
    
    echo json_encode([
        'success' => true,
        'current_xp' => $xp_for_current_level,
        'next_level_xp' => $xp_needed_for_next_level,
        'total_xp' => $current_xp,
        'current_level' => $current_level,
        'next_level' => $next_level,
        'percentage' => round($progress_percentage, 1)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching user progress: ' . $e->getMessage()
    ]);
}
?>
