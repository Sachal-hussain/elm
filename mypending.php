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
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.2.0/css/dataTables.dataTables.css">
    
    <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
    <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
    <script src="assets/plugins/materialize/js/materialize.min.js"></script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/2.2.0/js/dataTables.js"></script>
    
        
</head>
        
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
                                 <!-- This is your button -->
    

                                                
                        
                                <table id="example" class="stripe row-border order-column nowrap" style="width:100%">
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
                                                    <tr>
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
                                                        <!-- <td>
                                                            <a href="leave-details.php?leaveid=<?php //echo htmlentities($row['lid']); ?>" 
                                                            class="waves-effect waves-light"><i class="material-icons" title="view">visibility</i>
                                                            </a>
                                                            <a class="modal-trigger waves-effect waves-light " href="#modal1"><i class="material-icons" title="view">edit_square</i></a>
                                                            <form name="adminaction" method="post">
                                                                <div id="modal1" class="modal">
                                                                  <div class="modal-content" style="width:90%">
                                                                    <h4>Leave Take Action</h4>
                                                                    <select class="browser-default form-control" name="status" required="">
                                                                      <option value="">Choose your option</option>
                                                                      <option value="1">Approved</option>
                                                                      <option value="3">Decline</option>
                                                                      <option value="4">Cancel</option>
                                                                    </select>
                                                                    
                                                                    <textarea id="textarea1" name="description" class="materialize-textarea" placeholder="Description" length="500" maxlength="500" required></textarea>
                                                                  </div>
                                                                  <div class="modal-footer" style="width:90%">
                                                                    <input type="submit" class=" btn blue m-b-xs m-t-xs" name="update" value="Submit">
                                                                  </div>
                                                                  <input type="hidden" name="leaveid" value="<?php //echo htmlentities($row['lid']);?>">
                                                                </div>
                                                            </form>
                                                        </td> -->
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
        <!-- <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="assets/plugins/materialize/js/materialize.min.js"></script>
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/2.2.0/js/dataTables.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/5.0.4/js/dataTables.fixedColumns.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/5.0.4/js/fixedColumns.dataTables.js"></script> -->
        
        <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>

        <!-- <script type="text/javascript">
            
    new DataTable('#example', {
    columnDefs: [
        {
            orderable: false,
            render: DataTable.render.select(),
            targets: 0
        }
    ],
    fixedColumns: {
        start: 2
    },
    order: [[1, 'asc']],
    paging: false,
    scrollCollapse: true,
    scrollX: true,
    scrollY: 300,
    
    select: {
        style: 'os',
        selector: 'td:first-child'
    }
});
</script> -->
    <script>
$(document).ready(function() {
    let selectedRow = null;

    // Handle checkbox selection
    $(".leave-checkbox").change(function() {
        let row = $(this).closest("tr");
        if ($(this).prop("checked")) {
            selectedRow = row;
            // Enable buttons
            $("#approveBtn, #declineBtn, #cancelBtn").prop("disabled", false);
        } else {
            selectedRow = null;
            // Disable buttons if checkbox is unchecked
            $("#approveBtn, #declineBtn, #cancelBtn").prop("disabled", true);
        }
    });

    // Approve button functionality
    $("#approveBtn").click(function() {
        if (selectedRow) {
            let leaveId = selectedRow.data('leave-id');
            $.post("update_leave_status.php", { leaveid: leaveId, status: 1 }, function(response) {
                if (response.success) {
                    selectedRow.find(".status").html('<span style="color: green">Approved</span>');
                }
            });
        }
    });

    // Decline button functionality
    $("#declineBtn").click(function() {
        if (selectedRow) {
            let leaveId = selectedRow.data('leave-id');
            $.post("update_leave_status.php", { leaveid: leaveId, status: 3 }, function(response) {
                if (response.success) {
                    selectedRow.find(".status").html('<span style="color: red">Declined</span>');
                }
            });
        }
    });

    // Cancel button functionality
    $("#cancelBtn").click(function() {
        if (selectedRow) {
            let leaveId = selectedRow.data('leave-id');
            $.post("update_leave_status.php", { leaveid: leaveId, status: 4 }, function(response) {
                if (response.success) {
                    selectedRow.find(".status").html('<span style="color: gray">Cancelled</span>');
                }
            });
        }
    });
});
</script>




 <!-- DataTables JS -->
    
     
        
    </body>
</html>
<?php } ?>