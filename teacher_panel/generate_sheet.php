<?php
include('../assets/config.php');

// LOGIC: AJAX HANDLER PARA SA EDIT/DELETE NG PUBLISHED GRADES
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'update_published') {
        $pub_id = mysqli_real_escape_string($conn, $_POST['id']);
        $new_code = mysqli_real_escape_string($conn, $_POST['code']);
        $new_desc = mysqli_real_escape_string($conn, $_POST['desc']);
        mysqli_query($conn, "UPDATE published_grades SET subject_code = '$new_code', subject_description = '$new_desc' WHERE id = '$pub_id'");
        exit("success");
    }
    if ($_POST['action'] == 'delete_published') {
        $pub_id = mysqli_real_escape_string($conn, $_POST['id']);
        mysqli_query($conn, "DELETE FROM published_grades SET id = '$pub_id'");
        exit("success");
    }
}

// LOGIC: PRG PATTERN FIX (Anti-Duplicate)
if (isset($_POST['section'])) {
    $class = mysqli_real_escape_string($conn, $_POST['class'] ?? ''); 
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $class_name = isset($_POST['class_name']) ? mysqli_real_escape_string($conn, $_POST['class_name']) : ''; 
    
    $sheet_name = isset($_POST['sheet_name']) ? mysqli_real_escape_string($conn, $_POST['sheet_name']) : ''; 
    
    $boys = (int)($_POST['boys'] ?? 0); 
    $girls = (int)($_POST['girls'] ?? 0);
    $fa = (int)$_POST['fa']; 
    $wo = (int)$_POST['wo']; 
    $pt = (int)$_POST['pt']; 
    $st = (int)$_POST['st'];
    $wo_percent = (float)$_POST['wo_percent']; 
    $pt_percent = (float)$_POST['pt_percent']; 
    $st_percent = (float)$_POST['st_percent'];

    $sql = "INSERT INTO grade_sheets (sheet_name, class_name, section_name, num_boys, num_girls, num_fa, num_wo, num_pt, num_st, wo_pct, pt_pct, st_pct) 
            VALUES ('$sheet_name', '$class', '$section', '$boys', '$girls', '$fa', '$wo', '$pt', '$st', '$wo_percent', '$pt_percent', '$st_percent')";
    
    mysqli_query($conn, $sql);
    $new_id = mysqli_insert_id($conn);

    header("Location: generate_sheet.php?id=" . $new_id . "&class=" . urlencode($class_name)); 
    exit();

} else if(isset($_GET['id'])) {
    $sheet_id = mysqli_real_escape_string($conn, $_GET['id']);
    $class_name = isset($_GET['class']) ? mysqli_real_escape_string($conn, $_GET['class']) : ''; 
    
    $res = mysqli_query($conn, "SELECT * FROM grade_sheets WHERE id = '$sheet_id'");
    $data = mysqli_fetch_assoc($res);
    
    if(!$data) { die("Sheet not found."); }

    $section = $data['section_name'];
    $sheet_alias = $data['sheet_name']; 
    $boys = $data['num_boys']; 
    $girls = $data['num_girls'];
    $fa = $data['num_fa']; 
    $wo = $data['num_wo'];
    $pt = $data['num_pt']; 
    $st = $data['num_st'];
    $wo_percent = $data['wo_pct']; 
    $pt_percent = $data['pt_pct']; 
    $st_percent = $data['st_pct'];
} else {
    header("Location: marks.php");
    exit();
}

$db_students = ['MALE' => [], 'FEMALE' => []];

if (!empty($class_name)) {
    $sql_query = "SELECT id, fname, lname, gender FROM students WHERE class = '$class_name' AND section = '$section' ORDER BY lname ASC";
} else {
    $sql_query = "SELECT id, fname, lname, gender FROM students WHERE LOWER(section) = LOWER('$section') ORDER BY lname ASC";
}

$stud_query = mysqli_query($conn, $sql_query);

while($row = mysqli_fetch_assoc($stud_query)) {
    $fullname = strtoupper($row['lname']) . ", " . $row['fname']; 
    $gender_val = strtoupper($row['gender']); 
    
    $student_data_array = [
        'id' => $row['id'], 
        'name' => $fullname
    ];

    if($gender_val == 'MALE' || $gender_val == 'BOY' || $gender_val == 'M') {
        $db_students['MALE'][] = $student_data_array;
    } else {
        $db_students['FEMALE'][] = $student_data_array;
    }
}

$saved_scores = [];
$score_query = mysqli_query($conn, "SELECT row_index, student_name, scores_json, transmuted FROM student_data WHERE sheet_id = '$sheet_id'");
while($s_row = mysqli_fetch_assoc($score_query)) {
    $saved_scores[$s_row['row_index']] = [
        'name' => $s_row['student_name'],
        'scores' => json_decode($s_row['scores_json'], true),
        'transmuted' => $s_row['transmuted']
    ];
}

$p_vals = isset($saved_scores['PERFECT']) ? $saved_scores['PERFECT']['scores'] : [];
?>

<?php include('partials/_header.php'); ?>
<?php include('partials/_sidebar.php'); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<style>
    :root {
        --bg-main: #f1f5f9; --bg-card: #ffffff; --text-main: #1e293b; --border-color: #cbd5e1;
        --table-header-bg: #e2e8f0; --table-header-text: #1e293b; --perfect-bg: #fefce8;
        --perfect-text: #854d0e; --comp-header-bg: #1e3a8a; --comp-header-text: #ffffff; 
        --comp-col-bg: #eff6ff; --final-header-bg: #166534; --final-header-text: #ffffff;
        --final-col-bg: #f0fdf4; --excel-fail-bg: #ffc7ce; --excel-fail-text: #9c0006;
    }
    body.dark {
        --bg-main: #0b1120; --bg-card: #1e293b; --text-main: #f1f5f9; --border-color: #334155;
        --table-header-bg: #0f172a; --table-header-text: #f1f5f9; --perfect-bg: #422006;
        --perfect-text: #fefce8; --comp-header-bg: #1e40af; --comp-col-bg: #172554;
        --final-header-bg: #064e3b; --final-col-bg: #022c22; --excel-fail-bg: #450a0a;
        --excel-fail-text: #ff8a8a;
    }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-main); color: var(--text-main); transition: all 0.3s ease; }
    
    .button-group { 
        display: flex; 
        gap: 10px; 
        flex-wrap: wrap; 
        align-items: center;
    }
    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 42px;
        padding: 0 18px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 120px;
    }
    .btn-action:hover { opacity: 0.9; transform: translateY(-1px); }
    .btn-back { background: #64748b; color: white; }
    .btn-save { background: #2563eb; color: white; }
    .btn-publish { background: #f59e0b; color: white; }
    .btn-export { background: #166534; color: white; }

    .portal-card { background: var(--bg-card); border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); padding: 20px; margin: 20px; }
    .grade-wrapper { width: 100%; overflow-x: auto; border-radius: 8px; border: 1px solid var(--border-color); position: relative; }
    .grade-table { width: max-content; border-collapse: separate; border-spacing: 0; font-size: 12px; }
    .grade-table th:not(.student-name-col), .grade-table td:not(.student-name-col) { width: 65px !important; min-width: 65px !important; }
    .grade-table thead th { background: var(--table-header-bg); color: var(--table-header-text); padding: 12px; border: 0.5px solid var(--border-color); position: sticky; top: 0; z-index: 20; font-weight: 700; text-align: center; }
    
    .header-final-group { background: var(--final-header-bg) !important; color: var(--final-header-text) !important; }
    .header-trans-group { background: #92400e !important; color: #ffffff !important; }
    .grade-table td { padding: 0 !important; border-bottom: 1px solid var(--border-color); border-right: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); text-align: center; }
    
    .student-name-col { 
        position: sticky; 
        left: 0; 
        z-index: 15; 
        background: var(--bg-card) !important; 
        width: 320px !important; 
        min-width: 320px !important; 
        text-align: left !important;
        border-right: 2px solid var(--border-color) !important;
    }
    
    .perfect-row td.student-name-col {
        background-color: var(--perfect-bg) !important;
        z-index: 16;
    }

    select.name-input { width: 100%; border: none; background: transparent; color: var(--text-main); font-weight: 600; outline: none; cursor: pointer; appearance: none; }
    .perfect-row td { background-color: var(--perfect-bg) !important; color: var(--perfect-text) !important; font-weight: 700; }
    .final-cell { font-weight: 800; width: 85px; color: var(--text-main); }
    .transmuted-cell { font-weight: 900; width: 90px; border-left: 2px solid #92400e !important; }
    input[type="number"], .name-input { width: 100%; height: 100%; display: block; border: none; padding: 8px 5px; background: transparent; text-align: center; outline: none; }
    input.is-failing { background-color: var(--excel-fail-bg) !important; color: var(--excel-fail-text) !important; font-weight: bold; }
    
    .gender-row { background: var(--border-color) !important; font-weight: 800; text-align: left !important; color: var(--text-main); height: 45px !important; letter-spacing: 2px; text-transform: uppercase; }
    .gender-row td { padding-left: 15px !important; position: sticky; left: 0; z-index: 5; }

    .btn-view-link {
        color: #2563eb;
        font-size: 18px;
        text-decoration: none;
        margin-right: 8px;
        display: inline-flex;
        align-items: center;
        transition: 0.2s;
    }
    .btn-view-link:hover { transform: scale(1.2); color: #1e40af; }

    @media (max-width: 768px) {
        .portal-card { margin: 10px; padding: 15px; }
        .student-name-col { width: 220px !important; min-width: 220px !important; }
    }
</style>

<div class="content">
    <?php include('partials/_navbar.php'); ?>
    <div class="main-content">
        <div class="portal-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h3 style="font-weight: 800; margin: 0; color: var(--text-main);">
                        <i class='bx bxs-graduation-cap'></i> 
                        <?php echo !empty($sheet_alias) ? $sheet_alias : "Section: " . $section; ?>
                    </h3>
                    <?php if(!empty($sheet_alias)): ?>
                        <small style="color: var(--text-muted); font-weight: 600;">Section: <?php echo $section; ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="button-group">
                    <a href="marks.php" class="btn-action btn-back">
                        <i class='bx bx-chevron-left'></i> Return
                    </a>
                    <button onclick="saveAllRows()" class="btn-action btn-save">
                        <i class='bx bx-save'></i> Save All
                    </button>
                    <button onclick="openPublishModal()" class="btn-action btn-publish">
                        <i class='bx bx-upload'></i> Publish
                    </button>
                    <button onclick="exportToExcel()" class="btn-action btn-export">
                        <i class='bx bxs-file-export'></i> Export
                    </button>
                </div>
            </div>
            
            <div class="grade-wrapper">
                <table class="grade-table" id="gradeTable">
                    <thead>
                        <tr>
                            <th rowspan="2" class="student-name-col" style="z-index: 25;">Student Name</th>
                            <th colspan="<?php echo $wo + 1; ?>">Written (<?php echo $wo_percent * 100; ?>%)</th>
                            <th colspan="<?php echo $pt + 1; ?>">Performance (<?php echo $pt_percent * 100; ?>%)</th>
                            <th colspan="<?php echo $st + 1; ?>">Summative (<?php echo $st_percent * 100; ?>%)</th>
                            <th rowspan="2" class="header-final-group">Initial</th>
                            <th rowspan="2" class="header-trans-group">Final Grade</th>
                        </tr>
                        <tr>
                            <?php for($i=1; $i<=$wo; $i++) echo "<th>WO$i</th>"; ?><th>Sum</th>
                            <?php for($i=1; $i<=$pt; $i++) echo "<th>PT$i</th>"; ?><th>Sum</th>
                            <?php for($i=1; $i<=$st; $i++) echo "<th>ST$i</th>"; ?><th>Sum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $currentRow = 0;
                        ?>
                        <tr class="perfect-row" data-row-id="PERFECT">
                            <td class="student-name-col" style="padding-left: 15px !important;"><strong>PERFECT SCORE</strong></td>
                            <?php 
                            $colCount = 0;
                            for($i=0; $i<$wo; $i++) {
                                echo "<td><input type='number' class='wo_p' data-row='$currentRow' data-col='$colCount' value='".($p_vals["wo_$i"] ?? '1')."' onkeydown='handleNavigation(event, this)'></td>";
                                $colCount++;
                            }
                            ?>
                            <td><span class="wo_p_total">0</span></td>
                            <?php 
                            for($i=0; $i<$pt; $i++) {
                                echo "<td><input type='number' class='pt_p' data-row='$currentRow' data-col='$colCount' value='".($p_vals["pt_$i"] ?? '1')."' onkeydown='handleNavigation(event, this)'></td>";
                                $colCount++;
                            }
                            ?>
                            <td><span class="pt_p_total">0</span></td>
                            <?php 
                            for($i=0; $i<$st; $i++) {
                                echo "<td><input type='number' class='st_p' data-row='$currentRow' data-col='$colCount' value='".($p_vals["st_$i"] ?? '1')."' onkeydown='handleNavigation(event, this)'></td>";
                                $colCount++;
                            }
                            ?>
                            <td><span class="st_p_total">0</span></td>
                            <td colspan="2"></td>
                        </tr>

<?php 
$genders = ['MALE', 'FEMALE'];
foreach($genders as $gender): ?>
    <tr class="gender-row"><td colspan="100%"><?php echo $gender; ?></td></tr>
    <?php 
    $student_list = $db_students[$gender] ?? [];
    foreach($student_list as $index => $s_info): 
        $currentRow++;
        $name = $s_info['name'];
        $db_student_id = $s_info['id']; 
        
        $s_num = $index + 1;
        $rIdx = $gender . "_" . $s_num;
        $s_vals = $saved_scores[$rIdx]['scores'] ?? [];
    ?>
    <tr data-row-id="<?php echo $rIdx; ?>">
        <td class="student-name-col">
            <div style="display:flex; align-items:center; padding-left: 10px;">
                <a href="view_student_grades.php?id=<?php echo $db_student_id; ?>&sheet_id=<?php echo $sheet_id; ?>" class="btn-view-link">
                    <i class='bx bx-show'></i>
                </a>
                <span style="margin-right:8px; font-weight:bold; min-width: 20px;"><?php echo $s_num; ?>.</span>
                
                <select class="name-input">
                    <option value="<?php echo $name; ?>" selected><?php echo $name; ?></option>
                </select>
            </div>
        </td>
        <?php 
        $colCount = 0;
        for($i=0; $i<$wo; $i++) { 
            $v=$s_vals["wo_$i"]??''; 
            echo "<td><input type='number' class='wo' data-row='$currentRow' data-col='$colCount' value='$v' onkeydown='handleNavigation(event, this)'></td>"; 
            $colCount++;
        } 
        echo "<td class='wo_total'>0</td>";
        
        for($i=0; $i<$pt; $i++) { 
            $v=$s_vals["pt_$i"]??''; 
            echo "<td><input type='number' class='pt' data-row='$currentRow' data-col='$colCount' value='$v' onkeydown='handleNavigation(event, this)'></td>"; 
            $colCount++;
        } 
        echo "<td class='pt_total'>0</td>";
        
        for($i=0; $i<$st; $i++) { 
            $v=$s_vals["st_$i"]??''; 
            echo "<td><input type='number' class='st' data-row='$currentRow' data-col='$colCount' value='$v' onkeydown='handleNavigation(event, this)'></td>"; 
            $colCount++;
        } 
        echo "<td class='st_total'>0</td>";
        ?>
        <td class="final-grade-cell final-cell">0.00</td>
        <td class="transmuted-grade-cell transmuted-cell"><?php echo $saved_scores[$rIdx]['transmuted'] ?? 60; ?></td>
    </tr>
    <?php endforeach; ?>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="publishModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div style="background: var(--bg-card); margin: 10% auto; padding: 25px; border-radius: 12px; width: 400px; max-width: 90%;">
        <h3 style="margin-top:0;">Publish to Portal</h3>
        <div style="margin-bottom:15px;">
            <label style="display:block; font-weight:600; font-size:14px;">Subject Code</label>
            <select id="subject_code" style="width:100%; padding:10px; border-radius:6px; border:1px solid var(--border-color); background:var(--bg-main); color:var(--text-main);">
                <option value="" selected disabled>Select Subject Code</option>
                <optgroup label="Specialization">
                    <option value="Specialization 01">Specialization 01</option>
                    <option value="Specialization 02">Specialization 02</option>
                    <option value="Specialization 03">Specialization 03</option>
                </optgroup>
                <optgroup label="Applied Subjects">
                    <option value="Applied 01">Applied 01</option>
                    <option value="Applied 02">Applied 02</option>
                    <option value="Applied 03">Applied 03</option>
                    <option value="Applied 04">Applied 04</option>
                    <option value="Applied 05">Applied 05</option>
                </optgroup>
                <optgroup label="Core Subjects">
                    <?php for($i=1; $i<=10; $i++): ?>
                        <option value="Core Subject <?php echo $i; ?>">Core Subject <?php echo $i; ?></option>
                    <?php endfor; ?>
                </optgroup>
            </select>
        </div>
        <div style="margin-bottom:15px;">
            <label style="display:block; font-weight:600; font-size:14px;">Subject Description</label>
            <input type="text" id="subject_desc" style="width:100%; padding:10px; border-radius:6px; border:1px solid var(--border-color); background:var(--bg-main); color:var(--text-main);" placeholder="e.g. Introduction to Computing">
        </div>
        <div style="margin-bottom:15px;">
            <label style="display:block; font-weight:600; font-size:14px;">Grading Period</label>
            <select id="grading_period" style="width:100%; padding:10px; border-radius:6px; border:1px solid var(--border-color); background:var(--bg-main); color:var(--text-main);">
                <option value="MIDTERM">Midterm Grades</option>
                <option value="FINAL">Final Grades</option>
            </select>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button onclick="closePublishModal()" style="background:#64748b; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;">Cancel</button>
            <button onclick="confirmPublish()" style="background:#2563eb; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;">Upload Now</button>
        </div>
    </div>
</div>

<script>
const WEIGHTS = { wo: <?php echo $wo_percent; ?>, pt: <?php echo $pt_percent; ?>, st: <?php echo $st_percent; ?> };

function handleNavigation(e, input) {
    const row = parseInt(input.dataset.row);
    const col = parseInt(input.dataset.col);
    let target;

    switch(e.key) {
        case "ArrowLeft":
            target = document.querySelector(`input[data-row="${row}"][data-col="${col - 1}"]`);
            break;
        case "ArrowRight":
            target = document.querySelector(`input[data-row="${row}"][data-col="${col + 1}"]`);
            break;
        case "ArrowUp":
            e.preventDefault(); 
            let prevRow = row - 1;
            while(prevRow >= 0 && !target) {
                target = document.querySelector(`input[data-row="${prevRow}"][data-col="${col}"]`);
                prevRow--;
            }
            break;
        case "ArrowDown":
        case "Enter":
            e.preventDefault();
            let nextRow = row + 1;
            const maxRows = <?php echo $currentRow; ?>;
            while(nextRow <= maxRows && !target) {
                target = document.querySelector(`input[data-row="${nextRow}"][data-col="${col}"]`);
                nextRow++;
            }
            break;
    }

    if (target) {
        target.focus();
        target.select(); 
    }
}

function updatePubRecord(id) {
    const row = document.getElementById('pub_row_' + id);
    if(!row) return;
    const code = row.querySelector('.p-code').value;
    const desc = row.querySelector('.p-desc').value;
    const fd = new FormData();
    fd.append('action', 'update_published');
    fd.append('id', id);
    fd.append('code', code);
    fd.append('desc', desc);
    fetch('generate_sheet.php', { method: 'POST', body: fd });
}

function deletePubRecord(id) {
    if(!confirm("Are you sure?")) return;
    const fd = new FormData();
    fd.append('action', 'delete_published');
    fd.append('id', id);
    fetch('generate_sheet.php', { method: 'POST', body: fd }).then(() => {
        const row = document.getElementById('pub_row_' + id);
        if(row) row.remove();
    });
}

function saveAllRows() {
    const saveBtn = document.querySelector('.btn-save');
    saveBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Saving...";
    document.querySelectorAll('tbody tr[data-row-id]').forEach(row => autoSaveRow(row));
    setTimeout(() => { saveBtn.innerHTML = "<i class='bx bx-check'></i> Saved!"; }, 1000);
}

function autoSaveRow(rowElement) {
    const sheetId = "<?php echo $sheet_id; ?>";
    const rowId = rowElement.getAttribute('data-row-id');
    const nameInput = rowElement.querySelector('.name-input');
    const studentName = (rowId === 'PERFECT') ? 'TOTAL PERFECT SCORE' : (nameInput ? nameInput.value : "");
    const transGrade = rowElement.querySelector('.transmuted-grade-cell')?.innerText || 0;
    let scores = {};
    if (rowId === 'PERFECT') {
        rowElement.querySelectorAll('.wo_p').forEach((inp, i) => { scores['wo_' + i] = inp.value; });
        rowElement.querySelectorAll('.pt_p').forEach((inp, i) => { scores['pt_' + i] = inp.value; });
        rowElement.querySelectorAll('.st_p').forEach((inp, i) => { scores['st_' + i] = inp.value; });
    } else {
        rowElement.querySelectorAll('.wo').forEach((inp, i) => { scores['wo_' + i] = inp.value; });
        rowElement.querySelectorAll('.pt').forEach((inp, i) => { scores['pt_' + i] = inp.value; });
        rowElement.querySelectorAll('.st').forEach((inp, i) => { scores['st_' + i] = inp.value; });
    }
    const formData = new FormData();
    formData.append('sheet_id', sheetId);
    formData.append('student_name', studentName);
    formData.append('row_index', rowId);
    formData.append('scores', JSON.stringify(scores));
    formData.append('transmuted', transGrade);
    fetch('save_data.php', { method: 'POST', body: formData });
}

function openPublishModal() { document.getElementById('publishModal').style.display = 'block'; }
function closePublishModal() { document.getElementById('publishModal').style.display = 'none'; }

function confirmPublish() {
    const code = document.getElementById('subject_code').value;
    const desc = document.getElementById('subject_desc').value;
    const period = document.getElementById('grading_period').value;
    if(!code || !desc) { alert("Fill all fields."); return; }
    const studentData = [];
    document.querySelectorAll('tbody tr[data-row-id]:not(.perfect-row)').forEach(row => {
        studentData.push({ name: row.querySelector('.name-input').value, grade: row.querySelector('.transmuted-grade-cell').innerText });
    });
    const formData = new FormData();
    formData.append('section', '<?php echo $section; ?>');
    formData.append('subject_code', code);
    formData.append('subject_desc', desc);
    formData.append('period', period);
    formData.append('students', JSON.stringify(studentData));
    fetch('publish_grades.php', { method: 'POST', body: formData }).then(() => location.reload());
}

function transmute(score) {
    const s = parseFloat(score) || 0;
    if (s >= 100) return 100; if (s >= 98.4) return 99; if (s >= 96.8) return 98; if (s >= 95.2) return 97; if (s >= 93.6) return 96;
    if (s >= 92) return 95; if (s >= 90.4) return 94; if (s >= 88.8) return 93; if (s >= 87.2) return 92; if (s >= 85.6) return 91;
    if (s >= 84) return 90; if (s >= 82.4) return 89; if (s >= 80.8) return 88; if (s >= 79.2) return 87; if (s >= 77.6) return 86;
    if (s >= 76) return 85; if (s >= 74.4) return 84; if (s >= 72.8) return 83; if (s >= 71.2) return 82; if (s >= 69.6) return 81;
    if (s >= 68) return 80; if (s >= 66.4) return 79; if (s >= 64.8) return 78; if (s >= 63.2) return 77; if (s >= 61.6) return 76;
    if (s >= 60) return 75; if (s >= 56) return 74; if (s >= 52) return 73; if (s >= 48) return 72; if (s >= 44) return 71;
    if (s >= 40) return 70; if (s >= 36) return 69; if (s >= 32) return 68; if (s >= 28) return 67; if (s >= 24) return 66;
    if (s >= 20) return 65; if (s >= 16) return 64; if (s >= 12) return 63; if (s >= 8) return 62; if (s >= 4) return 61;
    return 60;
}

function updatePerfect(cat) {
    let sum = 0;
    document.querySelectorAll('.perfect-row .' + cat + '_p').forEach(input => sum += parseFloat(input.value) || 0);
    const target = document.querySelector('.' + cat + '_p_total');
    if(target) target.innerText = Math.round(sum);
    return sum || 1; 
}

function compute() {
    const P = { wo: updatePerfect('wo'), pt: updatePerfect('pt'), st: updatePerfect('st') };
    const perfectScores = {
        wo: Array.from(document.querySelectorAll('.perfect-row .wo_p')).map(i => parseFloat(i.value) || 0),
        pt: Array.from(document.querySelectorAll('.perfect-row .pt_p')).map(i => parseFloat(i.value) || 0),
        st: Array.from(document.querySelectorAll('.perfect-row .st_p')).map(i => parseFloat(i.value) || 0)
    };
    document.querySelectorAll('tbody tr[data-row-id]:not(.perfect-row)').forEach(row => {
        let currentInitialGrade = 0;
        ['wo', 'pt', 'st'].forEach(g => {
            let catSum = 0;
            row.querySelectorAll('.' + g).forEach((input, index) => {
                let score = parseFloat(input.value) || 0;
                catSum += score;
                if (input.value !== "" && score < (perfectScores[g][index] * 0.75)) input.classList.add('is-failing'); else input.classList.remove('is-failing');
            });
            const totalCell = row.querySelector('.' + g + '_total');
            if(totalCell) totalCell.innerText = Math.round(catSum);
            currentInitialGrade += (catSum / P[g]) * (WEIGHTS[g] * 100);
        });
        const initialCell = row.querySelector('.final-grade-cell');
        if(initialCell) initialCell.innerText = currentInitialGrade.toFixed(2);
        
        const transCell = row.querySelector('.transmuted-grade-cell');
        if(transCell) {
            const transVal = transmute(currentInitialGrade);
            transCell.innerText = transVal;
            transCell.style.backgroundColor = (transVal < 75) ? "var(--excel-fail-bg)" : "var(--final-col-bg)";
        }
    });
}

function exportToExcel() {
    const table = document.getElementById("gradeTable");
    const workbook = XLSX.utils.book_new();
    
    // Create a clone to manipulate values without affecting the UI
    const tableClone = table.cloneNode(true);
    
    // Convert inputs and selects to plain text for Excel
    tableClone.querySelectorAll('input, select').forEach(input => {
        const span = document.createElement('span');
        span.innerText = input.value;
        input.parentNode.replaceChild(span, input);
    });

    // Create worksheet
    const worksheet = XLSX.utils.table_to_sheet(tableClone);

    // REVISION: Apply Styles for Excel (Colors & Borders)
    const range = XLSX.utils.decode_range(worksheet['!ref']);
    
    for (let R = range.s.r; R <= range.e.r; ++R) {
        for (let C = range.s.c; C <= range.e.c; ++C) {
            const cell_address = XLSX.utils.encode_cell({c: C, r: R});
            if (!worksheet[cell_address]) continue;

            // Initialize style object
            if (!worksheet[cell_address].s) worksheet[cell_address].s = {};
            
            // Add Default Border
            worksheet[cell_address].s.border = {
                top: { style: "thin", color: { rgb: "cbd5e1" } },
                bottom: { style: "thin", color: { rgb: "cbd5e1" } },
                left: { style: "thin", color: { rgb: "cbd5e1" } },
                right: { style: "thin", color: { rgb: "cbd5e1" } }
            };

            // 1. Header Styling (Rows 0 and 1)
            if (R <= 1) {
                worksheet[cell_address].s.fill = { fgColor: { rgb: "e2e8f0" } };
                worksheet[cell_address].s.font = { bold: true };
            }

            // 2. Identify and Color Transmuted Grades (Failing vs Passing)
            // The Transmuted Grade is the last column
            if (C === range.e.c && R > 1) {
                const val = parseFloat(worksheet[cell_address].v);
                if (!isNaN(val)) {
                    if (val < 75) {
                        worksheet[cell_address].s.fill = { fgColor: { rgb: "ffc7ce" } }; // Red
                        worksheet[cell_address].s.font = { color: { rgb: "9c0006" }, bold: true };
                    } else {
                        worksheet[cell_address].s.fill = { fgColor: { rgb: "f0fdf4" } }; // Green
                        worksheet[cell_address].s.font = { color: { rgb: "166534" }, bold: true };
                    }
                }
            }
            
            // 3. Perfect Score Row Color
            if (R === 2) {
                worksheet[cell_address].s.fill = { fgColor: { rgb: "fefce8" } };
            }
        }
    }

    XLSX.utils.book_append_sheet(workbook, worksheet, "Grade Sheet");
    XLSX.writeFile(workbook, "Grade_Sheet_<?php echo $section; ?>.xlsx");
}

document.addEventListener('input', (e) => { 
    if(e.target.classList.contains('pub-input')) return;
    compute();
    const row = e.target.closest('tr');
    if(row && row.hasAttribute('data-row-id')) { autoSaveRow(row); }
});
window.onload = compute;
</script>

<?php include('partials/_footer.php'); ?>