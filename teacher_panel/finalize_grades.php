<?php
include 'db.php';
include 'teacher_guard.php';

$subject = intval($_POST['subject_id']);

mysqli_query($conn, "
UPDATE grades
SET is_finalized = 1
WHERE subject_id = $subject
");