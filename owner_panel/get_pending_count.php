<?php
include('../assets/config.php');
$query = mysqli_query($conn, "SELECT id FROM published_grades WHERE status = 'PENDING'");
echo mysqli_num_rows($query);
?>