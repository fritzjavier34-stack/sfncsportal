<?php
include('../assets/config.php'); 

if (isset($_POST['class_id'])) {
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);

    // PINALITAN: 'students' na ang table name base sa database screenshot mo
    $query = "SELECT DISTINCT section FROM students WHERE class = '$class_id' AND section != '' ORDER BY section ASC";
    $result = mysqli_query($conn, $query);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            echo '<option value="" selected disabled>Select Section</option>';
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<option value="' . htmlspecialchars($row['section']) . '">' . htmlspecialchars($row['section']) . '</option>';
            }
        } else {
            echo '<option value="">No Section Found</option>';
        }
    } else {
        echo '<option value="">SQL Error: ' . mysqli_error($conn) . '</option>';
    }
}
?>