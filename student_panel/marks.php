<?php
include('../assets/config.php');
include('partials/_header.php');

// Assumption: student_name is stored in session
// Make sure this session variable matches your login script
$student_full_name = $_SESSION['student_name']; 

$query = mysqli_query($conn, "SELECT * FROM published_grades WHERE student_name = '$student_full_name' ORDER BY subject_code ASC");
?>

<style>
    :root {
        --card-bg: #ffffff;
        --text-primary: #1e293b;
        --border-color: #e2e8f0;
        --header-bg: #f8fafc;
        --pass-color: #166534;
        --fail-color: #991b1b;
        --pass-bg: #dcfce7;
        --fail-bg: #fee2e2;
    }
    body.dark {
        --card-bg: #1e293b;
        --text-primary: #f8fafc;
        --border-color: #334155;
        --header-bg: #0f172a;
        --pass-color: #86efac;
        --fail-color: #fca5a5;
        --pass-bg: #14532d;
        --fail-bg: #7f1d1d;
    }
    .report-card {
        background: var(--card-bg);
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        padding: 25px;
        max-width: 1000px;
        margin: 30px auto;
        color: var(--text-primary);
    }
    .report-header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 15px;
    }
    .student-info {
        font-size: 1.1rem;
        margin-bottom: 5px;
    }
    .marks-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    .marks-table th {
        background: var(--header-bg);
        padding: 12px;
        text-align: left;
        font-weight: 700;
        border-bottom: 2px solid var(--border-color);
        color: var(--text-primary);
    }
    .marks-table td {
        padding: 12px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
    }
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .passed { background: var(--pass-bg); color: var(--pass-color); }
    .failed { background: var(--fail-bg); color: var(--fail-color); }
</style>

<div class="content">
    <?php include('partials/_navbar.php'); ?>
    <div class="main-content">
        
        <div class="report-card">
            <div class="report-header">
                <h2>Progress Report Card</h2>
                <div class="student-info">Student Name: <strong><?php echo $student_full_name; ?></strong></div>
            </div>

            <div style="overflow-x:auto;">
                <table class="marks-table">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Description</th>
                            <th class="text-center">Midterm</th>
                            <th class="text-center">Final Term</th>
                            <th class="text-center">Semestral Grade</th>
                            <th class="text-center">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($query) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td style="font-weight:bold;"><?php echo $row['subject_code']; ?></td>
                                <td><?php echo $row['subject_description']; ?></td>
                                
                                <td class="text-center"><?php echo $row['midterm_grade'] ? $row['midterm_grade'] : '-'; ?></td>
                                <td class="text-center"><?php echo $row['final_term_grade'] ? $row['final_term_grade'] : '-'; ?></td>
                                
                                <td class="text-center" style="font-weight:bold;">
                                    <?php echo $row['semestral_grade'] ? $row['semestral_grade'] : '-'; ?>
                                </td>

                                <td class="text-center">
                                    <?php 
                                        $rem = strtoupper($row['remarks']);
                                        $badgeClass = ($rem == 'PASSED') ? 'passed' : (($rem == 'FAILED') ? 'failed' : '');
                                        
                                        if($rem == 'ONGOING' || $rem == 'PENDING') {
                                            echo "<span style='color:gray; font-style:italic;'>$rem</span>";
                                        } else {
                                            echo "<span class='status-badge $badgeClass'>$rem</span>";
                                        }
                                    ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 20px;">No grades published yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php include('partials/_footer.php'); ?>