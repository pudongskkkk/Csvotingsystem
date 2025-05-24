<?php
header('Content-Type: application/json');

$dbHost = 'localhost';
$dbUsername = 'root'; // Replace with your database username
$dbPassword = ''; // Replace with your database password
$dbName = 'cs_election';
// Create connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

try {
    // Validate data
    $required = ['student_id', 'last_name', 'first_name', 'course', 'year'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Check if student ID already exists
    $check = $conn->prepare("SELECT student_id FROM student_records WHERE student_id = ?");
    $check->bind_param("s", $data['student_id']);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        throw new Exception("Student ID already exists");
    }

    // Insert new student
    $stmt = $conn->prepare("INSERT INTO student_records 
                          (student_id, last_name, first_name, middle_name, course, year) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", 
        $data['student_id'],
        $data['last_name'],
        $data['first_name'],
        $data['middle_name'],
        $data['course'],
        $data['year']
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Student added successfully']);
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>