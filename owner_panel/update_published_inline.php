<?php
include("../assets/config.php");
session_start();

// Siguraduhin na Admin/Owner lang ang pwedeng mag-access nito
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $column = mysqli_real_escape_string($conn, $_POST['column']);
    $value = mysqli_real_escape_string($conn, $_POST['value']);

    // Validation: tanging subject_code at subject_description lang ang pwedeng i-edit rito
    if ($column === 'subject_code' || $column === 'subject_description') {
        $query = "UPDATE published_grades SET $column = '$value' WHERE id = '$id'";
        if (mysqli_query($conn, $query)) {
            echo "success";
        } else {
            echo mysqli_error($conn);
        }
    } else {
        echo "Invalid Column";
    }
}
?>