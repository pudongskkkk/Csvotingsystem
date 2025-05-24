<?php
header('Content-Type: application/json');

// Database configuration
$dbHost = 'localhost';
$dbUsername = 'root'; // Replace if needed
$dbPassword = '';     // Replace if needed
$dbName = 'cs_election';

// Create connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

try {
    $query = "SELECT student_id, last_name, first_name, middle_name, course, year 
              FROM student_records 
              ORDER BY last_name, first_name";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $students = [];

    while ($row = $result->fetch_assoc()) {
        $studentId = $row['student_id'];

        // Check if student has voted
        $checkVote = $conn->prepare("SELECT 1 FROM vote_logs WHERE student_id = ? LIMIT 1");
        $checkVote->bind_param("s", $studentId);
        $checkVote->execute();
        $checkVote->store_result();

        $row['status'] = $checkVote->num_rows > 0 ? 'Voted' : 'Not Voted';

        $checkVote->close();
        $students[] = $row;
    }

    echo json_encode(['success' => true, 'students' => $students]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>
