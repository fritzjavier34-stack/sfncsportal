<?php
    include('../assets/config.php');
?>

<?php include('partials/_header.php'); ?>
<?php include('partials/_sidebar.php'); ?>

<div class="content">
<?php include('partials/_navbar.php'); ?>

<!DOCTYPE html>
<html>
<head>
<title>DMS Grade Setup</title>
<style>
.card{
width:600px;
margin:50px auto;
padding:30px;
border-radius:10px;
background:#fff;
box-shadow:0 10px 25px rgba(0,0,0,.1);
font-family:Arial;
}
input{
width:100%;
padding:8px;
margin-bottom:12px;
}
button{
background:#198754;
color:#fff;
border:none;
padding:12px;
width:100%;
font-size:16px;
cursor:pointer;
}
button:hover{background:#157347;}
</style>
</head>

<body>

<div class="card">
<h2>DMS – Grade Sheet Setup</h2>

<form action="grade_panel.php" method="POST">

<label>Section</label>
<input type="text" name="section" required>

<label>No. of Boys</label>
<input type="number" name="boys" required>

<label>No. of Girls</label>
<input type="number" name="girls" required>

<hr>

<label>Formative Assessment</label>
<input type="number" name="fa" required>

<label>Written Output</label>
<input type="number" name="wo" required>

<label>Performance Task</label>
<input type="number" name="pt" required>

<label>Summative Test</label>
<input type="number" name="st" required>

<button type="submit">
📊 GENERATE GRADE SHEET
</button>

</form>
</div>

</body>
</html>
<?php include('partials/_footer.php'); ?>