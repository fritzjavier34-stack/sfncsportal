<?php
include('../assets/config.php');

if(isset($_POST['sheet_id'])) {
    $sheet_id = mysqli_real_escape_string($conn, $_POST['sheet_id']);
    $student_name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $row_index = mysqli_real_escape_string($conn, $_POST['row_index']);
    $scores = mysqli_real_escape_string($conn, $_POST['scores']); 
    $transmuted = isset($_POST['transmuted']) ? (int)$_POST['transmuted'] : 0;

    // 1. I-save sa student_data (Dito pumapasok lahat pati PERFECT score)
    $check = mysqli_query($conn, "SELECT id FROM student_data WHERE sheet_id='$sheet_id' AND row_index='$row_index'");
    
    if(mysqli_num_rows($check) > 0) {
        $query = "UPDATE student_data SET student_name='$student_name', scores_json='$scores' WHERE sheet_id='$sheet_id' AND row_index='$row_index'";
    } else {
        $query = "INSERT INTO student_data (sheet_id, student_name, row_index, scores_json) VALUES ('$sheet_id', '$student_name', '$row_index', '$scores')";
    }
    mysqli_query($conn, $query);

    // 2. I-save sa final_grades (Para lang sa mga estudyante, skip ang PERFECT row)
    if($row_index !== 'PERFECT' && !empty($student_name) && $student_name !== 'TOTAL PERFECT SCORE') {
        // ... (yung existing final_grades logic mo dito)
    }
    echo "Success";
}
?>