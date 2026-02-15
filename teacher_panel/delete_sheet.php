<?php
include('../assets/config.php');

if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // SQL to delete
    $sql = "DELETE FROM grade_sheets WHERE id = '$id'";
    
    if(mysqli_query($conn, $sql)) {
        // Redirect pabalik sa marks.php na may success message (optional)
        header("Location: marks.php?msg=deleted");
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
} else {
    header("Location: marks.php");
}
?>