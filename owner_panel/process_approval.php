<?php
include('../assets/config.php');

if(isset($_POST['id']) && isset($_POST['action'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $action = $_POST['action'];
    
    // Kapag approve, gawing 'APPROVED'. Kapag reject, pwedeng 'REJECTED' o burahin.
    $status = ($action == 'approve') ? 'APPROVED' : 'REJECTED';
    
    $query = "UPDATE published_grades SET status = '$status' WHERE id = '$id'";
    
    if(mysqli_query($conn, $query)){
        echo "success";
    } else {
        echo "error";
    }
}
?>