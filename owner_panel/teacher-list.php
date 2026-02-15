<?php
include("../assets/noSessionRedirect.php"); 
include('./fetch-data/verfyRoleRedirect.php');
include('../assets/config.php');
error_reporting(0);
session_start();
$uid=$_SESSION['id'];
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
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <title>ERP - Teacher List</title>
    <link rel="stylesheet" href="../css/oranbyte-google-translator.css">
    <script src="../js/oranbyte-google-translator.js"></script>
</head>
<body>
<div class="header">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">SCHOOL MANAGEMENT</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="teacher-list.php">Teachers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="student-list.php">Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="approve_grades.php" style="color: #ffc107; font-weight: bold;">
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
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            Fee Pay
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="make-payment.php">PAYROLL</a></li>
                            <li><a class="dropdown-item" href="see-payment.php">See Payment</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center ms-lg-3"> 
                    <div id="oranbyte-google-translator" class="me-2"
                         data-default-lang="en"
                         data-lang-root-style="code-flag"
                         data-lang-list-style="code-flag">
                    </div>
                    <input class="form-control me-2" type="search" placeholder="Search" id="search-teacher" aria-label="Search" style="width: 200px;">
                    <button class="btn btn-outline-success" type="button">Search</button>
                </div>
            </div>
        </div>
    </nav>
</div>
    <div class="teacher-list mt-4 container">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th scope="col">Sr_NO</th>
                    <th scope="col">NAME</th>
                    <th scope="col">Gender</th>
                    <th scope="col">MORE DETAILS</th>
                    <th scope="col">ACCESS ACCOUNT</th> 
                </tr>
            </thead>
            <tbody id="tb"></tbody>
        </table>
    </div>

    <script type="text/javascript">
        $(document).ready(function(){
            function load_table(){
                $.ajax({
                    url: "fetch-data/fetch-teachers.php",
                    method: "POST",
                    success: function(data){ $("#tb").html(data); }
                });
            }
            load_table();

            $("#search-teacher").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#tb tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>
</html>