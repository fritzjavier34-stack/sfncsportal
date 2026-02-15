<?php
// attendance_action.php
include('../assets/config.php');

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action == 'fetch_grid') {
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $month = (int)$_POST['month'];
    $year = (int)$_POST['year'];

    $students = [];
    $sql_stud = "SELECT id, fname, lname FROM students WHERE class='$class' AND section='$section' ORDER BY lname ASC";
    $res_stud = mysqli_query($conn, $sql_stud);
    
    while($row = mysqli_fetch_assoc($res_stud)) {
        $row['attendance'] = [];
        // REVISION: Keep ID as a string to support "S1718..." format
        $students[(string)$row['id']] = $row;
    }

    $start_date = "$year-$month-01";
    $end_date = date("Y-m-t", strtotime($start_date));

    $sql_att = "SELECT student_id, DAY(attendance_date) as day, status_code 
                FROM attendance_logs 
                WHERE class_name='$class' AND section_name='$section' 
                AND attendance_date BETWEEN '$start_date' AND '$end_date'";
    
    $res_att = mysqli_query($conn, $sql_att);
    while($row = mysqli_fetch_assoc($res_att)) {
        $sid = (string)$row['student_id'];
        if(isset($students[$sid])) {
            $students[$sid]['attendance'][$row['day']] = $row['status_code'];
        }
    }

    echo json_encode([
        'status' => 'success', 
        'students' => array_values($students), 
        'days_in_month' => (int)date("t", strtotime($start_date))
    ]);
    exit;
}

if ($action == 'get_sections_by_class') {
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $sections = [];
    // REVISION: Pull sections directly from your students table dynamically
    $sql = "SELECT DISTINCT section FROM students WHERE class = '$class_id' AND section != ''";
    $res = mysqli_query($conn, $sql);
    while($r = mysqli_fetch_assoc($res)) { $sections[] = $r['section']; }

    echo json_encode(['status' => 'success', 'sections' => $sections]);
    exit;
}

if ($action == 'save_attendance') {
    $data = json_decode($_POST['attendance_data'], true);
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $month = (int)$_POST['month'];
    $year = (int)$_POST['year'];

    foreach ($data as $record) {
        // REVISION: Removed (int) casting. Using string escape for alphanumeric IDs.
        $student_id = mysqli_real_escape_string($conn, $record['student_id']);
        $day = (int)$record['day'];
        $code = (int)$record['code'];

        if ($code < 1 || $code > 4) continue;

        $date_str = "$year-$month-$day";
        
        $sql = "INSERT INTO attendance_logs (student_id, class_name, section_name, attendance_date, status_code) 
                VALUES ('$student_id', '$class', '$section', '$date_str', '$code') 
                ON DUPLICATE KEY UPDATE status_code = '$code'";
        mysqli_query($conn, $sql);
    }

    echo json_encode(['status' => 'success']);
    exit;
}
?>