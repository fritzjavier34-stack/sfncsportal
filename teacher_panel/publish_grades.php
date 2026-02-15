<?php
include('../assets/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $period = mysqli_real_escape_string($conn, $_POST['period']);
    $sub_code = mysqli_real_escape_string($conn, $_POST['subject_code']);
    $sub_desc = mysqli_real_escape_string($conn, $_POST['subject_desc']);
    $students = json_decode($_POST['students'], true);

    if (!empty($students)) {
        foreach ($students as $s) {
            // Pinipilit nating maging "LASTNAME, FIRSTNAME" format at ALL CAPS
            $name = mysqli_real_escape_string($conn, strtoupper(trim($s['name'])));
            $grade = (float)$s['grade'];

            $check = mysqli_query($conn, "SELECT id, midterm_grade, final_term_grade FROM published_grades 
                                        WHERE UPPER(TRIM(student_name)) = '$name' AND subject_code = '$sub_code'");
            
            if (mysqli_num_rows($check) > 0) {
                $row = mysqli_fetch_assoc($check);
                $id = $row['id'];
                $mid = ($period == 'MIDTERM') ? $grade : $row['midterm_grade'];
                $fin = ($period == 'FINAL') ? $grade : $row['final_term_grade'];
                $sem = ($mid > 0 && $fin > 0) ? ($mid + $fin) / 2 : ($mid ?: $fin);
                $rem = ($sem >= 75) ? 'PASSED' : 'FAILED';

                mysqli_query($conn, "UPDATE published_grades SET 
                    midterm_grade = ".($mid ? "'$mid'" : "NULL").", 
                    final_term_grade = ".($fin ? "'$fin'" : "NULL").", 
                    semestral_grade = '$sem', 
                    remarks = '$rem',
                    subject_description = '$sub_desc' 
                    WHERE id = '$id'");
            } else {
                $mid_val = ($period == 'MIDTERM') ? "'$grade'" : "NULL";
                $fin_val = ($period == 'FINAL') ? "'$grade'" : "NULL";
                $rem = ($grade >= 75) ? 'PASSED' : 'FAILED';

                $sql = "INSERT INTO published_grades (student_name, section, subject_code, subject_description, midterm_grade, final_term_grade, semestral_grade, remarks) 
                        VALUES ('$name', '$section', '$sub_code', '$sub_desc', $mid_val, $fin_val, '$grade', '$rem')";
                mysqli_query($conn, $sql);
            }
        }
        echo "Grades successfully published!";
    } else {
        echo "Error: No students found in the list.";
    }
}
?>