<?php
include('../assets/config.php');
include('partials/_header.php');
include('partials/_sidebar.php');

$query = mysqli_query($conn, "SELECT * FROM grade_sheets ORDER BY created_at DESC");
?>

<input type="hidden" value="8" id="checkFileName">
<style>
    :root { 
        --bg-main: #f1f5f9; 
        --bg-card: #ffffff; 
        --text-main: #1e293b; 
        --text-muted: #64748b; 
        --border-color: #cbd5e1; 
        --input-bg: #ffffff; 
        --list-hover: #f8fafc; 
        --table-border: #dee2e6;
        --modal-btn-light: #f8f9fa;
    }

    body.dark { 
        --bg-main: #0b1120; 
        --bg-card: #1e293b; 
        --text-main: #f1f5f9; 
        --text-muted: #94a3b8; 
        --border-color: #334155; 
        --input-bg: #0f172a; 
        --list-hover: #2d3748; 
        --table-border: #334155;
        --modal-btn-light: #334155;
    }

    body { background-color: var(--bg-main) !important; color: var(--text-main); transition: all 0.3s ease; }
    
    .portal-card { 
        background: var(--bg-card); 
        border: 1px solid var(--border-color); 
        border-radius: 12px; 
        padding: 25px; 
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); 
        color: var(--text-main); 
        height: 100%; 
    }

    h3, h4, h5, label, th, td, strong { color: var(--text-main) !important; }
    .text-muted { color: var(--text-muted) !important; }
    
    .form-control, .form-select { 
        background-color: var(--input-bg) !important; 
        border: 1px solid var(--border-color) !important; 
        color: var(--text-main) !important; 
        border-radius: 8px;
    }

    .form-control::placeholder { color: var(--text-muted); opacity: 0.7; }

    /* TABLE DARK MODE FIX */
    .table { color: var(--text-main) !important; border-color: var(--table-border) !important; }
    .table-bordered td, .table-bordered th { border-color: var(--table-border) !important; }

    /* CUSTOM MODAL DESIGN */
    .modal-content {
        background-color: var(--bg-card) !important;
        color: var(--text-main) !important;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    }
    .modal-header { border-bottom: 1px solid var(--border-color); padding: 20px; }
    .modal-footer { border-top: 1px solid var(--border-color); padding: 15px; }
    .btn-light { background-color: var(--modal-btn-light) !important; border-color: var(--border-color) !important; color: var(--text-main) !important; }
    .btn-close { filter: invert(var(--dark-mode-invert, 0)); } /* Handled by JS or manual class if needed */
    body.dark .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }

    /* Delete Modal Specific */
    .delete-icon-box {
        width: 70px;
        height: 70px;
        background: #fee2e2;
        color: #ef4444;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 35px;
        margin: 0 auto 20px;
    }
    body.dark .delete-icon-box { background: #450a0a; color: #f87171; }

    /* Existing Sheets List */
    .list-group-item {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-color) !important;
        transition: background 0.2s;
    }
    .list-group-item:hover { background: var(--list-hover) !important; }
</style>

<div class="content">
    <?php include('partials/_navbar.php'); ?>
    <div class="main-content" style="padding: 20px;">
        <div class="row">
            <div class="col-md-7 mb-4">
                <div class="portal-card">
                    <h3>SFNCS Grading System</h3>
                    <p class="text-muted">Select the class and section to generate the sheet.</p>
                    <hr style="border-color: var(--border-color);">
                    
                    <form action="generate_sheet.php" method="POST">
                        <input type="hidden" name="class_name" id="class_name_hidden">
                        
                        <div class="mb-3">
                            <label class="form-label">Name of Sheet</label>
                            <input type="text" name="sheet_name" class="form-control" placeholder="Name of Sheet" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Class Indicator</label>
                                <select class="form-select" name="class" id="search-class" required>
                                    <option value="" selected disabled>Select Class</option>
                                    <?php 
                                    $class_query = mysqli_query($conn, "SELECT DISTINCT class FROM students ORDER BY class ASC");
                                    while($c = mysqli_fetch_assoc($class_query)) {
                                        echo "<option value='".$c['class']."'>".$c['class']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Section Indicator</label>
                                <select class="form-select" name="section" id="search-section" required>
                                    <option value="" selected disabled>Select Class First</option>
                                </select>
                            </div>
                        </div>

                        <hr style="border-color: var(--border-color);">
                        <h5>Number of Assessments</h5>
                        <div class="row g-3 mb-3">
                            <div class="col-4 col-md-4"><label>WO Count</label><input class="form-control" type="number" name="wo" value="3" required></div>
                            <div class="col-4 col-md-4"><label>PT Count</label><input class="form-control" type="number" name="pt" value="2" required></div>
                            <div class="col-4 col-md-4"><label>ST Count</label><input class="form-control" type="number" name="st" value="1" required></div>
                        </div>

                        <hr style="border-color: var(--border-color);">
                        <h5>Assessment Weights</h5>
                        <table class="table table-bordered">
                            <thead><tr><th>Type</th><th>Percentage (Decimal)</th></tr></thead>
                            <tbody>
                                <tr><td>WO</td><td><input class="form-control" type="number" step="0.01" name="wo_percent" value="0.0"></td></tr>
                                <tr><td>PT</td><td><input class="form-control" type="number" step="0.01" name="pt_percent" value="0.0"></td></tr>
                                <tr><td>ST</td><td><input class="form-control" type="number" step="0.01" name="st_percent" value="0.0"></td></tr>
                            </tbody>
                        </table>

                        <button class="btn btn-primary btn-lg w-100 mt-3" type="submit" style="font-weight: 600;">
                            <i class='bx bx-spreadsheet'></i> Generate Grade Sheet
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-md-5 mb-4">
                <div class="portal-card">
                    <h3>📂 Existing Sheets</h3>
                    <div class="list-group mt-3 custom-scroll" style="max-height: 600px; overflow-y: auto;">
                        <?php if($query && mysqli_num_rows($query) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($query)): 
                                $displayName = (!empty($row['sheet_name'])) ? $row['sheet_name'] : $row['section_name'];
                            ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center" style="margin-bottom: 5px; padding: 10px; border-radius: 8px;">
                                    <a href="generate_sheet.php?id=<?php echo $row['id']; ?>&class=<?php echo urlencode($row['class_name']); ?>" style="text-decoration:none; color:inherit; flex-grow:1;">
                                        <strong id="display-name-<?php echo $row['id']; ?>"><?php echo $displayName; ?></strong><br>
                                        <small class="text-muted">Section: <?php echo $row['section_name']; ?> | <?php echo $row['class_name']; ?></small>
                                    </a>
                                    <div class="btn-group">
                                        <button onclick="openRenameModal(<?php echo $row['id']; ?>, '<?php echo addslashes($displayName); ?>')" class="btn btn-sm btn-outline-primary"><i class='bx bx-edit-alt'></i></button>
                                        <button onclick="deleteSheet(<?php echo $row['id']; ?>)" class="btn btn-sm btn-outline-danger"><i class='bx bx-trash'></i></button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-center text-muted py-4">No records found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="renameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class='bx bx-rename'></i> Rename Grade Sheet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rename_target_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Name of Sheet</label>
                    <input type="text" id="new_sheet_name" class="form-control" placeholder="">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" onclick="confirmRename()" class="btn btn-primary px-4">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="modal-body">
                <div class="delete-icon-box">
                    <i class='bx bx-trash'></i>
                </div>
                <h4 class="fw-bold">Are you sure?</h4>
                <p class="text-muted">You are about to delete this sheet. This action cannot be undone.</p>
                <input type="hidden" id="delete_target_id">
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Cancel</button>
                <button type="button" onclick="confirmDelete()" class="btn btn-danger w-100">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
let renameModal, deleteModal;
document.addEventListener('DOMContentLoaded', function() {
    renameModal = new bootstrap.Modal(document.getElementById('renameModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
});

function deleteSheet(id) {
    document.getElementById('delete_target_id').value = id;
    deleteModal.show();
}

function confirmDelete() {
    const id = document.getElementById('delete_target_id').value;
    window.location.href = "delete_sheet.php?id=" + id;
}

function openRenameModal(id, currentName) {
    document.getElementById('rename_target_id').value = id;
    document.getElementById('new_sheet_name').value = currentName;
    renameModal.show();
}

function confirmRename() {
    const id = document.getElementById('rename_target_id').value;
    const newName = document.getElementById('new_sheet_name').value.trim();
    if (newName === "") { alert("Paki-lagay ang pangalan."); return; }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('sheet_name', newName);

    fetch('rename_sheet.php', { method: 'POST', body: formData })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === "success") {
            document.getElementById('display-name-' + id).innerText = newName;
            renameModal.hide();
        } else { alert("Error: " + data); }
    })
    .catch(error => console.error('Error:', error));
}

document.getElementById("search-class").addEventListener("change", function() {
    const selectedClass = this.value;
    const sectionSelect = document.getElementById("search-section");
    const selectedText = this.options[this.selectedIndex].text;
    document.getElementById("class_name_hidden").value = selectedText;
    sectionSelect.innerHTML = '<option value="" selected disabled>Loading...</option>';
    fetch(`../assets/get_sections.php?class=${encodeURIComponent(selectedClass)}`)
        .then(response => response.json())
        .then(data => {
            sectionSelect.innerHTML = '<option value="" selected disabled>Select Section</option>';
            if (data.status === 'success' && data.sections.length > 0) {
                data.sections.forEach(section => {
                    const option = document.createElement("option");
                    const sectionVal = (typeof section === 'object') ? section.section_name : section;
                    option.value = sectionVal;
                    option.textContent = sectionVal;
                    sectionSelect.appendChild(option);
                });
            } else { sectionSelect.innerHTML = '<option value="" selected disabled>No sections found</option>'; }
        })
        .catch(error => { console.error('Error:', error); });
});
</script>
<?php include('partials/_footer.php'); ?>