<?php
include('../assets/config.php');

if (isset($_POST['class_id']) && isset($_POST['section_id'])) {
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $section_id = mysqli_real_escape_string($conn, $_POST['section_id']);

    // Query using the correct table 'students' and joining with 'published_grades'
    $sql = "SELECT pg.* FROM published_grades pg
            INNER JOIN students sd ON (pg.student_name = CONCAT(sd.fname, ' ', sd.lname) OR pg.student_name = CONCAT(sd.lname, ', ', sd.fname))
            WHERE sd.class = '$class_id' 
            AND sd.section = '$section_id'
            AND pg.status = 'PENDING'
            ORDER BY pg.student_name ASC";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                <td class='fw-bold'>".htmlspecialchars($row['student_name'])."</td>
                <td>".htmlspecialchars($row['subject_description'])."</td>
                <td class='text-center'>{$row['midterm_grade']}</td>
                <td class='text-center'>{$row['final_term_grade']}</td>
                <td class='text-center fw-bold text-primary'>{$row['semestral_grade']}</td>
                
                <td class='text-center'>
                    <a href='view_student_portal.php?name=".urlencode($row['student_name'])."' target='_blank' class='btn btn-outline-info btn-sm'>
                        <i class='fa-solid fa-eye'></i> View
                    </a>
                </td>

                <td class='text-center'>
                    <div class='btn-group'>
                        <button onclick=\"processGrade({$row['id']}, 'approve')\" class='btn btn-success btn-sm'>Approve</button>
                        <button onclick=\"processGrade({$row['id']}, 'reject')\" class='btn btn-danger btn-sm'>Reject</button>
                    </div>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='7' class='text-center py-4 text-muted'>No pending grades found.</td></tr>";
    }
}
?>