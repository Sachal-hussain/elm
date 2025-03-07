<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
    {   
header('location:index.php');
}

 ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Admin | Manage Employees</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        <meta name="author" content="Naeem Amin" />
        <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
        <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
        <link href="assets/plugins/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="assets/css/custom.css" rel="stylesheet" type="text/css"/>
       <style>
        select{
            display: block !important;
        }
       </style>
    </head>
    <body>
       <?php include('includes/header.php'); ?>
       <?php include('includes/sidebar.php'); ?>
            <main class="mn-inner">
                <div class="row">
                    <div class="col s12">
                        <div class="page-title">Manage Employees</div>
                    </div>
                    <div class="col s12 m12 l12">
                        <div class="card">
                            <div class="card-content">
                                <span class="card-title">Employees Info</span>
                                <?php if($msg){?><div class="succWrap"><strong>SUCCESS</strong> : <?php echo htmlentities($msg); ?> </div><?php }?>
                                <table id="example" class="display responsive-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Agent ID</th>
                                            <th>Full Name</th>
                                            <th>Shift</th>
                                            <th>Sick</th>
                                            <th>Annual</th>
                                            <th>Action</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $sql = "SELECT user.id AS userid, user.agentid, user.fullname, user.shift, employee_leaves.sick_leave_remaining, employee_leaves.annual_leave_remaining, employee_leaves.id AS emp_id
                                                FROM user
                                                LEFT JOIN employee_leaves 
                                                ON employee_leaves.empid = user.id
                                                WHERE user.status='Active' AND user.department IN ('Redeem', 'Live Chat', 'Q&A', 'Designer', 'Social Media', 'IT', 'Developer', 'HR') ORDER BY user.fullname ASC";
                                        $result = $connect->query($sql);

                                        $cnt = 1;
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) { ?>
                                                <tr>
                                                    <td> <?php echo htmlentities($cnt); ?></td>
                                                    <td><?php echo htmlentities($row['agentid']); ?></td>
                                                    <td><a href="leavehistory.php?empid=<?php echo htmlentities($row['userid']); ?>"> <?php echo htmlentities($row['fullname']); ?></a></td>
                                                    <td><?php echo htmlentities($row['shift']); ?></td>
                                                    <!-- Making Sick and Annual editable -->
                                                    <td contenteditable="false" class="sick" data-empid="<?php echo $row['userid']; ?>"><?php echo htmlentities($row['sick_leave_remaining']); ?></td>
                                                    <td contenteditable="false" class="annual" data-empid="<?php echo $row['userid']; ?>"><?php echo htmlentities($row['annual_leave_remaining']); ?></td>
                                                    <td>
                                                        <button class="btn btn-primary edit-btn">Edit</button>
                                                        <button class="save-btn" style="display: none;">&#10004;</button> <!-- Tick button for save -->
                                                    </td> 
                                                </tr>
                                            <?php 
                                                $cnt++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='6'>No records found</td></tr>";
                                        }
                                        $connect->close();
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

        <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="assets/plugins/materialize/js/materialize.min.js"></script>
        <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
        <script src="assets/plugins/datatables/js/jquery.dataTables.min.js"></script>
        <script src="assets/js/alpha.min.js"></script>
        

       <!--  <script type="text/javascript">
            $(document).ready(function() {
                // Initialize DataTable
                var table = $('#example').DataTable({
                    "paging": true,
                    "lengthChange": false,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    "drawCallback": function(settings) {
                        // Re-enable contenteditable for each row after table is redrawn
                        $('.sick, .annual').on('blur', function() {
                            var emp_id = $(this).data('empid');
                            var sick = $(this).closest('tr').find('.sick').text();
                            var annual = $(this).closest('tr').find('.annual').text();
                            
                            // Call the saveChanges function
                            saveChanges(emp_id, sick, annual);
                        });
                    }
                });
            
                // Function to save the changes in editable cells
                function saveChanges(emp_id, sick, annual) {
                    $.ajax({
                        url: "update_employee.php",
                        method: "POST",
                        data: {
                            emp_id: emp_id,
                            sick: sick,
                            annual: annual
                        },
                        success: function(response) {
                            alert("Data updated successfully");
                        }
                    });
                }

                // Listen for changes in the editable cells
                $('.sick, .annual').on('blur', function() {
                    var emp_id = $(this).data('empid');
                    var sick = $(this).closest('tr').find('.sick').text();
                    var annual = $(this).closest('tr').find('.annual').text();
                    
                    // Call the saveChanges function
                    saveChanges(emp_id, sick, annual);
                });
            });

        </script> -->

        <script type="text/javascript">
            $(document).ready(function() {
                // Initialize DataTable
                var table = $('#example').DataTable({
                    "paging": true,
                    "lengthChange": true,
                    "lengthMenu": [10, 25, 50, 100], // Rows selection option
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                     
                                });

                // Function to save the changes in editable cells
                function saveChanges(emp_id, sick, annual) {
                    $.ajax({
                        url: "update_employee.php",
                        method: "POST",
                        data: {
                            emp_id: emp_id,
                            sick: sick,
                            annual: annual
                        },
                        success: function(response) {
                            alert("Data updated successfully");
                        },
                        error: function() {
                            alert("Error updating data");
                        }
                    });
                }

                // Listen for "Edit" button click
                $('#example').on('click', '.edit-btn', function() {
                    var row = $(this).closest('tr');
                    row.find('.sick, .annual').attr('contenteditable', 'true'); // Make sick and annual editable
                    row.find('.edit-btn').hide(); // Hide the "Edit" button
                    row.find('.save-btn').show(); // Show the "tick" button
                });

                // Listen for "Save" (tick) button click
                $('#example').on('click', '.save-btn', function() {
                    var row = $(this).closest('tr');
                    var emp_id = row.find('.sick').data('empid');
                    var sick = row.find('.sick').text();
                    var annual = row.find('.annual').text();

                    // Call the saveChanges function
                    saveChanges(emp_id, sick, annual);

                    row.find('.sick, .annual').attr('contenteditable', 'false'); // Make sick and annual non-editable again
                    row.find('.edit-btn').show(); // Show the "Edit" button
                    row.find('.save-btn').hide(); // Hide the "tick" button
                });
            });
        </script>
    </body>
</html>

