<?php
header('Content-Type: application/json');

// Database connection details
$host = 'localhost';
$dbname = 'cs_election';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the voter key from POST data
    $voterKey = $_POST['voterKey'] ?? '';

    if (empty($voterKey)) {
        echo json_encode(['success' => false, 'message' => 'Voter key is required']);
        exit;
    }

    // First, check if the voter has already voted
    $voteCheck = $conn->prepare("SELECT id FROM vote_logs WHERE voter_key = :voterKey");
    $voteCheck->bindParam(':voterKey', $voterKey);
    $voteCheck->execute();

    if ($voteCheck->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'You have already submitted your vote.']);
        exit;
    }

    // Check if voter key exists in approved_voters table
    $stmt = $conn->prepare("SELECT id, student_id, first_name, middle_name, last_name, course, year_level FROM approved_voters WHERE voter_key = :voterKey");
    $stmt->bindParam(':voterKey', $voterKey);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $voter = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'voter_info' => [
                'id' => $voter['id'],
                'student_id' => $voter['student_id'],
                'first_name' => $voter['first_name'],
                'middle_name' => $voter['middle_name'],
                'last_name' => $voter['last_name'],
                'course' => $voter['course'],
                'year' => $voter['year_level'],
                'voter_key' => $voterKey
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid voter key']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
