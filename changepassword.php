<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
    {   
header('location:index.php');
}
else{
// Code for change password 
if (isset($_POST['change'])) {
    $password = md5($_POST['password']); // Current password
    $newpassword = md5($_POST['newpassword']); // New password
    $username = $_SESSION['alogin']; // Logged-in username

    // Assuming $connect is your MySQLi connection
    // Step 1: Verify the current password
    $sql = "SELECT Password FROM admin WHERE UserName = ? AND Password = ?";
    $stmt = $connect->prepare($sql);

    if ($stmt) {
        // Bind parameters
        $stmt->bind_param('ss', $username, $password);

        // Execute query
        $stmt->execute();

        // Store the result
        $stmt->store_result();

        // Check if the user exists with the given password
        if ($stmt->num_rows > 0) {
            // Step 2: Update the password
            $stmt->close(); // Close the first statement

            $update_sql = "UPDATE admin SET Password = ? WHERE UserName = ?";
            $update_stmt = $connect->prepare($update_sql);

            if ($update_stmt) {
                // Bind parameters
                $update_stmt->bind_param('ss', $newpassword, $username);

                // Execute update
                if ($update_stmt->execute()) {
                    $msg = "Your Password successfully changed";
                } else {
                    $error = "Failed to update password: " . $update_stmt->error;
                }

                // Close the update statement
                $update_stmt->close();
            } else {
                $error = "Failed to prepare update query: " . $connect->error;
            }
        } else {
            $error = "Your current password is wrong";
        }

        // Close the initial statement
        $stmt->close();
    } else {
        $error = "Failed to prepare query: " . $connect->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        
        <!-- Title -->
        <title>Admin | Change Password</title>
        
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
    </head>
    <body>
  <?php include('includes/header.php');?>
            
       <?php include('includes/sidebar.php');?>
            <main class="mn-inner">
                <div class="row">
                    <div class="col s12">
                        <div class="page-title">Change Pasword</div>
                    </div>
                    <div class="col s12 m12 l6">
                        <div class="card">
                            <div class="card-content">
                              
                                <div class="row">
                                    <form class="col s12" name="chngpwd" method="post">
                                        <?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php } 
                                        else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>
                                        <div class="row">
                                            <div class="input-field col s12">
                                            <input id="password" type="password"  class="validate" autocomplete="off" name="password"  required>
                                                <label for="password">Current Password</label>
                                            </div>

                                            <div class="input-field col s12">
                                                <input id="password" type="password" name="newpassword" class="validate" autocomplete="off" required>
                                                <label for="password">New Password</label>
                                            </div>

                                            <div class="input-field col s12">
                                            <input id="password" type="password" name="confirmpassword" class="validate" autocomplete="off" required>
                                             <label for="password">Confirm Password</label>
                                            </div>


                                            <div class="input-field col s12">
                                            <button type="submit" name="change" class="waves-effect waves-light btn indigo m-b-xs" onclick="return valid();">Change</button>

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