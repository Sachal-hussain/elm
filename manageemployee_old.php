<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
    {   
header('location:index.php');
}

 ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        
        <!-- Title -->
        <title>Admin | Manage Employees</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        <meta name="author" content="Naeem Amin" />
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
                        <div class="page-title">Manage Employees</div>
                    </div>
                   
                    <div class="col s12 m12 l12">
                        <div class="card">
                            <div class="card-content">
                                <span class="card-title">Employees Info</span>
                                <?php if($msg){?><div class="succWrap"><strong>SUCCESS</strong> : <?php echo htmlentities($msg); ?> </div><?php }?>
                                <table id="example" class="display responsive-table ">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Agent ID</th>
                                            <th>Full Name</th>
                                           <!--  <th>Department</th> -->
                                            <th>Shift</th>
                                           <!--  <th>Type</th> -->
                                            <th>Sick</th>
                                            <th>Annual</th>
                                        </tr>
                                    </thead>
                                 
                                    <tbody>
                                        <?php $sql = "SELECT 
                                            user.id AS userid, 
                                            user.agentid, 
                                            user.fullname, 
                                            user.shift, 
                                            user.department, 
                                            user.type, 
                                            employee_leaves.empid AS leave_empid, 
                                            employee_leaves.sick_leave_remaining, 
                                            employee_leaves.annual_leave_remaining, 
                                            employee_leaves.id AS emp_id
                                        FROM 
                                            user
                                        LEFT JOIN 
                                            employee_leaves 
                                        ON 
                                            employee_leaves.empid = user.id
                                        WHERE user.status='Active' 
                                        AND user.department IN ('Redeem', 'Live Chat', 'Q&A')
                                        ORDER BY user.fullname ASC   
                                        ";
                                        $result = $connect->query($sql);

                                        $cnt = 1;

                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) { ?>
                                                <tr>
                                                    <td> <?php echo htmlentities($cnt); ?></td>
                                                    <td><?php echo htmlentities($row['agentid']); ?></td>
                                                    <td>
                                                        <a href="leavehistory.php?empid=<?php echo htmlentities($row['userid']); ?>"> <?php echo htmlentities($row['fullname']); ?>
                                                        </a>
                                                    </td>
                                                   
                                                    <td><?php echo htmlentities($row['shift']); ?></td>
                                                   
                                                    <td><?php echo htmlentities($row['sick_leave_remaining']); ?></td>
                                                    <td><?php echo htmlentities($row['annual_leave_remaining']); ?></td>
                                                    
                                                </tr>
                                            <?php 
                                                $cnt++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='7'>No records found</td></tr>";
                                        }

                                        // Close the connection
                                        $connect->close();?>
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
