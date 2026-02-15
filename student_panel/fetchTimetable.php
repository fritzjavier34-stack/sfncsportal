<?php
include("../assets/config.php");
session_start();
header("Content-Type: application/json");

$response = [
    "status" => "error",
    "data" => [
        "mon" => [],
        "tue" => [],
        "wed" => [],
        "thu" => [],
        "fri" => [],
        "sat" => [],
        "sun" => []
    ]
];

// ✅ 1. Get the student’s class & section from the session
$studentId = $_SESSION['uid'] ?? '';

if (empty($studentId)) {
    $response["message"] = "Not logged in.";
    echo json_encode($response);
    exit;
}

// ✅ 2. Fetch the student’s class and section from `students` table
$query = "SELECT class, section FROM students WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $studentId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$student) {
    $response["message"] = "Student not found in database.";
    echo json_encode($response);
    exit;
}

$studentClass = strtolower(trim($student["class"]));
$studentSection = strtolower(trim($student["section"]));

// ✅ 3. Fetch timetable entries that match the student’s class and section
$query = "SELECT * FROM time_table WHERE LOWER(class) = ? AND LOWER(section) = ? ORDER BY s_no ASC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $studentClass, $studentSection);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $timetable = [
        "mon" => [],
        "tue" => [],
        "wed" => [],
        "thu" => [],
        "fri" => [],
        "sat" => [],
        "sun" => []
    ];

    while ($row = mysqli_fetch_assoc($result)) {
        $timetable["mon"][] = [
            "start_time" => $row["mon_start"],
            "end_time"   => $row["mon_end"],
            "subject"    => $row["mon"]
        ];
        $timetable["tue"][] = [
            "start_time" => $row["tue_start"],
            "end_time"   => $row["tue_end"],
            "subject"    => $row["tue"]
        ];
        $timetable["wed"][] = [
            "start_time" => $row["wed_start"],
            "end_time"   => $row["wed_end"],
            "subject"    => $row["wed"]
        ];
        $timetable["thu"][] = [
            "start_time" => $row["thu_start"],
            "end_time"   => $row["thu_end"],
            "subject"    => $row["thur"]
        ];
        $timetable["fri"][] = [
            "start_time" => $row["fri_start"],
            "end_time"   => $row["fri_end"],
            "subject"    => $row["fri"]
        ];
        $timetable["sat"][] = [
            "start_time" => $row["sat_start"],
            "end_time"   => $row["sat_end"],
            "subject"    => $row["sat"]
        ];
        $timetable["sun"][] = [
            "start_time" => "—",
            "end_time"   => "—",
            "subject"    => "No class"
        ];
    }

    $response["status"] = "success";
    $response["data"] = $timetable;
} else {
    $response["status"] = "not_found";
    $response["message"] = "No timetable found for class {$studentClass}, section {$studentSection}.";
}

echo json_encode($response);
?>