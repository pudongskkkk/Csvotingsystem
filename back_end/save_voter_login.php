<?php
// --- DATABASE CONFIGURATION ---
$host = 'localhost';
$dbname = 'cs_election';
$username = 'root';
$password = '';

// --- CONNECT TO DATABASE ---
$conn = new mysqli($host, $username, $password, $dbname);
header('Content-Type: application/json');

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// --- HANDLE JSON INPUT ---
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No input received']);
    exit;
}

// --- EXTRACT FIELDS ---
$voter_key   = $data['voter_key'] ?? '';
$student_id  = $data['student_id'] ?? '';
$first_name  = $data['first_name'] ?? '';
$middle_name = $data['middle_name'] ?? '';
$last_name   = $data['last_name'] ?? '';
$course      = $data['course'] ?? '';
$year        = $data['year'] ?? '';

// --- VALIDATE REQUIRED FIELDS ---
if (empty($voter_key) || empty($student_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// --- CHECK FOR RECENT DUPLICATE LOGIN ---
$checkStmt = $conn->prepare("SELECT id FROM login_logs WHERE voter_key = ? AND login_time >= NOW() - INTERVAL 5 MINUTE");
$checkStmt->bind_param("s", $voter_key);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Duplicate login attempt within 5 minutes']);
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// --- INSERT INTO DATABASE ---
try {
    $stmt = $conn->prepare("INSERT INTO login_logs 
        (voter_key, student_id, first_name, middle_name, last_name, course, year, login_time)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->bind_param("sssssss", 
        $voter_key, $student_id, $first_name, $middle_name, $last_name, $course, $year);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database insert failed: ' . $stmt->error]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>
