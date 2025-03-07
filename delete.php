<?php
include('includes/config.php');

if(isset($_POST['checkbox'])){
    $ids = $_POST['checkbox'];
    foreach($ids as $id) {
        // Update the status of the selected leaves to 1 (approved)
        $query = "UPDATE tblleaves SET Status = 1 WHERE id = $id AND Status = 0";
        mysqli_query($connect, $query);
    }
}
?>