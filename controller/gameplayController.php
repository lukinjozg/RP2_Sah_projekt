<?php 

session_start();

require_once __DIR__ . '/../controller/gameController.php';

$controller = new GameController();

// Check if the request is made through AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Call the gameplay function directly
    $controller->gameplay();
}

?>