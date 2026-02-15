<?php
include('../assets/config.php');
include('partials/_header.php');
// I-assume natin na ang $logged_in_name ay galing sa session ng student
$student_full_name = $_SESSION['student_name']; 

$query = mysqli_query($conn, "SELECT * FROM final_grades WHERE student_name = '$student_full_name' ORDER BY created_at DESC");
?>

<div class="content">
    <div class="main-content">
        <div class="card" style="max-width:800px; margin:auto; padding:20px;">
            <div class="text-center mb-4">
                <h4>Progress Report Card</h4>
                <p>Student: <strong><?php echo $student_full_name; ?></strong></p>
            </div>

            <table class="table table-bordered text-center">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th>Subject / Section</th>
                        <th>Final Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($query) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><?php echo $row['section_name']; ?></td>
                            <td style="font-weight:bold; <?php echo ($row['transmuted_grade'] < 75) ? 'color:red;' : ''; ?>">
                                <?php echo $row['transmuted_grade']; ?>
                            </td>
                            <td>
                                <?php if($row['transmuted_grade'] >= 75): ?>
                                    <span class="badge badge-success">Passed</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Failed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No grades published yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>