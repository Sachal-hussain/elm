<?php
session_start();
error_reporting(E_ALL); // For development, set this to E_ALL, but change to E_NONE in production
include('includes/config.php');

// Check if the user is logged in, else redirect to login page
if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit; // Make sure no further code is executed after redirect
}
?>

<?php

$leavetype='';
            $query = mysqli_query($connect, "SELECT  LeaveType from tblleavetype");
            if(mysqli_num_rows($query) > 0){
                while($row=mysqli_fetch_array($query)){
                    $LeaveType=$row['LeaveType'];
                    $leavetype.='<option value="'.$LeaveType.'">'.$LeaveType.'</option>';
                  
                }
            }
        ?>

<?php
            $remaining_leaves='';
            $query = mysqli_query($connect, "SELECT * FROM employee_leaves
                WHERE empid='';
                    ");
            if(mysqli_num_rows($query) > 0){
                while($row=mysqli_fetch_array($query)){
                    $id=$row['id'];
                    $sick=$row['sick_leave_remaining'];
                    $annual=$row['annual_leave_remaining'];
                    $remaining_leaves.='
                        <tr>
                            <td>'.$sick.'</td>
                            <td>'.$annual.'</td>
                        </tr>
                    ';
                  
                }
            } 
        ?>

        <?php

// Handle form submission
if (isset($_POST['apply'])) {
    // Sanitize input values to prevent SQL Injection
    $leavetype = mysqli_real_escape_string($connect, $_POST['leavetype']);
    $daterange = mysqli_real_escape_string($connect, $_POST['daterange']); // This should be a comma-separated string
    $description = mysqli_real_escape_string($connect, $_POST['description']);
    $emp_id = mysqli_real_escape_string($connect, $_POST['employee_id']); // Get selected employee id
     $isread = 0;
     $created_at = date('Y-m-d H:i:s');


    // Split the comma-separated dates into an array
    $selectedDates = explode(',', $daterange);
    $days_applied = count($selectedDates); // Get the number of dates

    $balanceCheckQuery = "SELECT sick_leave_remaining, annual_leave_remaining FROM employee_leaves WHERE empid = ?";
    $stmtCheck = $connect->prepare($balanceCheckQuery);
    $stmtCheck->bind_param('i', $emp_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 0) {
        $defaultSickLeave = 7;
        $defaultAnnualLeave = 12;
        $insertLeaveQuery = "INSERT INTO employee_leaves (empid, sick_leave_remaining, annual_leave_remaining) VALUES (?, ?, ?)";
        $stmtInsert = $connect->prepare($insertLeaveQuery);
        $stmtInsert->bind_param('iii', $emp_id, $defaultSickLeave, $defaultAnnualLeave);
        $stmtInsert->execute();
        $stmtInsert->close();

        // Re-fetch leave balance
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
    }

    $leaveBalance = $resultCheck->fetch_assoc();

    // Validate leave balance
    if (($leavetype === 'Sick' && $leaveBalance['sick_leave_remaining'] < $days_applied) || 
        ($leavetype === 'Annual' && $leaveBalance['annual_leave_remaining'] < $days_applied)) {
        $errorMessage = $leavetype === 'Sick' ? "Sick Leave" : "Annual Leave";
        echo "insuffiecient balance ";
        exit();
    }


    // Insert leave application into the database for each date
    foreach ($selectedDates as $date) {
        // Clean up each date by trimming any spaces
        
        $days_applied1 = 1;
        // Insert the leave into the database
        $query = "INSERT INTO tblleaves (leaveType, FromDate, days_applied, Description, empid, status, PostingDate) 
                  VALUES ('$leavetype', '$date', '$days_applied1',  '$description', '$emp_id', '0', '$created_at')";

        // Execute the query and check if it was successful
        if (mysqli_query($connect, $query)) {
            $msg="Leave applied successfully";
        } else {
            $error="Something went wrong. Please try again";
        }
    }
}

if (in_array($leavetype, ['Sick', 'Annual'])) {
        $column = $leavetype === 'Sick' ? "sick_leave_remaining" : "annual_leave_remaining";
        $updateBalanceQuery = "UPDATE employee_leaves SET $column = $column - ? WHERE empid = ?";
        $stmtUpdate = $connect->prepare($updateBalanceQuery);
        $stmtUpdate->bind_param('ii', $days_applied, $emp_id);
        $stmtUpdate->execute();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Employee | Apply Leave</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta charset="UTF-8">
    <meta name="description" content="Responsive Admin Dashboard Template" />
    <meta name="keywords" content="admin,dashboard" />
    <meta name="author" content="Steelcoders" />
    
    <!-- Styles -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet"> 
    <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/custom.css" rel="stylesheet" type="text/css"/>


    <style>
        .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #dd3d36;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        .succWrap{
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #5cb85c;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <main class="mn-inner">
        <div class="row">
            <div class="col s12">
                <div class="page-title">Apply for Leave</div>
            </div>
            <div class="col s12 m12 l8">
                <div class="card">
                    <div class="card-content">
                        <form id="example-form" method="post" name="addemp">
                            <div>
                                <h3>Apply for Leave</h3>
                                <section>
                                    <div class="wizard-content">
                                        <div class="row">
                                            <div class="col m12">
                                                <div class="row">
                                                    <div class="input-field col s12">
                                                        <select name="employee_id" autocomplete="off" required>
                                                            <option value="">Select Employee</option>
                                                            <?php
                                                            $sql = "SELECT employee_leaves.empid, user.agentid, user.fullname, user.shift, employee_leaves.sick_leave_remaining, employee_leaves.annual_leave_remaining
                                                                    FROM user
                                                                    LEFT JOIN employee_leaves 
                                                                    ON employee_leaves.empid = user.id
                                                                    WHERE user.status='Active' AND user.department IN ('Redeem', 'Live Chat', 'Q&A', 'Designer', 'Social Media', 'IT', 'Developer', 'HR')
                                                                    ORDER BY user.fullname ASC";
                                                            $query = mysqli_query($connect, $sql);

                                                            if (mysqli_num_rows($query) > 0) {
                                                                while ($row = mysqli_fetch_assoc($query)) {
                                                                    echo '<option value="' . htmlentities($row['empid']) . '">' . htmlentities($row['fullname']) . '</option>';
                                                                }
                                                            } else {
                                                                echo '<option value="">No active users found</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <div class="input-field col s12">
                                                        <select name="leavetype" autocomplete="off" required>
                                                            <option value="">Select leave type...</option>
                                                            <option value="Sick">Sick</option>
                                                            <option value="Annual">Annual</option>
                                                        </select>
                                                    </div>

                                                     <div class="col m12 s12">
                                                        <div class="input-field col m12 s12">
                                                            <label for="dateRange">Select Date Range</label>
                                                            <input type="text" id="dateRange" name="daterange" class="masked" required>
                                                        </div>
                                                    </div>

                                                    <div class="input-field col m12 s12">
                                                        <label for="description">Description</label>
                                                        <textarea id="textarea1" name="description" class="materialize-textarea" length="500" required></textarea>
                                                    </div>
                                                </div>
                                                <button type="submit" name="apply" id="apply" class="waves-effect waves-light btn indigo m-b-xs">Apply</button>                                             
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Javascripts -->
    <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
    <script src="assets/plugins/materialize/js/materialize.min.js"></script>
    <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
    <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
    <script src="assets/js/alpha.min.js"></script>
    <script src="assets/js/pages/form_elements.js"></script>
    <script src="assets/js/pages/form-input-mask.js"></script>
    <script src="assets/plugins/jquery-inputmask/jquery.inputmask.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#dateRange", {
            mode: "multiple", // This allows selecting a range of dates
            dateFormat: "Y-m-d", // This formats the date as Year-Month-Day
            onChange: function(selectedDates, dateStr, instance) {
                // Optionally, do something with the selected date range
                console.log("Selected date range: ", selectedDates);
            }
        });
    </script>
</body>
</html>
