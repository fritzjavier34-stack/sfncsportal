<?php
include 'db.php';

$subject_id = intval($_GET['subject_id'] ?? $_POST['subject_id']);

$check = mysqli_query($conn, "
    SELECT id FROM subjects
    WHERE id = $subject_id
    AND teacher_id = {$_SESSION['teacher_id']}
");

if (mysqli_num_rows($check) === 0) {
    http_response_code(403);
    exit('Not your subject');
}