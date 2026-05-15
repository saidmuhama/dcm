<?php
include('../config/db.php'); 
if(isset($_POST['main_id'])){

    $main_id = intval($_POST['main_id']);

    $sql = mysqli_query($db, "SELECT * FROM tbl_sub_academic_levels  WHERE main_level = $main_id");

    if(mysqli_num_rows($sql) > 0){

        echo '<option value="">Select Level</option>';

        while($row = mysqli_fetch_assoc($sql)){
            echo '<option value="'.$row['id'].'">'.$row['sub_level_title'].'</option>';
        }

    } else {
        echo '<option>No data found</option>';
    }

} else {
    echo '<option>Invalid request</option>';
}