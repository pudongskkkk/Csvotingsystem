<?php
header("Content-Type: application/json");

// Database credentials
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'cs_election';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit;
}

// ✅ Auto-delete duplicate student_id entries in voter_request that already exist in approved_voters
$cleanupQuery = "
    DELETE FROM voter_request
    WHERE student_id IN (
        SELECT student_id FROM approved_voters
    )
";

$conn->query($cleanupQuery); // Run cleanup before fetching

// Fetch remaining pending voter requests
$sql = "SELECT * FROM voter_request ORDER BY registration_date DESC";
$result = $conn->query($sql);

$voters = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $voters[] = $row;
    }
    echo json_encode(["success" => true, "data" => $voters]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to fetch voters"]);
}

$conn->close();
?>
