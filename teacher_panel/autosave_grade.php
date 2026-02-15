<?php
include("../../assets/config.php");

mysqli_query($conn,"
REPLACE INTO grades
(student_id,subject_id,fa,wo,pt,st,final_grade)
VALUES(
$_POST[student_id],
$_POST[subject_id],
'$_POST[fa]',
'$_POST[wo]',
'$_POST[pt]',
'$_POST[st]',
$_POST[final]
)");