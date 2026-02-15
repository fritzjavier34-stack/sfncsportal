<?php
include("config.php");
session_start();
header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $uid = $_SESSION['uid'] ?? null;
    $jsonData = file_get_contents('php://input');
    $decodedData = json_decode($jsonData, true);

    if (!$uid || !$decodedData) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request or session expired']);
        exit;
    }

    $class = strtoupper(trim($decodedData['class']));
    $section = strtoupper(trim($decodedData['section']));
    $dayOfWeak = (int)$decodedData['dayOfWeak'];
    $data = $decodedData['data'] ?? [];

    // days map
    $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
    if ($dayOfWeak < 1 || $dayOfWeak > 7) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid day']);
        exit;
    }

    $day = $days[$dayOfWeak - 1];
    $startCol = $day . '_start';
    $endCol = $day . '_end';
    $subjectCol = ($day === 'thu') ? 'thur' : $day; // ✅ use 'thur' for Thursday

    $updated = 0;

    foreach ($data as $row) {
        $rowId = $row['rowId'] ?? null;
        $start = $row['startTime'] ?? '';
        $end = $row['endTime'] ?? '';
        $subject = $row['subject'] ?? '';

        if (!$rowId) continue;

        // ✅ Proper update query with class & section check
        $query = "UPDATE time_table 
                  SET `$startCol`=?, `$endCol`=?, `$subjectCol`=?, 
                      `editor_id`=?, `timestamp`=CURRENT_TIMESTAMP 
                  WHERE `s_no`=? AND LOWER(`class`)=LOWER(?) AND LOWER(`section`)=LOWER(?)";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssiiss", $start, $end, $subject, $uid, $rowId, $class, $section);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) >= 0) {
            $updated++;
        }

        mysqli_stmt_close($stmt);
    }

    if ($updated > 0) {
        $response = ['status' => 'success', 'message' => "Updated $updated row(s) successfully."];
    } else {
        $response = ['status' => 'error', 'message' => 'No changes saved (maybe same values or invalid input).'];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Invalid request method'];
}

echo json_encode($response);
?>