<?php
include("../assets/noSessionRedirect.php"); 
include('./fetch-data/verfyRoleRedirect.php');
include('../assets/config.php');

error_reporting(0);
session_start();
$uid = $_SESSION['id'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="../images/1.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Approve Grades - ERP Owner</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/oranbyte-google-translator.css">
    <script src="../js/oranbyte-google-translator.js"></script>
    <style>
        .badge-pending { background: #fef9c3; color: #a16207; padding: 5px 10px; border-radius: 4px; font-weight: 700; }
        .filter-box { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee; }
    </style>
</head>
<body>

    <div class="header">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">SCHOOL MANAGEMENT</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active position-relative" href="approve_grades.php" style="color: #ffc107; font-weight: bold;">
                                Approve Grades 
                                <?php 
                                    $count_q = mysqli_query($conn, "SELECT id FROM published_grades WHERE status = 'PENDING'");
                                    $pending_count = mysqli_num_rows($count_q);
                                    if($pending_count > 0) {
                                        echo "<span class='badge rounded-pill bg-danger' style='font-size: 10px;'>$pending_count</span>";
                                    }
                                ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="notices.php">Notice</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Fee Pay
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="make-payment.php">PAYROLL</a></li>
                                <li><a class="dropdown-item" href="see-payment.php">See Payment</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="change-password.php">Change-Password</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center">
                        <div id="oranbyte-google-translator" class="me-2"
                            data-default-lang="en"
                            data-lang-root-style="code-flag"
                            data-lang-list-style="code-flag">
                        </div>
                        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                        <button class="btn btn-outline-success" type="submit">Search</button>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <div class="container mt-4">
        <div class="filter-box shadow-sm">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Class</label>
                    <select class="form-select" id="search-class">
                        <option value="">Select Class</option>
                        <option value="12c">12</option>
                        <option value="11c">11</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Section</label>
                    <select class="form-select" id="search-section">
                        <option value="">Select Class First</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary w-100" id="btn_find_grades">
                        <i class="fa-solid fa-search me-2"></i> Find Students
                    </button>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary"><i class="fa-solid fa-file-signature me-2"></i>Grade Approval List</h5>
                <p class="text-muted small mb-0" id="filter_label">Select class and section to view pending grades.</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Subject</th>
                                <th class="text-center">Midterm</th>
                                <th class="text-center">Finals</th>
                                <th class="text-center">Semestral</th>
                                <th class="text-center">Portal View</th> 
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="grade_table_body">
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Please use the filters above to load grades.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // 1. KAPAG NAG-CHANGE ANG CLASS
        $('#search-class').on('change', function() {
            var class_id = $(this).val();
            
            if (class_id !== "") {
                $('#search-section').html('<option>Loading...</option>');
                
                $.ajax({
                    url: 'get_sections.php', 
                    type: 'POST',
                    data: { class_id: class_id },
                    success: function(html) {
                        $('#search-section').html(html);
                    },
                    error: function() {
                        alert("Error: Connection failed.");
                    }
                });
            } else {
                $('#search-section').html('<option value="">Select Class First</option>');
            }
        });

        // 2. KAPAG PININDOT ANG FIND STUDENTS
        $('#btn_find_grades').click(function() {
            var class_id = $('#search-class').val();
            var section_id = $('#search-section').val();
            var section_name = $('#search-section option:selected').text();

            if (!class_id || !section_id) {
                alert("Please select Class and Section.");
                return;
            }

            $('#grade_table_body').html('<tr><td colspan="7" class="text-center py-4">Loading data...</td></tr>');
            
            $.ajax({
                url: 'fetch_pending_by_section.php', 
                type: 'POST',
                data: { class_id: class_id, section_id: section_id },
                success: function(response) {
                    $('#grade_table_body').html(response);
                    $('#filter_label').html("Showing pending grades for Section: <b>" + section_name + "</b>");
                },
                error: function() {
                    alert("Error loading students.");
                }
            });
        });
    });

    // REVISED: Pinagsama ang View at Action logic
    function processGrade(gradeId, action) {
        if(confirm("Are you sure you want to " + action + " this grade?")) {
            $.ajax({
                url: 'process_approval.php', // Tiyaking ito ang filename ng iyong action handler
                type: 'POST',
                data: { id: gradeId, action: action },
                success: function(res) {
                    if(res.trim() == "success") {
                        alert("Grade successfully " + action + "ed!");
                        $('#btn_find_grades').click(); // Auto refresh table
                    } else {
                        alert("Error: " + res);
                    }
                }
            });
        }
    }
</script>
</body>
</html>