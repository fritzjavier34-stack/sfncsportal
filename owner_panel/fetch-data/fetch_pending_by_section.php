<?php
include('../assets/config.php');

if (isset($_POST['class_id']) && isset($_POST['section_id'])) {
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $section_id = mysqli_real_escape_string($conn, $_POST['section_id']); // Ito ay pangalan ng section (hal. Sylvester)

    // QUERY: Join ang published_grades at student_details para masala gamit ang Class at Section
    $sql = "SELECT pg.* FROM published_grades pg
            INNER JOIN student_details sd ON pg.student_name = CONCAT(sd.fname, ' ', sd.lname)
            WHERE sd.class = '$class_id' 
            AND sd.section = '$section_id'
            AND pg.status = 'PENDING'
            ORDER BY pg.student_name ASC";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            ?>
            <tr>
                <td class="fw-bold"><?php echo htmlspecialchars($row['student_name']); ?></td>
                <td>
                    <div class="small text-muted"><?php echo htmlspecialchars($row['subject_code']); ?></div>
                    <div><?php echo htmlspecialchars($row['subject_description']); ?></div>
                </td>
                <td class="text-center"><?php echo $row['midterm_grade'] ?: '-'; ?></td>
                <td class="text-center"><?php echo $row['final_term_grade'] ?: '-'; ?></td>
                <td class="text-center fw-bold text-primary"><?php echo $row['semestral_grade']; ?></td>
                <td class="text-center">
                    <a href="view_student_portal.php?name=<?php echo urlencode($row['student_name']); ?>" target="_blank" class="btn btn-outline-info btn-sm">
                        <i class="fa-solid fa-eye"></i> View
                    </a>
                </td>
                <td>
                    <div class="btn-group">
                        <button onclick="processGrade(<?php echo $row['id']; ?>, 'approve')" class="btn btn-success btn-sm">Approve</button>
                        <button onclick="processGrade(<?php echo $row['id']; ?>, 'reject')" class="btn btn-danger btn-sm">Reject</button>
                    </div>
                </td>
            </tr>
            <?php
        }
    } else {
        echo "<tr><td colspan='7' class='text-center py-5 text-muted'>No pending grades found for Section: <b>$section_id</b></td></tr>";
    }
}
?>