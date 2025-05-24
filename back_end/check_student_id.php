<?php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "";
$db = "cs_election";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["exists" => false, "error" => "Connection failed"]);
    exit;
}

$studentId = $_POST['student_id'] ?? '';

if (empty($studentId)) {
    echo json_encode(["exists" => false, "error" => "Student ID is required"]);
    exit;
}

// Assume your table name is student_records
$stmt = $conn->prepare("SELECT COUNT(*) FROM student_records WHERE student_id = ?");
$stmt->bind_param("s", $studentId);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

echo json_encode(["exists" => $count > 0]);
$conn->close();
