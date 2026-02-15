<?php
include('../assets/config.php');

$section = $_POST['section'];
$boys = (int)$_POST['boys'];
$girls = (int)$_POST['girls'];

$fa = (int)$_POST['fa'];
$wo = (int)$_POST['wo'];
$pt = (int)$_POST['pt'];
$st = (int)$_POST['st'];

$wo_percent = (float)$_POST['wo_percent'];
$pt_percent = (float)$_POST['pt_percent'];
$st_percent = (float)$_POST['st_percent'];

$fa_percent = 1 - ($wo_percent + $pt_percent + $st_percent);
$total_students = $boys + $girls;
?>

<?php include('partials/_header.php'); ?>
<?php include('partials/_sidebar.php'); ?>

<div class="content">
<?php include('partials/_navbar.php'); ?>

<style>
.grade-wrapper{width:100%;overflow-x:auto}
.grade-table{width:max-content;min-width:1200px;border-collapse:collapse}
.grade-table th,.grade-table td{border:1px solid #ccc;padding:6px;text-align:center;min-width:70px}
.grade-table input{width:65px;text-align:center}
.student-name{width:180px;text-align:left}
.perfect-row{background:#fff3cd;font-weight:bold}
</style>

<div class="main-content">
<h3>Grade Sheet – Section <?= htmlspecialchars($section) ?></h3>

<div class="grade-wrapper">
<table class="grade-table">
<thead class="bg-dark text-white">
<tr>
<th rowspan="2">Student</th>
<th colspan="<?= $fa+3 ?>">FA</th>
<th colspan="<?= $wo+3 ?>">WO</th>
<th colspan="<?= $pt+3 ?>">PT</th>
<th colspan="<?= $st+3 ?>">ST</th>
<th rowspan="2">TOTAL</th>
<th rowspan="2">FINAL</th>
</tr>
<tr>
<?php for($i=1;$i<=$fa;$i++) echo "<th>FA$i</th>"; ?>
<th>T</th><th>A</th><th>x100</th>

<?php for($i=1;$i<=$wo;$i++) echo "<th>WO$i</th>"; ?>
<th>T</th><th>A</th><th>x100</th>

<?php for($i=1;$i<=$pt;$i++) echo "<th>PT$i</th>"; ?>
<th>T</th><th>A</th><th>x100</th>

<?php for($i=1;$i<=$st;$i++) echo "<th>ST$i</th>"; ?>
<th>T</th><th>A</th><th>x100</th>
</tr>
</thead>

<tbody>

<!-- PERFECT SCORE ROW -->
<tr class="perfect-row">
<td>PERFECT SCORE</td>

<?php for($i=1;$i<=$fa;$i++): ?>
<td><input type="number" class="perfect fa_p" value="10"></td>
<?php endfor; ?>
<td class="fa_p_total">0</td><td>-</td><td>-</td>

<?php for($i=1;$i<=$wo;$i++): ?>
<td><input type="number" class="perfect wo_p" value="10"></td>
<?php endfor; ?>
<td class="wo_p_total">0</td><td>-</td><td>-</td>

<?php for($i=1;$i<=$pt;$i++): ?>
<td><input type="number" class="perfect pt_p" value="10"></td>
<?php endfor; ?>
<td class="pt_p_total">0</td><td>-</td><td>-</td>

<?php for($i=1;$i<=$st;$i++): ?>
<td><input type="number" class="perfect st_p" value="10"></td>
<?php endfor; ?>
<td class="st_p_total">0</td><td>-</td><td>-</td>

<td>-</td><td>-</td>
</tr>

<?php for($s=1;$s<=$total_students;$s++): ?>
<tr>
<td class="student-name" contenteditable="true">Student <?= $s ?></td>

<?php for($i=1;$i<=$fa;$i++): ?>
<td><input class="score fa"></td>
<?php endfor; ?>
<td class="fa_total">0</td><td class="fa_avg">0</td><td class="fa_x100">0</td>

<?php for($i=1;$i<=$wo;$i++): ?>
<td><input class="score wo"></td>
<?php endfor; ?>
<td class="wo_total">0</td><td class="wo_avg">0</td><td class="wo_x100">0</td>

<?php for($i=1;$i<=$pt;$i++): ?>
<td><input class="score pt"></td>
<?php endfor; ?>
<td class="pt_total">0</td><td class="pt_avg">0</td><td class="pt_x100">0</td>

<?php for($i=1;$i<=$st;$i++): ?>
<td><input class="score st"></td>
<?php endfor; ?>
<td class="st_total">0</td><td class="st_avg">0</td><td class="st_x100">0</td>

<td class="grand_total">0</td>
<td class="final">0</td>
</tr>
<?php endfor; ?>

</tbody>
</table>
</div>
</div>
</div>

<script>
const W = {
  fa: <?= $fa_percent ?>/100,
  wo: <?= $wo_percent ?>/100,
  pt: <?= $pt_percent ?>/100,
  st: <?= $st_percent ?>/100
};

function perfect(g){
  let t = 0;
  document.querySelectorAll('.'+g+'_p').forEach(i=>{
    t += parseFloat(i.value) || 0;
  });
  document.querySelector('.'+g+'_p_total').innerText = t.toFixed(2);
  return t || 1;
}

document.addEventListener('input', ()=>{

  let P = {
    fa: perfect('fa'),
    wo: perfect('wo'),
    pt: perfect('pt'),
    st: perfect('st')
  };

  document.querySelectorAll('tbody tr:not(.perfect-row)').forEach(row=>{
    let FINAL = 0;
    let TOTAL = 0;

    ['fa','wo','pt','st'].forEach(g=>{
      let sum = 0;
      row.querySelectorAll('.'+g).forEach(i=>{
        sum += parseFloat(i.value) || 0;
      });

      let ratio = sum / P[g];          // 0–1
      let percent = ratio * 100;       // 0–100

      row.querySelector('.'+g+'_total').innerText = sum.toFixed(2);
      row.querySelector('.'+g+'_avg').innerText   = ratio.toFixed(2);
      row.querySelector('.'+g+'_x100').innerText  = percent.toFixed(2);

      TOTAL += percent;
      FINAL += percent * W[g];
    });
    
    row.querySelector('.grand_total').innerText = TOTAL.toFixed(2);
    row.querySelector('.final').innerText = FINAL.toFixed(2);
  });

});
</script>

<?php include('partials/_footer.php'); ?>
