<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
    {   
header('location:index.php');
}
else{
if (isset($_GET['del'])) {
    // Get the ID from the URL
    $id = $_GET['del'];

    // Prepare the SQL query to delete the record
    $sql = "DELETE FROM tblleavetype WHERE id = ?";

    // Initialize the prepared statement
    if ($stmt = mysqli_prepare($connect, $sql)) {
        // Bind the parameter to the query
        mysqli_stmt_bind_param($stmt, "i", $id);

        // Execute the query
        if (mysqli_stmt_execute($stmt)) {
            $msg = "Leave type record deleted";
        } else {
            $msg = "Error deleting record: " . mysqli_error($connect);
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        $msg = "Error preparing query: " . mysqli_error($connect);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        
        <!-- Title -->
        <title>Admin | Manage Leave Type</title>
        
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

            
        <!-- Theme Styles -->
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
       <?php include('includes/header.php');?>
            
       <?php include('includes/sidebar.php');?>
            <main class="mn-inner">
                <div class="row">
                    <div class="col s12">
                        <div class="page-title">Manage Leave Type</div>
                    </div>
                   
                    <div class="col s12 m12 l12">
                        <div class="card">
                            <div class="card-content">
                                <span class="card-title">Leave Type Info</span>
                                <?php if($msg){?><div class="succWrap"><strong>SUCCESS</strong> : <?php echo htmlentities($msg); ?> </div><?php }?>
                                <table id="example" class="display responsive-table ">
                                    <thead>
                                        <tr>
                                            <th>Sr no</th>
                                            <th>Leave Type</th>
                                            <th>Description</th>
                                            <th>Creation Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                 
                                    <tbody>
                                        <?php
                                        // SQL query to fetch all records from tblleavetype
                                        $sql = "SELECT * FROM tblleavetype";

                                        // Execute the query
                                        if ($result = mysqli_query($connect, $sql)) {
                                            $cnt = 1;

                                            // Check if any records are returned
                                            if (mysqli_num_rows($result) > 0) {
                                                // Loop through each record
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($cnt); ?></td>
                                                        <td><?php echo htmlentities($row['LeaveType']); ?></td>
                                                        <td><?php echo htmlentities($row['Description']); ?></td>
                                                        <td><?php echo htmlentities($row['CreationDate']); ?></td>
                                                        <td>
                                                            <a href="editleavetype.php?lid=<?php echo htmlentities($row['id']); ?>">
                                                                <i class="material-icons">mode_edit</i>
                                                            </a>
                                                            <a href="manageleavetype.php?del=<?php echo htmlentities($row['id']); ?>" 
                                                               onclick="return confirm('Do you want to delete');">
                                                                <i class="material-icons">delete_forever</i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    $cnt++;
                                                }
                                            } else {
                                                // If no records found
                                                echo "<tr><td colspan='5'>No records found</td></tr>";
                                            }
                                            // Free result set
                                            mysqli_free_result($result);
                                        } else {
                                            // Display error if query fails
                                            echo "Error: " . mysqli_error($connect);
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
        <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
        <script src="assets/plugins/datatables/js/jquery.dataTables.min.js"></script>
        <script src="assets/js/alpha.min.js"></script>
        <script src="assets/js/pages/table-data.js"></script>
        
    </body>
</html>
<?php } ?>