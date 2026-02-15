<?php 
session_start();
include('../assets/config.php');

$logged_id = $_SESSION['student_id'] ?? '';

// Kunin ang info ng student
$res = mysqli_query($conn, "SELECT id, class, section, lname, fname FROM students WHERE id = '$logged_id'");
$row = mysqli_fetch_assoc($res);

$myClass = $row['class'] ?? '';     
$mySection = $row['section'] ?? ''; 
$student_full_name = ($row['lname'] ?? '') . ", " . ($row['fname'] ?? '');
?>

<input type="hidden" id="viewClass" value="<?php echo $myClass; ?>">
<input type="hidden" id="viewSection" value="<?php echo $mySection; ?>">

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Attendance | SFNCS Portal</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: block; width: 100%; margin: 0; background: #f6f6f9; font-family: 'Poppins', sans-serif; }
        header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.8rem 4%; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            position: sticky; top:0; z-index: 1000;
        }
        main { padding: 2rem 4%; }
        .attendance-container { background: white; padding: 1.5rem; border-radius: 2rem; box-shadow: 0 2rem 3rem rgba(132, 139, 200, 0.18); }
        .filter-group { display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: center; }
        select, button { padding: 0.6rem 1rem; border-radius: 0.4rem; border: 1px solid #dce1eb; background: white; }
        button { background: #7380ec; color: white; cursor: pointer; border: none; font-weight: 500; }
        
        .table-responsive { overflow-x: auto; margin-top: 1rem; border-radius: 0.8rem; border: 1px solid #eee; }
        table { width: 100%; border-collapse: collapse; text-align: center; }
        th, td { padding: 12px 8px; border: 1px solid #f0f0f0; font-size: 0.85rem; }
        th { background: #fbfbfb; color: #7d8da1; }

        /* Summary Style */
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .summary-card { background: white; padding: 1.5rem; border-radius: 1.5rem; box-shadow: 0 1rem 2rem rgba(132, 139, 200, 0.1); border-left: 5px solid #7380ec; }
        .summary-card h3 { color: #7d8da1; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 0.5rem; }
        .summary-card div { font-size: 2rem; font-weight: 700; color: #363949; }

        .status-badge { font-weight: 700; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; color: white; }
        .p-bg { background: #41f1b6; color: #004b34; } 
        .l-bg { background: #ffbb55; color: #573b00; } 
        .a-bg { background: #ff7782; color: #5c0006; } 
        .ed-bg { background: #7380ec; color: #fff; }
    </style>
</head>
<body>
    <header>
        <div class="logo"><h2>SFNCS<span style="color:#ff7782">Portal</span></h2></div>
        <nav><a href="logout.php">Logout</a></nav>
    </header>

    <main>
        <h1>Attendance Summary</h1>

        <div class="summary-grid">
            <div class="summary-card" style="border-left-color: #363949;">
                <h3>Total School Days</h3>
                <div id="totalSchoolDays">0</div>
            </div>
            <div class="summary-card" style="border-left-color: #41f1b6;">
                <h3>Days Present</h3>
                <div id="totalPresent">0</div>
            </div>
            <div class="summary-card" style="border-left-color: #ff7782;">
                <h3>Absences</h3>
                <div id="totalAbsent">0</div>
            </div>
        </div>

        <div class="attendance-container">
            <div class="filter-group">
                <select id="reqMonth">
                    <?php for($m=1; $m<=12; $m++) {
                        $sel = ($m == date('n')) ? 'selected' : '';
                        echo "<option value='$m' $sel>".date('F', mktime(0,0,0,$m,10))."</option>";
                    } ?>
                </select>
                <select id="reqYear">
                    <option value="2025" selected>2025</option>
                    <option value="2026">2026</option>
                </select>
                <button onclick="loadAttendance()">View Record</button>
            </div>

            <div class="table-responsive">
                <table id="attendanceGrid">
                    <thead></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const UI_MAP = { 1: {t:'P', c:'p-bg'}, 2: {t:'L', c:'l-bg'}, 3: {t:'A', c:'a-bg'}, 4: {t:'ED', c:'ed-bg'} };

        function loadAttendance() {
            const fd = new FormData();
            fd.append('action', 'fetch_grid');
            fd.append('class', document.getElementById('viewClass').value);
            fd.append('section', document.getElementById('viewSection').value);
            fd.append('month', document.getElementById('reqMonth').value);
            fd.append('year', document.getElementById('reqYear').value);

            fetch('attendance_action.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                const thead = document.querySelector('#attendanceGrid thead');
                const tbody = document.querySelector('#attendanceGrid tbody');
                const loggedInId = "<?php echo $logged_id; ?>";
                
                // Build Header
                let hRow = `<tr><th style="text-align:left;">Student Name</th>`;
                for(let i=1; i<=data.days_in_month; i++) { hRow += `<th>${i}</th>`; }
                thead.innerHTML = hRow + `</tr>`;

                tbody.innerHTML = "";
                let schoolDays = 0;
                let present = 0;
                let absent = 0;

                // Find the student's row
                const me = data.students.find(s => s.id == loggedInId);

                if(me) {
                    let row = `<tr><td style="text-align:left; font-weight:600;">${me.lname}, ${me.fname}</td>`;
                    for(let i=1; i<=data.days_in_month; i++) {
                        const code = me.attendance[i];
                        if(code) {
                            schoolDays++; // REVISION: Kung may number, counted as School Day
                            if(code == 1) present++;
                            if(code == 3) absent++;
                            row += `<td><span class="status-badge ${UI_MAP[code].c}">${UI_MAP[code].t}</span></td>`;
                        } else {
                            row += `<td>-</td>`;
                        }
                    }
                    tbody.innerHTML = row + `</tr>`;
                }

                // Update Database-driven Summary
                document.getElementById('totalSchoolDays').innerText = schoolDays;
                document.getElementById('totalPresent').innerText = present;
                document.getElementById('totalAbsent').innerText = absent;
            });
        }
        window.onload = loadAttendance;
    </script>
</body>
</html>