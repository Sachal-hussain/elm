<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
    {   
header('location:index.php');
}
else{



 ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        
        <!-- Title -->
        <title>Admin | Total Leave </title>
        
        <?php include('links.php');?>
        
    </head>
    <body>
       <?php include('includes/header.php');?>
            
       <?php include('includes/sidebar.php');?>
            <main class="mn-inner">
                <div class="row">
                    <div class="col s12">
                        <div class="page-title">Leave History</div>
                    </div>
                   
                    <div class="col s12 m12 l12">
                        <div class="card">
                            <div class="card-content">
                                <span class="card-title">Leave History</span>
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
                                            <!-- <th align="center">Action</th> -->
                                        </tr>
                                    </thead>
                                 
                                    <tbody>
                                        <?php 
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
                                                FROM 
                                                    tblleaves 
                                                JOIN 
                                                    user 
                                                ON 
                                                    tblleaves.empid = user.id 
                                                ORDER BY 
                                                    lid DESC";

                                        $result = mysqli_query($connect, $sql);
                                        $cnt = 1;

                                        if ($result && mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) { 
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
                                                        $status = $row['Status'];
                                                        if ($status == 1) {
                                                          echo '<span style="color: green">Approved</span>';
                                                        } elseif ($status == 2) {
                                                          echo '<span style="color: red">Not Approved</span>';
                                                        } elseif ($status == 3) {
                                                          echo '<span style="color: red">Decline</span>';
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
                                                    <!-- <td>
                                                        <a href="leave-details.php?leaveid=<?php echo htmlentities($row['lid']); ?>" 
                                                           class="waves-effect waves-light"><i class="material-icons" title="view">visibility</i>
                                                       </a>
                                                       <a class="modal-trigger waves-effect waves-light " href="#modal1"><i class="material-icons" title="view">edit_square</i></a>
                                                            <form name="adminaction" method="post">
                                                                <div id="modal1" class="modal">
                                                                  <div class="modal-content" style="width:90%">
                                                                    <h4>Leave Take Action</h4>
                                                                    <select class="browser-default form-control" name="status" required="">
                                                                      <option value="">Choose your option</option>
                                                                      <option value="1">Approve</option>
                                                                      <option value="3">Decline</option>
                                                                      <option value="4">Cancel</option>
                                                                    </select>
                                                                    
                                                                    <textarea id="textarea1" name="description" class="materialize-textarea" placeholder="Description" length="500" maxlength="500" required></textarea>
                                                                  </div>
                                                                  <div class="modal-footer" style="width:90%">
                                                                    <input type="submit" class=" btn blue m-b-xs m-t-xs" name="update" value="Submit">
                                                                  </div>
                                                                  <input type="hidden" name="leaveid" value="<?php echo htmlentities($row['lid']);?>">
                                                                </div>
                                                            </form>

                                                    </td> -->
                                                </tr>
                                            <?php 
                                                $cnt++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='6'>No records found</td></tr>";
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
        
    </body>
</html>
<?php } ?>