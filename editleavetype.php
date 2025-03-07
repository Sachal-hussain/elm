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
    // Get the ID of the leave type from the URL
    $lid = intval($_GET['lid']);
    
    // Get form data
    $leavetype = $_POST['leavetype'];
    $description = $_POST['description'];
    
    // Prepare SQL query for updating the leave type
    $sql = "UPDATE tblleavetype SET LeaveType = ?, Description = ? WHERE id = ?";
    
    // Prepare the statement
    if ($stmt = mysqli_prepare($connect, $sql)) {
        // Bind parameters to the query
        mysqli_stmt_bind_param($stmt, "ssi", $leavetype, $description, $lid);
        
        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Success message
            $msg = "Leave type updated successfully";
        } else {
            // Error message
            $msg = "Error updating record: " . mysqli_error($connect);
        }
        
        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        // Error preparing the statement
        $msg = "Error preparing query: " . mysqli_error($connect);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        
        <!-- Title -->
        <title>Admin | Edit Leave Type</title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        <meta name="description" content="Responsive Admin Dashboard Template" />
        <meta name="keywords" content="admin,dashboard" />
        <meta name="author" content="Steelcoders" />
        
        <!-- Styles -->
        <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
        <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet"> 
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
                        <div class="page-title">Edit Leave Type</div>
                    </div>
                    <div class="col s12 m12 l6">
                        <div class="card">
                            <div class="card-content">
                              
                                <div class="row">
                                    <form class="col s12" name="chngpwd" method="post">
                                        <?php if($error){?><div class="errorWrap"><strong>ERROR</strong> : <?php echo htmlentities($error); ?> </div><?php } 
                                        else if($msg){?><div class="succWrap"><strong>SUCCESS</strong> : <?php echo htmlentities($msg); ?> </div><?php }?>
                                            <?php
                                            $lid = intval($_GET['lid']); // Sanitize input to ensure it's an integer

                                            // Prepare the SQL query
                                            $sql = "SELECT * FROM tblleavetype WHERE id = ?";
                                            if ($stmt = mysqli_prepare($connect, $sql)) {
                                                // Bind the parameter
                                                mysqli_stmt_bind_param($stmt, "i", $lid);
                                                
                                                // Execute the statement
                                                mysqli_stmt_execute($stmt);
                                                
                                                // Get the result
                                                $result = mysqli_stmt_get_result($stmt);
                                                
                                                // Check if rows were found
                                                if (mysqli_num_rows($result) > 0) {
                                                    // Fetch the results
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                    ?>
                                                    <div class="row">
                                                        <div class="input-field col s12">
                                                            <input id="leavetype" type="text" class="validate" autocomplete="off" name="leavetype" value="<?php echo htmlentities($row['LeaveType']); ?>" required>
                                                            <label for="leavetype">Leave Type</label>
                                                        </div>

                                                        <div class="input-field col s12">
                                                            <textarea id="textarea1" name="description" class="materialize-textarea" length="500"><?php echo htmlentities($row['Description']); ?></textarea>
                                                            <label for="deptshortname">Description</label>
                                                        </div>
                                                    </div>
                                                    <?php
                                                    }
                                                } else {
                                                    echo "<p>No record found.</p>";
                                                }
                                                
                                                // Close the statement
                                                mysqli_stmt_close($stmt);
                                            } else {
                                                echo "<p>Error preparing query: " . mysqli_error($connect) . "</p>";
                                            }
                                            ?>



                                            <div class="input-field col s12">
                                            <button type="submit" name="update" class="waves-effect waves-light btn indigo m-b-xs">Update</button>

                                            </div>




                                        </div>
                                       
                                    </form>
                                </div>
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
        <script src="assets/js/alpha.min.js"></script>
        <script src="assets/js/pages/form_elements.js"></script>
        
    </body>
</html>
<?php } ?> 