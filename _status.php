<?php
session_start();
include('includes/config.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $column = $_POST['column'];
    $value = $_POST['value'];

    // Validate the column name to prevent SQL injection
    $validColumns = ['sick_leave_remaining', 'annual_leave_remaining'];
    if (!in_array($column, $validColumns)) {
        echo json_encode(['success' => false, 'message' => 'Invalid column']);
        exit;
    }

    // Prepare the SQL statement
    $sql = "UPDATE employee_leaves SET $column = ? WHERE empid = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param('si', $value, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }

    $stmt->close();
    $connect->close();
}
?>
