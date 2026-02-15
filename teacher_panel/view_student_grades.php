<?php
include('../assets/config.php');

// AJAX handler para sa delete at update ng records
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'delete_published') {
        $pub_id = mysqli_real_escape_string($conn, $_POST['id']);
        if(mysqli_query($conn, "DELETE FROM published_grades WHERE id = '$pub_id'")) {
            exit("success");
        }
        exit("error");
    }

    if ($_POST['action'] == 'update_field') {
        $pub_id = mysqli_real_escape_string($conn, $_POST['id']);
        $field = mysqli_real_escape_string($conn, $_POST['field']);
        $value = mysqli_real_escape_string($conn, $_POST['value']);
        
        // Map 'subject' field back to database column 'subject_description'
        $column = ($field == 'subject') ? 'subject_description' : 'subject_code';
        
        if(mysqli_query($conn, "UPDATE published_grades SET $column = '$value' WHERE id = '$pub_id'")) {
            exit("success");
        }
        exit("error");
    }
}

if (!isset($_GET['id'])) {
    die("Student ID is missing.");
}

$student_id = mysqli_real_escape_string($conn, $_GET['id']);

$sheet_id = isset($_GET['sheet_id']) ? mysqli_real_escape_string($conn, $_GET['sheet_id']) : '';

// Kuhanin ang info ng student
$stud_res = mysqli_query($conn, "SELECT * FROM students WHERE id = '$student_id'");
$student = mysqli_fetch_assoc($stud_res);

if (!$student) {
    die("Student not found.");
}

$fullname = strtoupper($student['lname']) . ", " . $student['fname'];
?>

<?php include('partials/_header.php'); ?>
<?php include('partials/_sidebar.php'); ?>

<style>
    /* Custom styles para sa grade view table */
    .grade-container { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
    .student-header { border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; }
    .student-header h2 { color: #1e3a8a; font-weight: 800; margin: 0; }
    
    .table-custom { width: 100%; border-collapse: collapse; }
    .table-custom th { background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; padding: 12px; border: 1px solid #e2e8f0; }
    .table-custom td { padding: 12px; border: 1px solid #e2e8f0; font-size: 14px; }
    
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
    .badge-approved { background: #dcfce7; color: #15803d; }
    .badge-pending { background: #fef9c3; color: #a16207; }
    /* REVISION: Added Rejected Badge Style */
    .badge-rejected { background: #fee2e2; color: #ef4444; }
    
    .btn-delete { color: #ef4444; background: #fee2e2; border: none; padding: 6px; border-radius: 4px; cursor: pointer; transition: 0.2s; }
    .btn-delete:hover { background: #fecaca; }

    /* REVISION: Return Button Design matching generate_sheet.php */
.btn-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        height: 42px;
        padding: 0 20px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none !important;
        background: #64748b; /* Kulay mula sa screenshot mo */
        color: white !important;
        border: none;
        transition: 0.2s ease;
    }
    .btn-back:hover { opacity: 0.9; transform: translateY(-1px); }
    /* Editable styling */
    [contenteditable="true"]:focus {
        outline: 2px solid #2563eb;
        background: #fff;
    }
</style>

<div class="content">
    <?php include('partials/_navbar.php'); ?>
    
    <div class="main-content">
        <div class="grade-container">
<div class="student-header">
    <div style="display:flex; justify-content: space-between; align-items: center;">
        <div>
            <h2><?php echo $fullname; ?></h2>
            </div>
        
        <a href="generate_sheet.php?id=<?php echo $sheet_id; ?>" class="btn-back">
            <i class='bx bx-chevron-left' style="font-size: 22px;"></i> Return
        </a>
    </div>
</div>

            <h4 style="font-weight: 700; margin-bottom: 15px;"><i class='bx bx-list-ul'></i> Published Academic Records</h4>
            
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject</th> <th style="text-align:center;">Midterm</th>
                        <th style="text-align:center;">Final</th>
                        <th style="text-align:center;">Semestral</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $pub_q = mysqli_query($conn, "SELECT * FROM published_grades WHERE UPPER(TRIM(student_name)) = '$fullname' ORDER BY id DESC");
                    
                    if(mysqli_num_rows($pub_q) > 0):
                        while($prow = mysqli_fetch_assoc($pub_q)):
                            $status = strtoupper($prow['status'] ?? 'PENDING');
                            
                            // REVISION: Logic for Badge colors including REJECTED
                            if ($status == 'APPROVED') {
                                $badge_class = 'badge-approved';
                            } elseif ($status == 'REJECTED') {
                                $badge_class = 'badge-rejected';
                            } else {
                                $badge_class = 'badge-pending';
                            }
                    ?>
                    <tr id="row_<?php echo $prow['id']; ?>">
                        <td contenteditable="true" 
                            onblur="updateRecord(<?php echo $prow['id']; ?>, 'code', this.innerText)"
                            style="font-weight: 700; color: #2563eb; cursor: pointer;">
                            <?php echo $prow['subject_code']; ?>
                        </td>
                        <td contenteditable="true" 
                            onblur="updateRecord(<?php echo $prow['id']; ?>, 'subject', this.innerText)"
                            style="cursor: pointer;">
                            <?php echo $prow['subject_description']; ?>
                        </td>
                        <td align="center"><?php echo $prow['midterm_grade'] ?: '-'; ?></td>
                        <td align="center"><?php echo $prow['final_term_grade'] ?: '-'; ?></td>
                        <td align="center" style="font-weight: 800; color: #166534;"><?php echo $prow['semestral_grade']; ?></td>
                        <td align="center">
                            <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                        </td>
                        <td align="center">
                            <button onclick="deleteGrade(<?php echo $prow['id']; ?>)" class="btn-delete">
                                <i class='bx bx-trash-alt'></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="7" align="center" style="padding: 30px; color: #94a3b8; font-style: italic;">No published records found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// AJAX function para sa pag-update ng Subject Code at Name
function updateRecord(id, field, value) {
    const formData = new FormData();
    formData.append('action', 'update_field');
    formData.append('id', id);
    formData.append('field', field);
    formData.append('value', value.trim());

    fetch('view_student_grades.php?id=<?php echo $student_id; ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if(data.trim() !== "success") {
            alert("Error updating record.");
            location.reload();
        }
    });
}

function deleteGrade(id) {
    if(confirm("Sigurado ka bang gusto mong burahin ang grade na ito?")) {
        const formData = new FormData();
        formData.append('action', 'delete_published');
        formData.append('id', id);

        fetch('view_student_grades.php?id=<?php echo $student_id; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if(data.trim() === "success") {
                document.getElementById('row_' + id).remove();
            } else {
                alert("May error sa pagbura ng grade.");
            }
        });
    }
}
</script>

<?php include('partials/_footer.php'); ?>