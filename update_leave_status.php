<?php
include('includes/config.php');
use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $did = $_POST['leaveid'];
    $status = $_POST['status'];
    $description = isset($_POST['description']) ? $_POST['description'] : "";
    $admremarkdate = date('Y-m-d G:i:s', strtotime("now"));

    // Prepare the SQL query to update the leave
    $sql = "UPDATE tblleaves SET AdminRemark = ?, Status = ?, AdminRemarkDate = ? WHERE id = ?";

    // Create a prepared statement
    if ($stmt = mysqli_prepare($connect, $sql)) {
        // Bind the parameters to the query
        mysqli_stmt_bind_param($stmt, "sisi", $description, $status, $admremarkdate, $did);

        // Execute the update query
        if (mysqli_stmt_execute($stmt)) {
            $msg = "";

            // Check if the status is Decline (3) or Cancel (4)
            if ($status == 3 || $status == 4) {
                // Fetch leave details
                $fetchQuery = "SELECT LeaveType, empid, days_applied FROM tblleaves WHERE id = ?";
                if ($fetchStmt = mysqli_prepare($connect, $fetchQuery)) {
                    mysqli_stmt_bind_param($fetchStmt, "i", $did);
                    mysqli_stmt_execute($fetchStmt);
                    $result = mysqli_stmt_get_result($fetchStmt);

                    if ($row = mysqli_fetch_assoc($result)) {
                        $leaveType = $row['LeaveType'];
                        $empid = $row['empid'];
                        $daysApplied = intval($row['days_applied']); // Ensure it's an integer

                        if ($daysApplied > 0) {
                            $updateBalanceQuery = $leaveType === 'Sick'
                                ? "UPDATE employee_leaves SET sick_leave_remaining = sick_leave_remaining + ? WHERE empid = ?"
                                : ($leaveType === 'Annual'
                                    ? "UPDATE employee_leaves SET annual_leave_remaining = annual_leave_remaining + ? WHERE empid = ?"
                                    : null);

                            if ($updateBalanceQuery) {
                                if ($balanceStmt = mysqli_prepare($connect, $updateBalanceQuery)) {
                                    mysqli_stmt_bind_param($balanceStmt, "ii", $daysApplied, $empid);
                                    if (mysqli_stmt_execute($balanceStmt)) {
                                        $msg .= " Leave updated successfully.";
                                    } else {
                                        echo json_encode(['success' => false, 'message' => "Error updating leave balance: " . mysqli_error($connect)]);
                                        exit;
                                    }
                                    mysqli_stmt_close($balanceStmt);
                                }
                            }
                        } else {
                            echo json_encode(['success' => false, 'message' => "Error: Invalid days applied."]);
                            exit;
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => "Error: Leave record not found."]);
                        exit;
                    }
                    mysqli_stmt_close($fetchStmt);
                } else {
                    echo json_encode(['success' => false, 'message' => "Error fetching leave details: " . mysqli_error($connect)]);
                    exit;
                }
            }

           

            // Fetch user details
            $fetchUserQuery = "SELECT email, fullname FROM user WHERE id = (SELECT empid FROM tblleaves WHERE id = ?)";
            if ($userStmt = mysqli_prepare($connect, $fetchUserQuery)) {
                mysqli_stmt_bind_param($userStmt, "i", $did);
                mysqli_stmt_execute($userStmt);
                $userResult = mysqli_stmt_get_result($userStmt);

                if ($user = mysqli_fetch_assoc($userResult)) {
                    $userEmail = $user['email'];
                    $userName = $user['fullname'];

                    require_once "PHPMailer/PHPMailer.php";
                    require_once "PHPMailer/SMTP.php";
                    require_once "PHPMailer/Exception.php";

                    $mail = new PHPMailer(true);
                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host = "mail.itschatters.com";
                        $mail->SMTPAuth = true;
                        $mail->Username = "webmaster@itschatters.com";
                        $mail->Password = 'Webmaster@itschatter';
                        $mail->Port = 465;
                        $mail->SMTPSecure = "ssl";
                        $mail->isHTML(true);
                        $mail->setFrom('webmaster@itschatters.com', 'Leave Status');
                        $mail->addAddress($userEmail, $userName);

                        // Email content
                        $mail->Subject = 'Leave Request Status';
                        $mail->Body = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; color: #333; background-color: #f4f4f4; padding: 20px; }
                                .email-container { max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #ddd; padding: 20px; border-radius: 8px; }
                                .header { text-align: center; padding-bottom: 20px; }
                                .header h1 { color: #0056b3; font-size: 24px; margin: 0; }
                                .content { font-size: 16px; line-height: 1.6; }
                                .footer { margin-top: 20px; font-size: 14px; text-align: center; color: #777; }
                                .footer a { color: #0056b3; text-decoration: none; }
                                .signature { margin-top: 30px; font-size: 14px; font-style: italic; color: #555; }
                                .img { height: 100px; }
                            </style>
                        </head>
                        <body>
                            <div class='email-container'>
                                <div class='header'>
                                    <h1>Leave Request Status Update</h1>
                                </div>
                                <div class='content'>
                                    <p>Dear $userName,</p>";
                        if ($status == 3) {
                            $mail->Body .= "<p style='color: red;'>Your leave request has been declined.</p>";
                        } elseif ($status == 4) {
                            $mail->Body .= "<p style='color: orange;'>Your leave request has been canceled.</p>";
                        } elseif ($status == 1) {
                            $mail->Body .= "<p style='color: green;'>Your leave request has been approved.</p>";
                        } elseif ($status == 0) {
                            $mail->Body .= "<p style='color: blue;'>Your leave request has been pending.</p>";
                        }
                        $mail->Body .= "
                                </div>
                                <div class='footer'>
                                    <p>If you have any questions, please contact us.</p>
                                </div>
                                <div class='signature'>
                                    <p>Best regards,</p>
                                    <p><strong>The Leave Management Team</strong><br>
                                    <img src='https://shjinternational.com/wp-content/uploads/2022/10/AWSA-GLOBAL-BW-01-2-1.png' class='img'>
                                </div>
                            </div>
                        </body>
                        </html>";

                        // Send the email
                        $mail->send();
                        echo json_encode(['success' => true, 'message' => 'Email has been sent to the user.']);
                    } catch (Exception $e) {
                        echo json_encode(['success' => false, 'message' => "Error sending email: {$mail->ErrorInfo}"]);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error: User not found.']);
                }
                mysqli_stmt_close($userStmt);
            } else {
                echo json_encode(['success' => false, 'message' => "Error fetching user details: " . mysqli_error($connect)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => "Error: Could not execute query. " . mysqli_error($connect)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Error: Could not prepare the query. " . mysqli_error($connect)]);
    }
}


    // Prepare the query to update the leave status
    // $sql = "UPDATE tblleaves SET Status = ? WHERE id = ?";
    // if ($stmt = mysqli_prepare($connect, $sql)) {
    //     mysqli_stmt_bind_param($stmt, "ii", $status, $leaveid);
        
    //     if (mysqli_stmt_execute($stmt)) {
    //         // Respond back with success message
    //         echo json_encode(['success' => true]);
    //     } else {
    //         // Error response
    //         echo json_encode(['success' => false, 'message' => 'Failed to update the status.']);
    //     }

    //     mysqli_stmt_close($stmt);
    // } else {
    //     echo json_encode(['success' => false, 'message' => 'Query preparation failed.']);
    // }
// }
?>
