<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
ob_clean();

$host = "localhost";
$db = "cs_election";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$tableCheck = $conn->query("SHOW TABLES LIKE 'voting_count'");
if ($tableCheck->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Election data not available yet']);
    exit;
}

$sql = "SELECT candidate_name, partylist, position, photo_path, votes FROM voting_count ORDER BY position, votes DESC";
$result = $conn->query($sql);
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching results']);
    exit;
}

$candidates = [];
while ($row = $result->fetch_assoc()) {
    $candidates[] = $row;
}

$conn->close();

if (empty($candidates)) {
    http_response_code(404);
    echo json_encode(['message' => 'No candidates found']);
    exit;
}

ob_clean();
echo json_encode($candidates, JSON_UNESCAPED_SLASHES);
exit;
?>
