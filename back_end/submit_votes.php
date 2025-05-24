<?php
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$dbname = 'cs_election';
$username = 'root';
$password = '';

try {
    // Set up the PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get JSON data from frontend
$data = json_decode(file_get_contents('php://input'), true);
$votes = $data['votes'] ?? [];
$voterId = $data['voter_id'] ?? null;

// Validate data
if (!$votes || !$voterId) {
    echo json_encode(['success' => false, 'error' => 'Invalid data provided.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Update vote counts
    foreach ($votes as $vote) {
        $stmt = $pdo->prepare("UPDATE voting_count SET votes = votes + 1 WHERE id = ? AND position = ?");
        $stmt->execute([$vote['candidate_id'], $vote['position']]);
    }

    // Get voter details including student_id, course and year_level
    $stmt = $pdo->prepare("SELECT student_id, first_name, middle_name, last_name, voter_key, course, year_level FROM approved_voters WHERE id = ?");
    $stmt->execute([$voterId]);
    $voter = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($voter) {
        $studentId = $voter['student_id'];
        $middleInitial = $voter['middle_name'] ? strtoupper(substr($voter['middle_name'], 0, 1)) . '.' : '';
        $fullName = "{$voter['last_name']}, {$voter['first_name']} {$middleInitial}";
        $voterKey = $voter['voter_key'];
        $course = $voter['course'];
        $yearLevel = $voter['year_level'];

        // Insert log into vote_logs table with student_id
        $logStmt = $pdo->prepare("INSERT INTO vote_logs (voter_key, student_id, full_name, course, year_level) VALUES (?, ?, ?, ?, ?)");
        $logStmt->execute([$voterKey, $studentId, $fullName, $course, $yearLevel]);
    } else {
        throw new Exception("Voter not found with ID: $voterId");
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
