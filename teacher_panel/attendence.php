<?php include('partials/_header.php') ?>
<?php include('partials/_sidebar.php') ?>
<input type="hidden" value="3" id="checkFileName">

<style>
    /* Compact Filter Styling - REVISED TO FIX WIDE BOXES AND ALIGN BUTTON */
    .filter-card {
        background: var(--color-white);
        padding: 1.2rem;
        border-radius: var(--card-border-radius);
        box-shadow: var(--box-shadow);
        margin-bottom: 1.5rem;
    }

    .filter-grid {
        display: flex; 
        flex-wrap: wrap;
        gap: 1.5rem;
        align-items: flex-end; /* Ensures button aligns with the bottom of the selects */
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    /* Fixed widths for dropdowns so they are not too wide */
    #attClass, #attSection { width: 200px; }
    #attMonth { width: 150px; }
    #attYear { width: 100px; }

    .filter-group label {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--color-dark);
        text-transform: uppercase;
    }

    .form-select, .form-control {
        padding: 0.5rem;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 0.85rem;
        background-color: #f9f9f9;
    }

    /* Grading Sheet Style Table - NO CHANGES BELOW */
    .attendance-container {
        background: var(--color-white);
        padding: 1rem;
        border-radius: var(--card-border-radius);
        box-shadow: var(--box-shadow);
    }

    #attendanceGrid {
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.85rem;
    }

    #attendanceGrid thead th {
        background: #e9ecef; 
        color: #333;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        border: 1px solid #dee2e6;
        vertical-align: middle;
        padding: 10px;
    }

    #attendanceGrid th:first-child, 
    #attendanceGrid td:first-child {
        position: sticky;
        left: 0;
        background: white;
        z-index: 5;
        min-width: 250px;
        border-right: 2px solid #dee2e6;
        text-align: left;
        padding-left: 15px;
    }

    #attendanceGrid thead th:first-child {
        z-index: 11;
        background: #e9ecef;
    }

    #attendanceGrid tbody td {
        border: 1px solid #dee2e6;
        padding: 0;
        height: 40px;
    }

    .att-input {
        width: 100% !important;
        height: 100%;
        border-radius: 0;
        font-weight: bold;
        background: transparent;
        border: none !important;
    }

    .att-input:focus {
        background-color: #fffde7 !important;
        outline: 2px solid var(--color-primary);
    }

    .legend-row {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .legend-row .badge {
        font-size: 0.7rem;
        padding: 5px 10px;
    }

    .btn-primary-custom {
        background-color: #1a73e8;
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        height: 38px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary-custom:hover {
        background-color: #1557b0;
    }

    /* Remove arrows from number input for cleaner look */
    .att-input::-webkit-outer-spin-button,
    .att-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .att-input[type=number] {
        -moz-appearance: textfield;
    }
</style>

<div class="content">
    <?php include("partials/_navbar.php"); ?>

    <main>
        <div class="header">
            <div class="left">
                <h1>Attendance Management</h1>
            </div>
        </div>

        <div class="bottom-data">
            <div class="orders">
                
                <div class="filter-card">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>Class</label>
                            <select class="form-select" id="attClass" onchange="fetchSections(); saveCurrentState();">
                                <option value="">Select Class</option>
                                <?php include('partials/select_classes.php') ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Section</label>
                            <select class="form-select" id="attSection" onchange="saveCurrentState()">
                                 <option value="">Select Section</option>
                                 <?php include('partials/selelct_section.php') ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Month</label>
                            <select class="form-select" id="attMonth" onchange="saveCurrentState()">
                                <?php 
                                for($m=1; $m<=12; $m++){
                                    $monthName = date('F', mktime(0, 0, 0, $m, 10));
                                    $selected = (date('m') == $m) ? 'selected' : '';
                                    echo "<option value='$m' $selected>$monthName</option>";
                                } 
                                ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Year</label>
                            <input type="number" class="form-control" id="attYear" value="<?php echo date('Y'); ?>" onchange="saveCurrentState()">
                        </div>
                        <div class="filter-group">
                            <button class="btn-primary-custom" onclick="loadAttendanceGrid()">
                                <i class='bx bx-search'></i> Load Sheet
                            </button>
                        </div>
                    </div>
                </div>

                <div class="attendance-container">
                    <div class="legend-row">
                        <span class="badge bg-success">1 - Present</span>
                        <span class="badge bg-warning text-dark">2 - Late</span>
                        <span class="badge bg-danger">3 - Absent</span>
                        <span class="badge bg-info text-dark">4 - Early Dismissal</span>
                    </div>

                    <div class="table-responsive" style="max-height: 600px; overflow: auto; border: 1px solid #dee2e6;">
                        <table class="table table-hover mb-0" id="attendanceGrid">
                            <thead class="position-sticky top-0" style="z-index: 10;">
                            </thead>
                            <tbody>
                                <tr><td colspan="32" class="text-center py-4">Select filters and click Load Sheet to display attendance.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    

            </div>
        </div>
    </main>
</div>

<script>
function saveCurrentState() {
    localStorage.setItem('attClass', document.getElementById('attClass').value);
    localStorage.setItem('attSection', document.getElementById('attSection').value);
    localStorage.setItem('attMonth', document.getElementById('attMonth').value);
    localStorage.setItem('attYear', document.getElementById('attYear').value);
}

document.addEventListener('DOMContentLoaded', () => {
    const savedClass = localStorage.getItem('attClass');
    const savedSection = localStorage.getItem('attSection');
    const savedMonth = localStorage.getItem('attMonth');
    const savedYear = localStorage.getItem('attYear');

    if (savedClass) {
        document.getElementById('attClass').value = savedClass;
        if (savedMonth) document.getElementById('attMonth').value = savedMonth;
        if (savedYear) document.getElementById('attYear').value = savedYear;
        
        fetchSections(savedSection, () => {
            if (savedSection) {
                loadAttendanceGrid();
            }
        });
    }
});

function fetchSections(targetSection = null, callback = null) {
    const classId = document.getElementById('attClass').value.trim();
    const sectionSelect = document.getElementById('attSection');
    
    sectionSelect.innerHTML = '<option value="">Loading...</option>';

    if (!classId) {
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        return;
    }

    const fd = new FormData();
    fd.append('action', 'get_sections_by_class');
    fd.append('class_id', classId);

    fetch('attendance_action.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success' && data.sections && data.sections.length > 0) {
            let options = '<option value="">Select Section</option>';
            data.sections.forEach(sec => {
                options += `<option value="${sec}">${sec}</option>`;
            });
            sectionSelect.innerHTML = options;
            
            if (targetSection) {
                sectionSelect.value = targetSection;
            }
        } else {
            sectionSelect.innerHTML = '<option value="">No sections found</option>';
        }

        if (callback) callback();
    })
    .catch(err => {
        console.error("Fetch Error:", err);
        sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
    });
}

function loadAttendanceGrid() {
    const cls = document.getElementById('attClass').value;
    const sec = document.getElementById('attSection').value;
    const mon = document.getElementById('attMonth').value;
    const yr = document.getElementById('attYear').value;

    if(!cls || !sec) { 
        alert("Please select Class and Section"); 
        return; 
    }

    const fd = new FormData();
    fd.append('action', 'fetch_grid');
    fd.append('class', cls);
    fd.append('section', sec);
    fd.append('month', mon);
    fd.append('year', yr);

    fetch('attendance_action.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        const thead = document.querySelector('#attendanceGrid thead');
        const tbody = document.querySelector('#attendanceGrid tbody');
        thead.innerHTML = '';
        tbody.innerHTML = '';

        let hRow = `<tr><th style="min-width: 250px; position:sticky; left:0; background:#e9ecef; z-index:11;">Student Name</th>`;
        for(let i=1; i<=data.days_in_month; i++) {
            hRow += `<th class="text-center" style="min-width: 40px;">${i}</th>`;
        }
        hRow += `</tr>`;
        thead.innerHTML = hRow;

        if(!data.students || data.students.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${data.days_in_month+1}" class="text-center py-4">No students found.</td></tr>`;
            return;
        }

        let studentIndex = 0;
        data.students.forEach(std => {
            let row = `<tr>
                <td style="position:sticky; left:0; background:#fff; font-weight:bold; border-right: 2px solid #dee2e6;">${std.lname}, ${std.fname}</td>`;
            
            for(let i=1; i<=data.days_in_month; i++) {
                let val = (std.attendance && std.attendance[i]) ? std.attendance[i] : '';
                let bgStyle = getCellStyle(val);
                
                row += `<td class="p-0">
                    <input type="number" min="1" max="4" 
                    class="form-control text-center att-input" 
                    style="${bgStyle}"
                    data-sid="${std.id}" 
                    data-day="${i}" 
                    data-row="${studentIndex}" 
                    data-col="${i}"
                    value="${val}"
                    onchange="validateInput(this)"
                    onkeydown="handleNavigation(event, this)">
                </td>`;
            }
            row += `</tr>`;
            tbody.innerHTML += row;
            studentIndex++;
        });
    });
}

// Navigation Function for Arrow Keys
function handleNavigation(e, input) {
    const row = parseInt(input.dataset.row);
    const col = parseInt(input.dataset.col);
    let target;

    switch(e.key) {
        case "ArrowLeft":
            target = document.querySelector(`.att-input[data-row="${row}"][data-col="${col - 1}"]`);
            break;
        case "ArrowRight":
            target = document.querySelector(`.att-input[data-row="${row}"][data-col="${col + 1}"]`);
            break;
        case "ArrowUp":
            e.preventDefault(); // Prevent cursor moving to start of input
            target = document.querySelector(`.att-input[data-row="${row - 1}"][data-col="${col}"]`);
            break;
        case "ArrowDown":
            e.preventDefault(); // Prevent cursor moving to end of input
            target = document.querySelector(`.att-input[data-row="${row + 1}"][data-col="${col}"]`);
            break;
        case "Enter":
            e.preventDefault();
            target = document.querySelector(`.att-input[data-row="${row + 1}"][data-col="${col}"]`);
            break;
    }

    if (target) {
        target.focus();
        target.select(); // Select content for easier overwriting
    }
}

function getCellStyle(val) {
    val = parseInt(val);
    if(val === 1) return "background-color: #d1e7dd; color: #0f5132;"; 
    if(val === 2) return "background-color: #fff3cd; color: #664d03;"; 
    if(val === 3) return "background-color: #f8d7da; color: #842029;"; 
    if(val === 4) return "background-color: #cff4fc; color: #055160;"; 
    return "background-color: white; color: black;";
}

function validateInput(input) {
    const val = parseInt(input.value);
    input.style.cssText = getCellStyle(val);

    if (val >= 1 && val <= 4) {
        autoSaveSingle(input.dataset.sid, input.dataset.day, val);
    } else {
        input.value = ''; 
        input.style.backgroundColor = "white";
    }
}

function autoSaveSingle(studentId, day, code) {
    const changes = [{
        student_id: studentId,
        day: day,
        code: code
    }];
    sendSaveRequest(changes, false);
}

function saveAttendanceManual() {
    const changes = [];
    document.querySelectorAll('.att-input').forEach(inp => {
        if(inp.value) {
            changes.push({
                student_id: inp.dataset.sid,
                day: inp.dataset.day,
                code: inp.value
            });
        }
    });
    sendSaveRequest(changes, true);
}

function sendSaveRequest(data, showAlert) {
    const fd = new FormData();
    fd.append('action', 'save_attendance');
    fd.append('attendance_data', JSON.stringify(data));
    fd.append('class', document.getElementById('attClass').value);
    fd.append('section', document.getElementById('attSection').value);
    fd.append('month', document.getElementById('attMonth').value);
    fd.append('year', document.getElementById('attYear').value);

    fetch('attendance_action.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        if(res.status === 'success' && showAlert) {
            alert("Attendance Saved Successfully!");
        }
    })
    .catch(e => console.error("Save Error:", e));
}
</script>

<?php include('partials/_footer.php'); ?>