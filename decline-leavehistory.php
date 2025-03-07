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
                $msg = "Leave updated";

                // Check if the status is Decline (3) or Cancel (4)
               if ($status == 0 || $status == 1) {
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
                                            // Subtract balance for status 0 or 1 (Leave requested or rejected)
                                            $updateBalanceQuery = $leaveType === 'Sick'
                                                ? "UPDATE employee_leaves SET sick_leave_remaining = sick_leave_remaining - ? WHERE empid = ?"
                                                : ($leaveType === 'Annual'
                                                    ? "UPDATE employee_leaves SET annual_leave_remaining = annual_leave_remaining - ? WHERE empid = ?"
                                                    : null);

                                            if ($updateBalanceQuery) {
                                                if ($balanceStmt = mysqli_prepare($connect, $updateBalanceQuery)) {
                                                    mysqli_stmt_bind_param($balanceStmt, "ii", $daysApplied, $empid);
                                                    if (mysqli_stmt_execute($balanceStmt)) {
                                                        $msg .= " Leave balance updated successfully (subtracted).";
                                                    } else {
                                                        $msg .= " Error updating leave balance (subtracted): " . mysqli_error($connect);
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
        <title>Admin | Decline Leave leaves </title>
        
        <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
        <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
        
        <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>     
    </head>
    <style>
            .pagination li.active a {
                    color: #141313;
                }
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
            #pendingBtn:enabled {
                background-color: blue;
                color: white;
            }

            /* Approve button color */
            #approveBtn:enabled {
                background-color: green;
                color: white;
            }

            /* Decline button color */
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
                        <div class="page-title">Declined Leave History</div>
                    </div>
                   
                    <div class="col s12 m12 l12">
                        <div class="card">
                            <div class="card-content">
                                <span class="card-title">Leave History</span>
                                <?php if($msg){?><div class="succWrap"><strong>SUCCESS</strong> : <?php echo htmlentities($msg); ?> </div><?php }?>
                                 <div class="buttons-container">
                                        <button id="pendingBtn" disabled>Pending</button>
                                        <button id="approveBtn" disabled>Approve</button>
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
                                        $status = 3; // Approved status
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
                                                                if ($status == 1) {
                                                                  echo '<span style="color: green">Approved</span>';
                                                                } elseif ($status == 2) {
                                                                  echo '<span style="color: red">Not Approved</span>';
                                                                } elseif ($status == 3) {
                                                                  echo '<span style="color: red">Declined</span>';
                                                                }
                                                                elseif ($status == 4) {
                                                                  echo '<span style="color: red">Cancel</span>';
                                                                }
                                                                elseif ($status == 0) {
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
                                                echo "<tr><td colspan='6'>No decline leaves found.</td></tr>";
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
            // When a modal trigger is clicked
            $(document).ready(function(){
                // When modal is triggered
                $(document).on('click','#editview', function() {
                    // Get the value of data-lid from the clicked anchor tag
                    var lid = $(this).data('lid');
                    // alert(lid);
                    // Set the lid value to the hidden input inside the modal form
                    $('input[name="leaveid"]').val(lid);
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

        // Main function to handle all actions
        function handleTableActions(action) {
            let isAnyChecked = $(".leave-checkbox:checked").length > 0;

            // Append a loader element and message container
            $("body").append('<div id="loader" style="display:none; position: fixed; top: 0; left: 50%; transform: translateX(-50%); background-color: rgba(0, 0, 0, 0.7); color: #fff; padding: 10px 20px; font-size: 16px; z-index: 9999;">Processing...</div>');
            $("#example").before('<div id="messageContainer" style="margin-bottom: 10px;"></div>');

            // Show the loader
            $("#loader").show();

            // Update button state
            $("#pendingBtn, #approveBtn, #cancelBtn").prop("disabled", !isAnyChecked);

            // Disable/Enable sorting based on checkbox state
            table.settings()[0].aoColumns.forEach(function(column) {
                column.bSortable = !isAnyChecked;
            });
            table.draw();

            if (['pending', 'approve', 'cancel'].includes(action)) {
                $(".leave-checkbox:checked").each(function() {
                    let row = $(this).closest("tr");
                    let leaveId = row.data('leave-id');
                    let status, statusText, statusColor;

                    // Set status values based on action
                    switch (action) {
                        case 'pending':
                            status = 0;
                            statusText = 'Pending';
                            statusColor = 'blue';
                            break;
                        case 'approve':
                            status = 1;
                            statusText = 'Approved';
                            statusColor = 'green';
                            break;
                        case 'cancel':
                            status = 4;
                            statusText = 'Cancelled';
                            statusColor = 'red';
                            break;
                    }

                    // Make the POST request
                    $.post("update_decleave_status.php", { leaveid: leaveId, status: status }, function(response) {
                        // Hide the loader when processing is complete
                        $("#loader").hide();

                        if (response.success) {
                            showMessage(`Leave ${statusText} successfully!`);

                            // Remove the row from the table
                            table.row(row).remove().draw();

                            // Optionally, update the row content with the new status
                            row.find("td:last").html(`<span style="color: ${statusColor}">${statusText}</span>`);
                            row.find("input[type='checkbox']").prop("disabled", true);
                        } else {
                            showMessage("Something went wrong. Please try again.");
                        }
                    }, "json");
                });
            }
        }

        // Function to display messages
        function showMessage(message) {
            if ($("#messagePopup").length === 0) {
                $("body").append('<div id="messagePopup" style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #333; color: #fff; padding: 10px 20px; border-radius: 5px; display: none; z-index: 9999;"></div>');
            }

            // Set and show the message
            $("#messagePopup").text(message).fadeIn();

            // Hide the message after 3 seconds
            setTimeout(function() {
                $("#messagePopup").fadeOut();
            }, 3000);
        }

        // Handle individual checkbox selection
        $(".leave-checkbox").change(function() {
            selectedRow = $(this).closest("tr");
            selectedLeaveId = selectedRow.data('leave-id');
            handleTableActions(); // Call without action
        });

        // Handle "Select All" checkbox functionality
        $("#selectAll").change(function() {
            $(".leave-checkbox").prop("checked", $(this).prop("checked"));
            handleTableActions(); // Call without action
        });

        // Button functionalities
        $("#pendingBtn").click(function() {
            handleTableActions('pending');
        });

        $("#approveBtn").click(function() {
            handleTableActions('approve');
        });

        $("#cancelBtn").click(function() {
            handleTableActions('cancel');
        });
    });
</script>
 -->

 <script>
                $(document).ready(function() {
                var table = $('#example').DataTable({
                    "paging": true,          // Enable pagination
                    "searching": true,       // Enable searching
                    "ordering": true,        // Enable sorting by default
                    "info": true,            // Show info (number of entries)
                    "lengthChange": true,    // Enable changing the number of rows per page
                    "pageLength": 10,        // Set the initial number of rows per page
                    "responsive": true,      // Enable responsive table behavior for mobile devices
                    "stateSave": true        // Save the state (pagination) on page reload
                });

                let selectedLeaveId = null;
                let selectedRow = null;

                // Function to display a message
                function showMessage(message) {
                    if ($("#messagePopup").length === 0) {
                        $("body").append('<div id="messagePopup" style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #333; color: #fff; padding: 10px 20px; border-radius: 5px; display: none; z-index: 9999;"></div>');
                    }

                    // Hide any existing message before showing a new one
                    $("#messagePopup").fadeOut(200, function() {
                        $("#messagePopup").text(message).fadeIn(200); // Show the new message
                    });

                    // Hide the message after 3 seconds
                    setTimeout(function() {
                        $("#messagePopup").fadeOut(200);
                    }, 3000);
                }

                // Full screen loader function
                function showFullScreenLoader() {
                    if ($("#fullScreenLoader").length === 0) {
                        $("body").append('<div id="fullScreenLoader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 9999;"><div class="loader" style="border: 16px solid #f3f3f3; border-top: 16px solid #3498db; border-radius: 50%; width: 120px; height: 120px; animation: spin 2s linear infinite;"></div></div>');
                    }
                }

                function hideFullScreenLoader() {
                    $("#fullScreenLoader").fadeOut(200);
                }

                // Update button states based on selected checkboxes
                function updateButtonState() {
                    let isAnyChecked = $(".leave-checkbox:checked").length > 0;
                    if (isAnyChecked) {
                        $("#pendingBtn, #approveBtn, #cancelBtn").prop("disabled", false);
                    } else {
                        $("#pendingBtn, #approveBtn, #cancelBtn").prop("disabled", true);
                    }
                }

                // Handle table actions like approve, decline, cancel
                function handleTableActions(action) {
                    let isAnyChecked = $(".leave-checkbox:checked").length > 0;

                    // Show the full-screen loader when any checkbox is selected
                    if (isAnyChecked) {
                        showFullScreenLoader();
                    }

                    if (action === 'pending' || action === 'approve' || action === 'cancel') {
                        $(".leave-checkbox:checked").each(function() {
                            let row = $(this).closest("tr");
                            let leaveId = row.data('leave-id');
                            let status;
                            let statusText;
                            let statusColor;

                            if (action === 'pending') {
                                status = 0;
                                statusText = 'Pending';
                                statusColor = 'blue';
                            } else if (action === 'approve') {
                                status = 1;
                                statusText = 'Approve';
                                statusColor = 'green';
                            } else if (action === 'cancel') {
                                status = 4;
                                statusText = 'Cancelled';
                                statusColor = 'gray';
                            }

                            // Make the POST request
                            $.post("update_decleave_status.php", { leaveid: leaveId, status: status }, function(response) {
                                // Hide the full-screen loader after the action completes
                                hideFullScreenLoader();

                                if (response.success) {
                                    // Show success message and update row
                                    showMessage(`Leave ${statusText} successfully!`);

                                    // Update the status text and disable checkbox
                                     table.row(row).remove().draw();
                                    row.find("input[type='checkbox']").prop("disabled", true); // Disable checkbox after action
                                } else {
                                    // Show error message if something went wrong
                                    showMessage("Something went wrong. Please try again.");
                                }
                            }, "json");
                        });
                    }
                }

                // Handle individual checkbox selection
                $(document).on('change', '.leave-checkbox', function() {
                    updateButtonState(); // Update button state when a checkbox is selected/unselected
                });

                // Handle "Select All" checkbox functionality
                $("#selectAll").change(function() {
                    $(".leave-checkbox").prop("checked", $(this).prop("checked"));
                    updateButtonState(); // Update button state when "Select All" is clicked
                });

                // Approve button functionality
                $("#pendingBtn").click(function() {
                    handleTableActions('pending');
                });

                // Decline button functionality
                $("#approveBtn").click(function() {
                    handleTableActions('approve');
                });

                // Cancel button functionality
                $("#cancelBtn").click(function() {
                    handleTableActions('cancel');
                });

                // Re-attach event listeners after every page draw (due to pagination)
                table.on('draw', function() {
                    // Re-attach the change event listener for checkboxes after each redraw (pagination)
                    $(".leave-checkbox").change(function() {
                        updateButtonState(); // Update button state when checkbox is selected/unselected
                    });

                    // Re-attach button functionality after each draw
                    $("#pendingBtn").click(function() {
                        handleTableActions('pending');
                    });

                    $("#approveBtn").click(function() {
                        handleTableActions('approve');
                    });

                    $("#cancelBtn").click(function() {
                        handleTableActions('cancel');
                    });

                    updateButtonState(); // Ensure buttons are updated after table redraw
                });

                // Initial checkbox state handling
                $(".leave-checkbox").change(function() {
                    updateButtonState(); // Ensure buttons are updated after table is loaded
                });
            });
</script>
        
    </body>
</html>
<?php } ?>