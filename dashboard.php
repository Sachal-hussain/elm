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
        <title>Admin | Dashboard</title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        <meta name="author" content="Naeem Amin" />
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        
        <!-- Styles -->
        <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
        <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">    
        <link href="assets/plugins/metrojs/MetroJs.min.css" rel="stylesheet">
        <link href="assets/plugins/weather-icons-master/css/weather-icons.min.css" rel="stylesheet">

        	
        <!-- Theme Styles -->
        <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="assets/css/custom.css" rel="stylesheet" type="text/css"/>
        
    </head>
    <body>
        <?php include('includes/header.php');?>
            
        <?php include('includes/sidebar.php');?>

            <main class="mn-inner">
                <div class="">
                    <div class="row no-m-t no-m-b">
                        

                        <a href="manageleavetype.php" target="blank">
                            <div class="col s12 m12 l4">
                                <div class="card stats-card">
                                    <div class="card-content">
                                        <span class="card-title">Listed leave Type</span>
                                           <?php
                                               
                                                $sql = "SELECT id FROM tblleavetype";
                                                $result = mysqli_query($connect, $sql);

                                                if ($result) {
                                                    
                                                    $leavtypcount = mysqli_num_rows($result);
                                                } 
                                                else {
                                                    
                                                    echo "Error: " . mysqli_error($connect);
                                                    $leavtypcount = 0;
                                                }
                                            ?>
         
                                        <span class="stats-counter"><span class="counter"><?php echo htmlentities($leavtypcount);?></span></span>
                              
                                    </div>
                                    <div class="progress stats-card-progress">
                                        <div class="determinate" style="width: 70%"></div>
                                    </div>
                                </div>
                            </div>
                        </a>


                        <a href="leaves.php" target="blank">
                            <div class="col s12 m12 l4">
                                <div class="card stats-card">
                                    <div class="card-content">
                                        <span class="card-title">Total</span>
                                            <?php
                                                // SQL query to get the count of leaves
                                                $sql = "SELECT id FROM tblleaves";

                                                // Execute the query
                                                $result = mysqli_query($connect, $sql);

                                                // Check if the query was successful
                                                if ($result) {
                                                    // Count the total number of rows
                                                    $totalleaves = mysqli_num_rows($result);
                                                } else {
                                                    // Handle query errors
                                                    echo "Error: " . mysqli_error($connect);
                                                    $totalleaves = 0;
                                                }
                                            ?>
          
                                        <span class="stats-counter"><span class="counter"><?php echo htmlentities($totalleaves);?></span></span>
                              
                                    </div>
                                    <div class="progress stats-card-progress">
                                        <div class="success" style="width: 70%"></div>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a href="approvedleave-history.php" target="blank">
                            <div class="col s12 m12 l4">
                                <div class="card stats-card">
                                    <div class="card-content">
                                        <span class="card-title">Approved</span>
                                            <?php
                                                // SQL query to get the count of approved leaves
                                                $sql = "SELECT id FROM tblleaves WHERE Status=1";

                                                // Execute the query
                                                $result = mysqli_query($connect, $sql);

                                                // Check if the query was successful
                                                if ($result) {
                                                    // Count the total number of rows
                                                    $approvedleaves = mysqli_num_rows($result);
                                                } else {
                                                    // Handle query errors
                                                    echo "Error: " . mysqli_error($connect);
                                                    $approvedleaves = 0;
                                                }
                                                
                                            ?>

                                        <span class="stats-counter"><span class="counter"><?php echo htmlentities($approvedleaves);?></span></span>
                              
                                    </div>
                                    <div class="progress stats-card-progress">
                                        <div class="success" style="width: 70%"></div>
                                    </div>
                                </div>
                            </div>
                        </a>



                        <a href="pending-leavehistory.php" target="blank">
                            <div class="col s12 m12 l4">
                                <div class="card stats-card">
                                    <div class="card-content">
                                        <span class="card-title">Pending </span>
                                            <?php
                                                // SQL query to get the count of leaves where Status=0 (not approved)
                                                $sql = "SELECT id FROM tblleaves WHERE Status=0";

                                                // Execute the query
                                                $result = mysqli_query($connect, $sql);

                                                // Check if the query was successful
                                                if ($result) {
                                                    // Count the total number of rows
                                                    $notapprovedleaves = mysqli_num_rows($result);
                                                } else {
                                                    // Handle query errors
                                                    echo "Error: " . mysqli_error($connect);
                                                    $notapprovedleaves = 0;
                                                }
                                                
                                            ?>

                                        <span class="stats-counter"><span class="counter"><?php echo htmlentities($notapprovedleaves);?></span></span>
                              
                                    </div>
                                    <div class="progress stats-card-progress">
                                        <div class="success" style="width: 70%"></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="decline-leavehistory.php" target="blank">
                            <div class="col s12 m12 l4">
                                <div class="card stats-card">
                                    <div class="card-content">
                                        <span class="card-title">Declined </span>
                                            <?php
                                                // SQL query to get the count of leaves where Status=0 (not approved)
                                                $sql = "SELECT id FROM tblleaves WHERE Status=3";

                                                // Execute the query
                                                $result = mysqli_query($connect, $sql);

                                                // Check if the query was successful
                                                if ($result) {
                                                    // Count the total number of rows
                                                    $notapprovedleaves = mysqli_num_rows($result);
                                                } else {
                                                    // Handle query errors
                                                    echo "Error: " . mysqli_error($connect);
                                                    $notapprovedleaves = 0;
                                                }
                                                
                                            ?>

                                        <span class="stats-counter"><span class="counter"><?php echo htmlentities($notapprovedleaves);?></span></span>
                              
                                    </div>
                                    <div class="progress stats-card-progress">
                                        <div class="success" style="width: 70%"></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="cancel-leavehistory.php" target="blank">
                            <div class="col s12 m12 l4">
                                <div class="card stats-card">
                                    <div class="card-content">
                                        <span class="card-title">Cancelled </span>
                                            <?php
                                                // SQL query to get the count of leaves where Status=0 (not approved)
                                                $sql = "SELECT id FROM tblleaves WHERE Status=4";

                                                // Execute the query
                                                $result = mysqli_query($connect, $sql);

                                                // Check if the query was successful
                                                if ($result) {
                                                    // Count the total number of rows
                                                    $notapprovedleaves = mysqli_num_rows($result);
                                                } else {
                                                    // Handle query errors
                                                    echo "Error: " . mysqli_error($connect);
                                                    $notapprovedleaves = 0;
                                                }
                                                
                                            ?>

                                        <span class="stats-counter"><span class="counter"><?php echo htmlentities($notapprovedleaves);?></span></span>
                              
                                    </div>
                                    <div class="progress stats-card-progress">
                                        <div class="success" style="width: 70%"></div>
                                    </div>
                                </div>
                            </div>
                        </a>

                    </div>
                 
                    <div class="row no-m-t no-m-b">
                        <div class="col s15 m12 l12">
                            <div class="card invoices-card">
                                <div class="card-content">
                                 
                                    <span class="card-title">Latest Leave Applications</span>
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
                                            // SQL query to fetch leave details
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
                                                    ORDER BY lid DESC 
                                                    LIMIT 6";

                                            // Execute the query
                                            $result = mysqli_query($connect, $sql);

                                            // Initialize counter
                                            $cnt = 1;

                                            // Check if any rows are returned
                                            if (mysqli_num_rows($result) > 0) {
                                                // Fetch rows as associative array
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $lid = $row['lid'];
                                                    $firstName = $row['fullname'];
                                                    $employeeId = $row['id'];
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
                                                            <a href="leavehistory.php?empid=<?php echo htmlentities($row['id']); ?>"><?php echo htmlentities($firstName); ?> 
                                                            </a>
                                                            
                                                        </td>
                                                        <td><?php echo htmlentities($leaveType); ?></td>
                                                        <td><?php echo htmlentities($FromDate); ?></td>
                                                        <td><?php echo htmlentities($postingDate); ?></td>
                                                        <td><?php echo htmlentities($Description); ?></td>
                                                        <td><?php echo htmlentities($AdminRemark); ?></td>
                                                        <td>
                                                            <?php if ($status == 1) { ?>
                                                                <span style="color: green">Approved</span>
                                                            <?php } elseif ($status == 2) { ?>
                                                                <span style="color: red">Not Approved</span>
                                                            <?php } elseif ($status == 3) { ?>
                                                                <span style="color: red">Declined</span>
                                                            <?php } elseif ($status == 4) { ?>
                                                                <span style="color: red">Cancelled</span>        
                                                            <?php } elseif ($status == 0) { ?>
                                                                <span style="color: blue">Pending</span>
                                                            <?php } ?>
                                                        </td>
                                                        <?php
                                                            $documentLink = $documents ? '<a href="/chatter2/assets/images/leaves/' . $documents . '" target="_blank">View Document</a>'
                                                            : '-'; 
                                                        ?>
                                                        <td><?php echo $documentLink; ?></td>

                                                        <!-- <td>
                                                            <a href="leave-details.php?leaveid=<?//php echo htmlentities($lid); ?>" class="waves-effect waves-light btn blue m-b-xs">
                                                                View Details
                                                            </a>
                                                        </td> -->
                                                    </tr>

                                                    <?php
                                                    $cnt++;
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              
            </main>
          
        </div>

        
        
        <!-- Javascripts -->
        <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="assets/plugins/materialize/js/materialize.min.js"></script>
        <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
        <script src="assets/plugins/waypoints/jquery.waypoints.min.js"></script>
        <script src="assets/plugins/counter-up-master/jquery.counterup.min.js"></script>
        <script src="assets/plugins/jquery-sparkline/jquery.sparkline.min.js"></script>
        <script src="assets/plugins/chart.js/chart.min.js"></script>
        <script src="assets/plugins/flot/jquery.flot.min.js"></script>
        <script src="assets/plugins/flot/jquery.flot.time.min.js"></script>
        <script src="assets/plugins/flot/jquery.flot.symbol.min.js"></script>
        <script src="assets/plugins/flot/jquery.flot.resize.min.js"></script>
        <script src="assets/plugins/flot/jquery.flot.tooltip.min.js"></script>
        <script src="assets/plugins/curvedlines/curvedLines.js"></script>
        <script src="assets/plugins/peity/jquery.peity.min.js"></script>
        <script src="assets/js/alpha.min.js"></script>
        <script src="assets/js/pages/dashboard.js"></script>
        
    </body>
</html>
<?php } ?>