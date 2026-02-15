<?php
session_start();
include('../assets/config.php');

if (isset($_GET['id']) && isset($_GET['role'])) {
    $target_id = mysqli_real_escape_string($conn, $_GET['id']);
    $role = $_GET['role'];

    // 1. Hanapin ang user sa database para makuha ang kumpletong info
    $table = ($role == 'teacher') ? 'teachers' : 'students';
    $check = mysqli_query($conn, "SELECT * FROM $table WHERE id = '$target_id'");
    
    if (mysqli_num_rows($check) > 0) {
        $user_data = mysqli_fetch_assoc($check);

        // 2. ETO ANG PINAKAMAHALAGA:
        // Dapat 'uid' ang gamitin natin para makalusot sa noSessionRedirect.php
        $_SESSION['uid'] = $target_id; 
        $_SESSION['id'] = $target_id;   // Backup lang
        $_SESSION['role'] = $role;
        
        // I-set din natin ang pangalan para display sa dashboard
        $_SESSION['fname'] = $user_data['fname'];
        $_SESSION['lname'] = $user_data['lname'];

        // Siguraduhin na naisulat na ang session bago lumipat ng page
        session_write_close();

        // 3. REDIRECT: Siguraduhin na tama ang path ng dashboard
        if ($role == 'teacher') {
            header("Location: ../teacher_panel/dashboard.php");
        } else {
            // Kung ang student panel mo ay index.php o student.php ang main:
            header("Location: ../student_panel/index.php"); 
        }
        exit();
    } else {
        die("User ID not found in $table table.");
    }
} else {
    header("Location: index.php");
    exit();
}
?>