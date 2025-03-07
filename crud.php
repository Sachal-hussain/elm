<?php
include('includes/config.php');
$res=mysqli_query($connect,"select * from tblleaves");
?>
<a href="javascript:void(0)" class="link_approve" onclick="approve_all()">Approve</a>
<form method="post" id="frm">
	<table id="customers">
		<tr>
			<th width="15%"><input type="checkbox" onclick="select_all()"  id="select_all"/></th>
			<th>#</th>
            <th>Leave Type</th>
            <th>Date Range</th>
            <th>Requesting Date</th>
            <th>Reason</th>
            <th>Remarks</th>                 
            <th>Status</th>
           
		</tr>
		<?php
		while($row=mysqli_fetch_assoc($res)){
			?>
			<tr id="box<?php echo $row['id']?>">
				<td><input type="checkbox" id="<?php echo $row['id']?>" name="checkbox[]" value="<?php echo $row['id']?>"/></td>
				<td><?php echo $row['id']?></td>
				<td><?php echo $row['LeaveType']?></td>
				<td><?php echo $row['FromDate']?></td>
				<td><?php echo $row['PostingDate']?></td>
				<td><?php echo $row['Description']?></td>
				<td><?php echo $row['AdminRemark']?></td>
				<td><?php echo $row['Status']?></td>
			</tr>
			<?php
		}
		?>
	</table>
</form>
<script
  src="https://code.jquery.com/jquery-3.6.0.min.js"
  ></script>
<script>
function select_all(){
	if(jQuery('#select_all').prop("checked")){
		jQuery('input[type=checkbox]').each(function(){
			jQuery('#'+this.id).prop('checked',true);
		});
	}else{
		jQuery('input[type=checkbox]').each(function(){
			jQuery('#'+this.id).prop('checked',false);
		});
	}
}

function approve_all() {
    var check = confirm("Are you sure you want to approve the selected leaves?");
    if (check == true) {
        jQuery.ajax({
            url: 'delete.php',  // Make sure this points to the correct file for updating status
            type: 'post',
            data: jQuery('#frm').serialize(),
            success: function(result) {
                // On success, update the status in the table to 1 (Approved)
                jQuery('input[type=checkbox]').each(function(){
                    if (jQuery('#' + this.id).prop("checked")) {
                        // Change the status in the table to '1' (Approved)
                        jQuery('#box' + this.id).find('td:last').text('1'); 
                    }
                });
            }
        });
    }
}

</script>

<style>
#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 30%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #4CAF50;
  color: white;
}
#frm{
	margin-top:10px;
}
.link_approve{
	font-size: 20px;
    color: black;
    font-family: arial;
}
</style>