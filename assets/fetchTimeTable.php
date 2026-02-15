<?php
include("config.php");
session_start();
header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $uid = $_SESSION['uid'];
    $jsonData = file_get_contents('php://input');
    $decodedData = json_decode($jsonData, true);

    if (isset($decodedData["dayOfWeak"])) {
        $dayOfWeak = (int)$decodedData['dayOfWeak'];
        $class = strtoupper(trim($decodedData['class']));
        $section = strtoupper(trim($decodedData['section']));

        // ✅ Column names
        $arrayOfDays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $dayName = $arrayOfDays[$dayOfWeak - 1] ?? 'mon';

        // ✅ Query timetable for that class/section
        $query = "SELECT * FROM `time_table`
                  WHERE LOWER(`class`) = LOWER(?) 
                    AND LOWER(`section`) = LOWER(?) 
                  ORDER BY s_no ASC";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $class, $section);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $response['status'] = "success";
            $response['day'] = ($dayOfWeak === 7) ? "sunday" : "working_day";
            $response['table1Message'] = "";
            $response['table2Message'] = "";

            $editorId = "";
            $editingTime = "";
            $rowCount = 0;

            while ($row = mysqli_fetch_assoc($result)) {
                $editorId = $row['editor_id'] ?? '';
                $timestamp = $row['timestamp'] ?? '';
                $editingTime = $timestamp ? date('d M, Y', strtotime($timestamp)) : 'N/A';

                // ✅ Determine correct columns
                $dayPrefix = $arrayOfDays[$dayOfWeak - 1];
                $startKey = $dayPrefix . '_start';
                $endKey = $dayPrefix . '_end';
                $subjectKey = ($dayPrefix === 'thu') ? 'thur' : $dayPrefix;

                // ✅ Create HTML row with data-id (used for saving)
$rowHtml = "
<tr class='tableRow' data-id='{$row['s_no']}'>
    <td class='tableData'>
        <input class='form-control tableInput startTime_' type='text' value='" . htmlspecialchars($row[$startKey]) . "' disabled>
    </td>
    <td class='tableData'>
        <input class='form-control tableInput endTime_' type='text' value='" . htmlspecialchars($row[$endKey]) . "' disabled>
    </td>
    <td class='tableData'>
        <input class='form-control tableInput subject_' type='text' value='" . htmlspecialchars($row[$subjectKey]) . "' disabled>
    </td>
</tr>
";

                if ($rowCount < 4) {
                    $response['table1Message'] .= $rowHtml;
                } else {
                    $response['table2Message'] .= $rowHtml;
                }
                $rowCount++;
            }

            // ✅ Get editor name
            $query2 = "SELECT CONCAT(fname, ' ', lname) AS full_name FROM admins WHERE id=? 
                       UNION 
                       SELECT CONCAT(fname, ' ', lname) AS full_name FROM teachers WHERE id=?";
            $stmt2 = mysqli_prepare($conn, $query2);
            mysqli_stmt_bind_param($stmt2, "ss", $editorId, $editorId);
            mysqli_stmt_execute($stmt2);
            $result2 = mysqli_stmt_get_result($stmt2);
            mysqli_stmt_close($stmt2);

            $editorFullName = "REMOVED";
            if ($result2 && mysqli_num_rows($result2) > 0) {
                $row2 = mysqli_fetch_assoc($result2);
                $editorFullName = ucfirst(strtolower($row2['full_name']));
            }

            if ($editorId === $uid) $editorFullName = "You";

            $response['editorName'] = $editorFullName;
            $response['editingTime'] = $editingTime;

        } else {
            $response['status'] = "not_found";
            $response['message'] = "No timetable found for $class-$section.";
        }
    } else {
        $response['status'] = "error";
        $response['message'] = "Missing required fields.";
    }
} else {
    $response['status'] = "error";
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
?>