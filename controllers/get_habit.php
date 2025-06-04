<?php
// controllers/get_habit.php - Get habit data for editing

session_start();
require_once '../config/database.php';
require_once 'HabitController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if habit ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Habit ID is required']);
    exit();
}

$habit_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

try {
    // Create habit controller instance
    $habitController = new HabitController();
    
    // Create a habit instance to fetch data
    require_once '../models/Habit.php';
    $habit = new Habit($conn);
    
    // Get habit by ID
    if ($habit->getHabitById($habit_id)) {
        // Check if the habit belongs to the current user
        if ($habit->user_id != $user_id) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access to habit']);
            exit();
        }
        
        // Get all categories for the dropdown
        $categories = $habitController->getAllCategories();
        
        // Return habit data
        echo json_encode([
            'success' => true,
            'habit' => [
                'id' => $habit->id,
                'title' => $habit->title,
                'description' => $habit->description,
                'category_id' => $habit->category_id,
                'xp_reward' => $habit->xp_reward,
                'frequency_type' => $habit->frequency_type,
                'frequency_value' => $habit->frequency_value,
                'start_date' => $habit->start_date,
                'end_date' => $habit->end_date
            ],
            'categories' => $categories
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Habit not found']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
