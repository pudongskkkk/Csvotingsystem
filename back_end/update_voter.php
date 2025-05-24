<?php
header("Content-Type: application/json");

// Database connection configuration
$host = "localhost";
$user = "root";
$password = ""; // Set your DB password here
$database = "cs_election";

// Read and decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(["success" => false, "error" => "Invalid input."]);
    exit;
}

// Connect to the database
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Prepare and bind update statement
$stmt = $conn->prepare("
    UPDATE approved_voters 
    SET student_id=?, last_name=?, first_name=?, middle_name=?, course=?, year_level=?, email=?, voter_key=? 
    WHERE id=?
");
$stmt->bind_param("ssssssssi", 
    $data['student_id'], 
    $data['last_name'], 
    $data['first_name'], 
    $data['middle_name'], 
    $data['course'], 
    $data['year_level'], 
    $data['email'],
    $data['voter_key'],
    $data['id']
);

// Execute and return result
if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
