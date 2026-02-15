<?php 
session_start();
include('../assets/config.php'); 

// 1. Kunin ang ID mula sa session
$session_id = $_SESSION['uid'] ?? ''; 

$student_full_name = ""; 
$my_alphanumeric_id = ""; 
$attendance_data = [];
$counts = ['P' => 0, 'L' => 0, 'A' => 0, 'ED' => 0];
$total_school_days = 0; 

if (!empty($session_id)) {
    // REVISION: Query para sa tamang profile
    $user_query = mysqli_query($conn, "SELECT id, lname, fname FROM students WHERE s_no = '$session_id' OR id = '$session_id'");
    
    if ($user_query && mysqli_num_rows($user_query) > 0) {
        $user_data = mysqli_fetch_assoc($user_query);
        $my_alphanumeric_id = $user_data['id']; 
        $lname = strtoupper(trim($user_data['lname']));
        $fname = strtoupper(trim($user_data['fname']));
        $student_full_name = $lname . ", " . $fname;
    }
}

// 3. HANDLE FILTERS (Month & Year)
$selected_month = $_POST['month'] ?? date('m');
$selected_year = $_POST['year'] ?? date('Y');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$selected_month, (int)$selected_year);

// 4. QUERY SA ATTENDANCE_LOGS
if (!empty($my_alphanumeric_id)) {
    $att_sql = "SELECT DAY(attendance_date) as day, status_code 
                FROM attendance_logs 
                WHERE student_id = '$my_alphanumeric_id' 
                AND MONTH(attendance_date) = '$selected_month' 
                AND YEAR(attendance_date) = '$selected_year'";
    
    $att_res = mysqli_query($conn, $att_sql);
    
    if ($att_res) {
        while ($log = mysqli_fetch_assoc($att_res)) {
            $d = (int)$log['day'];
            $code = (int)$log['status_code'];
            
            if ($code >= 1 && $code <= 4) {
                $attendance_data[$d] = $code;
                $total_school_days++; 

                if ($code == 1) $counts['P']++;
                elseif ($code == 2) $counts['L']++;
                elseif ($code == 3) $counts['A']++;
                elseif ($code == 4) $counts['ED']++;
            }
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
    <title>My Attendance | SFNCS Portal</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="logo.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/oranbyte-google-translator.css">
    
    <style>
        /* REVISION: Dark Theme Root Variables */
        :root {
            --color-primary: #7380ec;
            --color-danger:rgb(32, 73, 187);
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
        }

        .dark-theme-variables {
            --color-background: #181a1e;
            --color-white: #202528;
            --color-dark: #edeffd;
            --color-dark-variant: #a3bdcc;
            --color-light: rgba(0, 0, 0, 0.4);
            --box-shadow: 0 2rem 3rem var(--color-light);
        }

        body { display: block; width: 100vw; margin: 0; background: var(--color-background); overflow-x: hidden; color: var(--color-dark); }
        
        main { 
            margin-top: 1.4rem; 
            padding: 0 1rem; 
            max-width: 1200px; 
            margin-left: auto; 
            margin-right: auto; 
        }
        
        .attendance-container { 
            background: var(--color-white); 
            padding: 1.5rem; 
            border-radius: 2rem; 
            box-shadow: var(--box-shadow); 
            margin-top: 1rem; 
        }

        .filter-group { 
            display: flex; 
            gap: 1rem; 
            margin-bottom: 1.5rem; 
            align-items: center; 
            flex-wrap: wrap;
            background: var(--color-light);
            padding: 1rem;
            border-radius: 1.2rem;
        }

        .filter-group select { 
            padding: 0.6rem 1rem; 
            border-radius: 0.5rem; 
            border: 1px solid var(--color-info-dark); 
            background: var(--color-white); 
            color: var(--color-dark);
            font-size: 0.95rem;
            min-width: 140px;
        }

        .filter-group button { 
            padding: 0.6rem 1.5rem; 
            border-radius: 0.5rem; 
            background: var(--color-primary); 
            color: white; 
            border: none; 
            font-weight: 600;
            cursor: pointer;
        }

        /* REVISION: Table Compacting to avoid Sliding */
        .table-responsive { 
            overflow-x: hidden; /* Tinanggal ang sliding */
            margin-top: 1rem; 
            border-radius: 0.8rem; 
            border: 1px solid var(--color-info-light); 
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            text-align: center; 
            background: var(--color-white);
            table-layout: fixed; /* Pinupuwersa ang columns na magkasya */
        }

        th, td { 
            padding: 8px 2px; 
            border: 1px solid var(--color-info-light); 
            font-size: 0.72rem; /* Ginawang maliit para kumasya lahat */
            color: var(--color-dark);
            word-wrap: break-word;
        }

        th { background: var(--color-light); color: var(--color-info-dark); }
        
        /* Specific width for Name column to give more space to numbers */
        th:first-child, td:first-child {
            width: 150px;
            text-align: left;
            padding-left: 10px;
            font-size: 0.8rem;
        }

        .status-badge { 
            font-weight: bold; 
            padding: 2px 4px; 
            border-radius: 3px; 
            color: white; 
            display: inline-block; 
            font-size: 0.65rem;
        }
        
        .p-bg { background: #41f1b6; color: #004b34; } 
        .l-bg { background: #ffbb55; color: #573b00; } 
        .a-bg { background: #ff7782; color: #5c0006; } 
        .ed-bg { background: #7380ec; color: #fff; }
        
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-top: 2rem; padding-bottom: 2rem; }
        .sum-box { 
            background: var(--color-white); 
            padding: 1.2rem; 
            border-radius: 1.5rem; 
            text-align: center; 
            box-shadow: var(--box-shadow); 
            border-bottom: 5px solid var(--color-primary); 
        }
        .sum-box h2 { font-size: 1.8rem; color: var(--color-dark); }
        .sum-box p { color: var(--color-info-dark); font-size: 0.75rem; font-weight: 700; }
        
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
            <a href="exam.php"><span class="material-icons-sharp">grid_view</span><h3>Grades</h3></a>
            <a href="workspace.php" class="active"><span class="material-icons-sharp">description</span><h3>Attendance</h3></a>      
            <a href="password.php"><span class="material-icons-sharp">password</span><h3>Change Password</h3></a>
            <a href="logout.php"><span class="material-icons-sharp">logout</span><h3>Logout</h3></a>
        </div>
        <div id="profile-btn"><span class="material-icons-sharp">person</span></div>
        <div class="theme-toggler">
            <span class="material-icons-sharp active">light_mode</span>
            <span class="material-icons-sharp">dark_mode</span>
        </div>
    </header>

    <main>
        <h1>My Attendance</h1>
        <div class="attendance-container">
            <div style="margin-bottom: 1rem; color: var(--color-info-dark); font-size: 0.9rem;">
                Showing record for: <b style="color: var(--color-primary);"><?php echo $student_full_name; ?></b>
            </div>
            
            <form method="POST" class="filter-group">
                <select name="month">
                    <?php for($m=1; $m<=12; $m++) { 
                        $monthName = date('F', mktime(0, 0, 0, $m, 10));
                        echo "<option value='$m' ".($selected_month == $m ? 'selected' : '').">$monthName</option>";
                    } ?>
                </select>
                <select name="year">
                    <option value="2026" <?php if($selected_year == "2026") echo 'selected'; ?>>2026</option>
                    <option value="2025" <?php if($selected_year == "2025") echo 'selected'; ?>>2025</option>
                </select>
                <button type="submit">Load Record</button>
            </form>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>DAYS</th>
                            <?php for($i=1; $i<=$daysInMonth; $i++) echo "<th>$i</th>"; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><b><?php echo $student_full_name ?: 'No Name'; ?></b></td>
                            <?php 
                            for($i=1; $i<=$daysInMonth; $i++) {
                                if(isset($attendance_data[$i])) {
                                    $c = $attendance_data[$i];
                                    $map = [1=>['P','p-bg'], 2=>['L','l-bg'], 3=>['A','a-bg'], 4=>['ED','ed-bg']];
                                    echo "<td><span class='status-badge {$map[$c][1]}'>{$map[$c][0]}</span></td>";
                                } else {
                                    echo "<td>-</td>";
                                }
                            }
                            ?>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="summary">
                <div class="sum-box" style="border-color: var(--color-dark);">
                    <h2><?php echo $total_school_days; ?></h2><p>SCHOOL DAYS</p>
                </div>
                <div class="sum-box" style="border-color: #41f1b6;">
                    <h2><?php echo $counts['P']; ?></h2><p>PRESENT</p>
                </div>
                <div class="sum-box" style="border-color: #ffbb55;">
                    <h2><?php echo $counts['L']; ?></h2><p>LATE</p>
                </div>
                <div class="sum-box" style="border-color: #ff7782;">
                    <h2><?php echo $counts['A']; ?></h2><p>ABSENT</p>
                </div>
                <div class="sum-box" style="border-color: #7380ec;">
                    <h2><?php echo $counts['ED']; ?></h2><p>EARLY DISMISSAL</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        const themeToggler = document.querySelector(".theme-toggler");
        
        // Pag-load ng page, i-check ang preference
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-theme-variables');
            themeToggler.querySelector('span:nth-child(1)').classList.remove('active');
            themeToggler.querySelector('span:nth-child(2)').classList.add('active');
        }

        themeToggler.addEventListener('click', () => {
            document.body.classList.toggle('dark-theme-variables');
            themeToggler.querySelector('span:nth-child(1)').classList.toggle('active');
            themeToggler.querySelector('span:nth-child(2)').classList.toggle('active');
            
            // I-save ang preference para hindi mawala sa refresh
            if (document.body.classList.contains('dark-theme-variables')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        });
    </script>
    <script src="../js/oranbyte-google-translator.js"></script>
    <script type="text/javascript" src="index.js"></script>
</body>
</html>