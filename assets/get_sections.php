<?php
include("config.php");
header('Content-Type: application/json');

if (isset($_GET['class'])) {
    $class = mysqli_real_escape_string($conn, $_GET['class']);
    
    // Kukunin ang mga unique na sections para sa napiling class mula sa students table
    $query = "SELECT DISTINCT section FROM students WHERE LOWER(class) = LOWER('$class') ORDER BY section ASC";
    $result = mysqli_query($conn, $query);

    $sections = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sections[] = $row['section'];
        }
        echo json_encode(['status' => 'success', 'sections' => $sections]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No class provided']);
}
?>