<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
    {   
header('location:index.php');
}
else{

// code for action taken on leave
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
        if ($status == 3 || $status == 4) {
            // Retrieve the leave type, empid, and days_applied
            $fetchQuery = "SELECT LeaveType, empid, days_applied FROM tblleaves WHERE id = ?";
            if ($fetchStmt = mysqli_prepare($connect, $fetchQuery)) {
                mysqli_stmt_bind_param($fetchStmt, "i", $did);
                mysqli_stmt_execute($fetchStmt);
                $result = mysqli_stmt_get_result($fetchStmt);

                if ($row = mysqli_fetch_assoc($result)) {
                    $leaveType = $row['LeaveType'];
                    $empid = $row['empid'];
                    $daysApplied = $row['days_applied'];

                    // Update the employee's leave balance based on the leave type
                    if ($leaveType === 'Sick') {
                        $updateBalanceQuery = "UPDATE employee_leaves SET sick_leave_remaining = sick_leave_remaining + ? WHERE empid = ?";
                    } elseif ($leaveType === 'Annual') {
                        $updateBalanceQuery = "UPDATE employee_leaves SET annual_leave_remaining = annual_leave_remaining + ? WHERE empid = ?";
                    } else {
                        $msg = "Error: Invalid leave type.";
                        exit();
                    }

                    // Prepare and execute the balance update query
                    if ($balanceStmt = mysqli_prepare($connect, $updateBalanceQuery)) {
                        mysqli_stmt_bind_param($balanceStmt, "ii", $daysApplied, $empid);
                        if (mysqli_stmt_execute($balanceStmt)) {
                            $msg .= " successfully.";
                        } else {
                            $msg .= " Error updating leave balance: " . mysqli_error($connect);
                        }
                        mysqli_stmt_close($balanceStmt);
                    }
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
        <title>Admin | Approved Leaves </title>
        
        <?php include('links.php');?>
    </head>
    <body>
       <?php include('includes/header.php');?>
            
       <?php include('includes/sidebar.php');?>
            <main class="mn-inner">
                <div class="row">
                    <div class="col s12">
                        <div class="page-title">Approved Leave History</div>
                    </div>
                   
                    <div class="col s12 m12 l12">
                        <div class="card">
                            <div class="card-content">
                                <span class="card-title">Approved Leave History</span>
                                <?php if($msg){?><div class="succWrap"><strong>SUCCESS</strong> : <?php echo htmlentities($msg); ?> </div><?php }?>
                                <table id="example" class="display responsive-table ">
                                    <thead>
                                        <tr>
                                        <th>#</th>
                                            <th>Employe Name</th>
                                                <th>Leave Type</th>
                                                <th>Date Range</th>
                                                 <th>Requesting Date</th>
                                                 <th>Reason</th>
                                                 <th>Remarks</th>                 
                                                <th>Status</th>
                                                <th>Documents</th>
                                            <th align="center">Action</th>
                                        </tr>
                                    </thead>
                                 
                                    <tbody>
                                        <?php
                                        // Assuming you have a database connection in $connect
                                        $status = 1; // Approved status
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
                                                    // echo "<pre>";
                                                    // print_r($row);
                                                    $lid=$row['lid'];
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
                                                            } else {
                                                                echo '<span style="color: blue">Pending</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <?php
                                                            $documentLink = $documents ? '<a href="/chatter2/assets/images/leaves/' . $documents . '" target="_blank">View Document</a>'
                                                            : '-'; 
                                                        ?>
                                                        <td><?php echo $documentLink; ?></td>
                                                        <td>
                                                            <!-- <a href="leave-details.php?leaveid=<?php //echo htmlentities($row['lid']); ?>" 
                                                            class="waves-effect waves-light"><i class="material-icons" title="view">visibility</i>
                                                            </a> -->
                                                            <a class="modal-trigger waves-effect waves-light" id="editview" href="#modal1" data-lid="<?php echo htmlentities($lid); ?>">
                                                                <i class="material-icons" title="view">edit_square</i>
                                                            </a>
                                                            <form name="adminaction" method="post">
                                                                <div id="modal1" class="modal">
                                                                  <div class="modal-content" style="width:90%">
                                                                    <h4>Leave Take Action</h4>
                                                                    <select class="browser-default form-control" name="status" required="">
                                                                      <option value="">Choose your option</option>
                                                                      
                                                                     <option value="0">Pending</option>
                                                                      <option value="3">Decline</option>
                                                                      <option value="4">Cancel</option>
                                                                    </select>
                                                                    
                                                                    <textarea id="textarea1" name="description" class="materialize-textarea" placeholder="Description" length="500" maxlength="500" ></textarea>
                                                                  </div>
                                                                  <div class="modal-footer" style="width:90%">
                                                                    <input type="submit" class=" btn blue m-b-xs m-t-xs" name="update" value="Submit">
                                                                  </div>
                                                                  <input type="hidden" name="leaveid" value="">
                                                                </div>
                                                            </form>
                                                        </td>
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
       <?php include ('footer.php');?>


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

        
    </body>
</html>
<?php } ?>