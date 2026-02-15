<?php
// 1. I-on ang error reporting para makita kung may problema sa code
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Auto-detect kung nasaan ang config.php (Susubukan nito ang iba't ibang folder)
$config_paths = [
    '../assets/config.php',   // Karaniwang location kapag nasa admin folder
    '../../assets/config.php', // Kung nasa subfolder
    'assets/config.php',       // Kung nasa root
    '../config.php'            // Alternative location
];

$conn_found = false;
foreach ($config_paths as $path) {
    if (file_exists($path)) {
        include($path);
        $conn_found = true;
        break;
    }
}

// 3. Kung hindi nahanap ang config, mag-e-error ito sa dropdown
if (!$conn_found) {
    echo '<option value="">Error: Config file not found!</option>';
    exit;
}

// 4. Check connection
if (!$conn) {
    echo '<option value="">Error: Database Connection Failed!</option>';
    exit;
}

// 5. Main Logic
if (isset($_POST['class_id'])) {
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);

    // PINALITAN: 'students' table ang gamitin
    $query = "SELECT DISTINCT section FROM students WHERE class = '$class_id' AND section != '' ORDER BY section ASC";
    $result = mysqli_query($conn, $query);

    // Ibalik ang result
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            echo '<option value="" selected disabled>Select Section</option>';
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<option value="' . htmlspecialchars($row['section']) . '">' . htmlspecialchars($row['section']) . '</option>';
            }
        } else {
            // Walang nakitang section para sa class na ito
            echo '<option value="">No Section Found for Class ' . htmlspecialchars($class_id) . '</option>';
        }
    } else {
        // May mali sa SQL Query
        echo '<option value="">SQL Error: ' . mysqli_error($conn) . '</option>';
    }
}
?>