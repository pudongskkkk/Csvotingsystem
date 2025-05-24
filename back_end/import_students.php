<?php
header('Content-Type: application/json');

// Database configuration
$dbHost = 'localhost';
$dbUsername = 'root'; // Replace with your database username
$dbPassword = ''; // Replace with your database password
$dbName = 'cs_election';

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get the POST data
$json = file_get_contents('php://input');
$students = json_decode($json, true);

if (!is_array($students)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data format']);
    exit;
}

try {
    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO student_records 
                          (student_id, last_name, first_name, middle_name, course, year) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $insertedCount = 0;
    
    // Process each student
    foreach ($students as $student) {
        // Validate required fields
        if (empty($student['student_id']) || empty($student['last_name']) || empty($student['first_name'])) {
            continue; // Skip incomplete records
        }

        // Bind parameters and execute
        $stmt->bind_param(
            "ssssss", 
            $student['student_id'],
            $student['last_name'],
            $student['first_name'],
            $student['middle_name'],
            $student['course'],
            $student['year']
        );
        
        if ($stmt->execute()) {
            $insertedCount++;
        }
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'insertedCount' => $insertedCount,
        'message' => "Imported $insertedCount records successfully"
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>