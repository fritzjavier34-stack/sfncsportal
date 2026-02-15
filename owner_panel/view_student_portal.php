<?php 
include("../assets/config.php"); 
session_start();

// Kunin ang pangalan mula sa URL
$student_full_name = isset($_GET['name']) ? $_GET['name'] : ""; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Portal - <?php echo $student_full_name; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="../student/style.css"> 
    
    <style>
        /* Pinagsamang style para sa Navbar at Preview Banner */
        body { background: #f6f6f9; }
        .preview-banner { 
            background: #ffc107; 
            color: #000; 
            text-align: center; 
            padding: 8px; 
            font-weight: bold;
            font-size: 0.9rem;
        }
        .report-container { 
            background: #fff; 
            border-radius: 2rem; 
            padding: 2rem; 
            box-shadow: 0 2rem 3rem rgba(132, 139, 200, 0.18);
            margin-top: 2rem;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th { border-bottom: 1px solid #eee; padding: 15px; color: #7d8da1; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #f9f9f9; }
        .status-passed { color: #41f1b6; font-weight: 700; }
        .status-failed { color: #ff7782; font-weight: 700; }
        .grade-bold { font-weight: 800; color: #7380ec; }
        
        /* Fix para sa Student CSS conflict sa Navbar */
        .navbar h3 { font-size: 1rem; margin-bottom: 0; }
        .navbar-brand { font-weight: 800; color: #7380ec !important; }

        /* REVISION: Visual indicators for editable fields */
        .edit-field {
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 4px;
            padding: 5px;
        }
        .edit-field:hover {
            background-color: #fff9e6;
            outline: 1px dashed #ffc107;
        }
        .edit-field:focus {
            background-color: #fff;
            outline: 2px solid #7380ec;
            box-shadow: 0 0 8px rgba(115,128,236,0.2);
        }
    </style>
</head>
<body>

    <div class="header">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">SCHOOL MANAGEMENT</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="approve_grades.php" style="color: #ffc107;">
                                Approve Grades 
                                <?php 
                                    $count_q = mysqli_query($conn, "SELECT id FROM published_grades WHERE status = 'PENDING'");
                                    $pending_count = mysqli_num_rows($count_q);
                                    if($pending_count > 0) echo "<span class='badge rounded-pill bg-danger' style='font-size: 10px;'>$pending_count</span>";
                                ?>
                            </a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="notices.php">Notice</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">Fee Pay</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="make-payment.php">PAYROLL</a></li>
                                <li><a class="dropdown-item" href="see-payment.php">See Payment</a></li>
                            </ul>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="change-password.php">Change-Password</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <div class="preview-banner">
        <i class="fa-solid fa-eye me-2"></i> ADMIN PREVIEW MODE: Showing all published grades for <u><?php echo $student_full_name; ?></u>
    </div>

    <div class="container">
        <main>
            <div class="report-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Academic Performance</h1>
                    <div class="text-end">
                        <p class="mb-0 text-muted">Viewing Portal of:</p>
                        <h3 class="fw-bold"><?php echo $student_full_name; ?></h3>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Description</th>
                            <th class="text-center">Midterm</th>
                            <th class="text-center">Finals</th>
                            <th class="text-center">Semestral Grade</th>
                            <th class="text-center">Remarks</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM published_grades 
                                WHERE UPPER(student_name) = UPPER(?) 
                                ORDER BY id DESC";
                        
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $student_full_name);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $remarks = strtoupper($row['remarks']);
                                $class = ($remarks == 'PASSED') ? 'status-passed' : 'status-failed';
                                
                                $status_badge = ($row['status'] == 'APPROVED') ? 
                                    '<span class="badge bg-success">Visible to Student</span>' : 
                                    '<span class="badge bg-warning text-dark">Pending Approval</span>';

                                echo "<tr>
                                        <td>
                                            <div contenteditable='true' class='edit-field' 
                                                 data-id='{$row['id']}' data-column='subject_code' 
                                                 title='Click to edit Code'>{$row['subject_code']}</div>
                                        </td>
                                        <td>
                                            <div contenteditable='true' class='edit-field' 
                                                 data-id='{$row['id']}' data-column='subject_description' 
                                                 title='Click to edit Description'>{$row['subject_description']}</div>
                                        </td>
                                        
                                        <td class='text-center'>".($row['midterm_grade'] ?: '-')."</td>
                                        <td class='text-center'>".($row['final_term_grade'] ?: '-')."</td>
                                        <td class='text-center grade-bold'>".($row['semestral_grade'] ?: '-')."</td>
                                        <td class='text-center $class'>$remarks</td>
                                        <td class='text-center'>$status_badge</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center py-5'>No grades found for this student.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <div class="mt-4 pt-3 border-top text-end">
                    <button onclick="window.close()" class="btn btn-secondary">
                        <i class="fa-solid fa-circle-xmark me-1"></i> Close Preview
                    </button>
                    <a href="approve_grades.php" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Approvals
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        $('.edit-field').on('blur', function() {
            const $this = $(this);
            const id = $this.data('id');
            const column = $this.data('column');
            const newValue = $this.text().trim();

            // Huwag ituloy kung walang laman
            if (newValue === "") {
                alert("Field cannot be empty.");
                location.reload();
                return;
            }

            $.ajax({
                url: 'update_published_inline.php',
                method: 'POST',
                data: {
                    id: id,
                    column: column,
                    value: newValue
                },
                success: function(response) {
                    if(response.trim() === "success") {
                        // Optional: Visual feedback na na-save
                        $this.css('background-color', '#d1e7dd');
                        setTimeout(() => {
                            $this.css('background-color', 'transparent');
                        }, 1000);
                    } else {
                        alert("Error saving: " + response);
                        location.reload();
                    }
                }
            });
        });
    });
    </script>

</body>
</html>