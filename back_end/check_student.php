<?php
header('Content-Type: application/json');

// Database configuration
$host = "localhost";       // Change if different
$username = "root";        // Change if different
$password = "";            // Change if different
$database = "cs_election"; // Your actual DB name

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Get the posted data
    $data = json_decode(file_get_contents('php://input'), true);
    $studentId = isset($data['studentId']) ? trim($data['studentId']) : '';

    if (empty($studentId)) {
        echo json_encode(['success' => false, 'error' => 'Student ID is required']);
        exit;
    }

    // Query the database
    $stmt = $pdo->prepare("SELECT * FROM student_records WHERE student_id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();

    if ($student) {
        // Return student data if found
        echo json_encode([
            'success' => true,
            'student' => [
                'course' => $student['course'],
                'year' => $student['year'],
                'last_name' => $student['last_name'],
                'first_name' => $student['first_name'],
                'middle_name' => $student['middle_name']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Student ID not found']);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
