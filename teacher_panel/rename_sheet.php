<?php
include('../assets/config.php');

if(isset($_POST['id']) && isset($_POST['sheet_name'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $sheet_name = mysqli_real_escape_string($conn, $_POST['sheet_name']);
    
    // UPDATE: sheet_name lang ang gagalawin natin
    $query = "UPDATE grade_sheets SET sheet_name = '$sheet_name' WHERE id = '$id'";
    
    if(mysqli_query($conn, $query)){
        echo "success";
    } else {
        echo "error";
    }
}
?>