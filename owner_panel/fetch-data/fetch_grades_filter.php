<?php
include('../assets/config.php');

if(isset($_POST['class_id']) && isset($_POST['section_id'])) {
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $section_id = mysqli_real_escape_string($conn, $_POST['section_id']);

    // Kunin ang names para i-match sa published_grades
    $c_query = mysqli_fetch_assoc(mysqli_query($conn, "SELECT class_name FROM classes WHERE id = '$class_id'"));
    $s_query = mysqli_fetch_assoc(mysqli_query($conn, "SELECT section_name FROM sections WHERE id = '$section_id'"));
    
    $class_name = $c_query['class_name'];
    $section_name = $s_query['section_name'];

    // Query published_grades base sa nahanap na Class at Section
    // REVISION: PENDING lang ang ipapakita para mawala sa listahan kapag nireject
    $sql = "SELECT * FROM published_grades 
            WHERE section = '$section_name' 
            AND status = 'PENDING' 
            ORDER BY student_name ASC";
            
    $res = mysqli_query($conn, $sql);

    if(mysqli_num_rows($res) > 0) {
        while($row = mysqli_fetch_assoc($res)) {
            echo "<tr>
                <td class='fw-bold'>{$row['student_name']}</td>
                <td>
                    <small class='text-muted d-block'>{$row['subject_code']}</small>
                    {$row['subject_description']}
                </td>
                <td class='text-center'>".($row['midterm_grade'] ?: '-')."</td>
                <td class='text-center'>".($row['final_term_grade'] ?: '-')."</td>
                <td class='text-center fw-bold text-primary'>{$row['semestral_grade']}</td>
                <td class='text-center'>
                    <a href='view_student_portal.php?name=".urlencode($row['student_name'])."' 
                       target='_blank' class='btn btn-outline-info btn-sm'>
                       <i class='fa-solid fa-eye'></i>
                    </a>
                </td>
                <td>
                    <div class='btn-group'>
                        <button onclick='processGrade({$row['id']}, \"approve\")' class='btn btn-success btn-sm'>Approve</button>
                        <button onclick='processGrade({$row['id']}, \"reject\")' class='btn btn-danger btn-sm'>Reject</button>
                    </div>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='7' class='text-center py-4 text-muted'>No pending grades for this section.</td></tr>";
    }
}
?>