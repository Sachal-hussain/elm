<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
    {   
header('location:index.php');
}
else{

 $isread = 1;
$did = intval($_GET['leaveid']);  

// Ensure a valid leave ID is provided
if ($did > 0) {
  date_default_timezone_set('Asia/Kolkata');
  $admremarkdate = date('Y-m-d G:i:s', strtotime("now"));

  // Assuming $connect is your MySQLi connection
  $sql = "UPDATE tblleaves SET IsRead = ? WHERE id = ?";
  $stmt = $connect->prepare($sql);

  if ($stmt) {
    // Bind parameters
    $stmt->bind_param('ii', $isread, $did);
    $stmt->execute();
    $stmt->close();
  } 
} 
else {
  echo "Invalid leave ID.";
} 
// code for action taken on leave
if (isset($_POST['update'])) {
  // Retrieve form inputs
  $did = intval($_GET['leaveid']);
  $description = $_POST['description'];
  $status = intval($_POST['status']);
  $daterange = $_POST['daterange']; // Admin-approved dates (if any)
  $applied_dates = $_POST['applied_dates']; // Original applied dates

  // Set current timestamp for admin remark date
  $admremarkdate = date('Y-m-d G:i:s');

  // Convert date ranges into arrays for comparison
  $appliedDatesArray = explode(',', $applied_dates);
  $approvedDatesArray = explode(',', $daterange);
  $nonApprovedDatesArray = array_diff($appliedDatesArray, $approvedDatesArray);

  // Count days
  $approvedDays = count($approvedDatesArray);
  $nonApprovedDays = count($nonApprovedDatesArray);
  $totalAppliedDays = count($appliedDatesArray);

  // Update the leave record
  $sql = "
    UPDATE tblleaves 
    SET AdminRemark = ?, Status = ?, AdminRemarkDate = ?, FromDate = ?, days_applied = ? 
    WHERE id = ?"
  ;
  if ($stmt = mysqli_prepare($connect, $sql)) {
    mysqli_stmt_bind_param($stmt, "sissii", $description, $status, $admremarkdate, $daterange, $approvedDays, $did);

    if (mysqli_stmt_execute($stmt)) {
      $msg = "Leave updated successfully.";
      $fetchQuery = "SELECT LeaveType, empid FROM tblleaves WHERE id = ?";
      if ($fetchStmt = mysqli_prepare($connect, $fetchQuery)) {
        mysqli_stmt_bind_param($fetchStmt, "i", $did);
        mysqli_stmt_execute($fetchStmt);
        $result = mysqli_stmt_get_result($fetchStmt);

        if ($row = mysqli_fetch_assoc($result)) {
          $leaveType = $row['LeaveType'];
          $empid = $row['empid'];

          // Determine the leave column
          $leaveColumn = ($leaveType === 'Sick Leaves') 
              ? 'sick_leave_remaining' 
              : 'annual_leave_remaining';

          if ($status == 1) { // Approved
            if ($nonApprovedDays > 0) {
               
              $updateBalanceQuery = "
                UPDATE employee_leaves 
                SET $leaveColumn = $leaveColumn + ? 
                WHERE empid = ?"
              ;
              if ($balanceStmt = mysqli_prepare($connect, $updateBalanceQuery)) {
                mysqli_stmt_bind_param($balanceStmt, "ii", $nonApprovedDays, $empid);
                if (mysqli_stmt_execute($balanceStmt)) {
                  $msg .= " Non-approved leave days added back to balance.";
                } 
                else {
                  $msg .= " Error updating leave balance: " . mysqli_error($connect);
                }
                mysqli_stmt_close($balanceStmt);
              }
            }
          } 
          elseif ($status == 3 || $status == 4) { // Decline or Cancel
             
            $updateBalanceQuery = "
              UPDATE employee_leaves 
              SET $leaveColumn = $leaveColumn + ? 
              WHERE empid = ?";
            if ($balanceStmt = mysqli_prepare($connect, $updateBalanceQuery)) {
              mysqli_stmt_bind_param($balanceStmt, "ii", $totalAppliedDays, $empid);
              if (mysqli_stmt_execute($balanceStmt)) {
                $msg .= " All leave days added back to balance.";
              } 
              else {
                $msg .= " Error updating leave balance: " . mysqli_error($connect);
              }
              mysqli_stmt_close($balanceStmt);
            }
          } 
          else {
            $msg = "Error: Invalid status.";
          }
        } 
        else {
          $msg .= " Error: Leave details not found.";
        }
        mysqli_stmt_close($fetchStmt);
      } 
      else {
        $msg .= " Error fetching leave details: " . mysqli_error($connect);
      }
    } 
    else {
      $msg = "Error: Could not execute update query. " . mysqli_error($connect);
    }
    mysqli_stmt_close($stmt);
  } 
  else {
    $msg = "Error: Could not prepare the update query. " . mysqli_error($connect);
  }

}






?>
<!DOCTYPE html>
<html lang="en">
  <head>
      
    <!-- Title -->
    <title>Admin | Leave Details </title>
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta charset="UTF-8">
    <meta name="description" content="Responsive Admin Dashboard Template" />
    <meta name="keywords" content="admin,dashboard" />
    <meta name="author" content="Steelcoders" />
    
    <!-- Styles -->
    <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
    <link href="assets/plugins/datatables/css/jquery.dataTables.min.css" rel="stylesheet">

    <link href="assets/plugins/google-code-prettify/prettify.css" rel="stylesheet" type="text/css"/>  
    <!-- Theme Styles -->
    <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/custom.css" rel="stylesheet" type="text/css"/>
    <!-- datepicker css -->
    <link rel="stylesheet" href="assets/flatpickr/flatpickr.min.css">
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
          <div class="page-title" style="font-size:24px;">Leave Details</div>
        </div>

        <div class="col s12 m12 l12">
          <div class="card">
            <div class="card-content">
              <span class="card-title">Leave Details</span>
              <?php if($msg){?><div class="succWrap"><strong>SUCCESS</strong> : <?php echo htmlentities($msg); ?> </div><?php }?>
              <table id="example" class="display responsive-table ">


                <tbody>
                  <?php 
                  $lid = intval($_GET['leaveid']);

                           
                  $sql = "SELECT tblleaves.id AS lid,user.fullname,user.agentid,user.shift,user.department,user.type,tblleaves.LeaveType,tblleaves.ToDate,tblleaves.FromDate,tblleaves.Description,tblleaves.PostingDate,tblleaves.Status,tblleaves.AdminRemark,tblleaves.AdminRemarkDate,tblleaves.days_applied 
                  FROM 
                  tblleaves 
                  JOIN 
                  user ON tblleaves.empid=user.id 
                  WHERE 
                  tblleaves.id= ?";

                           
                  if ($stmt = mysqli_prepare($connect, $sql)) {
                             
                    mysqli_stmt_bind_param($stmt, "i", $lid);

                             
                    mysqli_stmt_execute($stmt);

                             
                    $result = mysqli_stmt_get_result($stmt);
                    if (mysqli_num_rows($result) > 0) {
                      while ($row = mysqli_fetch_assoc($result)) {?>
                        <tr>
                          <td style="font-size:16px;"><b>Agent ID :</b></td>
                          <td><?php echo htmlentities($row['agentid']); ?></td>
                          <td style="font-size:16px;"><b>Full Name :</b></td>
                          <td> <?php echo htmlentities($row['fullname']); ?></td>
                          <td style="font-size:16px;"><b>Shift :</b></td>
                          <td><?php echo htmlentities($row['shift']); ?></td>
                          <td style="font-size:16px;"><b>Department :</b></td>
                          <td><?php echo htmlentities($row['department']); ?></td>
                        </tr>
                        <tr>
                          <td style="font-size:16px;"><b>Leave Type :</b></td>
                          <td><?php echo htmlentities($row['LeaveType']); ?></td>
                          <td style="font-size:16px;"><b>Leave Date :</b></td>
                          <td> <?php echo htmlentities($row['FromDate']); ?> </td>
                          <td style="font-size:16px;"><b>Total Days :</b></td>
                          <td><?php echo htmlentities($row['days_applied']); ?></td>
                          <td style="font-size:16px;"><b>Requesting Date :</b></td>
                          <td><?php echo htmlentities($row['PostingDate']); ?></td>
                        </tr>
                        <tr>
                          <td style="font-size:16px;"><b>Reason :</b></td>
                          <td colspan="5"><?php echo htmlentities($row['Description']); ?></td>
                        </tr>
                        <tr>
                          <td style="font-size:16px;"><b>Leave Status :</b></td>
                          <td colspan="5">
                            <?php 
                            $status = $row['Status'];
                            if ($status == 1) {
                              echo '<span style="color: green">Approved</span>';
                            } elseif ($status == 2) {
                              echo '<span style="color: red">Not Approved</span>';
                            } elseif ($status == 3) {
                              echo '<span style="color: red">Declined</span>';
                            }
                            elseif ($status == 4) {
                              echo '<span style="color: red">Cancelled</span>';
                            }
                            elseif ($status == 0) {
                              echo '<span style="color: blue">Pending</span>';
                            }
                            ?>
                          </td>
                        </tr>
                        <tr>
                          <td style="font-size:16px;"><b>Admin Remark:</b></td>
                          <td colspan="5">
                            <?php
                            echo empty($row['AdminRemark']) ? "-" : htmlentities($row['AdminRemark']);
                            ?>
                          </td>
                        </tr>
                        <tr>
                          <td style="font-size:16px;"><b>Admin Updated At:</b></td>
                          <td colspan="5">
                            <?php
                            echo empty($row['AdminRemarkDate']) ? "-" : htmlentities($row['AdminRemarkDate']);
                            ?>
                          </td>
                        </tr>
                        <?php 
                        if ($status == 0) { 
                          ?>
                          <tr>
                            <td colspan="5">
                              <a class="modal-trigger waves-effect waves-light btn" href="#modal1">Take&nbsp;Action</a>
                              <form name="adminaction" method="post">
                                <div id="modal1" class="modal">
                                  <div class="modal-content" style="width:90%">
                                    <h4>Leave Take Action</h4>
                                    <select class="browser-default form-control" name="status" required="">
                                      <option value="">Choose your option</option>
                                      <option value="1">Approved</option>
                                     <!--  <option value="2">Not Approved</option> -->
                                      <option value="3">Decline</option>
                                      <option value="4">Cancel</option>
                                    </select>
                                    
                                    <textarea id="textarea1" name="description" class="materialize-textarea" placeholder="Description" length="500" maxlength="500" required></textarea>
                                    <input type="text" name="daterange" id="daterange" class="form-control form-control-lg border-light bg-light-subtle" required value="<?php echo $row['FromDate'];?>">
                                  </div>
                                  <div class="modal-footer" style="width:90%">
                                    <input type="hidden" name="leave_id" value="<?php echo $row['lid']; ?>">
                                    <input type="hidden" name="leave_type" value="<?php echo $row['LeaveType']; ?>">
                                    <input type="hidden" name="applied_dates" value="<?php echo $row['FromDate']; ?>">
                                    <input type="submit" class=" btn blue m-b-xs m-t-xs" name="update" value="Submit">
                                  </div>
                                </div>
                              </form>
                            </td>
                          </tr>
                          <?php 
                        } 
                      }
                    }
                             
                    mysqli_free_result($result);
                  }
                            
                  mysqli_stmt_close($stmt);
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
    <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
    <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
    <script src="assets/plugins/datatables/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/alpha.min.js"></script>
    <script src="assets/js/pages/table-data.js"></script>
    <script src="assets/js/pages/ui-modals.js"></script>
    <script src="assets/plugins/google-code-prettify/prettify.js"></script>
    <!-- datepicker js -->
    <script src="assets/flatpickr/flatpickr.min.js"></script>

     <script type="text/javascript">
   
      // $(document).ready(function () {
        // Fetch the preselected dates from the input value
        var preselectedDates = $('#daterange').val().split(',').map(function (date) {
            return date.trim(); // Remove any leading or trailing spaces
        });

        // Initialize Flatpickr with preselected dates
        $('#daterange').flatpickr({
          mode: "multiple",
          dateFormat: "Y-m-d",
          defaultDate: preselectedDates, // Preselect these dates
          onOpen: function (selectedDates, dateStr, instance) {
            instance.clear(); // Clear current selections
            instance.setDate(preselectedDates); // Reset preselected dates on reopen
          }
        });
      // });

     
     </script> 
  </body>
</html>
<?php } ?>