<?php 
include("../assets/noSessionRedirect.php"); 
include("./verifyRoleRedirect.php");
include('../assets/config.php'); 

$id = $_SESSION['uid']; 

$student_full_name = ""; 

if (isset($id)) {
    $user_query = mysqli_query($conn, "SELECT lname, fname FROM students WHERE id = '$id' OR s_no = '$id'");
    
    if ($user_query && mysqli_num_rows($user_query) > 0) {
        $user_data = mysqli_fetch_assoc($user_query);
        $lname = strtoupper(trim($user_data['lname']));
        $fname = strtoupper(trim($user_data['fname']));
        $student_full_name = $lname . ", " . $fname;
    } else {
        $second_check = mysqli_query($conn, "SELECT lname, fname FROM users WHERE id = '$id'");
        if ($second_check && mysqli_num_rows($second_check) > 0) {
            $u_data = mysqli_fetch_assoc($second_check);
            $student_full_name = strtoupper(trim($u_data['lname'])) . ", " . strtoupper(trim($u_data['fname']));
        } else {
            $student_full_name = "NAME NOT FOUND (ID: $id)";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Grades | SFNCS Portal</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="shortcut icon" href="logo.png">
    <link rel="stylesheet" href="style.css">
    
    <style>
        :root {
            --color-primary: #7380ec;
            --color-danger: #ff7782;
            --color-success: #41f1b6;
            --color-warning: #ffbb55;
            --color-white: #fff;
            --color-info-dark: #7d8da1;
            --color-info-light: #dce1eb;
            --color-dark: #363949;
            --color-light: rgba(132, 139, 200, 0.18);
            --color-primary-variant: #111e88;
            --color-dark-variant: #677483;
            --color-background: #f6f6f9;
            --box-shadow: 0 2rem 3rem var(--color-light);
            --border-navy: #1a237e;
            --report-header-bg: #1a237e;
        }

        /* REVISION: Pinaputi ang text variables para sa Dark Mode */
        .dark-theme-variables {
            --color-background: #252a2e;
            --color-white: #32373d;
            --color-dark: #ffffff;         /* Pure white para sa main text/grades */
            --color-dark-variant: #ffffff; /* Pure white para sa descriptions/CLED */
            --color-light: rgba(0, 0, 0, 0.2);
            --box-shadow: 0 2rem 3rem var(--color-light);
            --border-navy: #ffffff;         /* Ginawang white pati ang grade accents */
            --report-header-bg: #3d4699;
        }

        body {
            background: var(--color-background);
            color: var(--color-dark);
            transition: all 300ms ease;
        }

        main { margin-top: 1.4rem; padding: 0 2rem; }

        .report-container {
            background: var(--color-white);
            width: 100%;
            max-width: 1000px;
            margin: 2rem auto;
            border-radius: var(--card-border-radius); 
            padding: 0;
            box-shadow: var(--box-shadow); 
            overflow: hidden; 
            transition: all 300ms ease;
        }

        .report-header {
            background: var(--report-header-bg);
            color: #fff;
            padding: 1.5rem;
            text-align: center;
            border-bottom: 3px solid rgba(0,0,0,0.1);
        }
        
        .report-header h2 { margin: 0; font-size: 1.5rem; text-transform: uppercase; color: #fff; }
        .report-header small { color: #e0e0e0; font-size: 0.9rem; }

        .student-info {
            padding: 1.5rem 2rem;
            background: var(--color-white);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--color-info-light); 
        }
        
        .student-info b { color: var(--border-navy); }

        .table-wrapper { padding: 2rem; }
        
        table { 
            width: 100%; 
            border-collapse: collapse !important; 
            border: 1px solid var(--color-info-light); 
        }
        
        table th, table td { 
            border: 1px solid var(--color-info-light) !important; 
            padding: 14px 15px; 
            font-size: 0.9rem;
            color: var(--color-dark); /* Eto ang magiging white sa dark mode */
        }

        table thead th { 
            background-color: var(--color-light); 
            color: var(--border-navy); 
            text-transform: uppercase;
            font-weight: 700; 
            text-align: center;
        }

        /* Styling para sa CLED/Description column */
        tbody td:nth-child(2) {
            color: var(--color-dark-variant); 
            font-weight: 400;
        }

        .status-passed { color: var(--color-success) !important; font-weight: 600; } 
        .status-failed { color: var(--color-danger) !important; font-weight: 600; } 
        
        .grade-bold { 
            font-weight: 700; 
            font-size: 1rem; 
            color: var(--color-dark); /* Tinitiyak na white ito */
        } 

        header { position: sticky; top: 0; z-index: 1000; }
    </style>
</head>

<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="">
            <h2><span class="danger">SFNCS</span>Portal</h2>
        </div>
        <div class="navbar">
            <a href="index.php"><span class="material-icons-sharp">home</span><h3>Home</h3></a>
            <a href="timetable.php"><span class="material-icons-sharp">today</span><h3>Schedule</h3></a>
            <a href="exam.php" class="active"><span class="material-icons-sharp">grid_view</span><h3>Grades</h3></a>
            <a href="workspace.php"><span class="material-icons-sharp">description</span><h3>Attendance</h3></a>      
            <a href="password.php"><span class="material-icons-sharp">password</span><h3>Change Password</h3></a>
            <a href="logout.php"><span class="material-icons-sharp">logout</span><h3>Logout</h3></a>
        </div>
        <div class="theme-toggler">
            <span class="material-icons-sharp active">light_mode</span>
            <span class="material-icons-sharp">dark_mode</span>
        </div>
    </header>

    <main>
        <div class="report-container">
            <div class="report-header">
                <h2>Official Report Card</h2>
                <small>Semester Grades Overview</small>
            </div>

            <div class="student-info">
                <p>Student Name: <b style="font-size: 1.1rem; text-transform: uppercase;"><?php echo $student_full_name; ?></b></p>
                <p>Student ID: <b><?php echo $id; ?></b></p>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%;">Code</th>
                            <th style="width: 35%;">Subject Description</th>
                            <th style="text-align:center;">Midterm</th>
                            <th style="text-align:center;">Finals</th>
                            <th style="text-align:center;">Sem. Grade</th>
                            <th style="text-align:center;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM published_grades 
                                WHERE UPPER(REPLACE(REPLACE(student_name, ' ', ''), ',', '')) 
                                = UPPER(REPLACE(REPLACE(?, ' ', ''), ',', '')) 
                                AND status = 'APPROVED' 
                                ORDER BY subject_code ASC";
                        
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $student_full_name);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $semGrade = $row['semestral_grade'];
                                $remarks = strtoupper($row['remarks']);
                                $class = ($remarks == 'PASSED') ? 'status-passed' : 'status-failed';

                                echo "<tr>
                                        <td style='text-align:center; font-weight:500;'>{$row['subject_code']}</td>
                                        <td>{$row['subject_description']}</td>
                                        <td style='text-align:center;'>".($row['midterm_grade'] ?: '-')."</td>
                                        <td style='text-align:center;'>".($row['final_term_grade'] ?: '-')."</td>
                                        <td style='text-align:center;' class='grade-bold'>".($semGrade ?: '-')."</td>
                                        <td style='text-align:center;' class='$class'>$remarks</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding:3rem;'>
                                    <div style='color: var(--color-info-dark);'>
                                        <span class='material-icons-sharp' style='font-size: 3rem; display:block; margin-bottom:10px;'>hourglass_empty</span>
                                        No approved grades found yet.
                                    </div>
                                  </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const themeToggler = document.querySelector(".theme-toggler");
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-theme-variables');
            themeToggler.querySelector('span:nth-child(1)').classList.remove('active');
            themeToggler.querySelector('span:nth-child(2)').classList.add('active');
        }
        themeToggler.addEventListener('click', () => {
            document.body.classList.toggle('dark-theme-variables');
            themeToggler.querySelector('span:nth-child(1)').classList.toggle('active');
            themeToggler.querySelector('span:nth-child(2)').classList.toggle('active');
            localStorage.setItem('theme', document.body.classList.contains('dark-theme-variables') ? 'dark' : 'light');
        });
    </script>
</body>
</html>