<?php
session_start();
include('includes/config.php');

// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emp_id = $_POST['emp_id'];
    $sick = $_POST['sick'];
    $annual = $_POST['annual'];

    // // Validate the inputs
    // if (!empty($emp_id) && is_numeric($sick) && is_numeric($annual)) {
    //     // Update the database
    //     $stmt = $connect->prepare("UPDATE employee_leaves SET sick_leave_remaining = ?, annual_leave_remaining = ? WHERE empid = ?");
    //     $stmt->bind_param("dii", $sick, $annual, $emp_id);

    //     if ($stmt->execute()) {
    //         echo json_encode(["status" => "success"]);
    //     } else {
    //         echo json_encode(["status" => "error", "message" => "Failed to update data"]);
    //     }

    //     $stmt->close();
    // } else {
    //     echo json_encode(["status" => "error", "message" => "Invalid data"]);
    // }

    // $connect->close();

    if (!empty($emp_id)) {
        // Check if the employee ID exists in the database
        $stmt = $connect->prepare("SELECT COUNT(*) FROM employee_leaves WHERE empid = ?");
        $stmt->bind_param("i", $emp_id);
        $stmt->execute();
        $stmt->bind_result($emp_exists);
        $stmt->fetch();
        $stmt->close();
        // print_r($emp_exists);
        // exit;
        if ($emp_exists > 0) {
            // Employee exists, update the record
            $stmt = $connect->prepare("UPDATE employee_leaves SET sick_leave_remaining = ?, annual_leave_remaining = ? WHERE empid = ?");
            $stmt->bind_param("dii", $sick, $annual, $emp_id);

            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Record updated successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to update data"]);
            }
            $stmt->close();
        } else {
            // Employee does not exist, insert a new record
            $stmt = $connect->prepare("INSERT INTO employee_leaves (empid, sick_leave_remaining, annual_leave_remaining) VALUES (?, ?, ?)");
            $stmt->bind_param("dii", $emp_id, $sick, $annual);

            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Record inserted successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to insert data"]);
            }
            $stmt->close();
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid data"]);
    }

    $connect->close();
}
?>
