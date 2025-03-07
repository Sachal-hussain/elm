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
        <title>Admin | Leave History</title>
        
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
                                            <th>Days Applied</th>
                                            <th>Requesting Date</th>                 
                                            <th>Status</th>
                                            <th align="center">Action</th>
                                        </tr>
                                    </thead>
                                 
                                    <tbody>
                                        <?php
                                        $lempid=0;
                                        if(isset($_GET['empid'])){
                                            $lempid=$_GET['empid'];
                                        } 
                                        $sql = "SELECT 
                                                    tblleaves.id as lid,
                                                    user.fullname,
                                                    user.id as empid,
                                                    tblleaves.LeaveType,
                                                    tblleaves.PostingDate,
                                                    tblleaves.Status,
                                                    tblleaves.FromDate,
                                                    tblleaves.days_applied 
                                                FROM 
                                                    tblleaves 
                                                JOIN 
                                                    user 
                                                ON 
                                                    tblleaves.empid = user.id
                                                WHERE
                                                     tblleaves.empid=$lempid
                                                ORDER BY 
                                                    lid DESC";

                                        $result = mysqli_query($connect, $sql);
                                        $cnt = 1;
                                        $total_days = 0;

                                        if ($result && mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) { 
                                                $total_days += $row['days_applied'];
                                                $postingDate = date('m/d/y', strtotime($row['PostingDate']));
                                                ?>
                                                <tr>
                                                    <td><b><?php echo htmlentities($cnt); ?></b></td>
                                                    <td>
                                                        <!-- <a href="editemployee.php?empid=<?php echo htmlentities($row['empid']); ?>" target="_blank"> -->
                                                            <?php echo htmlentities($row['fullname']); ?>
                                                        <!-- </a> -->
                                                    </td>
                                                    <td><?php echo htmlentities($row['LeaveType']); ?></td>
                                                    <td><?php echo htmlentities($row['FromDate']); ?></td>
                                                    <td><?php echo htmlentities($row['days_applied']); ?></td>
                                                    <td><?php echo htmlentities($postingDate); ?></td>
                                                    <td>
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
                                                    <td>
                                                        <a href="leave-details.php?leaveid=<?php echo htmlentities($row['lid']); ?>" 
                                                           class="waves-effect waves-light btn blue m-b-xs">View Details</a>
                                                    </td>
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