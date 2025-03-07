<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
    {   
header('location:index.php');
}
else{
    if (isset($_POST['update'])) { 
        $did = intval($_POST['leaveid']);
        $description = $_POST['description'];
        $status = intval($_POST['status']);   
        
        // Set the timezone
        $admremarkdate = date('Y-m-d G:i:s', strtotime("now"));
      
        // Prepare the SQL query to update the leave
        $sql = "UPDATE tblleaves SET AdminRemark = ?, Status = ?, AdminRemarkDate = ? WHERE id = ?";
      
        // Create a prepared statement
        if ($stmt = mysqli_prepare($connect, $sql)) {
          // Bind the parameters to the query
          mysqli_stmt_bind_param($stmt, "sisi", $description, $status, $admremarkdate, $did);
      
          // Execute the update query
          if (mysqli_stmt_execute($stmt)) {
              $msg = "Leave updated successfully";
      
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
                                                $msg .= " Error updating leave balance: " . mysqli_error($connect);
                                            }
                                            mysqli_stmt_close($balanceStmt);
                                        }
                                    }
                                } else {
                                    $msg .= " Error: Invalid days applied.";
                                }
                            } else {
                                $msg .= " Error: Leave record not found.";
                            }
                            mysqli_stmt_close($fetchStmt);
                        
                    } else {
                      $msg .= " Error fetching leave details: " . mysqli_error($connect);
                  }
              }
          } else {
              $msg = "Error: Could not execute query. " . mysqli_error($connect);
          }
      
        } else {
            $msg = "Error: Could not prepare the query. " . mysqli_error($connect);
        }
      }


 ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        
        <!-- Title -->
        <title>Admin | Pending Leave leaves </title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        <meta name="author" content="Naeem Amin" />
        <!-- Styles -->
      
    <!-- Include required CSS & JS links -->
    <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    
    <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
   
       
        
    </head>
     <style>
        /* Default button styles */
button {
    padding: 10px 20px;
    font-size: 16px;
    border: solid;
    cursor: pointer;
    opacity: 0.5;
}

/* Enable button styles */
button:enabled {
    opacity: 1;
}

/* Approve button color */
#approveBtn:enabled {
    background-color: green;
    color: white;
}

/* Decline button color */
#declineBtn:enabled {
    background-color: orange;
    color: white;
}

/* Cancel button color */
#cancelBtn:enabled {
    background-color: red;
    color: white;
}
</style>
    <body>
       <?php include('includes/header.php');?>
            
       <?php include('includes/sidebar.php');?>
            <main class="mn-inner">
                <div class="row">
                    <div class="col s12">
                        <div class="page-title">Pending Leave History</div>
                    </div>
                   
                    <div class="col s12 m12 l12">
                        <div class="card">
                            <div class="card-content">
                                <span class="card-title">Leave History</span>
                                <?php if($msg){?><div class="succWrap"><strong>SUCCESS</strong> : <?php echo htmlentities($msg); ?> </div><?php }?>
                                 <div class="buttons-container">
                                        <button id="approveBtn" disabled>Approve</button>
                                        <button id="declineBtn" disabled>Decline</button>
                                        <button id="cancelBtn" disabled>Cancel</button>
                                    </div>
                                <table id="example" class="display responsive-table ">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAll"></th>
                                            <th>#</th>
                                            <th>Employe Name</th>
                                                <th>Leave Type</th>
                                                <th>Date Range</th>
                                                 <th>Requesting Date</th>
                                                 <th>Reason</th>
                                                 <th>Remarks</th>                 
                                                <th>Status</th>
                                                <th>Documents</th>
                                            <!-- <th align="center">Action</th> -->
                                        </tr>
                                    </thead>
                                 
                                   <tbody>
                                        <?php
                                        // Assuming you have a database connection in $connect
                                        $status = 0; // Pending status
                                        $sql = "SELECT tblleaves.id as lid, 
                                                           user.fullname, 
                                                           user.id, 
                                                           tblleaves.LeaveType,
                                                           tblleaves.FromDate,
                                                           tblleaves.Description,
                                                           tblleaves.PostingDate, 
                                                           tblleaves.AdminRemark,
                                                           tblleaves.Status,
                                                           tblleaves.documents
                                                FROM tblleaves 
                                                JOIN user ON tblleaves.empid = user.id 
                                                WHERE tblleaves.Status = ? 
                                                ORDER BY lid DESC";

                                        // Prepare the statement
                                        $stmt = $connect->prepare($sql);
                                        if ($stmt) {
                                            // Bind the parameter
                                            $stmt->bind_param('i', $status); // 'i' indicates the parameter is an integer
                                            $stmt->execute();
                                            $result = $stmt->get_result();

                                            $cnt = 1;
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    $leaveType = $row['LeaveType'];
                                                    $fromDates = explode(',', $row['FromDate']);  // Split the dates by comma
                                                    $formattedFromDates = [];
                                                        foreach ($fromDates as $date) {
                                                            $formattedFromDates[] = date('d-m-Y', strtotime(trim($date)));  // Format each date
                                                        }
                                                    $FromDate = implode(', ', $formattedFromDates); 
                                                    $postingDate = date('m/d/y', strtotime($row['PostingDate']));
                                                    $Description = $row['Description'];
                                                    $AdminRemark = $row['AdminRemark'];
                                                    $status = $row['Status'];
                                                    $documents = $row['documents'];
                                                    ?>
                                                    <tr data-leave-id="<?php echo $row['lid']; ?>">
                                                       
                                                        <td><input type="checkbox" class="leave-checkbox"></td>
                                                        <td><b><?php echo htmlentities($cnt); ?></b></td>
                                                        <td>
                                                            <a href="leavehistory.php?empid=<?php echo htmlentities($row['id']); ?>">
                                                            <?php echo htmlentities($row['fullname']); ?>
                                                            </a>
                                                        </td>
                                                        <td><?php echo htmlentities($leaveType); ?></td>
                                                        <td><?php echo htmlentities($FromDate); ?></td>
                                                        <td><?php echo htmlentities($postingDate); ?></td>
                                                        <td><?php echo htmlentities($Description); ?></td>
                                                        <td><?php echo htmlentities($AdminRemark); ?></td>
                                                        <td>
                                                            <?php
                                                            if ($row['Status'] == 1) {
                                                                echo '<span style="color: green">Approved</span>';
                                                            } elseif ($row['Status'] == 2) {
                                                                echo '<span style="color: red">Not Approved</span>';
                                                            }elseif ($row['Status'] == 3) {
                                                                echo '<span style="color: red">Declined</span>';
                                                            } 
                                                            else {
                                                                echo '<span style="color: blue">Pending</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <?php
                                                            $documentLink = $documents ? '<a href="/chatter2/assets/images/leaves/' . $documents . '" target="_blank">View Document</a>'
                                                            : '-'; 
                                                        ?>
                                                        <td><?php echo $documentLink; ?></td>
                                                        
                                                    </tr>
                                                    <?php
                                                    $cnt++;
                                                }
                                            } else {
                                                echo "<tr><td colspan='6'>No approved leaves found.</td></tr>";
                                            }
                                            $stmt->close();
                                        } else {
                                            echo "Error preparing the query: " . $connect->error;
                                        }
                                        ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
         
        </div>
        <div class="left-sidebar-hover"></div>
        
        <!-- Javascripts -->
        
        <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="assets/plugins/materialize/js/materialize.min.js"></script>
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script> 
<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            "paging": true,          // Enable pagination
            "searching": true,       // Enable searching
            "ordering": true,        // Enable sorting by default
            "info": true,            // Show info (number of entries)
            "lengthChange": true,    // Enable changing the number of rows per page
            "pageLength": 10,        // Set the initial number of rows per page
            "responsive": true       // Enable responsive table behavior for mobile devices
        });

        let selectedLeaveId = null;
        let selectedRow = null;

        // Main function to handle all actions
        function handleTableActions(action) {
            let isAnyChecked = $(".leave-checkbox:checked").length > 0;

            // Append a loader element and a message container to the page
            $("body").append('<div id="loader" style="display:none; position: fixed; top: 0; left: 50%; transform: translateX(-50%); background-color: rgba(0, 0, 0, 0.7); color: #fff; padding: 10px 20px; font-size: 16px; z-index: 9999;">Processing...</div>');
            $("#example").before('<div id="messageContainer" style="margin-bottom: 10px;"></div>');

            // Show the loader
            $("#loader").show();

            // Update button state
            if (isAnyChecked) {
                $("#approveBtn, #declineBtn, #cancelBtn").prop("disabled", false);
            } else {
                $("#approveBtn, #declineBtn, #cancelBtn").prop("disabled", true);
            }

            // Disable/Enable sorting
            let currentSettings = table.settings()[0];
            currentSettings.aoColumns.forEach(function(column) {
                column.bSortable = !isAnyChecked; // Disable sorting if any checkbox is checked
            });
            table.draw();

            if (action === 'approve' || action === 'decline' || action === 'cancel') {
                $(".leave-checkbox:checked").each(function() {
                    let row = $(this).closest("tr");
                    let leaveId = row.data('leave-id');
                    let status;
                    let statusText;
                    let statusColor;

                    // Set status values based on action
                    if (action === 'approve') {
                        status = 1;
                        statusText = 'Approved';
                        statusColor = 'green';
                    } else if (action === 'decline') {
                        status = 3;
                        statusText = 'Declined';
                        statusColor = 'red';
                    } else if (action === 'cancel') {
                        status = 4;
                        statusText = 'Cancelled';
                        statusColor = 'gray';
                    }

                    // Make the POST request
                    $.post("update_leave_status.php", { leaveid: leaveId, status: status }, function(response) {
                        // Hide the loader when processing is complete
                        $("#loader").hide();

                        if (response.success) {
                            // Show success message on the page before removing the row
                            showMessage(`Leave ${statusText} successfully!`);

                            // Remove the row from the table
                            table.row(row).remove().draw(); // Ensure the table is redrawn after removing the row

                            // Update the row content with status text (this part might not be needed if the row is being removed directly)
                            row.find("td:last").html(`<span style="color: ${statusColor}">${statusText}</span>`);
                            row.find("input[type='checkbox']").prop("disabled", true); // Disable checkbox after action
                        } else {
                            showMessage("Something went wrong. Please try again.");
                        }
                    }, "json");

                });
            }
        }

        // Function to display message
        function showMessage(message) {
            // Create a message element if it doesn't exist
            if ($("#messagePopup").length === 0) {
                $("body").append('<div id="messagePopup" style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #333; color: #fff; padding: 10px 20px; border-radius: 5px; display: none; z-index: 9999;"></div>');
            }

            // Set the message text
            $("#messagePopup").text(message);

            // Show the message
            $("#messagePopup").fadeIn();

            // Hide the message after 3 seconds
            setTimeout(function() {
                $("#messagePopup").fadeOut();
            }, 3000);
        }

        // Handle individual checkbox selection
        $(".leave-checkbox").change(function() {
            selectedRow = $(this).closest("tr");
            selectedLeaveId = selectedRow.data('leave-id');
            handleTableActions(); // Call the main function without action
        });

        // Handle "Select All" checkbox functionality
        $("#selectAll").change(function() {
            $(".leave-checkbox").prop("checked", $(this).prop("checked"));
            handleTableActions(); // Call the main function without action
        });

        // Approve button functionality
        $("#approveBtn").click(function() {
            handleTableActions('approve'); // Call with action 'approve'
        });

        // Decline button functionality
        $("#declineBtn").click(function() {
            handleTableActions('decline'); // Call with action 'decline'
        });

        // Cancel button functionality
        $("#cancelBtn").click(function() {
            handleTableActions('cancel'); // Call with action 'cancel'
        });
    });
</script>

<!-- <script>
                    $(document).ready(function() {
                        var table = $('#example').DataTable({
                            "paging": true,          // Enable pagination
                            "searching": true,       // Enable searching
                            "ordering": true,        // Enable sorting by default
                            "info": true,            // Show info (number of entries)
                            "lengthChange": true,    // Enable changing the number of rows per page
                            "pageLength": 10,        // Set the initial number of rows per page
                            "responsive": true       // Enable responsive table behavior for mobile devices
                        });

                        let selectedLeaveId = null;
                        let selectedRow = null;

                        // Function to disable sorting on all columns
                        function disableSorting() {
                            var currentSettings = table.settings()[0]; // Get current table settings
                            currentSettings.aoColumns.forEach(function(column) {
                                column.bSortable = false; // Disable sorting for each column
                            });
                            table.draw(); // Redraw the table to apply the settings
                        }

                        // Function to enable sorting on all columns
                        function enableSorting() {
                            var currentSettings = table.settings()[0]; // Get current table settings
                            currentSettings.aoColumns.forEach(function(column) {
                                column.bSortable = true; // Enable sorting for each column
                            });
                            table.draw(); // Redraw the table to apply the settings
                        }

                        // Function to update button state
                        function updateButtonState() {
                            let isAnyChecked = $(".leave-checkbox:checked").length > 0;
                            if (isAnyChecked) {
                                $("#approveBtn, #declineBtn, #cancelBtn").prop("disabled", false);
                            } else {
                                $("#approveBtn, #declineBtn, #cancelBtn").prop("disabled", true);
                            }
                        }

                        // Handle individual checkbox selection
                        $(".leave-checkbox").change(function() {
                            // Get the leave ID for the row
                            let row = $(this).closest("tr");
                            selectedLeaveId = row.data('leave-id');
                            selectedRow = row;

                            // Update button state
                            updateButtonState();

                            // Disable sorting when any checkbox is selected (either individual or "Select All")
                            let isAnyChecked = $(".leave-checkbox:checked").length > 0;
                            if (isAnyChecked) {
                                disableSorting(); // Disable sorting when any checkbox is checked
                            } else {
                                enableSorting();  // Enable sorting if no checkboxes are checked
                            }
                        });

                        // Handle "Select All" checkbox functionality
                        $("#selectAll").change(function() {
                            let isChecked = $(this).prop("checked");
                            $(".leave-checkbox").prop("checked", isChecked);

                            // Update button state when "Select All" is clicked
                            updateButtonState();

                            // Disable sorting when "Select All" is checked
                            if (isChecked) {
                                disableSorting();
                            } else {
                                enableSorting();
                            }
                        });

                        // Approve button functionality
                        $("#approveBtn").click(function() {
                            $(".leave-checkbox:checked").each(function() {
                                let row = $(this).closest("tr");
                                let leaveId = row.data('leave-id');
                                $.post("update_leave_status.php", { leaveid: leaveId, status: 1 }, function(response) {
                                    if (response.success) {
                                        // Update the status in the table dynamically
                                        row.find("td:last").html('<span style="color: green">Approved</span>');
                                        row.find("input[type='checkbox']").prop("disabled", true); // Disable checkbox after approval
                                        alert("Leave Approved!");

                                        // Remove the row from the DataTable
                                        table.row(row).remove().draw();
                                    } else {
                                        alert("Error: " + response.message);
                                    }
                                }, "json");
                            });
                        });

                        // Decline button functionality
                        $("#declineBtn").click(function() {
                            $(".leave-checkbox:checked").each(function() {
                                let row = $(this).closest("tr");
                                let leaveId = row.data('leave-id');
                                $.post("update_leave_status.php", { leaveid: leaveId, status: 3 }, function(response) {
                                    if (response.success) {
                                        // Update the status in the table dynamically
                                        row.find("td:last").html('<span style="color: red">Declined</span>');
                                        row.find("input[type='checkbox']").prop("disabled", true); // Disable checkbox after decline
                                        alert("Leave Declined!");

                                        // Remove the row from the DataTable
                                        table.row(row).remove().draw();
                                    } else {
                                        alert("Error: " + response.message);
                                    }
                                }, "json");
                            });
                        });

                        // Cancel button functionality
                        $("#cancelBtn").click(function() {
                            $(".leave-checkbox:checked").each(function() {
                                let row = $(this).closest("tr");
                                let leaveId = row.data('leave-id');
                                $.post("update_leave_status.php", { leaveid: leaveId, status: 4 }, function(response) {
                                    if (response.success) {
                                        // Update the status in the table dynamically
                                        row.find("td:last").html('<span style="color: gray">Cancelled</span>');
                                        row.find("input[type='checkbox']").prop("disabled", true); // Disable checkbox after cancel
                                        alert("Leave Cancelled!");

                                        // Remove the row from the DataTable
                                        table.row(row).remove().draw();
                                    } else {
                                        alert("Error: " + response.message);
                                    }
                                }, "json");
                            });
                        });
                         });
        </script> -->



    </body>
</html>
<?php } ?>